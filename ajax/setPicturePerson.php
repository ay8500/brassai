<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaPersonInPicture.class.php';

$dbPIP = new dbDaPersonInPicture($db);

header('Content-Type: application/json');

$personId=getParam("personid");
$pictureId=getParam("pictureid");
$x=getParam("x");
$y=getParam("y");
$w=getParam("w");



$ret = $dbPIP->savePersonInPicture($personId,$pictureId,$x,$y,$w);


echo(json_encode($ret));
?>