<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');

$id=getParam("id");
$type =getParam("type","person");
$count =getParam("count","");
$start = getIntParam("start",0);

$opinions = $db->getOpinions($id,$type,$count,$start);
$ret=array();
foreach ($opinions as $o) {
    $op = new stdClass();
    if ($count=="text" || $count="")
        $op->text = $o->text;
    $op->ip = $o->ip;
    $op->date = $o->date;
    $op->id = $o->id;
    $op->myopinion = $o->myopinion;
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