<?PHP 	
include_once("tools/sessionManager.php"); 
include_once 'tools/appl.class.php';

$SiteTitle = "A diakok a vílág térképén";
$SiteDescription= "Merre szórórdtak szét az Brassai Sámuel véndiákok a nagyvilágban";
\maierlabs\lpfw\Appl::$subTitle="Merre szóródtak szét az osztálytársak";
/*
Appl::addJs("//maps.googleapis.com/maps/api/js?key=AIzaSyCuHI1e-fFiQz3-LfVSE2rZbHo5q8aqCOY",false,false);
Appl::addJs("js/diakMap.js");
*/
\maierlabs\lpfw\Appl::addCss("//unpkg.com/leaflet@1.3.3/dist/leaflet.css");
\maierlabs\lpfw\Appl::addJs("https://unpkg.com/leaflet@1.3.3/dist/leaflet.js");
\maierlabs\lpfw\Appl::addJs("js/diakMapLeaflet.js");

include("homemenu.php");
?>

<table class="pannel" style="width:100%">
	<tr>
		<td>
			<?php if ( !userIsLoggedOn() ) {?> 
				<div style="text-align:center;font-size:12px; margin:10px">
					Mivel a weboldal látogatója anonim a koordináták véletlenszerüen el vannak kb. 10 km el tólva. Jelenkezz be a pontos poziciók megtekintéséhez.
				</div>
			<?php } ?>
			
			<div style="text-align:center;">
				<div id="map_canvas" style="width: 100%; height: 600px; text-align:center"></div>
				<div style="text-align:center;font-size:12px; margin:10px">
					Térkép részletek: 
					<a href="javascript:zoomMap(1);">Kolozsvár</a>
					<a href="javascript:zoomMap(2);">Budapest</a>
					<a href="javascript:zoomMap(3);">Erdély</a>
					<a href="javascript:zoomMap(4);">Magyarország</a>
					<a href="javascript:zoomMap(5);">Németország</a>
					<a href="javascript:zoomMap(6);">Europa</a>
					<a href="javascript:zoomMap(7);">Az egész világ</a>
				</div>				
				<div id="txtPerson" style="text-align:center;font-size:12px; margin:10px">Osztálytárs a térképen:</div> 
			</div>
		</td>
	</tr>
	<tr><td id="status"></td></tr>
</table>
<?php include("homefooter.php");?>