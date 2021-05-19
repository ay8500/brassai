<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';

$id = getIntParam("id" ,-1);
$visibility = getParam("attr");

if ($id==-1 || $visibility==null) {
    http_response_code(401);
    echo("Parameter id and attr are not correct!");
    die();
}

if ( isUserAdmin() || isUserEditor() || isAktUserTheLoggedInUser()) {

	$p=$db->getPictureById($id);
	$p["isVisibleForAll"]=$visibility;
	if ($db->savePicture($p)>0) {
		$row["error"] = "Error saving the picture";
	}
}
else {
    http_response_code(401);
    echo("Not authorized");
}
?>