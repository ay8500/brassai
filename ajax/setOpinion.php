<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbDaOpinion.class.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbChangeType.class.php';
include_once __DIR__ . '/../sendMail.php';

include_once Config::$lpfw.'dbDaTracker.class.php';
global $db;
$trackerDb = new \maierlabs\lpfw\dbDaTracker($db->dataBase);

header('Content-Type: application/json');

$id=getParam("id");
$table =getParam("type");
$type =getParam("count");
$text = getParam("text",'');
$phpUnitTest = getParam("test")=="test";

$ret = new stdClass();

if (getLoggedInUserId()==null) {
    if (
        ($table == 'person' && $type == 'friend') ||
        ($table == 'person' && $type == 'easter') ||
        ($table == 'music' && $type == 'favorite') ||
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

$oldOpinion = $dbOpinion->getOpinion($id,$table,$type,$type=="easter"?("changeDate > '".date("Y")."-01-01 00:00:00'"):null);
if (sizeof($oldOpinion)>0 && $type!='text') {
    $ret->result='exists';
    echo(json_encode($ret));
    die();
}

$ret->result='ok';
$ret->count=$dbOpinion->setOpinion($id,getLoggedInUserId(),$table,$type,$text);
if (!$phpUnitTest) {
    $db->saveRequest(changeType::opinion);
    if ($type == 'text') {
        \maierlabs\lpfw\Appl::sendHtmlMail(Config::$siteMail, 'id:' . $id . '<br/> table:' . $table . '<br/> text:' . $text, 'Vélemény: ');
    }
    if ($type == 'easter') {
        $person = $db->getPersonByID($id);
        $email = getFieldValue($person, "email");
        if ($email != "") {
            $text = "<h3>Kedves " . getPersonName($person) . "</h3>";
            $text .= "<p>A véndiákok honoldalán keresztül virtuálisan meglocsolt " . getPersonName($db->getPersonByID(getLoggedInUserId()), true) . "</p>";
            $text .= '<p>Ha szeretnél a locsolónak piros tojást adni, kattints a piros tojás linkre.';
            $text .= '<a href="https://kolozsvarivendiakok.blue-l.de/easteregg?id=' . encrypt_decrypt("encrypt", getLoggedInUserId()) . '&key=' . encrypt_decrypt("encrypt", $id) . '"> <img src="https://kolozsvarivendiakok.blue-l.de/images/easter.png" style="width: 32px"> piros tojás</a> </p>';
            $text .= "<p>Kellemes húsvéti ünnepeket!</p>";
            \maierlabs\lpfw\Appl::sendHtmlMail($email, $text, 'Virtuális locsolás. ');
        }
    }
}

echo(json_encode($ret));