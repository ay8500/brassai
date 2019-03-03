<?php
/**
 * start the session and set the lastReq date and time
 */
if(!isset($_SESSION)) session_start();
if ( isset($dieIfNoSessionActive) && !isset($_SESSION['lastReq']) ) {
    http_response_code(401);
    die('Unauthorized');
}

$_SESSION['lastReq']=date('d.m.Y H:i');
?>