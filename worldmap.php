<?PHP
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';

use maierlabs\lpfw\Appl;
global $db;

$title = "A véndiakok a vílág térképén";
$sTitle = "Merre szóródtak szét a kolozsvári diákok";
$db->handleClassSchoolChange(getParam("classid"),getParam("schoolid"));
if (getActSchoolId()!=null)
    $sTitle = "Merre szóródtak szét iskolánk diákjai";
if (getActClass()!=null)
    $sTitle = "Merre szóródtak szét az osztályunk diákjai";
Appl::setSiteTitle($sTitle,$sTitle);

Appl::addCss("//unpkg.com/leaflet@1.3.3/dist/leaflet.css");
Appl::addJs("https://unpkg.com/leaflet@1.3.3/dist/leaflet.js");

Appl::addCss("css/Control.OSMGeocoder.css");
Appl::addJs("js/Control.OSMGeocoder.js");


Appl::addJs("js/diakMapLeaflet.js");
Appl::addCssStyle('
#zoom>a>span>img{
    height:30px;
    border-radius:3px;
}
');

include("homemenu.inc.php");
?>

<table class="pannel" style="width:100%">
	<tr>
		<td>
			<?php if ( !isUserLoggedOn() ) {?>
				<div style="text-align:center;font-size:12px; margin:10px">
					A koordináták véletlenszerüen el vannak néhány kilométerrel tólva. Jelenkezz be a pontos poziciók megtekintéséhez.
				</div>
			<?php } ?>
			
			<div style="text-align:center;">
				<div id="map_canvas" style="width: 100%; height: 600px; text-align:center"></div>
				<div style="text-align:center;font-size:12px; margin:10px" id="zoom">
					Térkép részletek: 
					<a class="btn btn-default" href="javascript:zoomMap(1);"><span><img src="images/kolozsvar.png"></span> Kolozsvár</a>
					<a class="btn btn-default" href="javascript:zoomMap(2);"><span><img src="images/budapest.jpg"></span> Budapest</a>
					<a class="btn btn-default" href="javascript:zoomMap(3);"><span><img src="images/erdely.png"></span> Erdély</a>
					<a class="btn btn-default" href="javascript:zoomMap(4);"><span><img src="images/magyarcimer.jpg"></span> Magyarország</a>
					<a class="btn btn-default" href="javascript:zoomMap(5);"><span><img src="images/deutschland.png"></span> Németország</a>
					<a class="btn btn-default" href="javascript:zoomMap(6);"><span><img src="images/europa.png"></span> Europa</a>
					<a class="btn btn-default" href="javascript:zoomMap(7);"><span><img src="images/world.png"></span> Az egész világ</a>
				</div>				
				<div id="txtPerson" style="text-align:center;font-size:12px; margin:10px">Személyek a térképen:</div>
			</div>
		</td>
	</tr>
	<tr><td id="status"></td></tr>
</table>
<?php include("homefooter.inc.php");?>