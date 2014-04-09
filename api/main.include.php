<?php

// Set Include Path
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

// MySQL login credentials
DEFINE('DB_HOST', '192.168.0.50');
DEFINE('DB_NAME', 'microblog');
DEFINE('DB_PORT', 3306);
DEFINE('DB_USER', 'microblog');
DEFINE('DB_PASS', 'microblog');

require_once 'msgapi.class.php';
require_once 'database.php';
require_once 'msgservice.class.php';
