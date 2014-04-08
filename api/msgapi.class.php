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

    public static function apiError($errorName, $errorMessage) {
        $action = '';

        if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];

        $apiOutput = array(
            'error' => array(
                'method' => $_SERVER['REQUEST_METHOD'],
                'action' => $action,
                'name' => $errorName,
                'message' => $errorMessage
                )
            );

        self::apiResult($apiOutput);
    }

    public static function pdoError($db) {
        $error = $db->errorInfo();
        self::apiError(
            'PDO Error ('.$error[0].') '.$error[1],
            $error[2]
        );
    }

    // Ensures that the request was sent with the provided parameter,
    // and that is of the appropriate type
    public static function requiredParameter($request, $parameter, $type, $options = null) {
        if ($request === null) {
            self::apiError(
                'NoParam',
                'No params supplied for action with required parameters.'
            );
        }

        if (!isset($request[$parameter]))
            self::apiError('MissingParameter', 'Missing parameter: ' . $parameter);

        if ($options !== null && is_array($options) && !in_array($request[$parameter], $options))
            self::apiError(
                'InvalidParameter',
                'InvalidParameter: '.$parameter.' must be one of: '.implode(', ', $options)
            );

        return self::checkParameterType($request, $type, $parameter);
    }


    // Determines if an optional parameter was sent, and defaults to the default if not
    public static function optionalParameter($request, $parameter, $type, $default, $options = null) {
        if (isset($request[$parameter]) &&
            (!empty($request[$parameter]) ||
            $request[$parameter] === false)) {
            if ($options !== null && !in_array($request[$parameter], $options))
                self::apiError(
                    'InvalidParameter',
                    'InvalidParameter: if given, '.$parameter.' must be one of: '.implode(', ', $options)
                );

            return self::checkParameterType($request, $type, $parameter, true);
        } else {
            return $default;
        }
    }

    // Returns a successful API response and it's data, then exits
    public static function apiResult($result = null) {
        if ($result === null) $result = new stdClass();

        if (isset($result->error)) {
            $apiOutput = $result;
        } else {
            $apiOutput = array('result' => $result);
        }

        header('Content-Type: application/json');
        echo json_encode($apiOutput);
        exit();
    }


    // Validates the type of required and optional parameters
    public static function checkParameterType($request, $type, $parameter, $optional = false) {
        $optionalOutput = ($optional ? 'if given, ' : '');

        if (is_string($request[$parameter]) &&
            is_numeric($request[$parameter])) {
            if(strpos($request[$parameter], '.') !== false) {
                $request[$parameter] = (float) $request[$parameter];
            } else {
                $request[$parameter] = (int) $request[$parameter];
            }
        }

        if ($type == 'float') {
            if (!is_float($request[$parameter]) &&
                !is_int($request[$parameter])) {
                self::apiError(
                    'InvalidParameter',
                    'InvalidParameter: '.$optionalOutput.$parameter.' must be float.'
                );
            } else {
                return (float) $request[$parameter];
            }
        } elseif ($type == 'int') {
            if (!is_int($request[$parameter])) {
                self::apiError(
                    'InvalidParameter',
                    'InvalidParameter: '.$optionalOutput.$parameter.' must be integer.'
                );
            } else {
                return (int) $request[$parameter];
            }
        } elseif ($type == 'bool') {
            if ($request[$parameter] === true) {
                return true;
            } elseif ($request[$parameter] === false) {
                return false;
            } else {
                self::apiError(
                    'InvalidParameter',
                    'InvalidParameter: '.$optionalOutput.$parameter.' must be boolean.'
                );
            }
        } elseif ($type == 'string') {
            if (!is_string($request[$parameter])) {
                self::apiError(
                    'InvalidParameter',
                    'InvalidParameter: '.$optionalOutput.$parameter.' must be a string.'
                );
            } else {
                return (string) $request[$parameter];
            }
        } else {
            return $request[$parameter];
        }
    }
}
