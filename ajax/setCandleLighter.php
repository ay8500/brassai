<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/userManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';

$dbCandle = new dbDaCandle($db);

$id=getIntParam("id");	
$dbCandle->setCandleLighter($id,getLoggedInUserId());
?>