<?php
require_once 'database.php';

class AjaxEndpoint
{

    const DEFAULT_ERROR_MSG = 'Unknwon error';
    const NUMBER = 'number';
    const ARRAY_NUMBER = 'array:number';
    const TRUTH = 'truth';
    const BOOLEAN_SANITIZE = 'boolean';
    const NUMBER_RANGE = 'number_range';
    const EMAIL = 'email';
    const TEXT = 'text';
    const ENUM = 'enum';

    protected static $_formErrors;
    protected static $_failReason;

   /**
    * Returns the form error list from the latest form validation
    */
    public static function FormErrors($set_to_this=null) {
        if( !is_null($set_to_this) ) {
            self::$_formErrors = $set_to_this;
        }
        return self::$_formErrors;
    }

    public static function FailReason($set_to_this=null) {
        if( !is_null($set_to_this) ) {
            self::$_failReason = $set_to_this;
        }
        return self::$_failReason;
    }

    public function getFetch($key, $default=null) {
        return array_key_exists($key, $_GET)
                ? $_GET[$key]
                : $default;
    }

    public function getFetchOrDie($key, $error_msg=null) {
        $value = $this->getFetch($key);
        if( is_null($value) ) {
            return $this->error($error_msg);
        }
        return $value;
    }

    public function postFetch($key, $default=null) {
        return array_key_exists($key, $_POST)
            ? $_POST[$key]
            : $default;
    }

    public function postFetchOrDie($key, $error_msg=null) {
        $value = $this->postFetch($key);
        if( is_null($value) ) {
            return $this->error($error_msg);
        }
        return $value;
    }

    public function respond($type, $data) {

        if( is_array($data) && !array_key_exists('status', $data) ) {
            $data['status'] = $type;
        }

        header('Content-type: text/json');
        return die( json_encode(array(
            'type' => $type,
            'data' => $data
        )) );
    }

    public function success($data) {
        return $this->respond('success', $data);
    }

    public function error($message) {
        if( empty($message) ) {
            $message = self::DEFAULT_ERROR_MSG;
        }
        return $this->respond('error', array(
            'message' => $message
        ));
    }

    public function successOrError($test, $on_success, $on_error) {
        return $test ? $this->success($on_success) : $this->error($on_error);
    }

    public function requireMethod($request_method) {
        $allowed_methods = (array)$request_method;
        foreach($allowed_methods as $index=>$rm) {
            // convert them all to upper case
            $allowed_methods[$index] = strtoupper($rm);
        }
        if( strtoupper($request_method) != $this->requestMethod() ) {
            return $this->error('Method not implemented');
        }

        return true;
    }

    public function requestMethod() {
        return array_key_exists('REQUEST_METHOD', $_SERVER)
                ? strtoupper($_SERVER['REQUEST_METHOD'])
                : 'GET'; // if for some crazy reason it is not defined, call it GET
    }

    public function validateForm($form_details) {
        $form = array();

        $passes = true;

        $errors = array();

        // at the moment, just assume form comes only from POST
        foreach($form_details as $key=>$constraint) {
            $value = $this->postFetch($key);

            $form[$key] = $value;

            $key_check = $this->validate($form[$key], $constraint);

            if(!$key_check) {
                $errors[$key] = AjaxEndpoint::FailReason();
            }

            $passes = $passes && $key_check;
        }

        if (!$passes) {
            AjaxEndpoint::FormErrors($errors);
        }

        return $passes ? $form : false;
    }

    public function validateFormOrDie($form_details) {
        $form = $this->validateForm($form_details);
        if ($form === false) {
            return $this->error( AjaxEndpoint::FormErrors() );
        }
        return $form;
    }

    public function validate(&$variable, $check) {
        $pass = false;
        $details = null;

        if( is_array($check) ) {
            if( sizeof($check) > 1 ) {
                $details = $check[1];
            }
            $check = $check[0];
        }

        switch($check) {
            // check if a value is numeric
            case self::NUMBER:
                $pass = is_numeric($variable);
                if(!$pass) {
                    AjaxEndpoint::FailReason('Must be a number');
                }
            break;

            case self::ARRAY_NUMBER:
                foreach((array)$variable as $v) {
                    $pass = true;
                    if(!is_numeric($v)) {
                        $pass = false;
                        AjaxEndpoint::FailReason('Non-numeric value in array');
                    }
                }
            break;

            // check if a value passes truth test
            case self::TRUTH:
                $variable = (bool) $variable;
                $pass = $variable;
                if(!$pass) {
                    AjaxEndpoint::FailReason('Value is not true');
                }
            break;

            // boolean sanitize a value
            case self::BOOLEAN_SANITIZE:
                $pass = true;
            break;

            case self::NUMBER_RANGE:
                $min = $details['min'];
                $max = $details['max'];
                $pass = is_numeric($variable);
                if($pass) {
                    $pass = ($variable >= $min) && ($variable <= $max);
                }

                if(!$pass) {
                    AjaxEndpoint::FailReason(
                        sprintf('Value is not within range: %d-%d', $min, $max)
                    );
                }
            break;

            case self::EMAIL:
                $pass = RP_Utility::validateEmail($variable);
                if(!$pass) {
                    AjaxEndpoint::FailReason('Invalid email address');
                }
            break;

            case self::TEXT:
                $min = $details['min'];
                $max = $details['max'];

                $variable = (string)$variable;
                $len = strlen($variable);
                $pass = ($len >= $min) && ($len <= $max);

                if(!$pass) {
                    AjaxEndpoint::FailReason(
                        sprintf('String must be within %d-%d characters', $min, $max)
                    );
                }
            break;

            case self::ENUM:
                $pass = in_array($variable, $details);
                if(!$pass) {
                    AjaxEndpoint::FailReason(
                        sprintf('Value must be of: %s', implode(', ', $details))
                    );
                }
            break;

            default:
                $pass = true;
            break;
        }

        return $pass;
    }

    public function validateOrDie($variable, $check, $error_message=null) {
        if( !$this->validate($variable, $check) ) {
            return $this->error($error_message);
        }

        return true;
    }

}
