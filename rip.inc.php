<?php
\maierlabs\lpfw\Appl::addCssStyle('
	.person-candle {margin:5px;}
	.person-candle>a { color: #ffbb66 }
	.rip-element {background-color: black;border-color: #ffbb66;border-width: 1px;border-style: solid;
	               margin-right: 15px;margin-bottom: 15px;box-shadow: 3px 1px 9px 2px #ffbb66;}
');

/**
 * get the list of burning candles, used in the interpreted javascript file candles.js
 * @param $id
 * @return int
 */
function getActualCandles($id) {
	global $db;
    $dbCandle = new dbDaCandle($db);
	return $dbCandle->getCandlesByPersonId($id);
}

/**
 * display person with burning candles
 * @param dbDaCandle $db
 * @param array $person
 * @param bool $showClass
 * @param bool $showDate
 */
function displayRipPerson($db,$person,$diakClass=null,$showClass=false,$showDate=false) {
	$d=$person;
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
	<div class="element rip-element" >
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
                if(showField($d,"cementery")) 	echo '<div><div>Temető:</div><div style="color: #ffbb66;">'.getFieldValue($d["cementery"])."</div></div>";
				if(showField($d,"country")) 	echo '<div><div>Ország:</div><div style="color: #ffbb66;">'.getFieldValue($d["country"])."</div></div>";
				if(showField($d,"place")) 		echo '<div><div>Város:</div><div style="color: #ffbb66;">'.getFieldValue($d["place"])."</div></div>";
			?>
	  		</div>
	  		<div id="candles<?php echo $d['id']?>" style="text-align: center;" >
	  		</div>
	  		<button class="btn btn-warning" style="margin:10px;color:black" onclick="showPersonCandle(<?php echo $d['id']?>);" ><img style="height: 25px;border-radius: 33px;" src="images/candle1.gif"/> Meggyújtotta</button>
            <?php if (($daysLeft=$db->checkLightning($d["id"],getLoggedInUserId()))!==false) {?>
    	  		<button id="light-button<?php echo $d['id']?>" class="btn btn-warning" style="margin:10px;color:black" onclick="showLightCandle(<?php echo $d['id']?>);"><img style="height: 25px;border-radius: 33px;" src="images/match.jpg"/> Meggyújt</button>
	      		<div id="light-candle<?php echo $d['id']?>"  class="well" style="display:none;margin:10px;background-color: black; color: #ffbb66;border-color: #ffbb66;">
                    Az általad meggyúhtott<br/> gyertya még <?php echo($daysLeft)?>
                    napig ég!
                    <button class="btn btn-warning" style="margin:-13px;padding:3px;color:black;float: right" onclick="hideLightCandle(<?php echo $d['id']?>);">
                        <span class="glyphicon glyphicon-remove-circle"></span>
                    </button>
                    <div style="clear: both"></div><br/>
                    Látogass el újból és gyújts új gyertyát kedves tantárod vagy osztálytársad emlékére!
                </div>
            <?php } else {?>
                <button id="light-button<?php echo $d['id']?>" class="btn btn-warning" style="margin:10px;color:black" onclick="showLightCandle(<?php echo $d['id']?>);"><img style="height: 25px;border-radius: 33px;" src="images/match.jpg"/> Meggyújt</button>
                <div id="light-candle<?php echo $d['id']?>"  class="well" style="display:none;margin:10px;background-color: black; color: #ffbb66;border-color: #ffbb66;">
                    Meggyújtok egy gyertyát
                    <button class="btn btn-warning" style="margin:-13px;padding:3px;color:black;float: right" onclick="hideLightCandle(<?php echo $d['id']?>);">
                        <span class="glyphicon glyphicon-remove-circle"></span>
                    </button>
                    <div style="clear: both"></div><br/>
                    <?php if (!userIsLoggedOn()){ ?>
                        A gyertya 2 hónapig fog égni, látogass majd megint el, és gyújtsd meg újból.<br/><br/>
                        Jelentkezz be, ha szeretnéd hogy gyertyáid <b>6</b> hónapot égjenek.
                    <?php } else {?>
                        A gyertya 6 hónapig fog égni, látogass majd megint el, és gyújtsd meg újból.<br/><br/>
                        <button class="btn btn-warning" style="margin:10px;color:black" onclick="lightCandle(<?php echo $d['id']?>,false);hideLightCandle(<?php echo $d['id']?>);">
                            <img style="height: 25px;border-radius: 33px;" src="images/match.jpg"/> Meggyújtom nevem alatt
                        </button>
                    <?php } ?>
                    <button class="btn btn-warning" style="margin:10px;color:black" onclick="lightCandle(<?php echo $d['id']?>,true);hideLightCandle(<?php echo $d['id']?>);">
                        <img style="height: 25px;border-radius: 33px;" src="images/match.jpg"/> Meggyújtom mint anonim
                    </button>
                </div>
            <?php }?>
            <div id="person-candle<?php echo $d['id']?>" class="well" style="display:none;margin:10px;background-color: black; color: #ffbb66;border-color: #ffbb66;">
                <div id="personlist<?php echo $d['id']?>"></div>
                <button class="btn btn-warning" style="margin:10px;color:black" onclick="hidePersonCandle(<?php echo $d['id']?>);">Bezár</button>
            </div>
		</div>
	</div>
<?php } ?>
