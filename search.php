<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'displayOpinion.inc.php';
use \maierlabs\lpfw\Appl as Appl;

define ("ITEMSONPAGE",24);

global $db;
$personList=array();
$classList=array();
$pictureList=array();
$name="";
if (getIntParam("schoolid")!=0)
    setActSchool(getIntParam("schoolid"));
$start=getIntParam("start",0);
$link="search?type=".getParam("type")."&start=";
Appl::setSiteSubTitle("Találatok a véndiákok adatbankjában");
$fields = "person.*,concat(graduationYear,' ',name) as classText";
$join ="class on person.classID=class.id ";
$sort="lastName, firstName";
$title="Keresés";

if (null==getParam("type")) {
	$name=trim(html_entity_decode(getGetParam("srcText", "")));
	$personList=$db->searchForPerson((intval($name)>0)?'abcdefg':$name);
	$classList=$db->searchForClass($name);
	$pictureList=$db->searchForPicture($name);
} elseif ('jmlaureat'==getParam("type")) {
    $school = getActSchool();
    Appl::setSiteSubTitle($school["awardName"]." díjasok");
    $personList=$db->getPersonList("role like '%jmlaureat%'",null,null,"classText desc",$fields,$join);
    $personCount=$db->getTableCount("person","role like '%jmlaureat%'");
    $caption =$school["awardText"];
    $title = $school["awardName"]." díazottak";
} elseif ('incharge'==getParam("type")) {
    Appl::setSiteSubTitle("Osztályfelelősők");
    $personList=$db->getPersonList("role like '%editor%'",null,null,"classText desc",$fields,$join);
    $personCount=$db->getTableCount("person","role like '%editor%'");
    $caption ='Osztályfelelősők névsora. Kédésekkel osztálytalálkozokkal vagy osztálytársakkal kapcsolatban forduljatok az osztályfelelősőkhőz.';
    $title = "Osztályfelelősők";
} elseif ('unknown'==getParam("type")) {
    Appl::setSiteSubTitle("Iskolatársaink akikről sajnos nem tudunk semmit.");
    $personList=$db->getPersonList("role like '%unknown%'",null,null,"classText desc",$fields,$join);
    $personCount=$db->getTableCount("person","role like '%unknown%'");
    $caption ='Ezen a listán azok az egykori iskolatársak jelennek meg, akikről nem tudjuk mit történt velük. Segítsetek bármilyen információval. Egyszerüen módosítsátok az adatokat, írjatok üzenetet vagy e-mailt. Előre is nagyon szépen köszönjük.';
    $title ="Nem tudunk róluk";
} elseif ('bogancszurbolo'==getParam("type")) {
    unsetActSchool();
    $sql="role like '%bogancszurbolo%'";
    $personList=$db->getPersonList($sql,ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
    $personCount=$db->getTableCount("person",$sql);
    Appl::setSiteSubTitle("Bogáncs Zurboló tagok:".$personCount);
    $firstPicture= array();
    $firstPicture["file"]="images/bogancszurbolo.jpg";
    Appl::setMember("firstPicture",$firstPicture);
    $caption ='BOGÁNCS-ZURBOLÓ egyesület tagjai. Intenet elérhetőség a <a href="https://zurbolo.ro/bogancs-neptancegyuttes/">Bogáncs</a> néptáncegyüttes és a <a href="https://zurbolo.ro/zurbolo-tancegyuttes/">Zurboló</a> táncegyüttes.';
    $title ="Bogáncs Zurboló tagok:".$personCount;
} else {
    if (isUserAdmin() ) {
        switch (getParam("type")) {
            case "teacher": {
                $where = getActSchoolId()!=null?("schoolIdsAsTeacher like '%(".getActSchoolId().")%'"):"schoolIdsAsTeacher is not null";
                $personList=$db->getPersonList($where,ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person",$where);
                $caption ="Tanárok:".$personCount;
                break;
            }
            case "teacherdeceased": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is not null and deceasedYear is not null",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is not null and (email is not null and email <>'')");
                $caption ="Elhunyt tanárok:".$personCount;
                break;
            }
            case "teacherwithpicture": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is not null and (picture is not null and picture <>'')",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is not null and (picture is not null and picture <>'')");
                $caption ="Tanárok képpel:".$personCount;
                break;
            }
            case "teacherwithemail": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is not null and (email is not null and email <>'')",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is not null and (email is not null and email <>'')");
                $caption ="Tanárok email címmel:".$personCount;
                break;
            }
            case "teacherwithfacebook": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is not null and (facebook is not null and facebook <>'')",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is not null and (facebook is not null and facebook <>'')");
                $caption ="Tanárok facebookal:".$personCount;
                break;
            }
            case "teacherwithwikipedia": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is not null and wikipedia is not null and wikipedia != '' ",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is not null and wikipedia is not null and wikipedia != '' ");
                $caption ="Tanárok wikipédia oldallal:".$personCount;
                break;
            }

            case "classmate": {
                $personList=$db->getPersonList("class.graduationYear>1800",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person",getActSchoolId()!=null?("class.schoolID=".getActSchoolId()):"class.graduationYear>1800",null,$join);
                $caption ="Diákok:".$personCount;
                break;
            }
            case "classmatedeceased": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is null and deceasedYear is not null",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is null' and (email is not null and email <>'')");
                $caption ="Elhunyt diákok:".$personCount;
                break;
            }
            case "classmatewithpicture": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is null and (picture is not null and picture <>'')",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is null and (picture is not null and picture <>'')");
                $caption ="Diákok képpel:".$personCount;
                break;
            }
            case "classmatewithemail": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is null and (email is not null and email <>'')",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is null and (email is not null and email <>'')");
                $caption ="Diákok email címmel:".$personCount;
                break;
            }
            case "classmatewithfacebook": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is null and (facebook is not null and facebook <>'')",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is null and (facebook is not null and facebook <>'')");
                $caption ="Diákok facebookal:".$personCount;
                break;
            }
            case "classmatewithwikipedia": {
                $personList=$db->getPersonList("schoolIdsAsTeacher is null and wikipedia is not null and wikipedia != '' ",ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person","schoolIdsAsTeacher is null and wikipedia is not null and wikipedia != '' ");
                $caption ="Diákok wikipédia oldallal:".$personCount;
                break;
            }
            case "nogeo": {
                $sql="(geolat='' or geolat is null) and place <>'' and place is not null and place not like 'Kolozsv%'";
                $personList=$db->getPersonList($sql,ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person",$sql);
                $caption ="Diákok geokoordináta nélkül:".$personCount;
                break;
            }
            case "fbconnection": {
                $sql="facebookid <> '0' and facebookid is not null";
                $personList=$db->getPersonList($sql,ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person",$sql);
                $caption ="Diákok facebook kapcsolattal:".$personCount;
                break;
            }
            case "gender": {
                $sql="gender is null or gender =''";
                $personList=$db->getPersonList($sql,ITEMSONPAGE,ITEMSONPAGE*getIntParam("start",0),$sort,$fields,$join);
                $personCount=$db->getTableCount("person",$sql);
                $caption ="Személyek nem nélkül:".$personCount;
                break;
            }
        }
    } else {
        Appl::setMessage('Ezeknek az adatoknak a megtekintése csak bejelentkezett látogatok részére lehetséges.<br/>
            Jelentkezz be vagy regisztráld magad! Ez az oldal nagyon jó, barátságos, ingyenes és reklámmentes!', "warning");
    }
}
Appl::setSiteTitle($title);

Appl::addCssStyle('
  .page-nolink { color:gray !important; pointer-events: none; cursor: default; text-decoration: none;}
');

include("homemenu.inc.php");
include_once 'displayCards.inc.php';
?>

<div class="container-fluid">
	<?php if(sizeof($personList)>0) {?>
		<div class="well">
			<?php if (strlen($name)>0 || !isset($caption)) {?>
				Talált személyek száma:<?php echo sizeof($personList)?> <?php echo 'Keresett szó:"'.$name.'"'?>
			<?php } else {
                echo($caption);
            }
            $page = $start+1;
            $pages=round($personCount/ITEMSONPAGE + 0.5,0,);
            ?>
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item"><span class="page-link" ><?php echo "Oldalok:".$page."/".$pages ?></span></li>
                    <li class="page-item"><a class="<?php echo $page!=1?"page-link":"page-nolink"?> " href="<?php echo $link."0" ?>"><span class="glyphicon glyphicon-fast-backward"></span></a></li>
                    <li class="page-item"><a class="<?php echo $page>1?"page-link":"page-nolink"?>" href="<?php echo $link.($start-1) ?>"><span class="glyphicon glyphicon-step-backward"></span></a></li>
                    <li class="page-item"><a class="page-link" href="#"><?php echo (ITEMSONPAGE*$start+1)."-".(ITEMSONPAGE*$start+sizeof($personList)) ?></a></li>
                    <li class="page-item"><a class="<?php echo $page<$pages?"page-link":"page-nolink"?>" href="<?php echo $link.($start+1) ?>"><span class="glyphicon glyphicon-step-forward"></span></a></li>
                    <li class="page-item"><a class="<?php echo $page<$pages?"page-link":"page-nolink"?>" href="<?php echo $link.($pages-1) ?>"><span class="glyphicon glyphicon-fast-forward"></span></a></li>
                </ul>
            </nav>

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
            searchPersonAndPicture();
        }
    });
');
include("homefooter.inc.php");

?>
