<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaOpinion.class.php';

header('Content-Type: application/json');

$id=getParam("id");

$ret = new stdClass();
global $db;
$dbOpinion = new dbDaOpinion($db);
$ret=$dbOpinion->sendEasterEgg($id);

echo(json_encode($ret));
