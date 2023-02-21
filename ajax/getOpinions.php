<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaOpinion.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';
global $db;
$dbOpinions = new dbDaOpinion($db);
$dbCandles = new dbDaCandle($db);
include_once Config::$lpfw.'dbDaTracker.class.php';
$trackerDb = new \maierlabs\lpfw\dbDaTracker($db->dataBase);

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
        if ($count == "text" )
            $op->text = $o->text;
        else
            $op->text =null;
        if (isUserAdmin()) {
            $op->ip =  $o->ip;
        }else {
            $op->ip = '';
        }
        $op->date = \maierlabs\lpfw\Appl::dateTimeAsStr($o->date);
        $op->id = $o->id;
        $op->myopinion = $o->myopinion;
        $person = $db->getPersonByID($o->person);
        if ($person != null) {
            $op->name = getPersonLinkAndPicture($person);
        } else {
            $op->name = getPersonLinkAndPicture($db->getPersonDummy());
        }
        //Only for easter opinions form this year
        if ($count=="easter" && $o->date > date("Y")."-01-01 00:00:00" && (getLoggedInUserId()==$id || isUserAdmin()) ) {
            //Check if the girl allready sent an easter egg to the boy
            $egg=$dbOpinions->existOpinion($o->person,$id,"person","easteregg", "changeDate > '".date("Y")."-01-01 00:00:00'");
            $op->sendEgg = $egg?null:true;
        } else {
            $op->senEgg = null;
        }
        array_push($ret, $op);
    }
} else {
    $candles=$dbCandles->getCandleDetailByPersonId($id);
    foreach ($candles as $o) {
        $op = new stdClass();
        if (isUserAdmin()) {
            $op->ip =  $o['ip'];
        }else {
            $op->ip = '';
        }
        $op->date = \maierlabs\lpfw\Appl::dateTimeAsStr($o['lightedDate']);
        $op->id = $o['id'];
        $op->myopinion = false;
        $person = $db->getPersonByID($o['userID']);
        if ($person != null && intval($o["showAsAnonymous"])===0 ) {
            $op->name = getPersonLinkAndPicture($person);
        } else {
            $op->name = getPersonLinkAndPicture($db->getPersonDummy());
        }
        array_push($ret, $op);
    }
}
$r = new stdClass();
$r->type = $type;
$r->id = $id;
$r->count=$count;
$r->list = $ret;
echo(json_encode($r));
?>