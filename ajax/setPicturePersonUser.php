<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaPersonInPicture.class.php';

global $db;
$dbPIP = new dbDaPersonInPicture($db);

header('Content-Type: application/json');

$personId=getParam("personid");
$pictureId=getParam("pictureid");
$delete=getParam("delete");

$ret = $dbPIP->setUserForPersonInPicture($personId,$pictureId,$delete);

echo(json_encode($ret));
?>