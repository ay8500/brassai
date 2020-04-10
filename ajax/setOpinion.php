<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbDaOpinion.class.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbChangeType.class.php';
include_once __DIR__ . '/../sendMail.php';

include_once Config::$lpfw.'dbDaTracker.class.php';
$trackerDb = new \maierlabs\lpfw\dbDaTracker($db->dataBase);

header('Content-Type: application/json');

$id=getParam("id");
$table =getParam("type");
$type =getParam("count");
$text = getParam("text",'');

$ret = new stdClass();

if (getLoggedInUserId()==null) {
    if (
        ($table == 'person' && $type == 'friend') ||
        ($table == 'picture' && $type == 'favorite') ||
        ($table == 'message' && $type == 'favorite')
    ) {
        $ret->result='login';
        echo(json_encode($ret));
        die();
    }
}

if ($type=='text' && trim($text)=='') {
    $ret->result='empty';
    echo(json_encode($ret));
    die();
}

if (!$db->checkRequesterIP(changeType::opinion)) {
    $ret->result='count';
    echo(json_encode($ret));
    die();
}

$dbOpinion = new dbDaOpinion($db);

$oldOpinion = $dbOpinion->getOpinion($id,$table,$type);
if (sizeof($oldOpinion)>0 && $type!='text') {
    $ret->result='exists';
    echo(json_encode($ret));
    die();
}

$ret->result='ok';
$ret->count=$dbOpinion->setOpinion($id,getLoggedInUserId(),$table,$type,$text);
$db->saveRequest(changeType::opinion);
if ($type=='text') {
    \maierlabs\lpfw\Appl::sendHtmlMail(Config::$siteMail,'id:'.$id.'<br/> table:'.$table.'<br/> text:'.$text,'Vélemény: ');
}

echo(json_encode($ret));