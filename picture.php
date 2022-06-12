<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once("dbBL.class.php");

use maierlabs\lpfw\Appl as Appl;
global $db;

$id=getIntParam("id",-1);
if ($id>=0) {
	$picture=$db->getPictureById($id);
	if ($picture!=null) {
        if (isset($picture["schoolID"])) {
            $type="schoolID";
            setActSchool($picture["schoolID"]);
        }
        if (isset($picture["classID"])) {
            $type="classID";
            setActClass($picture["classID"],$picture["schoolID"]);
        }
		if (isset($picture["personID"])) {
            $type="personID";

        }
		$_GET["type"]=$type;
		$typeId=$picture[$type];
	}
}
if (!isset($type)) {
	$type=getParam("type");
	if ($type==null) {
		if (null!=getActClass()) {
			$type="classID";$typeId=getRealId(getActClass());
		} else {
			$type="schoolID";$typeId=getRealId(getActSchool());
		}
	}
	if(!in_array($type,array("personID","schoolID","classID"))) {
        header("HTTP/1.0 400 Bad Request");
        include "homemenu.inc.php";
        echo('<div class="well">Bad Request</div>');
        include "homefooter.inc.php";
        return;
    }
}
if (!isset($typeId)) {
	$typeId=getIntParam("typeid");
	if ($typeId==0) {
		if (null!=getActClass()) {
			$type="classID";$typeId=getRealId(getActClass());
		} else {
			$type="schoolID";$typeId=getRealId(getActSchool());
		}
	}
} else {
	if ($type=="classID") {
		setActClass($typeId);
	}
}
$album=getParam("album","");

if ($type=="classID") {
	$subtitle="Osztályképek: ".getActSchoolClassName()." ".$album;
} elseif ($type=="personID") {
	$person=$db->getPersonByID($typeId);
    setActClass($person["classID"],$person["schoolID"]);
	$subtitle=getPersonName($person)." képei";
	$link=getPersonLinkAndPicture($person)." képei";
} elseif ($type=="schoolID") {
	$subtitle="Iskola képek ".$album;
}
if ($album=="_tablo_" || $type=='tablo') {
	$subtitle="Tabló képek";
} elseif ($album=="_card_" || $type=='card') {
    $subtitle="Kicsengetési kártyák";
} elseif ($album=="_sport_" || $type=='sport') {
    $subtitle="Iskola sportolói";
}
Appl::setSiteTitle($subtitle);
Appl::setSiteSubTitle($type=="personID"?$link:$subtitle);
if (isset($picture)) {
    Appl::setMember("firstPicture",$picture);
}
Appl::addCss('css/chosen.css');
include("homemenu.inc.php");
?>
<div class="container-fluid">
    <?php include_once 'picture.inc.php';?>
</div>

<?php
Appl::addJs('js/chosen.jquery.js');
Appl::addJsScript('
    $(document).ready(function(){
        $(".chosen").chosen({width:"100%",no_results_text:"Ilyen tartalmi megjelölés nincs!"});
    });
');
include 'homefooter.inc.php';
?>