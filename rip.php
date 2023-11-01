<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaCandle.class.php';
include_once 'rip.inc.php';

use maierlabs\lpfw\Appl as Appl;
global $db;
$dbCandle = new dbDaCandle($db);

if (getParam("schoolid")=="all")
    unsetActSchool();
else
    $db->handleClassSchoolChange(getParam("classid"),getParam("schoolid"));

$picture["file"] = "images/candle3.gif";
Appl::setMember("firstPicture",$picture);

//Type of canlde list new, teacher, people
if (getParam("classid")!==NULL) {
    $class = $db->getClassById(getIntParam("classid"));
    $personList = $db->getSortedPersonList("deceasedYear is not null and classID=".$class["id"], 200, null);
    $teachers=$class["teachers"];
    if (!empty($class["headTeacherID"]))
        $teachers .=",".$class["headTeacherID"];
    if (!empty($class["secondHeadTeacherID"]))
        $teachers .=",".$class["secondHeadTeacherID"];
    $teacherList = $db->getSortedTeacherList("deceasedYear is not null and schoolIdsAsTeacher  is not null and id in(".$teachers.")", 200, null, getActSchoolId());
    $personList = array_merge($personList,$teacherList);
    $subTitle = $class["name"]." ".$class["text"] ." Tanárok és Diákok emlékeére gyújtott gyertyák";
} else if (isActionParam("teacher")) {
    $personList = $db->getSortedTeacherList("deceasedYear is not null and schoolIdsAsTeacher  is not null", 200, null, getActSchoolId());
    $subTitle = "Tanáraink emlékeére gyújtott gyertyák";
} else if (isActionParam("person")) {
    $personList = $db->getSortedPersonList("deceasedYear is not null ", 200, null, getActSchoolId());
    $subTitle = "Diákok emlékeére gyújtott gyertyák";
} else {
	$personList = $dbCandle->getLightedCandleList(getIntParam("id",null),48, getActSchoolId());
    $subTitle = "Nemrég gyújtott gyertyák";
}
$SiteDescription="Elhunyt tanáraink és diákok";
Appl::setSiteTitle($SiteDescription,$subTitle);

\maierlabs\lpfw\Appl::addJs('js/candles.js',true);


include("homemenu.inc.php");
?>

<div style="margin-top:20px;padding:10px;background-color: black; color: #ffbb66;">
    <div style="display: inline-block">
        <h2>Juhász Gyula: Consolatio</h2>
        <div style="display: inline-block; margin: 0px 25px 25px 0px;">
            Nem múlnak ők el, kik szívünkben élnek,<br />
            Hiába szállnak árnyak, álmok, évek.<br />
            Ők itt maradnak bennünk csöndesen még,<br />
            Hiszen hazánk nekünk a végtelenség.<br />
        </div><div style="display: inline-block; margin: 0px 25px 25px 0px;">
            Emlékük, mint a lámpafény az estben,<br />
            Kitündököl és ragyog egyre szebben<br />
            És melegít, mint kandalló a télben,<br />
            Derűs szelíden és örök fehéren.<br />
        </div><div style="display: inline-block">
            Szemünkben tükrözik tekintetük még<br />
            S a boldog órák drága, tiszta üdvét<br />
            Fölissza lelkünk, mint virág a napfényt<br />
            És élnek ők tovább, szűz gondolatként.<br />
        </div>
    </div>
    <div style="display: inline-block;width: 340px;">
        <object  class="embed-responsive embed-responsive-16by9" style="border-radius: 10px;">
            <embed src="https://www.youtube.com/v/UgmfzsYHqxA?enablejsapi=0&fs=1&rel=0&border=1&autoplay=0&showinfo=0&modestbranding=1&rel=0&start=13" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"  />
        </object>
    </div>
	<div class="well" style="background-color: black; color: #ffbb66;border-color: black;">
		<form>
			<b style="font-size: 20px;color: #ffbb66;">Emléküket örökké őrizzük!
                <?php if (getGetParam("id")!=null) { writePersonName($db->getPersonByID(getGetParam("id")));echo(' megemlékezései'); } ?>
            </b>
			<span style="display: inline-block;vertical-align: super;">
				<button class="btn btn-warning" style="margin:10px;color:black" name="action" value="recent">Nemrég gyújtott gyertyák</button>
				<button class="btn btn-warning" style="margin:10px;color:black" name="action" value="teacher">Tanáraink emlékére</button>
				<button class="btn btn-warning" style="margin:10px;color:black" name="action" value="person">Iskolatársaink emlékére</button>
			</span>
		</form>
        <h2 class="sub_title">
            Elhunyt tanáraink és iskolatársaink emlékére <?php echo $dbCandle->getAllCandlesCount() ?> gyertyából még <?php echo $dbCandle->getCandlesByPersonId() ?> gyertya ég
            <p>Gyújts te is gyertyákat szeretett tanáraidnál és iskolatársaidnál.</p>
        </h2>

	</div>
	<?php 	
	foreach ($personList as $d) {
		displayRipPerson($dbCandle,$d,$db->getClassById($d["classID"]),true);
	}
	?>
</div>
<?php 
include 'homefooter.inc.php';



