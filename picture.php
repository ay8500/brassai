
<?php 
include_once("tools/sessionManager.php");
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once("data.php");
$SiteTitle="Ballagási tabló és csoportképek".getAktClassName();
include("homemenu.php"); 

$id=getIntParam("id",-1);
if ($id>=0) {
	$p=$db->getPictureById($id);
	if ($p!=null) {
		if (isset($p["personID"])) $type="personID";
		if (isset($p["schoolID"])) $type="schoolID";
		if (isset($p["classID"]))  $type="classID";
		$typeId=$p[$type];
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
		<h2 class="sub_title"><?php echo("Képek:".getPersonName($person)); ?></h2>
	<?php } if ($type=="schoolID") { ?>
		<h2 class="sub_title">Képek iskolánkról.</h2>
	<?php }  ?>

<?php include_once 'pictureinc.php';?>

<?php include 'homefooter.php'; ?> 

