<?php
require_once 'database.php';

class msgservice
{
    private $db;
    private $user;

    public function __construct(&$db) {
        $this->db = $db;
    }

    public function ajaxAction($method, $action, $data = NULL) {
        // Check for these actions that require a post
        $actions_require_post = array(
            'msgadd',
            'useradd',
            'userchangepass',
            'followadd',
            'followremove'
        );

        if ($method !== 'POST' && in_array($action, $actions_require_post)) {
            msgapi::apiError(
                'BadMethodForRequest',
                'This request must be sent as a POST'
            );
        }

        switch ($action) {
            case 'msgadd':
                $msg = msgapi::requiredParameter($data, 'msg', 'string');
                $attrmsg = msgapi::optionalParameter($data, 'attrmsg', 'int', null);

                $status = self::postMessage($msg, $attrmsg);

                if ($status === true) {
                    $result = new stdClass();
                    $result->msg = $msg;
                    $result->attrmsg = $attrmsg;
                    return $result;
                } else {
                    return false;
                }

                break;

            case 'usergetinfo':
                $user_id = msgapi::optionalParameter(
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

                $result = new stdClass();
                $result->user = $user;

                return $result;
                break;

            case 'useradd':
                $username = msgapi::requiredParameter(
                    $data,
                    'username',
                    'string'
                );
                $password = msgapi::optionalParameter(
                    $data,
                    'password',
                    'string',
                    substr(sha1(microtime()), 0, 8)
                );

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
                    msgapi::pdoError($this->db);
                }

                break;

            case 'userchangepass':
                $password = msgapi::requiredParameter(
                    $data,
                    'password',
                    'string',
                    substr(sha1(microtime()), 0, 8)
                );

                $salt = self::genSalt($username, $password);
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
                    msgapi::pdoError($this->db);
                }

                break;

            case 'userlist':
                $sql = 'SELECT
                            users.user_id,
                            users.user_name,
                            IF(follows.follow_id IS NULL, 0, 1) AS is_following
                        FROM
                            users
                        LEFT JOIN
                            follows
                        ON
                            (users.user_id = follows.user_id)
                        ORDER BY
                            users.user_name';

                if ($request = $this->db->query($sql)) {
                    $result = $request->fetchAll(PDO::FETCH_ASSOC);
                    return $result;
                } else {
                    msgapi::pdoError($this->db);
                }

                break;

            case 'followadd':
                $userid = msgapi::requiredParameter($data, 'userid', 'int');
                $followuserid = msgapi::requiredParameter($data, 'followuserid', 'int');

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
                $request->bindValue(':user_id', $userid, PDO::PARAM_INT);
                $request->bindValue(':follow_user_id', $followuserid, PDO::PARAM_INT);

                if ($request->execute()) {
                    return true;
                } else {
                    msgapi::pdoError($this->db);
                }

                break;

            case 'followremove':
                $userid = msgapi::requiredParameter($data, 'userid', 'int');
                $followuserid = msgapi::requiredParameter($data, 'followuserid', 'int');

                $sql = "DELETE FROM
                            follows
                        WHERE
                            user_id = :user_id
                        AND
                            follow_user_id = :follow_user_id";

                $request = $this->db->prepare($sql);
                $request->bindValue(':user_id', $userid, PDO::PARAM_INT);
                $request->bindValue(':follow_user_id', $followuserid, PDO::PARAM_INT);

                if ($request->execute()) {
                    return true;
                } else {
                    msgapi::pdoError($this->db);
                }

                break;

            case 'followmsglist':
                $sql = 'SELECT
                            msgs.msg_id,
                            msgs.user_id,
                            users.user_name,
                            msgs.attribution_msg_id,
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
                        LIMIT 10';

                $request = $this->db->prepare($sql);
                $request->bindValue(
                    ':user_id',
                    $this->user->user_id,
                    PDO::PARAM_INT
                );

                if ($request->execute()) {
                    $result = $request->fetchAll(PDO::FETCH_CLASS);
                    return $result;
                } else {
                    msgapi::pdoError($this->db);
                }

                break;

            default:
                return null;
        }
        return null;
    }

    private function genSalt($username, $password) {
        $salt = hash(
            'sha512',
            str_shuffle($username . microtime() . rand(0, 9999999) . $password)
        );
        return $salt;
    }

    private function genHash($salt, $password) {
        $hashed = hash('sha512', $salt . $password);

        return $hashed;
    }

    public function requiresBasicAuth() {
        header('WWW-Authenticate: Basic realm="Assignment"');
        header('HTTP/1.1 401 Unauthorized');

        echo 'Invalid user credentials.';
        exit();
    }

    // Ensures that valid basic http auth credentials have been sent
    public function validateUser() {
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

            $prepared = $this->db->prepare($sql);
            $prepared->bindValue(':user_name', $_SERVER['PHP_AUTH_USER']);

            sqlQuery($prepared);

            $this->user = $prepared->fetch(PDO::FETCH_OBJ);

            if (!$this->user) {
                return self::requiresBasicAuth();
            }
            $this->user->user_id = (int) $this->user->user_id;

            $hashed = hash('sha512', $this->user->salt . $_SERVER['PHP_AUTH_PW']);

            if ($hashed != $this->user->password) {
                return self::requiresBasicAuth();
            }
        }

        return $this->user;
    }

    public function postMessage($msg, $attribution_msg_id = null) {
        $sql = 'INSERT INTO
                    msgs (
                        user_id,
                        msg,
                        attribution_msg_id
                    )
                    VALUES (
                        :user_id,
                        :msg,
                        :attr_msg_id
                    )';

        $request = $this->db->prepare($sql);
        $request->bindValue(':user_id', $this->user->user_id, PDO::PARAM_INT);
        $request->bindValue(':msg', $msg, PDO::PARAM_STR);
        $request->bindValue(':attr_msg_id', $attribution_msg_id, PDO::PARAM_INT);

        if ($request->execute()) {
            return true;
        } else {
            msgapi::pdoError($this->db);
        }

    }

    public function getUserByID($user_id) {
        $sql = 'SELECT
                    user_id,
                    user_name
                FROM
                    users
                WHERE
                    user_id = :user_id';

        $request = $this->db->prepare($sql);
        $request->bindValue(':user_id', $user_id, PDO::PARAM_INT);

        $user = null;
        if ($request->execute()) {
            $user = $request->fetchAll(PDO::FETCH_CLASS);
        }

        if ($user) {
            return $user;
        } else {
            return msgapi::pdoError($this->db);
        }
    }
}
