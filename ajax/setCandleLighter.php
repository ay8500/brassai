<?php
include_once __DIR__ . '/../lpfw/sessionManager.php';
include_once __DIR__ . '/../lpfw/userManager.php';
include_once __DIR__ . '/../lpfw/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';

$dbCandle = new dbDaCandle($db);

$id=getIntParam("id");	
$dbCandle->setCandleLighter($id,getLoggedInUserId());
?>