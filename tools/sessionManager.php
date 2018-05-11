<?php
if(!isset($_SESSION)) session_start();
$_SESSION['lastReq']=date('d.m.Y H:i');
?>