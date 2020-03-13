<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';

header('Content-Type: application/json');

$dbCandle = new dbDaCandle($db);

$id=getIntParam("id");
$asAnonymous =getIntParam("asAnonymous",1);
$dbOperation = $dbCandle->setCandleLighter($id,getLoggedInUserId(),$asAnonymous);

$ret = array("id"=>$id,"uId"=>getLoggedInUserId(),"result"=>$dbOperation);
echo json_encode($ret);
?>