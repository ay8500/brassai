<?php
/**
 * start the session and set the lastReq date and time
 */
if(!isset($_SESSION)) session_start();
$_SESSION['lastReq']=date('d.m.Y H:i');
?>