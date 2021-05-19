<?php
//\maierlabs\lpfw\Appl::addJs("//maps.googleapis.com/maps/api/js?key=AIzaSyCuHI1e-fFiQz3-LfVSE2rZbHo5q8aqCOY",false,false);
//\maierlabs\lpfw\Appl::addJs("js/diakEditGeo.js");
\maierlabs\lpfw\Appl::addCss("//unpkg.com/leaflet@1.3.3/dist/leaflet.css");
\maierlabs\lpfw\Appl::addJs("https://unpkg.com/leaflet@1.3.3/dist/leaflet.js");

\maierlabs\lpfw\Appl::addCss("css/Control.OSMGeocoder.css");
\maierlabs\lpfw\Appl::addJs("js/Control.OSMGeocoder.js");

\maierlabs\lpfw\Appl::addJs("js/diakEditGeoLeaflet.js");


if (isUserAdmin() || isUserEditor() || isAktUserTheLoggedInUser()) {
	//Save geo data
	if (getParam("action","")=="changegeo" && isUserLoggedOn()) {
	
		$geolat=getGetParam("geolat", "46.7719");
		$geolng=getGetParam("geolng", "23.5923");
	
		if ($db->savePersonGeolocation($diak["id"],$geolat,$geolng)>=0) {
			if (!isUserAdmin())
                \maierlabs\lpfw\Logger::_("SaveGeo\t".getLoggedInUserId());

            \maierlabs\lpfw\Appl::setMessage('Geokoordináták sikeresen módosítva!', 'success');
			$diak["geolat"]=$geolat;
			$diak["geolng"]=$geolng;
		} else {
			\maierlabs\lpfw\Appl::setMessage('Geokoordináták módosítása nem sikerült!', 'warning');
		}
	}
}
if ( isUserAdmin() || (isUserLoggedOn() && getAktClassId()==$db->getLoggedInUserClassId()) ) {
	$xrandom=0;
	$yrandom=0;
} else {
	srand(levenshtein("123.34.56.011", $_SERVER["REMOTE_ADDR"]));
	$xrandom=rand(-5,5)/100;
	$yrandom=rand(-5,5)/100;
}

?>	
	<script language="JavaScript" type="text/javascript">
		var diak='<b><?php echo $diak["lastname"]." ".$diak["firstname"]?></b><br />';
		diak +='<?php echo( getFieldValue($diak["address"]));?><br />';
		diak +='<?php echo( getFieldValue($diak["zipcode"]));?>';
		diak +='<?php echo( getFieldValue($diak["place"]))?><br />';
		<?php if ($diak["geolat"]!="") {?>
				var centerx = '<?php echo $diak["geolat"]+$xrandom?>'; 
				var centery = '<?php echo $diak["geolng"]+$yrandom?>';
		<?php } else {?>
			var centerx =46.771919; 
			var centery = 23.592248;
		<?php } ?>
	</script>

<?php if (!(isUserAdmin() || isUserEditor() || isAktUserTheLoggedInUser())) { ?>
	<div  class="panel panel-default" style="display: block;">
		<div class="panel-heading">A pontos lakhelyet csak bejelentkezett osztálytársak látják pontosan.</div>
	</div>
<?php } ?>	
	
	<div id="map_canvas" style="width: 100%; height: 400px;"></div>
	
<?php if (isUserAdmin() || isUserEditor() || isAktUserTheLoggedInUser()) { ?>
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
			<input class="btn btn-default" type="button" value="Keres" onclick="doSearch(this);return true;"/><br />
		</div>
	</div>
	<form name="geo">
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
				<input type="submit" class="btn btn-default" value="Geokoordinátákat módosít" title="" />
			</div>
		</div>
	</form>
<?php }?>