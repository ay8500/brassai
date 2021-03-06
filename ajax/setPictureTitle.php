<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';

$id = getIntParam("id",-1 );
$title = getParam("title", "");
$comment = getParam("comment", "");
$tag = getParam("tag", "");

$row = array();


if ( $db->checkRequesterIP(changeType::picturechange)) {
	$p=$db->getPictureById($id);
	$p["title"]=$title;
	$p["comment"]=$comment;
    $p["tag"]=$tag;
	if ($db->savePicture($p)>=0) {
		$db->saveRequest(changeType::picturechange);
	} else {
		$row["error"] = "Kép címének és tartalmának kimentése nem sikerült!";
	}
	$row["title"] = $title;
	$row["comment"] = $comment;
    $row["tag"] = $tag;
	$row["id"] = $id;
}
else
	$row["error"] = "Sajnáljuk, de tul sok képet probálsz modosítani!<br/>Az adatok módosítása anonim felhasználok részére korlatozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.";

header('Content-Type: application/json');
echo(json_encode($row));
?>