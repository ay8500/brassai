
<?php 
include_once("tools/sessionManager.php");
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once("data.php");
$SiteTitle="Ballagási tabló és csoportképek".getAktClassName();
include("homemenu.php"); 

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
		$type="classID";$typeId=getRealId(getAktClass());
	}
}
if (!isset($typeId)) {
	$typeId=getParam("typeid");
	if ($typeId==null) {
		$type="classID";$typeId=getRealId(getAktClass());
	}
}

?>

<div class="container-fluid">
	
	<?php if ($type=="classID") { ?>
		<h2 class="sub_title">A tanárok és diákok együtt a ballagási tablón és csoportképeken.</h2>
	<?php } if ($type=="personID") { $person=$db->getPersonByID($typeId); ?>
		<h2 class="sub_title"><?php writePersonLinkAndPicture($person);?> képei</h2>
	<?php } if ($type=="schoolID") { ?>
		<h2 class="sub_title">Képek iskolánkról.</h2>
	<?php } if ($type=="tablo") { ?>
		<h2 class="sub_title">Iskolánk tabló képei.</h2>
	<?php }  ?>

<?php include_once 'pictureinc.php';?>

</div>

<?php include 'homefooter.php'; ?> 

