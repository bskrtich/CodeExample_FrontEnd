<?php
require_once 'database.php';

class msgapi
{
    public static function httpError($http_response_code, $message) {
        $response = $_SERVER['SERVER_PROTOCOL'].' '.
            $http_response_code.
            ' Internal Server Error '.
            '('.$message.')';

        header($response, true, $http_response_code);
        echo $response;
        exit();
    }

    public static function apiError($method, $action, $errorName, $errorMessage) {
        $apiOutput = array(
            'error' => array(
                'method' => $method,
                'action' => $action,
                'name' => $errorName,
                'message' => $errorMessage
                )
            );

        header('Content-Type: application/json');
        echo json_encode($apiOutput);
        exit();
    }


    // Returns a successful API response and it's data, then exits
    public static function apiResult($result = true) {
        if ($result === true) $result = new stdClass();

        $apiOutput = array('result' => $result);

        header('Content-Type: application/json');
        echo json_encode($apiOutput);
        exit();
    }


    // Validates the type of required and optional parameters
    public static function checkParameterType($request, $type, $parameter, $optional = false) {
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
    public static function requiredParameter($request, $parameter, $type, $options = null) {
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
    public static function optionalParameter($request, $parameter, $type, $default, $options = null) {
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
}
