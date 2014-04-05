<?php
require '../api/msgservice.class.php';
//require '../api/user.class.php';
require '../api/msgapi.class.php';

$msgservice = new msgservice($db);
$user = $msgservice->validateUser();

if (isset($_REQUEST['action'])) {
    if (isset($_REQUEST['params'])) {
        $result = $msgservice->ajaxAction(
            $_SERVER['REQUEST_METHOD'],
            $_REQUEST['action'],
            $_REQUEST['params']
        );
    } else {
        $result = $msgservice->ajaxAction(
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
