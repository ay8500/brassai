<?php
include_once __DIR__ . '/../lpfw/sessionManager.php';
include_once __DIR__ . '/../lpfw/ltools.php';
include_once __DIR__ . '/../lpfw/appl.class.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaOpinion.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';

$dbOpinions = new dbDaOpinion($db);
$dbCandles = new dbDaCandle($db);

header('Content-Type: application/json');

$id=getParam("id");
$type =getParam("type","person");
$count =getParam("count","");
$start = getIntParam("start",0);

$ret = array();
if ($count!='candle') {
    $opinions = $dbOpinions->getOpinions($id, $type, $count, $start);

    foreach ($opinions as $o) {
        $op = new stdClass();
        if ($count == "text" || $count = "")
            $op->text = $o->text;
        if (userIsAdmin())
            $op->ip = ' '.$o->ip;
        else
            $op->ip = '';
            $op->date = \maierlabs\lpfw\Appl::dateTimeAsStr($o->date);
        $op->id = $o->id;
        $op->myopinion = $o->myopinion;
        $person = $db->getPersonByID($o->person);
        if ($person != null) {
            $op->name = getPersonLinkAndPicture($person);
        } else {
            $op->name = getPersonLinkAndPicture($db->getPersonDummy());
        }
        array_push($ret, $op);
    }
} else {
    $candles=$dbCandles->getCandleDetailByPersonId($id);
    foreach ($candles as $o) {
        $op = new stdClass();
        $op->ip = $o['ip'];
        $op->date = \maierlabs\lpfw\Appl::dateTimeAsStr($o['lightedDate']);
        $op->id = $o['id'];
        $op->myopinion = false;
        $person = $db->getPersonByID($o['userID']);
        if ($person != null) {
            $op->name = getPersonLinkAndPicture($person);
        } else {
            $op->name = getPersonLinkAndPicture($db->getPersonDummy());
        }
        array_push($ret, $op);
    }
}

echo(json_encode($ret));
?>