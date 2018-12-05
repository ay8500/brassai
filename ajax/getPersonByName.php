<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');

$class=$db->getClassByText(getParam("class"));
if (null==$class) {
    $class=$db->getClassById(getParam("classid"));
}

$name = getParam("name","");

$personList = array();

if (strlen(trim($name))>2) {
    $persons=$db->searchForPerson($name);
}

echo(json_encode($persons));
