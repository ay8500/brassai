<?php 
include_once("tools/sessionManager.php");
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once("data.php");

$id=getIntParam("id",-1);
if ($id>=0) {
	$picture=$db->getPictureById($id);
	if ($picture!=null) {
		if (isset($picture["personID"])) $type="personID";
		if (isset($picture["schoolID"])) $type="schoolID";
		if (isset($picture["classID"]))  $type="classID";
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
	$subtitle="A tanárok és diákok együtt a ballagási tablón és csoportképeken. ".getAktClassName();
} if ($type=="personID") { 
	$person=$db->getPersonByID($typeId);
	$subtitle=getPersonName($person)." képei";
	$link=getPersonLinkAndPicture($person)." képei";
} if ($type=="schoolID") { 
	$subtitle="Képek iskolánkról. ".$album;
} if ($type=="tablo") { 
	$subtitle="Iskolánk tabló képei.";
}  
$SiteTitle=$subtitle;
Appl::$subTitle= $type=="personID"?$link:$subtitle;
include("homemenu.php");
?>
<div class="container-fluid">
<?php include_once 'pictureinc.php';?>
</div>

<?php include 'homefooter.php'; ?> 