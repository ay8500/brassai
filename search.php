<?PHP
$SiteTitle="Brassaista Véndiákok"; 
$SiteDescription="Brassaista Véndiákok keresés";
include("homemenu.php");
include_once 'editDiakCard.php';

$personList=array();
$classList=array();
$pictureList=array();
$name="";

if (null==getParam("type")) {
	$name=trim(html_entity_decode(getGetParam("srcText", "")));
	$personList=$db->searchForPerson($name);
	$classList=$db->searchForClass($name);
	$pictureList=$db->searchForPicture($name);
} else {
	$start=getIntParam("start",0);
	$link="search.php?type=".getParam("type")."&start=";
	switch (getParam("type")) {
		case "teacher": {
			$personList=$db->getPersonList("isTeacher='1'",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='1'");
			$caption ="Tanárok:".$personCount;
			break;
		}
		case "teacherwithpicture": {
			$personList=$db->getPersonList("isTeacher='1' and (picture is not null and picture <>'')",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='1' and (picture is not null and picture <>'')");
			$caption ="Tanárok képpel:".$personCount;
			break;
		}
		case "teacherwithemail": {
			$personList=$db->getPersonList("isTeacher='1' and (email is not null and email <>'')",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='1' and (email is not null and email <>'')");
			$caption ="Tanárok email címmel:".$personCount;
			break;
		}
		case "teacherwithfacebook": {
			$personList=$db->getPersonList("isTeacher='1' and (facebook is not null and facebook <>'')",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='1' and (facebook is not null and facebook <>'')");
			$caption ="Tanárok facebookal:".$personCount;
			break;
		}
		case "teacherwithwikipedia": {
			$personList=$db->getPersonList("isTeacher='1' and homepage like '%wikipedia%'",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='1' and homepage like '%wikipedia%'");
			$caption ="Tanárok wikipédia oldallal:".$personCount;
			break;
		}
		
		case "classmate": {
			$personList=$db->getPersonList("isTeacher='0'",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='0'");
			$caption ="Diákok:".$personCount;
			break;
		}
		case "classmatewithpicture": {
			$personList=$db->getPersonList("isTeacher='0' and (picture is not null and picture <>'')",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='0' and (picture is not null and picture <>'')");
			$caption ="Diákok képpel:".$personCount;
			break;
		}
		case "classmatewithemail": {
			$personList=$db->getPersonList("isTeacher='0' and (email is not null and email <>'')",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='0' and (email is not null and email <>'')");
			$caption ="Diákok email címmel:".$personCount;
			break;
		}
		case "classmatewithfacebook": {
			$personList=$db->getPersonList("isTeacher='0' and (facebook is not null and facebook <>'')",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='0' and (facebook is not null and facebook <>'')");
			$caption ="Diákok facebookal:".$personCount;
			break;
		}
		case "classmatewithwikipedia": {
			$personList=$db->getPersonList("isTeacher='0' and homepage like '%wikipedia%'",20,getIntParam("start",0));
			$personCount=$db->getTableCount("person","isTeacher='0' and homepage like '%wikipedia%'");
			$caption ="Diákok wikipédia oldallal:".$personCount;
			break;
		}
	}
}

?>

<div class="container-fluid">
	<h2 style="text-align: center;"  class="sub_title" >Találatok a véndiákok adatbankjában</h2>
	<?php if(sizeof($personList)>0) {?>
		<div class="well">
			<?php if (strlen($name)>0) {?>
				Talált személyek száma:<?php echo sizeof($personList)?> <?php echo 'Keresett szó:"'.$name.'"'?>
			<?php }else {?>
				<nav aria-label="Page navigation example">
				  <ul class="pagination">
				  	<li class="page-item"><span class="page-link" ><?php echo ($caption)?></span></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $link."0" ?>"><span class="glyphicon glyphicon-fast-backward"></span></a></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $start>0?$link.($start-1):"#" ?>"><span class="glyphicon glyphicon-step-backward"></span></a></li>
				    <li class="page-item"><a class="page-link" href="#"><?php echo 20*getIntParam("start",0)+1?></a></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $start*20<$personCount?$link.($start+1):"#" ?>"><span class="glyphicon glyphicon-step-forward"></span></a></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $link.round($personCount/20) ?>"><span class="glyphicon glyphicon-fast-forward"></span></a></li>
				  </ul>
				</nav>
			<?php }?>
		</div>
		<?php
		foreach ($personList as $d)	{
			displayPerson($db,$d,true);
		}
	}
	?>
	<?php if(sizeof($classList)>0) {?>
		<div class="well">
			Talált osztályok száma:<?php echo sizeof($classList)?> Keresett szó:"<?php echo $name?>"
		</div>
		<?php
		foreach ($classList as $d)	{
			displayclass($db,$d);
		}
	}
	?>
	<?php if(sizeof($pictureList)>0) {?>
		<div class="well">
			Talált képek száma:<?php echo sizeof($pictureList)?> Keresett szó:"<?php echo $name?>"
		</div>
		<?php
		foreach ($pictureList as $d)	{
			displayPicture($db,$d);
		}
	}
	?>
</div>


<?php include ("homefooter.php");?>

<script type="text/javascript">
	$( document ).ready(function() {
		if ("<?php echo getGetParam("srcText", "")?>"!="") {
		    showSearchBox(true);
		}
	});
</script>
