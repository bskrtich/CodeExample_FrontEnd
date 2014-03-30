<?php
require_once 'database.php';

class User
{
    private $db;
    private $user_id;
    private $user_name;
    private $salt;
    private $password;

    function __construct(&$db) {
        $this->db = $db;
    }

    function getUserByID($userID) {
        $sql = 'SELECT
                    userID,
                    username
                FROM
                    users
                WHERE
                    userID = :userID';

        $prepared = $this->db->prepare($sql);

        $prepared->bindValue(':userID', $userID);

        sqlQuery($prepared);

        $user = $prepared->fetchObject('User');

        if ($user) {
            return $user;
        } else {
            return false;
        }
    }
}
