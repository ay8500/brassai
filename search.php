<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/appl.class.php';
include_once 'dbBL.class.php';
use \maierlabs\lpfw\Appl as Appl;

$personList=array();
$classList=array();
$pictureList=array();
$name="";
$start=getIntParam("start",0);
$link="search.php?type=".getParam("type")."&start=";
Appl::setSiteSubTitle("Találatok a véndiákok adatbankjában");
$fields = "person.*,class.text as classText";
$join ="class on person.classID=class.id ";
$sort="lastName, firstName";

if (null==getParam("type")) {
	$name=trim(html_entity_decode(getGetParam("srcText", "")));
	$personList=$db->searchForPerson($name);
	$classList=$db->searchForClass($name);
	$pictureList=$db->searchForPicture($name);
} elseif ('jmlaureat'==getParam("type")) {
    Appl::setSiteSubTitle("Juhász Máthé díjasok");
    $personList=$db->getPersonList("role like '%jmlaureat%'",null,null,"class.text desc",$fields,$join);
    $personCount=$db->getTableCount("person","role like '%jmlaureat%'");
    $caption ='Az iskolánk legjelentősebb díját a Juhász Máté István Emlékdíjat az iskola egykori diákja, a műegyetem hallgatójaként rákban elhunyt kiváló tanuló emlékére alapította családja és ösztályfőnöke, Gáll Dénes.';
} elseif ('unknown'==getParam("type")) {
    Appl::setSiteSubTitle("Iskolatársaink akikről sajnos nem tudunk semmit.");
    $personList=$db->getPersonList("role like '%unknown%'",null,null,"class.text desc",$fields,$join);
    $personCount=$db->getTableCount("person","role like '%unknown%'");
    $caption ='Ezen a listán azok az egykori iskolatársak jelennek meg, akikről nem tudjuk mit történt velük. Segítsetek bármilyen információval. Egyszerüen módosítsátok az adatokat, írjatok üzenetet vagy e-mailt. Előre is nagyon szépen köszönjük.';
} else {
    if (userIsAdmin() || userIsEditor() || userIsSuperuser()) {
        switch (getParam("type")) {
            case "teacher": {
                $personList=$db->getPersonList("isTeacher='1'",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='1'");
                $caption ="Tanárok:".$personCount;
                break;
            }
            case "teacherdeceased": {
                $personList=$db->getPersonList("isTeacher='1' and deceasedYear is not null",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='1' and (email is not null and email <>'')");
                $caption ="Elhunyt tanárok:".$personCount;
                break;
            }
            case "teacherwithpicture": {
                $personList=$db->getPersonList("isTeacher='1' and (picture is not null and picture <>'')",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='1' and (picture is not null and picture <>'')");
                $caption ="Tanárok képpel:".$personCount;
                break;
            }
            case "teacherwithemail": {
                $personList=$db->getPersonList("isTeacher='1' and (email is not null and email <>'')",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='1' and (email is not null and email <>'')");
                $caption ="Tanárok email címmel:".$personCount;
                break;
            }
            case "teacherwithfacebook": {
                $personList=$db->getPersonList("isTeacher='1' and (facebook is not null and facebook <>'')",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='1' and (facebook is not null and facebook <>'')");
                $caption ="Tanárok facebookal:".$personCount;
                break;
            }
            case "teacherwithwikipedia": {
                $personList=$db->getPersonList("isTeacher='1' and homepage like '%wikipedia%'",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='1' and homepage like '%wikipedia%'");
                $caption ="Tanárok wikipédia oldallal:".$personCount;
                break;
            }

            case "classmate": {
                $personList=$db->getPersonList("isTeacher='0'",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='0'");
                $caption ="Diákok:".$personCount;
                break;
            }
            case "classmatedeceased": {
                $personList=$db->getPersonList("isTeacher='0' and deceasedYear is not null",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='1' and (email is not null and email <>'')");
                $caption ="Elhunyt diákok:".$personCount;
                break;
            }
            case "classmatewithpicture": {
                $personList=$db->getPersonList("isTeacher='0' and (picture is not null and picture <>'')",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='0' and (picture is not null and picture <>'')");
                $caption ="Diákok képpel:".$personCount;
                break;
            }
            case "classmatewithemail": {
                $personList=$db->getPersonList("isTeacher='0' and (email is not null and email <>'')",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='0' and (email is not null and email <>'')");
                $caption ="Diákok email címmel:".$personCount;
                break;
            }
            case "classmatewithfacebook": {
                $personList=$db->getPersonList("isTeacher='0' and (facebook is not null and facebook <>'')",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='0' and (facebook is not null and facebook <>'')");
                $caption ="Diákok facebookal:".$personCount;
                break;
            }
            case "classmatewithwikipedia": {
                $personList=$db->getPersonList("isTeacher='0' and homepage like '%wikipedia%'",20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","isTeacher='0' and homepage like '%wikipedia%'");
                $caption ="Diákok wikipédia oldallal:".$personCount;
                break;
            }
            case "nogeo": {
                $sql="(geolat='' or geolat is null) and place <>'' and place is not null and place not like 'Kolozsv%' and geolat not like '46.77191%'";
                $personList=$db->getPersonList($sql,20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person",$sql);
                $caption ="Diákok geokoordináta nélkül:".$personCount;
                break;
            }
            case "fbconnection": {
                $sql="facebookid <> '0' and facebookid is not null";
                $personList=$db->getPersonList($sql,20,20*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person",$sql);
                $caption ="Diákok facebook kapcsolattal:".$personCount;
                break;
            }
        }
    } else {
        Appl::setMessage('Ezeknek az adatoknak a megtekintése csak bejelentkezett látogatok részére lehetséges.<br/> Jelentkezz be vagy regisztráld magad mert ez az oldal nagyon jó, barátságos, ingyenes és reklámmentes!', "warning");
    }
}
Appl::setSiteTitle(" Keresés");

include("homemenu.inc.php");
include_once 'displayCards.inc.php';
?>

<div class="container-fluid">
	<?php if(sizeof($personList)>0) {?>
		<div class="well">
			<?php if (strlen($name)>0 || !isset($caption)) {?>
				Talált személyek száma:<?php echo sizeof($personList)?> <?php echo 'Keresett szó:"'.$name.'"'?>
			<?php }elseif (strlen($caption)<40) {?>
				<nav aria-label="Page navigation example">
				  <ul class="pagination">
				  	<li class="page-item"><span class="page-link" ><?php echo ($caption)?></span></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $link."0" ?>"><span class="glyphicon glyphicon-fast-backward"></span></a></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $start>0?$link.($start-1):"#" ?>"><span class="glyphicon glyphicon-step-backward"></span></a></li>
				    <li class="page-item"><a class="page-link" href="#"><?php echo 20*getIntParam("start",0)+1?></a></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $start*20<$personCount?$link.($start+1):"#" ?>"><span class="glyphicon glyphicon-step-forward"></span></a></li>
				    <li class="page-item"><a class="page-link" href="<?php echo $link.floor($personCount/20) ?>"><span class="glyphicon glyphicon-fast-forward"></span></a></li>
				  </ul>
				</nav>
			<?php } else {echo($caption);}?>
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


<?php
\maierlabs\lpfw\Appl::addJsScript('
    $( document ).ready(function() {
        if ("'.getGetParam("srcText", "").'"!="") {
            showSearchBox(true);
        }
    });
');
include("homefooter.inc.php");

?>
