<?php
use PassByME\Authentication\Frontend;

/**
 * Example usage of passbyme2fa-client.
 * In this example we polling this file to check the authentication progress.
 */

require_once '../PassByME/Autoloader.php';
require_once 'conf/config.php';
require_once 'yourLogger.php';

$logger = new \YourLogger\Logger();
$pbmSession = filter_input(INPUT_POST, 'session', FILTER_SANITIZE_ENCODED);
$userId = filter_input(INPUT_POST, 'user_id');
$msg = filter_input(INPUT_POST, 'msg');
$cancel = filter_input(INPUT_POST, 'cancel');
$json = array();

try {
    $pbm = new Frontend($logger);

    //Check request progression by the request PBMSession
    if ($pbmSession) {
        if ($cancel) {
            $json = $pbm->authenticationCancel($pbmSession);
        } else {
            $json = $pbm->authenticationProgress($pbmSession);
        }
    } else {
        //Send request
        if ($userId) {
            $json = $pbm->authenticationRequest($userId, $msg);
        } else {
            throw new Exception("Missing PassBy[ME] user identifier!");
        }
    }
} catch (Exception $exc) {
    $logger->error('PBM error: ' . $exc->getMessage());
    $json = array('errormsg' => $exc->getMessage());
}
//JSON return
print json_encode($json);
