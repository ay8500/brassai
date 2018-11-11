<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');

$id=getParam("id");

$ret = new stdClass();
$ret=$db->deleteOpinion($id);

echo(json_encode($ret));
?>