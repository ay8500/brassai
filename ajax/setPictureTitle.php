<?php
include_once __DIR__ . '/../lpfw/sessionManager.php';
include_once __DIR__ . '/../lpfw/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

$id = getIntParam("id",-1 );
$title = getParam("title", "");
$comment = getParam("comment", "");

$row = array();


if ( $db->checkRequesterIP(changeType::picturechange)) {
	$p=$db->getPictureById($id);
	$p["title"]=$title;
	$p["comment"]=$comment;
	if ($db->savePicture($p)>=0) {
		$db->saveRequest(changeType::picturechange);
	} else {
		$row["error"] = "Kép címének és tartalmának kimentése nem sikerült!";
	}
	$row["title"] = $title;
	$row["comment"] = $comment;
	$row["id"] = $id;
}
else
	$row["error"] = "Sajnáljuk, de tul sok képet probálsz modosítani!<br/>Az adatok módosítása anonim felhasználok részére korlatozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.";

header('Content-Type: application/json');
echo(json_encode($row));
?>