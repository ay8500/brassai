<?php
use maierlabs\lpfw\Appl as Appl;

Appl::addCssStyle('
	.person-candle {margin:5px;}
	.person-candle>a { color: #ffbb66 }
	.rip-element {background-color: black;border-color: #ffbb66;border-width: 1px;border-style: solid;text-align:left;
	               margin-right: 15px;margin-bottom: 15px;box-shadow: 3px 1px 9px 2px #ffbb66;min-height:280px;}
	.rip-element-extended {min-height:480px;width:900px;}
	.popupr {display:none; margin:5px; background-color:black; color:#ffbb66; border-color:#ffbb66;max-width: 700px;}
	.popupt {display: inline-block;max-width:330px;min-width:200px; color:#ffbb66;vertical-align: top;margin-bottom:10px;}
	.popupt-extended { max-width: 570px;}
	.popupclose {margin:-15px; padding:7px; color:black; float:right; z-index:1100;}
	.popupbtn { color:black; z-index:1100;}
	.riptable  {min-width:400px; margin: 20px;}
	.riptable td:nth-child(2) {text-decoration: line-through;}
	.riptable tr:nth-child(1) td {font-weight: bold;text-decoration:none;}
	.riptable tr:nth-child(1) {border-bottom: 1px solid;border-top: 1px solid;}
	.riptable button {background-color: black;color: #ffbb66;border: 3px solid;border-radius: 18px;width: 28px;font-weight: bolder;}
	
	.deco-frt {position: absolute;right:0px;top:0px;}
	.deco-frb {position: absolute;right:-7px;bottom:-19px}
	
	.riphr {height: 35px}
	
	@media screen and (max-width: 500px) {
	    .popupt {margin-left: 40px;}
	    .rip-element-extended {min-height:480px;width:100%;box-shadow: none;border:none;border-top: solid 1px;}
	    .rip-element {width:100%;box-shadow: none;border:none; border-top:solid 1px;}
    	.riptable  {min-width: auto;margin: 0px;}
    	.riphr {height: 65px}
    	.deco-frb {width:200px}
    }
}
');

/**
 * get the list of burning candles, used in the interpreted javascript file candles.js
 * @param $id
 * @return int
 */
function getActualCandles($id) {
    if (intval($id<=0))
        return 0;
	global $db;
    $dbCandle = new dbDaCandle($db);
	return $dbCandle->getCandlesByPersonId($id);
}

/**
 * get decorations, used in the interpreted javascript file candles.js
 * @param $id
 * @return int
 */
function getActualDecorations($id) {
    if (intval($id<=0))
        return 0;
    global $db;
    $dbCandle = new dbDaCandle($db);
    return $dbCandle->getDecorationsByPersonId($id);
}

function getTitle($o) {
    $ret = "";
    if (isset($o->person)) {
        $ret .=$o->person["lastname"]." ".$o->person["firstname"];
    }
    if (isset($o->text)) {
        $ret .=': '.$o->text;
    }
    echo ' title="'.$ret.'" ';
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
    $decoration = getActualDecorations($d["id"]);
	if ($d["id"]>0) {
        $personLink=getPersonLink($d["lastname"],$d["firstname"])."-".$d["id"];
	} else {
		$personLink="javascript:alert('Sajnos erről a személyről nincsenek adatok.');";
	}
	//mini icon
	if (isset($person["picture"]) && $person["picture"]!="")
		$rstyle=$decoration->extended?' diak_image_original':' diak_image_medium';
		else {
			$rstyle=' diak_image_empty_rip';
		}
		?>
	<span class="element rip-element <?php echo $decoration->extended?"rip-element-extended":""?>" style="position: relative">
        <div style="display: inline-block; ">
			<a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>" style="display:inline-block;">
				<div>
					<img src="<?php echo getPersonPicture($d,$decoration->extended)?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="<?php echo $rstyle?>" />
					<?php if (isset($d["deceasedYear"]) && intval($d["deceasedYear"])>=0) {?>
                        <div style="background-color: black;color: #ffbb66;;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">
                            <?php if (isset($d["birthyear"]) && intval($d["birthyear"])>1800) { echo '* '.intval($d["birthyear"]).'&nbsp;'; } ?>
                            <?php echo intval($d["deceasedYear"])==0?"†":"† ".intval($d["deceasedYear"]); ?>
						</div>
                    <?php }?>
				</div>
			</a>
		</div>

		<div class="popupt <?php echo $decoration->extended?"popupt-extended":"" ?>">
            <a href="<?php echo $personLink?>"><h4 style="color: #ffbb66;"><?php echo getPersonName($d);?></h4></a>
            <?php if (getActSchoolId()==null && isset($d["schoolID"])) {?>
                <div style="margin-top: -13px"><?php echo getSchoolNameById($d["schoolID"]) ?></div>
            <?php } ?>
			<?php if($showClass) {?>
				<?php if ($d["schoolIdsAsTeacher"]!=NULL) { ?>
					<h5>Tanár
					<?php if (isset($d["function"])) { echo $d["function"]; }?></h5>
				<?php } else {
					$classText = getSchoolClassName($diakClass);
					if (isUserGuest($d)) {
						if ($d["classID"]!=0)
							echo '<h5 style="color: #ffbb66;">Jó barát:<a style="color: #ffbb66;" href="hometable?classid='.$d["classID"].'">'.$classText.'</a></h5>';
						else
							echo '<h5 style="color: #ffbb66;">Vendég:<a style="color: #ffbb66;" href="hometable?classid='.$d["classID"].'">'.$classText.'</a></h5>';
					} else {
						if (null!=$diakClass)
							echo '<h5 style="color: #ffbb66;">Véndiák:<a style="color: #ffbb66;" href="hometable?classid='.$d["classID"].'">'.$classText.'</a></h5>';
						else
							echo('<h5 style="color: #ffbb66;">Véndiák:'.-1 * $d["classID"].'</h5>'); //Graduation year for laurates that are not in the db
					}
				} ?>
			<?php } ?>
			<div class="fields" style="color: #ffbb66;">
			<?php
                if(showField($d,"cementery")) echo '<div><div>Temető:</div><div style="color: #ffbb66;">'.getFieldValue($d["cementery"])."</div></div>";
				if(showField($d,"country")) 	echo '<div><div>Ország:</div><div style="color: #ffbb66;">'.getFieldValue($d["country"])."</div></div>";
				if(showField($d,"place")) 	echo '<div><div>Város:</div><div style="color: #ffbb66;">'.getFieldValue($d["place"])."</div></div>";
			?>
	  		</div>
	  		<div id="candles<?php echo $d['id']?>" style="text-align: center;" >
	  		</div>
        </div>

        <div>
	  		<button class="btn btn-warning" style="margin:3px;color:black" onclick="showPersonCandle(<?php echo $d['id']?>);" >Látogatók</button>
            <button class="btn btn-warning" style="margin:3px;color:black" onclick="showLightCandle(<?php echo $d['id']?>);" id="light-button<?php echo $d['id']?>"><img style="height: 25px;border-radius: 33px;" src="images/match.jpg"  alt="Meggyújt"/> Gyertyát gyújt</button>
	  		<button class="btn btn-warning" style="margin:3px;color:black" onclick="showFlowers(<?php echo $d['id']?>);" ><img style="height: 25px;border-radius: 33px;" src="images/flower.png"  alt="Virágot teszek"/> Megemlékezés / virágok</button>
	      	<div id="light-candle<?php echo $d['id']?>"  class="well popupr" >
                <div id="lightcandle<?php echo $d['id']?>"></div>
            </div>
            <div id="person-flowers<?php echo $d['id']?>"  class="well popupr">
                <div id="personflower<?php echo $d['id']?>"></div>
            </div>
            <div id="person-candle<?php echo $d['id']?>" class="well popupr">
                <div id="personcandle<?php echo $d['id']?>"></div>
            </div>
            <div class="riphr"></div>
		</div>

        <?php  if ($decoration->flowerRightTop->count>0) { //Csokor jobboldalt fent?>
            <span <?php getTitle($decoration->flowerRightTop)?>>
                <span class="deco-frt"><img style="height:<?php echo $decoration->extended?325:230?>px;" src="images/flower_right_top.png" /></span></span>
        <?php } ?>
        <?php if ($decoration->flowerRightBottom->count>0) { //Csokor jobboldalt lent ?>
            <span <?php getTitle($decoration->flowerRightBottom)?>>
                <span class="deco-frb"><img style="height:<?php echo $decoration->extended?250:120?>px;" src="images/flower_right_bottom.png" /></span></span>
        <?php } ?>
        <?php if ($decoration->flowerLeft->count>0) { // Csokor baloldalt?>
            <span <?php getTitle($decoration->flowerLeft)?>>
                <span style="position: absolute;left:-37px;top:0px"><img style="height:180px;" src="images/flower_left.png"></span>
                <?php  if ($decoration->flowerLeft->count>1) { ?>
                    <span style="position: absolute;left:-37px;top:160px"><img style="height:180px;" src="images/flower_left.png"></span>
                <?php } if ($decoration->flowerLeft->count>2) { ?>
                    <span style="position: absolute;left:-37px;top:320px"><img style="height:180px;" src="images/flower_left.png"></span>
                <?php } ?></span>
        <?php } ?>
        <?php if ($decoration->rosesDown->count>0) { //Rozsák lent max 7 ?>
            <span <?php getTitle($decoration->rosesDown)?>>
                <span style="position: absolute;left:0px;bottom:0px"><img style="height:57px;" src="images/flower_bottom.png"></span>
                <?php if ($decoration->rosesDown->count>1) { ?>
                    <span style="position: absolute;left:60px;bottom:-6px"><img style="height:43px;" src="images/flower_bottom.png"></span>
                <?php } if ($decoration->rosesDown->count>2) { ?>
                    <span style="position: absolute;left:110px;bottom:0px"><img style="height:43px;" src="images/flower_bottom.png"></span>
                <?php } if ($decoration->rosesDown->count>3) { ?>
                    <span style="position: absolute;left:160px;bottom:-4px"><img style="height:53px;" src="images/flower_bottom.png"></span>
                <?php } if ($decoration->rosesDown->count>4) { ?>
                    <span style="position: absolute;left:210px;bottom:-6px"><img style="height:43px;" src="images/flower_bottom.png"></span>
                <?php } if ($decoration->rosesDown->count>5) { ?>
                    <span style="position: absolute;left:270px;bottom:0px"><img style="height:43px;" src="images/flower_bottom.png"></span>
                <?php } if ($decoration->rosesDown->count>6) { ?>
                    <span style="position: absolute;left:320px;bottom:-6px"><img style="height:33px;" src="images/flower_bottom.png"></span>
                <?php } ?></span>
        <?php } ?>
        <?php if ($decoration->rosesUp->count>0) { // Rózsák fent max 4 ?>
            <span <?php getTitle($decoration->rosesDown)?>>
                <span style="position: absolute;right:51px;top:-7px;z-index: "><img style="height:38px;transform: rotate(186deg);" src="images/flower.png"></span>
                <?php  if ($decoration->rosesUp->count>1) { ?>
                    <span style="position: absolute;right:81px;top:-7px"><img style="height:41px;transform: rotate(166deg);" src="images/flower.png"></span>
                <?php } if ($decoration->rosesUp->count>2) { ?>
                    <span style="position: absolute;right:121px;top:-7px"><img style="height:45px;transform: rotate(180deg);" src="images/flower.png"></span>
                <?php } if ($decoration->rosesUp->count>3) { ?>
                    <span style="position: absolute;right:161px;top:-7px"><img style="height:38px;transform: rotate(200deg);" src="images/flower.png"></span>
                <?php } ?></span>
        <?php } ?>

        <?php
            if ($decoration->extended) {
                Appl::addCssStyle('
                    #candles'.$d['id'].' img {width:130px !important;}
                ');
            }
        ?>

	</span>
<?php } ?>
