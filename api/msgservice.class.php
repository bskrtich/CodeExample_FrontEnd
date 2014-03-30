<?php
require_once 'database.php';

class msgservice
{
    private $db;

    function __construct(&$db) {
        $this->db = $db;
    }

    function action($method, $action, $data) {
        switch ($method) {
            case 'latestmsgs':
                $sql = 'SELECT
                            *
                        FROM
                            messages msg
                        JOIN
                            subscriptions sub
                        ON
                            msg.user_id = sub.following_user_id
                        WHERE
                            sub.user_id = 1';

                $result = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

                return $result;
                break;

            case 'newmsg' :
                $result = new stdClass();
                $result->newmsg = 'My New Message';

                apiResult($result);
                break;

            case 'GetUserInfo' :
                $user = getUserByID($userInfo->userID);

                if (!$user) apiError('NoUser', 'User does not exist with userID: ' . $userInfo->userID);

                $result = new stdClass();
                $result->user = $user;

                apiResult($result);
                break;

            case 'AddUser' :
                $username = requiredParameter('username', 'string');
                $password = optionalParameter('password', 'string', substr(sha1(microtime()), 0, 8));

                $salt = hash('sha512', str_shuffle($username . microtime() . rand(0, 9999999) . $password));
                $hashed = hash('sha512', $salt . $password);

                $sql = 'INSERT IGNORE INTO
                            users (
                                username,
                                password,
                                salt
                            )
                            VALUES (
                                :username,
                                :password,
                                :salt
                            )';

                $prepared = $sqlCon->prepare($sql);
                $prepared->bindValue(':username', $username);
                $prepared->bindValue(':password', $hashed);
                $prepared->bindValue(':salt', $salt);
                sqlQuery($prepared);

                apiResult();
                break;

            case 'users' :
                $sql = "SELECT id, username FROM users ORDER BY username";
                $result = $sqlCon->query($sql)->fetchAll(PDO::FETCH_ASSOC);

                apiResult($result);
                break;
            default:
                echo $request;
        }
    }


    function requiresBasicAuth() {
        header('WWW-Authenticate: Basic realm="Assignment"');
        header('HTTP/1.1 401 Unauthorized');

        echo 'Invalid user credentials.';
        exit();
    }


    // Ensures that valid basic http auth credentials have been sent
    function validateUser() {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            return requiresBasicAuth();
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

            $userInfo = $prepared->fetch(PDO::FETCH_OBJ);

            if (!$userInfo) {
                return requiresBasicAuth();
            }
            $userInfo->user_id = (int) $userInfo->user_id;

            $hashed = hash('sha512', $userInfo->salt . $_SERVER['PHP_AUTH_PW']);

            if ($hashed != $userInfo->password) {
                return requiresBasicAuth();
            }
        }

        return $userInfo;
    }
}
