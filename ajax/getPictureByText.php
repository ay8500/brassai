<?php
include_once __DIR__ . '/../lpfw/sessionManager.php';
include_once __DIR__ . '/../lpfw/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');



$text = getParam("text","");

$pictureList = array();

$pictureList=$db->searchForPicture($text);

echo(json_encode($pictureList));
