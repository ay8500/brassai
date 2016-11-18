<?php 
$SiteTitle="A kolozsvári Brassai Sámuel véndiák zenedoboz";
$SiteDescription="Kendvenc zenénket itt lejátszhatod";
include("homemenu.php");

//Save Youtube id
if (getParam("action")=="savesong" && userIsAdmin()) {
	$db->updateSong(getIntParam("id"),getParam("link"));
}

//Get playlist
$playlist=getParam("list");

//The link
if (isset( $_GET['link'])  ) { 
	$link = $_GET['link'];
	$nlink = explode("=",$link);
	if (isset($nlink[1])) 
		$Video=$nlink[1];
	else 
		$Video=$nlink[0];
}
else 
	$Video="";

//Check if video exists	
$apiPublicKey="AIzaSyDsdHR0UNecnOH6s9OdQZhJkFpOZv02ncM";
$response = file_get_contents('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $Video . '&key=' . $apiPublicKey);
$json = json_decode($response);
//print_r($json);

?>

<div class="sub_title">Zenedoboz</div>
<div class="container-fluid">
	<div style="text-align: center;">
		<?php if (is_object($json) ) {?>
			<div class="tabEmpty"><a style="margin-bottom: 10px" class="btn btn-default" href="zenetoplista.php">Vissza a toplistához. </a></div>
			<h2><?php echo $json->title?></h2>
			<object  class="embed-responsive embed-responsive-16by9">
				<embed src="http://www.youtube.com/v/<?php echo $Video?>&hl=de_DE&fs=1&rel=0&border=1&autoplay=0&showinfo=0&playlist=<?php echo $playlist?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"  ></embed>
			</object>
		<?php } else {?>
			<div class="resultDBoperation" ><div class="alert alert-warning" >Video nem létezik! Youtube cód:<?php echo $Video?></div></div>
		<?php }?>
		<div class="tabEmpty"><a style="margin: 10px" class="btn btn-default" href="zenetoplista.php">Vissza a toplistához. </a></div>
	</div>
</div>


<?php if (userIsAdmin()) {
	$song = $db->getSongById(getIntParam("id"));
?>
	<form>
		<div class="input-group" style="margin-bottom: 10px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Zene</span>	      		
			<input type=""text class="form-control" name="song" value="<?php  echo $song["name"] ?>" />
		</div>
		<div class="input-group" style="margin-bottom: 10px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Youtube kód</span>	      		
			<input type=""text class="form-control" name="link" value="<?php  echo $song["video"] ?>">
		</div>
		<input type="hidden" name="action" value="savesong" />
		<input type="hidden" name="id" value="<?php echo getIntParam("id") ?>" />
		<div style="text-align: center;">
			<button class="btn btn-default">Kiment</button>
		</div>
	</form>
<?php } ?>


 <?php  include "homefooter.php" ?>

