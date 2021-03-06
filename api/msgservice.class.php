<?php
namespace bskrtich\microblog;

class MsgService
{
    private $db;
    private $user;

    public function __construct(&$db)
    {
        $this->db = $db;
    }

    public function ajaxAction($method, $action, $data = null)
    {
        // Check for these actions that require a post
        $actions_require_post = array(
            'msgadd',
            'useradd',
            'userchangepass',
            'followadd',
            'followremove'
        );

        if ($method !== 'POST' && in_array($action, $actions_require_post)) {
            MsgApi::apiError(
                'BadMethodForRequest',
                'This request must be sent as a POST'
            );
        }

        switch ($action) {
            case 'msgadd':
                $msg = MsgApi::requiredParameter($data, 'msg', 'string');
                $attrmsgid = MsgApi::optionalParameter($data, 'attrmsgid', 'int', null);

                $status = self::postMessage($msg, $attrmsgid);

                if ($status === true) {
                    $result = new \stdClass();
                    $result->msg = $msg;
                    $result->attrmsgid = $attrmsgid;
                    return $result;
                } else {
                    return false;
                }

                break;

            case 'usergetinfo':
                $user_id = MsgApi::optionalParameter(
                    $data,
                    'userid',
                    'int',
                    $this->user->user_id
                );

                $user = self::getUserByID($user_id);

                if (!$user) {
                    apiError(
                        'NoUser',
                        'User does not exist with userID: '.$this->user->user_id
                    );
                }

                $result = new \stdClass();
                $result->user = $user;

                return $result;
                break;

            case 'useradd':
                $username = MsgApi::requiredParameter(
                    $data,
                    'username',
                    'string'
                );
                $password = MsgApi::optionalParameter(
                    $data,
                    'password',
                    'string',
                    substr(sha1(microtime()), 0, 8)
                );

                if (strlen($username) <= 3 || strlen($password) <= 3) {
                    MsgApi::apiError(
                        'NewPasswordLength',
                        'Your new username and password must be longer then 3 characters'
                    );
                }


                $salt = self::genSalt($username, $password);
                $hashed = self::genHash($salt, $password);

                $sql = 'INSERT IGNORE INTO
                            users (
                                user_name,
                                salt,
                                password
                            )
                            VALUES (
                                :user_name,
                                :salt,
                                :password
                            )';

                $request = $this->db->prepare($sql);
                $request->bindValue(':user_name', $username);
                $request->bindValue(':salt', $salt);
                $request->bindValue(':password', $hashed);

                if ($request->execute()) {
                    return true;
                } else {
                    MsgApi::pdoError($this->db);
                }

                break;

            case 'userchangepass':
                $password = MsgApi::requiredParameter(
                    $data,
                    'password',
                    'string',
                    substr(sha1(microtime()), 0, 8)
                );

                if (strlen($password) <= 3) {
                    MsgApi::apiError(
                        'NewPasswordLength',
                        'Your new password must be longer then 3 characters'
                    );
                }

                $salt = self::genSalt($this->user->user_name, $password);
                $hashed = self::genHash($salt, $password);

                $sql = "UPDATE
                            users
                        SET
                            salt = :salt,
                            password = :password
                        WHERE
                            user_id = :user_id";

                $request = $this->db->prepare($sql);
                $request->bindValue(':salt', $salt);
                $request->bindValue(':password', $hashed);
                $request->bindValue(':user_id', $this->user->user_id);

                if ($request->execute()) {
                    return true;
                } else {
                    MsgApi::pdoError($this->db);
                }

                break;

            case 'userlist':
                $sql = 'SELECT
                            users.user_id,
                            users.user_name,
                            follows.follow_user_id,
                            IF(follows.follow_id IS NULL, 0, 1) AS is_following
                        FROM
                            users
                        LEFT JOIN
                            (SELECT * FROM follows WHERE user_id = :user_id) follows
                        ON
                            (users.user_id = follows.follow_user_id)
                        ORDER BY
                            users.user_name';

                $request = $this->db->prepare($sql);
                $request->bindValue(':user_id', $this->user->user_id);

                if ($request->execute()) {
                    $result = $request->fetchAll(\PDO::FETCH_ASSOC);
                    return $result;
                } else {
                    MsgApi::pdoError($this->db);
                }

                break;

            case 'followadd':
                $followuserid = MsgApi::requiredParameter($data, 'followuserid', 'int');

                $sql = "INSERT IGNORE INTO
                            follows (
                                user_id,
                                follow_user_id
                            )
                            VALUES (
                                :user_id,
                                :follow_user_id
                            )";

                $request = $this->db->prepare($sql);
                $request->bindValue(':user_id', $this->user->user_id, \PDO::PARAM_INT);
                $request->bindValue(':follow_user_id', $followuserid, \PDO::PARAM_INT);

                if ($request->execute()) {
                    return true;
                } else {
                    MsgApi::pdoError($this->db);
                }

                break;

            case 'followremove':
                $followuserid = MsgApi::requiredParameter($data, 'followuserid', 'int');

                $sql = "DELETE FROM
                            follows
                        WHERE
                            user_id = :user_id
                        AND
                            follow_user_id = :follow_user_id";

                $request = $this->db->prepare($sql);
                $request->bindValue(':user_id', $this->user->user_id, \PDO::PARAM_INT);
                $request->bindValue(':follow_user_id', $followuserid, \PDO::PARAM_INT);

                if ($request->execute()) {
                    return true;
                } else {
                    MsgApi::pdoError($this->db);
                }

                break;

            case 'followmsglist':
                $sql = 'SELECT
                            msgs.msg_id,
                            msgs.user_id,
                            msgs.user_name,
                            msgs.attribution_user_id,
                            users.user_name AS attribution_user_name,
                            msgs.msg,
                            msgs.created,
                            msgs.modified
                        FROM (
                            SELECT
                                msgs.msg_id,
                                msgs.user_id,
                                users.user_name,
                                msgs.attribution_user_id,
                                msgs.msg,
                                msgs.created,
                                msgs.modified
                            FROM
                                msgs
                            JOIN
                                follows fow
                            ON
                                msgs.user_id = fow.follow_user_id
                            JOIN
                                users
                            ON
                                msgs.user_id = users.user_id
                            WHERE
                                fow.user_id = :user_id
                            ORDER BY
                                msgs.created DESC
                            LIMIT 10
                        )
                            msgs
                        LEFT JOIN
                            users
                        ON msgs.attribution_user_id = users.user_id';

                $request = $this->db->prepare($sql);
                $request->bindValue(
                    ':user_id',
                    $this->user->user_id,
                    \PDO::PARAM_INT
                );

                if ($request->execute()) {
                    $result = $request->fetchAll(\PDO::FETCH_CLASS);
                    return $result;
                } else {
                    MsgApi::pdoError($this->db);
                }

                break;

            default:
                return null;
        }
        return null;
    }

