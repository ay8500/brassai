<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaSongVote.class.php';

header('Content-Type: application/json');

$text = getParam("text","");

global $db;
$dbSongVote = new dbDaSongVote($db);
$music=$dbSongVote->searchForMusic($text);

echo(json_encode($music));
