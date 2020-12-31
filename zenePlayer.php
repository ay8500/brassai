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
if ( (isActionParam("savesong") || isActionParam("savesongback")) && userIsAdmin()) {
    $dbSongVote->updateSongFields(
        getIntParam("id"),
        getParam("link"),
        html_entity_decode(getParam("song"),ENT_NOQUOTES,"UTF-8"),
        getParam("language",""),
        getParam("genre",""),
        getParam("year"));
    if (isActionParam("savesongback")) {
        header("Location: zenetoplista");
    }
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

        <?php if (userIsAdmin() && getIntParam("id",-1)!=-1) {
            $song = $dbSongVote->getSongById(getIntParam("id"));
            ?>
            <div class="panel panel-default" style="padding:10px">
                <form>
                    <div class="input-group" style="margin-bottom: 10px;">
                        <label style="min-width:200px; text-align:right" for="song" class="input-group-addon" id="basic-addon1">Zene címe</label>
                        <input type="text" class="form-control" id="song" name="song" value="<?php  echo $song["name"] ?>" />
                    </div>
                    <div class="input-group" style="margin-bottom: 10px;">
                        <label style="min-width:200px; text-align:right" for="link" class="input-group-addon" id="basic-addon1">Youtube kód</label>
                        <input class="form-control" id="link" name="link" value="<?php  echo $song["video"] ?>"/>
                    </div>
                    <div class="input-group" style="margin-bottom: 10px;">
                        <label style="min-width:200px; text-align:right" for="link" class="input-group-addon" id="basic-addon1">Évszám</label>
                        <input class="form-control" id="year" name="year" value="<?php  echo intval($song["year"]) ?>"/>
                    </div>
                    <div class="input-group" style="margin-bottom: 10px;">
                        <label style="min-width:200px; text-align:right" for="link" class="input-group-addon" id="basic-addon1">Ezen a nyelven énekelnek</label>
                        <select class="form-control" id="language" name="language" >
                            <option value="">nemzetközi</option>
                            <option <?php echo $song["language"]==="hu"?'selected="selected"':''?> value="hu">Magyarul</option>
                            <option <?php echo $song["language"]==="en"?'selected="selected"':''?> value="en">Angolul</option>
                            <option <?php echo $song["language"]==="it"?'selected="selected"':''?> value="it">Olaszul</option>
                            <option <?php echo $song["language"]==="es"?'selected="selected"':''?> value="es">Spanyolul</option>
                            <option <?php echo $song["language"]==="pt"?'selected="selected"':''?> value="pt">Portugálul</option>
                            <option <?php echo $song["language"]==="fr"?'selected="selected"':''?> value="fr">Franciául</option>
                        </select>
                    </div>
                    <div class="input-group" style="margin-bottom: 10px;">
                        <label style="min-width:200px; text-align:right" for="link" class="input-group-addon" id="basic-addon1">Zenetipus</label>
                        <select type="text" class="form-control" id="genre" name="genre" >
                            <option value="">határozattlan</option>
                            <option <?php echo $song["genre"]==="dance"?'selected="selected"':''?> value="dance">Tánczene</option>
                            <option <?php echo $song["genre"]==="danceslow"?'selected="selected"':''?> value="danceslow">Lassú tánczene</option>
                            <option <?php echo $song["genre"]==="hardrock"?'selected="selected"':''?> value="hardrock">Hardrock</option>
                            <option <?php echo $song["genre"]==="classic"?'selected="selected"':''?> value="classic">Klasszikus</option>
                            <option <?php echo $song["genre"]==="folk"?'selected="selected"':''?> value="folk">Népzene/Dal</option>
                            <option <?php echo $song["genre"]==="relax"?'selected="selected"':''?> value="relax">Relax/Lazíts</option>
                            <option <?php echo $song["genre"]==="jazz"?'selected="selected"':''?> value="jazz">Jazz</option>
                        </select>
                    </div>
                    <input type="hidden" name="id" value="<?php echo getIntParam("id") ?>" />
                    <div style="text-align: center;">
                        <button name="action" value="savesong" class="btn btn-success">Kiment</button>
                        <button name="action" value="savesongback" class="btn btn-success">Kiment és vissza</button>
                    </div>
                </form>
            </div>
        <?php } ?>

        <?php if (is_object($json)&& !isset($json->error) ) {?>
			<div class="tabEmpty"><a style="margin-bottom: 10px" class="btn btn-default" href="zenetoplista">Vissza a toplistához. </a></div>
			<?php if (null==$playlist):?>
				<h2><?php echo (is_object($json)&&isset($json->title)?' '.$json->title:'')?></h2>
                <object  class="embed-responsive embed-responsive-16by9">
                    <embed src="https://www.youtube.com/v/<?php echo $Video?>&hl=de_DE&enablejsapi=0&fs=1&rel=0&border=1&autoplay=0&showinfo=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"  />
                </object>
			<?php else: ?>
				<h2>Toplista teljes lejátszása <?php echo getParam("listdir","")?></h2>
                <object  class="embed-responsive embed-responsive-16by9">
                    <embed src="https://www.youtube.com/v/<?php echo $Video?>&hl=de_DE&enablejsapi=0&fs=1&rel=0&border=1&autoplay=0&showinfo=0&playlist=<?php echo $playlist?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"  />
                </object>
			<?php endif;?>
		<?php } else {?>
			<div class="alert alert-warning" >Video nem létezik! Youtube cód:<?php echo $Video?>
                <?php if (userIsAdmin()) { print_r($json->error); } ?>
            </div>
		<?php }?>
		<div class="tabEmpty"><a style="margin: 10px" class="btn btn-default" href="zenetoplista">Vissza a toplistához. </a></div>
	</div>



</div>

 <?php include "homefooter.inc.php" ?>

