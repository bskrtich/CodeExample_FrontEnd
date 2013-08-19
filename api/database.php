<?php
	
	// MySQL login credentials
	DEFINE('DB_HOST', '127.0.0.1');
	DEFINE('DB_NAME', 'solidfire');
	DEFINE('DB_PORT', 3306);
	DEFINE('DB_USER', 'root');
	DEFINE('DB_PASSWORD', 'Biemedi@2190');

	try{
		$sqlCon = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD, array(PDO::ATTR_PERSISTENT => true));
	}
	catch (PDOException $e) {
		apiError('SQLConnectError', 'Error connecting to database: ' . $e->getMessage());
	}
	
	// Does some basic validation 
	function sqlQuery($prepared, $minAffected = false) {
		global $sqlCon;
		global $method;

		if (!$sqlCon) {
			logSystemError($method, 'NoDatabaseConnection', 'Missing valid database connection.');
			apiError('NoDatabaseConnection', 'Missing valid database connection.');
		}
		
		if (!$prepared) {
			$sqlError = $sqlCon->errorInfo();
			
			if ($sqlError)
			apiError('PreparedStatementError', 'Prepared statement error: ' . $sqlError[1] . ' : ' . $sqlError[2]);
			else
			apiError('PreparedStatementError', 'Invalid prepared statement.');
		}
		
		$success = $prepared->execute();
		if (!$success) {
			$traceback = debug_backtrace();
			$sqlError = $prepared->errorInfo();
			apiError('DatabaseQueryFailed', 'Query failed: ' . $prepared->queryString . "\n" . $sqlError[1] . ' : ' . $sqlError[2] . "\nFile: " . $traceback[0]['file'] . "\nLine: " . $traceback[0]['line']);
		}

		$affected = $prepared->rowCount();
		if ($minAffected && $affected < $minAffected) {
			$traceback = debug_backtrace();
			apiError('DatabaseQueryFailed', 'Query failed to alter rows. ' . "\n" . $prepared->queryString . "\nFile: " . $traceback[0]['file'] . "\nLine: " . $traceback[0]['line']);
		}
		
		return $prepared;
	}

	
?>