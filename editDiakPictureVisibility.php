<?php
include_once 'tools/sessionManager.php';
include_once 'tools/ltools.php';
include_once 'data.php';

$id = getIntParam("id",-1 );
$visibility = getParam("attr", "");

if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) {
	setPictureVisibleForAll($id,$visibility);
	echo($id."-".$visibility);
}
else 
	echo("Not authorized");
?>