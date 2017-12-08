<?PHP 	
include_once("tools/sessionManager.php"); 
//Change scool year and class if parameters are there 
if (isset($_GET['scoolYear'])) {
	$_SESSION['scoolYear']=$_GET['scoolYear'];
} 
if (isset($_GET['scoolClass']))  {
	$_SESSION['scoolClass']=$_GET['scoolClass'];	
}

$SiteTitle = "A diakok a vílág térképén";
$SiteDescription= "Merre szórórdtak szét az Brassai Sámuel véndiákok a nagyvilágban";
$googleMap = true;
include("homemenu.php");

?>


<h2 class="sub_title">Merre szóródtak szét az osztálytársak:</h2>
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