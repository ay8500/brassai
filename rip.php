<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/appl.class.php';
use \maierlabs\lpfw\Appl as Appl;
include_once 'tools/ltools.php';
include_once 'dbBL.class.php';
include_once  'dbDaCandle.class.php';
include_once 'rip.inc.php';

$SiteDescription="Elhunyt tanáraink és diákok";
Appl::setSiteTitle($SiteDescription);

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
	<h2 class="sub_title">Elhunyt tanáraink és iskolatársaink emlékére <?php echo $dbCandle->getCandlesByPersonId() ?> gyertya ég</h2>
	<div class="well" style="margin:10px;background-color: black; color: #ffbb66;border-color: #ffbb66;">
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
	</div>
	<?php 	
	foreach ($personList as $d) {
		displayRipPerson($dbCandle,$d,$db->getClassById($d["classID"]),true);
	}
	?>
</div>
<?php 
include 'homefooter.inc.php';



