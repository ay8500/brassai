<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../data.php';

$id = getIntParam("id",-1 );
$title = getParam("title", "");
$comment = getParam("comment", "");

$row = array();


if ( $db->getCountOfRequest(changeType::classchange)<20) {
	$p=$db->getPictureById($id);
	$p["title"]=$title;
	$p["comment"]=$comment;
	if ($db->savePicture($p)>=0) {
		$db->saveRequest(changeType::classchange);
	} else {
		$row["error"] = "Error saving the picture";
	}
	$row["title"] = $title;
	$row["comment"] = $comment;
	$row["id"] = $id;
}
else
	$row["error"] = "Not authorized!";

header('Content-Type: application/json');
echo(json_encode($row));
?>