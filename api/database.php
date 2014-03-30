<?php

// MySQL login credentials
DEFINE('DB_HOST', '192.168.0.50');
DEFINE('DB_NAME', 'solidfire');
DEFINE('DB_PORT', 3306);
DEFINE('DB_USER', 'solidfire');
DEFINE('DB_PASS', 'EBuGvUUACebze62C');

try {
	$dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8';
	$db = new PDO(
		$dsn,
		DB_USER,
		DB_PASS,
		array(
			PDO::ATTR_PERSISTENT => true,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
		)
	);
} catch (PDOException $e) {
	apiError('SQLConnectError', 'Error connecting to database: ' . $e->getMessage());
}

// Does some basic validation
function sqlQuery($prepared, $minAffected = false) {
	global $db;

	if (!$db) {
		apiError('NoDatabaseConnection', 'Missing valid database connection.');
	}

	if (!$prepared) {
		$sqlError = $db->errorInfo();

		if ($sqlError) {
			apiError(
				'PreparedStatementError',
				'Prepared statement error: ' . $sqlError[1] . ' : ' . $sqlError[2]
			);
		} else {
			apiError('PreparedStatementError', 'Invalid prepared statement.');
		}
	}

	$success = $prepared->execute();
	if (!$success) {
		$traceback = debug_backtrace();
		$sqlError = $prepared->errorInfo();
		apiError(
			'DatabaseQueryFailed',
			'Query failed: ' . $prepared->queryString .
			"\n" . $sqlError[1] . ' : ' . $sqlError[2] .
			"\nFile: " . $traceback[0]['file'] .
			"\nLine: " . $traceback[0]['line']
		);
	}

	$affected = $prepared->rowCount();
	if ($minAffected && $affected < $minAffected) {
		$traceback = debug_backtrace();
		apiError('DatabaseQueryFailed',
			'Query failed to alter rows. ' .
			"\n" . $prepared->queryString .
			"\nFile: " . $traceback[0]['file'] .
			"\nLine: " . $traceback[0]['line']
		);
	}

	return $prepared;
}
