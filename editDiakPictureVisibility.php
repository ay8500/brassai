<?php
include_once 'sessionManager.php';
include_once 'ltools.php';
include_once 'data.php';

$id = getIntParam("id",-1 );
$uid = getIntParam("uid",-1 );
$visibility = getParam("attr", "");

if ( (userIsLoggedOn() && $_SESSION["UID"]==$uid) || userIsAdmin() ) {
	setPictureVisibleForAll(getDatabaseName(),$uid,$id,$visibility);
	echo($uid."-".$id."-".$visibility);
}
else 
	echo("Not authorized");
?>