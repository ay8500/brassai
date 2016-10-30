<?php
include_once 'sessionManager.php';
include_once 'ltools.php';
include_once 'data.php';

$id = getIntParam("id",-1 );
$uid = getIntParam("uid",-1 );
$title = getParam("title", "");
$comment = getParam("comment", "");

if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) {

	$p=$db->getPictureById($id);
	$p["title"]=$title;
	$p["comment"]=$comment;
	$row = array();
	$row["title"] = $title;
	$row["comment"] = $comment;
	$row["id"] = $id;
	$row["uid"] = $uid;
	if ($db->savePicture($p)>0) {
		$row["error"] = "Error saving the picture";
	}
}
else
	$row["error"] = "Not authorized!";

header('Content-Type: application/json');
echo(json_encode($row));
?>