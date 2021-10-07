<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaPersonInPicture.class.php';

global $db;
$dbPIP = new dbDaPersonInPicture($db);

header('Content-Type: application/json');

$pictureId=getIntParam("pictureid");

$ret = array();

$ret["face"] = $dbPIP->getListOfPersonInPicture($pictureId);

$picture = $db->getPictureById($pictureId);
$ret["changeDate"]=\maierlabs\lpfw\Appl::dateTimeAsStr($picture["changeDate"]);
$ret["title"]=$picture["title"];
if(isset($picture["comment"]) && $picture["comment"]!=null && $picture["comment"]!="" && $picture["comment"]!="undefinied")
    $ret["comment"]=$picture["comment"];
if(isset($picture["tag"]) && $picture["tag"]!=null && $picture["tag"]!="" && $picture["tag"]!="undefinied")
    $ret["tag"]=$picture["tag"];
if(isset($picture["albumName"]) && $picture["albumName"]!=null && $picture["albumName"]!="" && $picture["tag"]!="albumName")
    $ret["album"]=$picture["albumName"];

echo(json_encode($ret));
?>