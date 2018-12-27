<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');



$name = getParam("name","");

$personList = array();

$persons=$db->searchForPerson($name);

echo(json_encode($persons));
