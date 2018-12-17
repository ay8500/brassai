<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../tools/appl.class.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaPersonInPicture.class.php';

$dbPIP = new dbDaPersonInPicture($db);

header('Content-Type: application/json');

$personId=getParam("personid");
$pictureId=getParam("pictureid");

$ret = array();

$ret = $dbPIP->getListOfPersonInPicture($pictureId);

echo(json_encode($ret));
?>