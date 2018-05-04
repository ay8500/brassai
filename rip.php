<?php
$SiteTitle="Brassai Sámuel emlék oldala"; 
$SiteDescription="Elhunyt tanáraink és diákok";
include("homemenu.php"); 
include_once 'editDiakCard.php';
?>
<style>
.person-candle {margin:5px;}
.person-candle>a { color: #ffbb66 }
</style>

<div style="margin-top:20px;padding:10px;background-color: black; color: #ffbb66;">
<h2 class="sub_title">Elhunyt tanáraink és iskolatársaink emlékére</h2>
<div class="well" style="margin:10px;background-color: black; color: #ffbb66;border-color: #ffbb66;">
<b style="font-size: 30px">Emléküket örökké őrizzük</b> 
<span style="display: inline-block;vertical-align: super;">
<form>
	<button class="btn btn-warning" style="margin:10px;color:black" name="action" value="recent">Nemrég gyújtott gyertyák</button>
	<button class="btn btn-warning" style="margin:10px;color:black" name="action" value="teacher">Tanáraink emlékére</button>
	<button class="btn btn-warning" style="margin:10px;color:black" name="action" value="person">Iskolatársaink emlékére</button>
</form>
</span>
</div>
<?php 

if (getParam("action")=="teacher")
	$personList = $db->getSortedPersonList("deceasedYear is not null and isTeacher=1");
else if (getParam("action")=="person")
	$personList = $db->getSortedPersonList("deceasedYear is not null and isTeacher<>1");
else {
	$personList = $db->getLightedCandleList();
}

foreach ($personList as $d) {
	displayRipPerson($db,$d,true);
}

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
	  			Gyertyát gyújtottak
	  			<div id="personlist<?php echo $d['id']?>"></div>
	  			<button class="btn btn-warning" style="margin:10px;color:black" onclick="hidePersonCandle(<?php echo $d['id']?>);">Bezár</button>
			</div>
		</div>
	</div>
<?php } 


?>
</div>
<script>
	function showPersonCandle(id) {
		hideLightCandle(id);
		$.ajax({
			url: "getCandleLighters.php?id="+id
		}).success(function(data) {
			$("#personlist"+id).html(data);
			$("#person-candle"+id).show();
		});
	}

	function hidePersonCandle(id) {
		$("#person-candle"+id).hide();
	}

	function showLightCandle(id) {
		hidePersonCandle(id);
		$("#light-candle"+id).show();
	}

	function hideLightCandle(id) {
		$("#light-candle"+id).hide();
	}
	function lightCandle(id) {
		candles[id] = ++candles[id];
		$.ajax({
			url: "setCandleLighter.php?id="+id
		}).success(function(data) {
			showCandles(id,candles[id]);
			$("#light-button"+id).prop("disabled",true);
		}).error (function(data) {
			alert('Nem sikerült.');
		}) ;
	}

	var candles = {
		<?php 
			$firstValue = true;
			foreach ($personList as $d) {
				if ($firstValue)
					$firstValue=false;
				else 
					echo(',');
				echo ''.$d["id"].':"'.getActualCandles($d['id']).'"';
			}
		?>
	};
	
	function showAllCandles() {
		for (var candle in candles) { 
			showCandles(candle,candles[candle]);
		}
		
	}

	function showCandles(id,candles) {
		var candleSize = candles>30?30:candles;
		var html="";
  		for ( var x = 1; x <= 9 && x<= candles; x++) {
			html +='<img src="images/candle'+(Math.floor(Math.random() * 2)+1)+'.gif" style="width: '+(candleSize*2+30)+'px;">';
		}
		$("#candles"+id).html(html);
		$("#candles"+id).prop('title', candles+' gyertya ég. Gyújts te is gyertyát emlékére.');
	}

	
	
	
</script>
<?php $showCandles=true; include ("homefooter.php");?>

