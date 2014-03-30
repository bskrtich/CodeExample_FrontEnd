<?php
require '../api/msgservice.class.php';
//require '../api/user.class.php';
require '../api/msgapi.class.php';

$msgservice = new msgservice($db);
$user = $msgservice->validateUser();

if (isset($_REQUEST['action'])) {
    if (issset($_REQUEST['data']) &&
        ($data = json_decode($_REQUEST['data'], true))) {

        $result = $msgservice->action(
            $_SERVER['REQUEST_METHOD'],
            $_REQUEST['action'],
            $data
        );
    } else {
        $result = $msgservice->action(
            $_SERVER['REQUEST_METHOD'],
            $_REQUEST['action']
        );
    }

    if (isset($result)) {
        msgapi::apiResult($result);
    } else {
        msgapi::httpError(500, 'RequestError: Unknown Request Error.');
    }

} else {
    msgapi::httpError(500, 'MissingAction: No Action specified.');
}
