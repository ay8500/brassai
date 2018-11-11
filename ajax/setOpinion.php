<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbChangeType.class.php';
include_once __DIR__ . '/../sendMail.php';

header('Content-Type: application/json');

$id=getParam("id");
$table =getParam("type");
$type =getParam("count");
$text = getParam("text",'');

$ret = new stdClass();

if (getLoggedInUserId()==null) {
    if (
        ($table == 'person' && $type == 'friend') ||
        ($table == 'picture' && $type == 'favorite')
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

$oldOpinion = $db->getOpinion($id,$table,$type);
if (sizeof($oldOpinion)>0 && $type!='text') {
    $ret->result='exists';
    echo(json_encode($ret));
    die();
}

$ret->result='ok';
$ret->count=$db->setOpinion($id,getLoggedInUserId(),$table,$type,$text);
$db->saveRequest(changeType::opinion);
if ($type=='text') {
    sendHtmlMail(Config::$siteMail,'id:'.$id.'<br/> text:'.$text,'Vélemény: '.Config::$SiteTitle);
}

echo(json_encode($ret));