<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once("dbBL.class.php");

$id=getIntParam("id",-1);
if ($id>=0) {
	$picture=$db->getPictureById($id);
	if ($picture!=null) {
		if (isset($picture["personID"])) $type="personID";
		if (isset($picture["schoolID"])) $type="schoolID";
		if (isset($picture["classID"]))  $type="classID";
		$_GET["type"]=$type;
		$typeId=$picture[$type];
	}
}
if (!isset($type)) {
	$type=getParam("type");
	if ($type==null) {
		if (null!=getAktClass()) {
			$type="classID";$typeId=getRealId(getAktClass());
		} else {
			$type="schoolID";$typeId=getRealId(getAktSchool());
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
	$typeId=getParam("typeid");
	if ($typeId==null) {
		if (null!=getAktClass()) {
			$type="classID";$typeId=getRealId(getAktClass());
		} else {
			$type="schoolID";$typeId=getRealId(getAktSchool());
		}
	}
} else {
	if ($type=="classID") {
		setAktClass($typeId);
	}
}
$album=getParam("album","");

if ($type=="classID") {
	$subtitle="Osztályképek: ".getAktClassName()." ".$album;
} elseif ($type=="personID") {
	$person=$db->getPersonByID($typeId);
	$subtitle=getPersonName($person)." képei";
	$link=getPersonLinkAndPicture($person)." képei";
} elseif ($type=="schoolID") {
	$subtitle="Képek iskolánkról: ".$album;
}
if ($album=="_tablo_" || $type=='tablo') {
	$subtitle="Iskolánk tabló képei";
} elseif ($album=="_card_" || $type=='card') {
    $subtitle="Kicsengetési kártyák";
} elseif ($album=="_sport_" || $type=='sport') {
    $subtitle="Oskolánk sportolói";
}
\maierlabs\lpfw\Appl::setSiteTitle($subtitle);
\maierlabs\lpfw\Appl::setSiteSubTitle($type=="personID"?$link:$subtitle);
if (isset($picture)) {
    \maierlabs\lpfw\Appl::setMember("firstPicture",$picture);
}
\maierlabs\lpfw\Appl::addCss('css/chosen.css');
include("homemenu.inc.php");
?>
<div class="container-fluid">
<?php include_once 'picture.inc.php';?>
</div>

<?php
\maierlabs\lpfw\Appl::addJs('js/chosen.jquery.js');
\maierlabs\lpfw\Appl::addJsScript('
    $(document).ready(function(){
        $(".chosen").chosen({width:"100%",no_results_text:"Ilyen tartalmi megjelölés nincs!"});
    });
');
include 'homefooter.inc.php';
?>