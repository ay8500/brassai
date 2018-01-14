<?php
if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) {

	//Save geo data
	if (getParam("action","")=="changegeo" && userIsLoggedOn()) {
	
		$geolat=getGetParam("geolat", "46.7719");
		$geolng=getGetParam("geolng", "23.5923");
	
		$r1 = $db->savePersonField($diak["id"], "geolat", $geolat);
		$r2 = $db->savePersonField($diak["id"], "geolng", $geolng);
		if ($r1>=0 && $r2>=0) {
			if (!userIsAdmin())
				saveLogInInfo("SaveGeo",$diak["id"],$diak["user"],"",true);
			$resultDBoperation='<div class="alert alert-success">Geokoordináták sikeresen módósítva!</div>';
			$diak["geolat"]=$geolat;
			$diak["geolng"]=$geolng;
		} else {
			$resultDBoperation='<div class="alert alert-warning">Geokoordináták módósítása nem sikerült!</div>';
		}
	}
?>	
	<script language="JavaScript" type="text/javascript">
		var diak='<b><?php echo $diak["lastname"]." ".$diak["firstname"]?></b><br />';
		diak +='<?php echo( getFieldValue($diak["address"]));?><br />';
		diak +='<?php echo( getFieldValue($diak["zipcode"]));?>';
		diak +='<?php echo( getFieldValue($diak["place"]))?><br />';
		<?php if ($diak["geolat"]!="") {?>
			var centerx = '<?php echo $diak["geolat"]?>'; 
			var centery = '<?php echo $diak["geolng"]?>';
		<?php } else {?>
			var centerx =46.771919; 
			var centery = 23.592248;
		<?php } ?>
	</script>
	
	
	<div id="map_canvas" style="width: 100%x; height: 400px;"></div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	
	<div  class="panel panel-default" style="display: block;">
		<div class="panel-heading"><b>Jelöld meg a térképen a lakhelyedet.</b> Ezt a poziciót csak az osztálytársak látják pontosan.</div>
	</div>

	<div class="input-group input-group-sl">
	Helységnév, utca, házszám keresés:	
	</div>
	<div class="input-group input-group-sl">
		<span style="min-width:110px; text-align:right" class="input-group-addon" >Cím</span>
		<input type="text" id="addres" placeholder="Kolozsvár, Brassai" value="<?php echo $diak["place"].' '.$diak["address"]?>" onKeyPress="if (window.event.keyCode == 13) {doSearch();}	" class="form-control"/>
		<div class="input-group-btn">
			<input class="btn btn-default" type="button" value="Keres" onclick="doSearch();return true;"/><br />
		</div>
	</div>
	<form action="<?PHP echo($SCRIPT_NAME); ?>" method="get" name="geo">
		<input type="hidden" value="changegeo" name="action" />
		<input type="hidden" value="<?PHP echo(getAktUserId())?>" name="uid" />
		<input type="hidden" value="<?PHP echo($tabOpen); ?>" name="tabOpen" />
		Koordináták:<br />
		<div class="input-group input-group-sl">
			<span style="min-width:110px; text-align:right" class="input-group-addon" >Szélesség</span>
			<input type="text" size="24" name="geolat" class="form-control" />
		</div>
		<div class="input-group">
			<span style="min-width:110px; text-align:right" class="input-group-addon" >Hosszúság</span>
			<input type="text" size="24" name="geolng" class="form-control" />
			<div class="input-group-btn">
				<input type="submit" class="btn btn-default" value="Geokoordinátákat módósít" title="" />
			</div>
		</div>
	</form>
<?php } else {	?>
	<div class="resultDBoperation" ><div class="alert alert-warning">Hozzáférésí jog hiánzik!</div></div>
<?php }?>	
