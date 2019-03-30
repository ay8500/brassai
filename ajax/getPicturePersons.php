<?php
include_once __DIR__ . '/../lpfw/sessionManager.php';
include_once __DIR__ . '/../lpfw/ltools.php';
include_once __DIR__ . '/../lpfw/appl.class.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaPersonInPicture.class.php';

$dbPIP = new dbDaPersonInPicture($db);

header('Content-Type: application/json');

$pictureId=getParam("pictureid");

$ret = array();

$ret["face"] = $dbPIP->getListOfPersonInPicture($pictureId);

$picture = $db->getPictureById($pictureId);
$ret["title"]=$picture["title"];

echo(json_encode($ret));
?>