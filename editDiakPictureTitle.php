<?php

include_once 'ltools.php';
include_once 'data.php';

$id = getIntParam("id",-1 );
$uid = getIntParam("uid",-1 );
$title = getParam("title", "");
$comment = getParam("comment", "");

setPictureAttributes(getDatabaseName(),$uid,$id,$title,$comment);

$row = array();
$row["title"] = $title;
$row["comment"] = $comment;
$row["id"] = $id;
$row["uid"] = $uid;

header('Content-Type: application/json');
echo(json_encode($row));

?>