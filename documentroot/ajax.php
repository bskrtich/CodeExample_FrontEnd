<?php
require '../api/main.include.php';

use bskrtich\microblog\MsgService;
use bskrtich\microblog\MsgApi;

$msgservice = new MsgService($db);
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
        MsgApi::apiResult($result);
    } else {
        MsgApi::httpError(500, 'RequestError: Unknown Request Error.');
    }

} else {
    MsgApi::httpError(500, 'MissingAction: No Action specified.');
}
