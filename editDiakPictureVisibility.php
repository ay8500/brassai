<?php
include_once 'sessionManager.php';
include_once 'ltools.php';
include_once 'data.php';

$id = getIntParam("id",-1 );
$uid = getIntParam("uid",-1 );
$visibility = getParam("attr", "");

if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) {
	setPictureVisibleForAll(getAktDatabaseName(),$uid,$id,$visibility);
	echo($uid."-".$id."-".$visibility);
}
else 
	echo("Not authorized");
?>