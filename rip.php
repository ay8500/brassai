<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
use \maierlabs\lpfw\Appl as Appl;
include_once 'dbBL.class.php';
include_once 'dbDaCandle.class.php';
include_once 'rip.inc.php';
global $db;
$SiteDescription="Elhunyt tanáraink és diákok";
Appl::setSiteTitle($SiteDescription);
$db->handleClassSchoolChange(getParam("classid"),getParam("schoolid"));
$picture["file"] = "images/candle3.gif";
\maierlabs\lpfw\Appl::setMember("firstPicture",$picture);

$dbCandle = new dbDaCandle($db);

//Type of canlde list new, teacher, people
if (isActionParam("teacher"))
	$personList = $db->getSortedPersonList("deceasedYear is not null and isTeacher=1");
else if (isActionParam("person"))
	$personList = $db->getSortedPersonList("deceasedYear is not null and isTeacher<>1");
else {
	$personList = $dbCandle->getLightedCandleList(getIntParam("id",null));
}
\maierlabs\lpfw\Appl::addJs('js/candles.js',true);


include("homemenu.inc.php");
?>

<div style="margin-top:20px;padding:10px;background-color: black; color: #ffbb66;">
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
        <h2 class="sub_title">Elhunyt tanáraink és iskolatársaink emlékére <?php echo $dbCandle->getCandlesByPersonId() ?> gyertya ég</h2>
	</div>
	<?php 	
	foreach ($personList as $d) {
		displayRipPerson($dbCandle,$d,$db->getClassById($d["classID"]),true);
	}
	?>
</div>
<?php 
include 'homefooter.inc.php';



