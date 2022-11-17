<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');

$name = getParam("name","");

global $db;
$persons=$db->searchForPerson($name);
$return = array();

foreach ($persons as $person) {
    $ret= new stdClass();
    $ret->id = $person["id"];
    $ret->title = $person["title"];
    $ret->firstname = $person["firstname"];
    $ret->lastname = $person["lastname"];
    $ret->birthname = $person["birthname"];
    $ret->gender = $person["gender"];
    $ret->picture = $person["picture"];
    $ret->schoolID = $person["schoolID"];
    $ret->schoolIdsAsTeacher = isset($person["schoolIdsAsTeacher"])?$person["schoolIdsAsTeacher"]:null;
    $ret->schoolLogo = $person["schoolLogo"];
    $ret->schoolYear = $person["schoolYear"];
    $ret->schoolClass = $person["schoolClass"];
    $ret->deceasedYear = $person["deceasedYear"];
    $return[] = $ret;
}

echo(json_encode($return));
