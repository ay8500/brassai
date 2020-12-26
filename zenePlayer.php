<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaSongVote.class.php';
global $db;
$dbSongVote = new dbDaSongVote($db);

use \maierlabs\lpfw\Appl as Appl;

//Save Youtube id
if (getParam("action")=="savesong" && userIsAdmin()) {
	$dbSongVote->updateSongFields(getIntParam("id"), getParam("link"), html_entity_decode(getParam("song"),ENT_NOQUOTES,"UTF-8"));
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
else {
	$v=$dbSongVote->getSongById(getIntParam("id"));
	if ($v!=null) {
		$Video=$v["video"];
	}
	else $Video="";
	
}
//Get the vote list for this music
$voters =$dbSongVote->getVotersListForMusicId(getIntParam("id"));

//Check if video exists
//https://console.developers.google.com/apis/api/youtubeoembed.googleapis.com/quotas?project=skilful-works-806
$apiPublicKey=encrypt_decrypt("decrypt","STRGZTdISFVONExKVVhkOE1Bay9ZOVhjMmVPQnZpUE5oNi84UlBBeDJ3OGZ6aDZyY3hWTGZkT3lEMHZUS0w4Ng==");
$response = maierlabs\lpfw\htmlParser::loadUrl('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $Video . '&key=' . $apiPublicKey);
$json = json_decode($response);
//print_r($json);

$Title="Zenedoboz".(is_object($json)&&isset($json->title)?': '.$json->title:'');
$SiteDescription="Kendvenc zenénket itt lejátszhatod";
Appl::setSiteTitle($Title,$Title,$SiteDescription);
include("homemenu.inc.php");
?>

<div class="container-fluid">
	<div style="text-align: center;">
		<?php if (sizeof($voters)>0) :?>
			<div class="panel panel-default" style="margin-top: 15px">
				<div class="panel-heading" >Akiknek tetszik ez a zene</div>
				<div class="panel-body"><div style="display:inline">
					<?php foreach ($voters as $voter) {?>
						<div class="personbox"><?php writePersonLinkAndPicture($db->getPersonByID($voter["personid"]))?></div>
					<?php }?>
				</div></div>
			</div>
		<?php endif;?>
		<?php if (is_object($json)&& !isset($json->error) ) {?>
			<div class="tabEmpty"><a style="margin-bottom: 10px" class="btn btn-default" href="zenetoplista">Vissza a toplistához. </a></div>
			<?php if (null==$playlist):?>
				<h2>
                    <?php //echo (is_object($json)&&isset($json->author_name)?' '.$json->author_name:'')?>
                    <?php echo (is_object($json)&&isset($json->title)?' '.$json->title:'')?>
                </h2>
			<?php else: ?>
				<h2>Toplista teljes lejátszása <?php echo getParam("listdir","")?></h2>
			<?php endif;?>
            <object  class="embed-responsive embed-responsive-16by9">
                <embed src="https://www.youtube.com/v/<?php echo $Video?>&hl=de_DE&enablejsapi=0&fs=1&rel=0&border=1&autoplay=0&showinfo=0&playlist=<?php echo $playlist?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"  />
            </object>
            <?php //echo (is_object($json)&&isset($json->html)?$json->html:'')?>
		<?php } else {?>
			<div class="alert alert-warning" >Video nem létezik! Youtube cód:<?php echo $Video?>
                <?php if (userIsAdmin()) { print_r($json->error); } ?>
            </div>
		<?php }?>
		<div class="tabEmpty"><a style="margin: 10px" class="btn btn-default" href="zenetoplista">Vissza a toplistához. </a></div>
	</div>
</div>


<?php if (userIsAdmin() && getIntParam("id",-1)!=-1) {
	$song = $dbSongVote->getSongById(getIntParam("id"));
?>
	<div class="panel panel-default" style="margin: 15px; padding:10px">
	<form>
		<div class="input-group" style="margin-bottom: 10px;">
			<label style="min-width:110px; text-align:right" for="song" class="input-group-addon" id="basic-addon1">Zene</label>
			<input type="text" class="form-control" id="song" name="song" value="<?php  echo $song["name"] ?>" />
		</div>
		<div class="input-group" style="margin-bottom: 10px;">
			<label style="min-width:110px; text-align:right" for="link" class="input-group-addon" id="basic-addon1">Youtube kód</label>
			<input type="text" class="form-control" id="link" name="link" value="<?php  echo $song["video"] ?>">
		</div>
		<input type="hidden" name="action" value="savesong" />
		<input type="hidden" name="id" value="<?php echo getIntParam("id") ?>" />
		<div style="text-align: center;">
			<button class="btn btn-success">Kiment</button>
		</div>
	</form>
	</div>
<?php } ?>


 <?php include "homefooter.inc.php" ?>

