<?php

try {
    $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8';
    $db = new \PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
        )
    );
} catch (PDOException $e) {
    exit('Error connecting to database: ' . $e->getMessage());
}
