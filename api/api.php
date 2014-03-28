<?php

include('database.php');

$userInfo = false; // Global representing the logged in user

// Returns a consistent error format and exits
function apiError($errorName, $errorMessage)
{
    global $method;

    $apiOutput = array(
        'error' => array(
            'method' => $method,
            'name' => $errorName,
            'message' => $errorMessage
            )
        );

    header('Content-Type: application/json');
    echo json_encode($apiOutput);
    exit();
}


// Returns a successful API response and it's data, then exits
function apiResult($result = null)
{
    if ($result === null) $result = new stdClass();

    $apiOutput = array('result' => $result);

    header('Content-Type: application/json');
    echo json_encode($apiOutput);
    exit();
}


// Validates the type of required and optional parameters
function checkParameterType($type, $parameter, $optional = false)
{
    global $request;

    $optionalOutput = ($optional ? 'if given, ' : '');

    if ($type == 'float') {
        if (!is_float($request['params'][$parameter]) &&
            !is_int($request['params'][$parameter])) {
            apiError(
                'InvalidParameter',
                'InvalidParameter: '.$optionalOutput.$parameter.' must be float.'
            );
        } else {
            return (float) $request['params'][$parameter];
        }
    } elseif ($type == 'int') {
        if (!is_int($request['params'][$parameter])) {
            apiError(
                'InvalidParameter',
                'InvalidParameter: '.$optionalOutput.$parameter.' must be integer.'
            );
        } else {
            return (int) $request['params'][$parameter];
        }
    } elseif ($type == 'bool') {
        if ($request['params'][$parameter] === true) {
            return true;
        } elseif ($request['params'][$parameter] === false) {
            return false;
        } else {
            apiError(
                'InvalidParameter',
                'InvalidParameter: '.$optionalOutput.$parameter.' must be boolean.'
            );
        }
    } elseif ($type == 'string') {
        if (!is_string($request['params'][$parameter])) {
            apiError(
                'InvalidParameter',
                'InvalidParameter: '.$optionalOutput.$parameter.' must be a string.'
            );
        } else {
            return (string) $request['params'][$parameter];
        }
    } else {
        return $request['params'][$parameter];
    }
}


// Ensures that the request was sent with the provided parameter,
// and that is of the appropriate type
function requiredParameter($parameter, $type, $options = null)
{
    global $request;

    if (!isset($request['params']))
        apiError(
            'NoParam',
            'No params supplied for method with required parameters.'
        );

    if (!isset($request['params'][$parameter]))
        apiError('MissingParameter', 'Missing parameter: ' . $parameter);

    if ($options !== null && !in_array($request['params'][$parameter], $options))
        apiError(
            'InvalidParameter',
            'InvalidParameter: '.$parameter.' must be one of: '.implode(', ', $options)
        );

    return checkParameterType($type, $parameter);
}


// Determines if an optional parameter was sent, and defaults to the default if not
function optionalParameter($parameter, $type, $default, $options = null)
{
    global $request;

    if (isset($request['params'][$parameter]) &&
        (!empty($request['params'][$parameter]) ||
        $request['params'][$parameter] === false)) {
        if ($options !== null && !in_array($request['params'][$parameter], $options))
            apiError(
                'InvalidParameter',
                'InvalidParameter: if given, '.$parameter.' must be one of: '.implode(', ', $options)
            );

        return checkParameterType($type, $parameter, true);
    } else {
        return $default;
    }
}


function requiresBasicAuth()
{
    header('WWW-Authenticate: Basic realm="Assignment"');
    header('HTTP/1.1 401 Unauthorized');

    echo 'Invalid user credentials.';
    exit();
}


// Ensures that valid basic http auth credentials have been sent
function validateUser()
{
    global $sqlCon;

    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        return requiresBasicAuth();
    } else {
        $sql = 'SELECT
                    id,
                    username,
                    password,
                    salt
                FROM
                    users
                WHERE
                    username = :username';

        $prepared = $sqlCon->prepare($sql);
        $prepared->bindValue(':username', $_SERVER['PHP_AUTH_USER']);

        sqlQuery($prepared);

        $userInfo = $prepared->fetch(PDO::FETCH_OBJ);

        if (!$userInfo) {
            return requiresBasicAuth();
        }
        $userInfo->id = (int) $userInfo->id;

        $hashed = hash('sha512', $userInfo->salt . $_SERVER['PHP_AUTH_PW']);

        if ($hashed != $userInfo->password) {
            return requiresBasicAuth();
        }
    }

    return $userInfo;
}
