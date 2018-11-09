<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');

$id=getParam("id");
$type =getParam("type","person");
$count =getParam("count","");
$start = getIntParam("start",0);

if ($type=="person") {
    if ($count=="friends") {
        $opinions = $db->getPersonOpinionCount($id,"friends");
    } else if ($count=="friendly") {
        $opinions = $db->getPersonOpinionCount($id,"friendly");
    } else if ($count=="sport") {
        $opinions = $db->getPersonOpinionCount($id,"sport");
    } else {
        $opinions = $db->getPersonOpinions($id, $start);
    }
} else if ($type=="picture") {
    if ($count=="favorite") {
        $opinions = $db->getPictureOpinionCount($id,"favorite");
    } else if ($count=="nice") {
        $opinions = $db->getPictureOpinionCount($id,"nice");
    } else if ($count=="content") {
        $opinions = $db->getPictureOpinionCount($id,"content");
    } else {
        $opinions = $db->getPictureOpinions($id, $start);
    }
}  else {
    $opinions = array();
}
$ret=array();
foreach ($opinions as $o) {
    $op = new stdClass();
    if ($count=="")
        $op->text = $o->text;
    $op->ip = $o->ip;
    $op->date = $o->date;
    $person=$db->getPersonByID($o->person);
    if ($person!=null) {
        $op->name = getPersonLinkAndPicture($person);
    } else {
        $op->name = getPersonLinkAndPicture($db->getPersonDummy());
    }
    array_push($ret,$op);
}
echo(json_encode($ret));
?>