    private function genSalt($username, $password)
    {
        $salt = hash(
            'sha512',
            str_shuffle($username . microtime() . rand(0, 9999999) . $password)
        );
        return $salt;
    }

    private function genHash($salt, $password)
    {
        $hashed = hash('sha512', $salt . $password);

        return $hashed;
    }

    public function requiresBasicAuth()
    {
        header('WWW-Authenticate: Basic realm="Assignment"');
        header('HTTP/1.1 401 Unauthorized');

        echo 'Invalid user credentials.';
        exit();
    }

    // Ensures that valid basic http auth credentials have been sent
    public function validateUser()
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            return self::requiresBasicAuth();
        } else {
            $sql = 'SELECT
                        user_id,
                        user_name,
                        password,
                        salt
                    FROM
                        users
                    WHERE
                        user_name = :user_name';

            $request = $this->db->prepare($sql);
            $request->bindValue(':user_name', $_SERVER['PHP_AUTH_USER']);

            if ($request->execute()) {
                $this->user = $request->fetch(\PDO::FETCH_OBJ);

                if (!$this->user) {
                    return self::requiresBasicAuth();
                }
                $this->user->user_id = (int) $this->user->user_id;

                $hashed = hash('sha512', $this->user->salt . $_SERVER['PHP_AUTH_PW']);

                if ($hashed != $this->user->password) {
                    return self::requiresBasicAuth();
                }
            } else {
                MsgApi::pdoError($this->db);
            }
        }

        return $this->user;
    }

    public function postMessage($msg, $attribution_user_id = null)
    {
        $sql = 'INSERT INTO
                    msgs (
                        user_id,
                        msg,
                        attribution_user_id
                    )
                    VALUES (
                        :user_id,
                        :msg,
                        :attr_user_id
                    )';

        $request = $this->db->prepare($sql);
        $request->bindValue(':user_id', $this->user->user_id, \PDO::PARAM_INT);
        $request->bindValue(':msg', $msg, \PDO::PARAM_STR);
        $request->bindValue(':attr_user_id', $attribution_user_id, \PDO::PARAM_INT);

        if ($request->execute()) {
            return true;
        } else {
            MsgApi::pdoError($this->db);
        }

    }

    public function getUserByID($user_id)
    {
        $sql = 'SELECT
                    user_id,
                    user_name
                FROM
                    users
                WHERE
                    user_id = :user_id';

        $request = $this->db->prepare($sql);
        $request->bindValue(':user_id', $user_id, \PDO::PARAM_INT);

        $user = null;
        if ($request->execute()) {
            $user = $request->fetchAll(\PDO::FETCH_CLASS);
        }

        if ($user) {
            return $user;
        } else {
            return MsgApi::pdoError($this->db);
        }
    }
}
