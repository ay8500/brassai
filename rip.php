<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/appl.class.php';
use \maierlabs\lpfw\Appl as Appl;
include_once 'tools/ltools.php';
include_once 'data.php';

$SiteDescription="Elhunyt tanáraink és diákok";
Appl::setSiteTitle($SiteDescription);

\maierlabs\lpfw\Appl::addCssStyle('
	.person-candle {margin:5px;}
	.person-candle>a { color: #ffbb66 }
');

//Type of canlde list new, teacher, people
if (isActionParam("teacher"))
	$personList = $db->getSortedPersonList("deceasedYear is not null and isTeacher=1");
else if (isActionParam("person"))
	$personList = $db->getSortedPersonList("deceasedYear is not null and isTeacher<>1");
else {
	$personList = $db->getLightedCandleList(getIntParam("id",null));
}
\maierlabs\lpfw\Appl::addJs('js/candles.js',true);
include("homemenu.php"); 
?>

<div style="margin-top:20px;padding:10px;background-color: black; color: #ffbb66;">
	<h2 class="sub_title">Elhunyt tanáraink és iskolatársaink emlékére <?php echo $db->getCandlesByPersonId() ?> gyertya ég</h2>
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
		displayRipPerson($db,$d,true);
	}
	?>
</div>
<?php 
include 'homefooter.php';

function getActualCandles($id) {
	global $db;
	return $db->getCandlesByPersonId($id);
}

function displayRipPerson($db,$person,$showClass=false,$showDate=false) {
	$d=$person;
	$disabled =$db->checkLightning($d["id"],getLoggedInUserId())?'':"disabled=disabled";
	if ($d["id"]!=-1) {
		if (userIsLoggedOn() || isLocalhost()) {
			$personLink="editDiak.php?uid=".$d["id"];
		} else {
			$personLink=getPersonLink($d["lastname"],$d["firstname"])."-".$d["id"];
		}
	} else {
		$personLink="javascript:alert('Sajnos erről a személyről nincsenek adatok.');";
	}
	//mini icon
	if (isset($person["picture"]) && $person["picture"]!="")
		$rstyle=' diak_image_medium';
		else {
			$rstyle=' diak_image_empty_rip';
		}
		?>
	<div class="element" style="background-color: black;border-color: #ffbb66;border-width: 1px;border-style: solid;">
		<div style="display: inline-block; ">
			<a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>" style="display:inline-block;">
				<div>
					<img src="<?php echo getPersonPicture($d)?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="<?php echo $rstyle?>" />
					<?php if (isset($d["deceasedYear"]) && intval($d["deceasedYear"])>=0) {?>
						<div style="background-color: black;color: #ffbb66;;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">
							<?php echo intval($d["deceasedYear"])==0?"†":"† ".intval($d["deceasedYear"]); ?>
						</div>
					<?php }?>
				</div>
			</a>
		</div>
		<div style="width: -webkit-fill-available; display: inline-block;max-width:310px;min-width:200px; vertical-align: top;margin-bottom:10px;">
			<a href="<?php echo $personLink?>"><h4 style="color: #ffbb66;"><?php echo getPersonName($d);?></h4></a>
			<?php if($showClass) {?>
				<?php if ($d["isTeacher"]==1) { ?>
					<h5>Tanár
					<?php if (isset($d["function"])) { echo $d["function"]; }?></h5>
				<?php } else { 
					$diakClass = $db->getClassById($d["classID"]);
					$classText = getClassName($diakClass);
					if (isPersonGuest($d)==1) {
						if ($d["classID"]!=0) 
							echo '<h5 style="color: #ffbb66;">Jó barát:<a style="color: #ffbb66;"href="hometable.php?classid='.$d["classID"].'">'.$classText.'</a></h5>';
						else
							echo '<h5 style="color: #ffbb66;">Vendég:<a style="color: #ffbb66;"href="hometable.php?classid='.$d["classID"].'">'.$classText.'</a></h5>';
					} else {
						if (null!=$diakClass)
							echo '<h5 style="color: #ffbb66;">Véndiák:<a style="color: #ffbb66;"href="hometable.php?classid='.$d["classID"].'">'.$classText.'</a></h5>';
						else 
							echo('<h5 style="color: #ffbb66;">Véndiák:'.-1 * $d["classID"].'</h5>'); //Graduation year for laurates that are not in the db
					}
				} ?>
			<?php } ?>
			<div class="fields" style="color: #ffbb66;"> 
			<?php 
				if(showField($d,"country")) 	echo '<div><div>Ország:</div><div style="color: #ffbb66;">'.getFieldValue($d["country"])."</div></div>";
				if(showField($d,"place")) 		echo '<div><div>Város:</div><div style="color: #ffbb66;">'.getFieldValue($d["place"])."</div></div>";
			?>
	  		</div>
	  		<div id="candles<?php echo $d['id']?>" style="text-align: center;" >
	  		</div>
	  		<button class="btn btn-warning" style="margin:10px;color:black" onclick="showPersonCandle(<?php echo $d['id']?>);" ><img style="height: 25px;border-radius: 33px;" src="images/candle1.gif"/> Meggyújtotta</button>
	  		<button id="light-button<?php echo $d['id']?>" <?php echo $disabled ?> class="btn btn-warning" style="margin:10px;color:black" onclick="showLightCandle(<?php echo $d['id']?>);"><img style="height: 25px;border-radius: 33px;" src="images/match.jpg"/> Meggyújt</button>
	  		<div id="light-candle<?php echo $d['id']?>"  class="well" style="display:none;margin:10px;background-color: black; color: #ffbb66;border-color: #ffbb66;">
	  			Meggyújtok egy gyertyát<br/><br/>
	  			A gyertya 2 hónapig fog égni, látogass majd megint el, és gyújtsd meg újból.<br/><br/>
	  			<?php if (!userIsLoggedOn()){ ?>
	  				<b>Nem vagy bejelentkezve</b>, a gyertya anonim név alatt fog égdegélni. Jelentkezz be ha szeretnéd mindenki tudja, hogy te gyújtottad ezt a gyertyát.
	  			<?php }?>
	  			<button class="btn btn-warning" style="margin:10px;color:black" onclick="lightCandle(<?php echo $d['id']?>);hideLightCandle(<?php echo $d['id']?>);"><img style="height: 25px;border-radius: 33px;" src="images/match.jpg"/> Meggyújtom</button>
			</div>
	  		<div id="person-candle<?php echo $d['id']?>" class="well" style="display:none;margin:10px;background-color: black; color: #ffbb66;border-color: #ffbb66;">
	  			<div id="personlist<?php echo $d['id']?>"></div>
	  			<button class="btn btn-warning" style="margin:10px;color:black" onclick="hidePersonCandle(<?php echo $d['id']?>);">Bezár</button>
			</div>
		</div>
	</div>
<?php } ?>


