<?php

	include('api.php');
	$userInfo = validateUser();

	$raw = file_get_contents('php://input');
	$request = json_decode($raw, true);

	//print_r($request);

	// Support both POST and GET data
	if (isset($request['method'])) {
		$method = $request['method'];
	} else if ($_GET['method']) {
		$method = $_GET['method'];
	} else {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error (MissingMethod: No method specified.)', true, 500);
	}

	//echo "\nMethod: ".$method;

	include('methods.php');
