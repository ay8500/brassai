<?php
include_once 'tools/sessionManager.php';
include_once 'tools/ltools.php';
include_once 'data.php';

$id = getIntParam("id",-1 );
$visibility = getParam("attr", "");

if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) {

	$p=$db->getPictureById($id);
	$p["isVisibleForAll"]=$visibility;
	if ($db->savePicture($p)>0) {
		$row["error"] = "Error saving the picture";
	}
}
else 
	echo("Not authorized");
?>