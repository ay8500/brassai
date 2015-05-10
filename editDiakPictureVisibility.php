<?php
include_once 'ltools.php';
include_once 'data.php';

$id = getIntParam("id",-1 );
$uid = getIntParam("uid",-1 );
$visibility = getParam("attr", "");

setPictureVisibleForAll(getDatabaseName(),$uid,$id,$visibility);

echo($uid."-".$id."-".$visibility);
?>