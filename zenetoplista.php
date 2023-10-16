<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once Config::$lpfw.'htmlParser.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaSongVote.class.php';
include_once 'displayCards.inc.php';
use \maierlabs\lpfw\Appl as Appl;

global $db;
$dbSongVote = new dbDaSongVote($db);
$dbOpinion = new dbDaOpinion($db);
$db->handleClassSchoolChange(getParam("classid"),getParam("schoolid"));

$firstPicture["file"] = "images/record-player.png";
Appl::setMember("firstPicture",$firstPicture);


Appl::setSiteTitle("A véndiákok ezeket a zenéket hallgatják szívesen");
if (getActSchool()==null) {
    Appl::setSiteSubTitle('Zene toplista. Ezt hallgatják szívesen a kolzsvári véndiákok.');
} else if (getActClass()==null) {
    Appl::setSiteSubTitle('Zene toplista. Ezt hallgatják szívesen az iskola véndiákjai.');
} else {
    Appl::setSiteSubTitle('A mi osztályunk zenetoplistája. Ezeket számokat szívesen hallgatjuk.');
}

Appl::addCssStyle('
.music-filter {
    margin-top: 9px;
}
.music-filter > div > div {
    background-color: white;
    display:inline-block;
    padding: 6px;
    border-radius: 5px;
    border: 1px lightgray solid;
}
.music-filter > div > div > input {
    vertical-align:top;
}
');


//Parameter Interpret
$pinterpret = getIntParam("interpret",0);
$pnewinterpret = html_entity_decode(getParam("newinterpret",""),ENT_NOQUOTES,"UTF-8");
if (($pinterpret=="0") && ($pnewinterpret<>"" )) {
    $pinterpret=$dbSongVote->saveInterpret(["id"=>-1,"name"=>$pnewinterpret]);
    if ($pinterpret>=0)
        Appl::setMessage("Előadó sikeresen kimentve","success");
    else
        Appl::setMessage("Előadó az adatbankban már létezik! Kimentés nem volt szükséges.","warning");
}

//Parameter Song
$psong=intval(getIntParam("song", "0"));
$pnewSong = html_entity_decode(getParam("newSong"),ENT_NOQUOTES,"UTF-8");
$pnewVideo = getParam("newVideo");
$pnewLink = getParam("newLink");
$pnewYear = getIntParam("newYear",1996);
$pnewLanguage = getParam("newLanguage","");
$pnewGenre = getParam("newGenre","");
if (($psong=="0") && ($pnewSong<>"" && isUserLoggedOn() )) {
    if (getSongName($pnewVideo)!="") {
        $psong=$dbSongVote->saveSong([
                'id'=>-1,
                'interpretID'=>$pinterpret,
                'name'=>$pnewSong,
                'language'=>$pnewLanguage,
                'genre'=>$pnewGenre,
                'year'=>$pnewYear,
                'video'=>$pnewVideo,
                'link'=>$pnewLink]);
        if ($psong>=0) {
            saveVote($dbOpinion,$psong);
            $psong=0;$pinterpret=0;
        } else {
            Appl::setMessage('Zene már az adatbankban létezik, válassz újból!','warning');
            $psong=0;
        }
    } else {
        Appl::setMessage('Videó nem létezik a youtubeon! Írd be a youtoube linkből a videó anzonosítót. Lásd a pédából a sárgán megjelöt azonosítót:<br/>https://www.youtube.com/watch?v=<b style="background-color:yellow;color:black">VjBefVAKmIM</b>&list=PLigfHYFbRfpKkCJjJGhf-0WB83q0eP_fT&index=51','warning');
        $psong=0;
    }
}
if ($psong>0 && isUserLoggedOn()) {
    saveVote($dbOpinion,$psong);
    $psong=0;$pinterpret=0;
}
	
//Read voters List by ClassID
if (getActClass()!=null)
    $votersList=$dbSongVote->getVotersListByClassId(getRealId(getActClass()));
else
    $votersList=$dbSongVote->getVotersListBySchoolId(getRealId(getActSchool()));
usort($votersList, "compareAlphabetical");

$allVotesNoAnonymous=0;
foreach ($votersList as $voter) {
    if (trim($voter["firstname"])!="")
        $allVotesNoAnonymous +=$voter["count"];
}
	
include("homemenu.inc.php");
?>

<div class="container-fluid">
	<?php if ( isUserLoggedOn() ) { ?>
    <?php if (getParam("interpret")==null ) {?>
        <div style="margin:10px">
            <button class="btn btn-default" onclick="$('#newEntry').show('slow');">Új zene, vagy kedvenceim kiválasztása</button>
        </div>
    <?php } ?>
    <form action="zenetoplista">
	<div id="newEntry" <?php echo (getParam("newinterpret")==null && getParam("interpret")==null)?'style="display: none"':''?> class="panel panel-default">
		<?php if (!($pinterpret>0)) { ?>
			<div class="panel-heading">
				<span id="dbDetails">Válaszd ki az előadót</span>
			</div>
			<div class="form-group navbar-form">
				Válaszd ki a kedvenc előadód, ha nem találod a listában akkor írd be a lenti mezőbe.
 	 		</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="interpret" id="search_left">Az adatbázisból </label>
				<select id="interpret" name="interpret" size="0" onChange="this.form.newinterpret.value=this.options[this.selectedIndex].text" class="form-control">
					<option value="0">...válassz!...</option>
					<?php
						$interpretList= $dbSongVote->getInterpretList();
						foreach ($interpretList as $interpret)	{
							if ($interpret['id']==$pinterpret) $def="selected"; else $def="";
							echo('<option value="'.$interpret['id'].'" '.$def.' >'.$interpret['name'].'</option>');
						}
					?>
				</select>
			</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="newinterpret" id="search_left">vagy írj be egy újat</label>
	    	   	<input name="newinterpret" id="newinterpret" type="text" size="50" onkeyup="autoComplete(this,this.form.interpret,'text',false)" class="form-control" placeholder="Például: ABBA, Hungaria, Vangelis stb." />
	    	</div>
			<div class="form-group navbar-form navbar">
	    		<button class="btn btn-default"><span class="glyphicon glyphicon-arrow-right"></span> Tovább</button>
	    	</div>
			<?php } else {?>
			<div class="panel-heading">
				<label id="dbDetails">Válaszd ki az éneket</label>
			</div>
			<div class="form-group navbar-form">
				Válaszd ki a kedvenc énkedet, ha nem találod a listában akkor írd be a lenti mezőbe.
 	 		</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="interpret" id="search_left">Előadó</label>
	    	   	<input readonly id="interpret" class="form-control" value="<?php echo $dbSongVote->getInterpretById($pinterpret)["name"]?>"/>
	    	</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="song" id="search_left">Az adatbázisból </label>
				<select name="song" id="song" size="0" onChange="this.form.newSong.value='';this.form.newVideo.value='';" class="form-control"  >
					<option value="0">...válassz!...</option>
				  	 <?php
				  	 	$songList= $dbSongVote->getSongList($pinterpret);
						foreach ($songList as $song)
						{
							if ($song['id']==$psong) $def="selected"; else $def="";
							echo('<option value='.$song['id'].' '.$def.' >'.$song['name'].'</option>');
						}
					 ?>
				</select>
			</div>
            <div style="margin: 15px; font-weight: bold">vagy írj be egy újat</div>
            <div class="input-group" style="margin-bottom: 10px;">
                <label style="min-width:200px; text-align:right" for="newSong" class="input-group-addon" id="basic-addon1">Cím</label>
	    	   	<input name="newSong" id="newSong" type="text" size="50"  onkeyup="autoComplete(this,this.form.song,'text',false)" class="form-control" value="<?php echo $pnewSong; ?>" placeholder="Például: Love Song, Létezem, A Kör,Csókkirály"/>
	    	</div>
            <div class="input-group" style="margin-bottom: 10px;">
                <label style="min-width:200px; text-align:right" for="link" class="input-group-addon" id="basic-addon1">Megjelenési év</label>
                <input name="newYear" id="newYear" type="text" size="50" class="form-control" value="<?php echo $pnewYear; ?>" />
            </div>
            <div class="input-group" style="margin-bottom: 10px;">
                <label style="min-width:200px; text-align:right" for="link" class="input-group-addon" id="basic-addon1">Ezen a nyelven énekelnek</label>
                <select class="form-control" id="newLanguage" name="newLanguage" >
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
                <label style="min-width:200px; text-align:right" for="newGenre" class="input-group-addon" id="basic-addon1">Zenetipus</label>
                <select type="text" class="form-control" id="newGenre" name="newGenre" >
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
            <div class="input-group" style="margin-bottom: 10px;">
                <label style="min-width:200px; text-align:right" for="newVideo" class="input-group-addon" id="basic-addon1">Youtube link vagy cód</label>
		    	<input name="newVideo" id="newVideo" type="text" size="50" class="form-control" value="<?php echo $pnewVideo; ?>" placeholder="a youtube kód pl: 234fwe523_1"/>
		    </div>
			<div class="form-group navbar-form navbar">
	    	   	<span style="min-width:300px;" ></span>
	    		<button class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Ez az én kedvencem!</button>
	    		<button class="btn btn-warning" onclick="document.location.href='zenetoplista?reload';return false;"><span class="glyphicon glyphicon-remove"></span> Újból előadót választok</button>
	    	</div>
	    	 <input name="interpret" type="hidden" value="<?PHP echo($pinterpret); ?>" />
			<?php } ?>
		</div>
    </form>
    <?php
	} else {?>
        <div class="panel-heading">
            Ha szeretnél új zenét felvenni a listába, akkor kattints a "Belépés"-re és jelentkezz be. Köszönjük az új zenét!
        </div>
    <?php }

$musicFilter = isset($_COOKIE["MUSIC_FILTER"])?json_decode($_COOKIE["MUSIC_FILTER"],true):array();

$language["hu"]=isset($musicFilter["filter_hu"])?$musicFilter["filter_hu"]:true;
$language["en"]=isset($musicFilter["filter_en"])?$musicFilter["filter_en"]:true;
$language["it"]=isset($musicFilter["filter_it"])?$musicFilter["filter_it"]:true;
$language["es"]=isset($musicFilter["filter_es"])?$musicFilter["filter_es"]:true;
$language["pt"]=isset($musicFilter["filter_pt"])?$musicFilter["filter_pt"]:true;
$language["fr"]=isset($musicFilter["filter_fr"])?$musicFilter["filter_fr"]:false;
$language["int"]=isset($musicFilter["filter_int"])?$musicFilter["filter_int"]:true;
$genre["dance"]=isset($musicFilter["filter_d"])?$musicFilter["filter_d"]:true;
$genre["danceslow"]=isset($musicFilter["filter_s"])?$musicFilter["filter_s"]:true;
$genre["hardrock"]=isset($musicFilter["filter_h"])?$musicFilter["filter_h"]:false;
$genre["classic"]=isset($musicFilter["filter_c"])?$musicFilter["filter_c"]:false;
$genre["folk"]=isset($musicFilter["filter_f"])?$musicFilter["filter_f"]:false;
$genre["relax"]=isset($musicFilter["filter_r"])?$musicFilter["filter_r"]:false;
$genre["jazz"]=isset($musicFilter["filter_j"])?$musicFilter["filter_j"]:false;
$year["1960"]=isset($musicFilter["filter_60"])?$musicFilter["filter_60"]:false;
$year["1970"]=isset($musicFilter["filter_70"])?$musicFilter["filter_70"]:false;
$year["1980"]=isset($musicFilter["filter_80"])?$musicFilter["filter_80"]:true;
$year["1990"]=isset($musicFilter["filter_90"])?$musicFilter["filter_90"]:true;
$year["2000"]=isset($musicFilter["filter_00"])?$musicFilter["filter_00"]:false;
$year["2010"]=isset($musicFilter["filter_10"])?$musicFilter["filter_10"]:true;
$year["2020"]=isset($musicFilter["filter_20"])?$musicFilter["filter_20"]:false;
if (getParam("srcText","")=="")
    $topList= $dbSongVote->readTopList (getRealId(getActClass()),getLoggedInUserId(),500, $language, $genre,$year);
else
    $topList= $dbSongVote->searchForMusic(getParam("srcText"));

?>
<div class="col-sm-9">
	<div class="panel panel-default">
		<div class="panel-heading" style_notgood="background-image: url(images/tenor.gif);background-size: contain;background-blend-mode: difference;">
			<span id="dbDetails">Kiválasztva: <?php echo sizeof($topList)?> Indítsd a lejátszót! (max 100)</span><br/>
            <div style="">
                <?php if (getParam("srcText","")=="") {?>
			        <button class="btn btn-success" onclick="playBackward();"><span class="glyphicon glyphicon-play"></span> Legjobb szám elsőnek</button>
			        <button class="btn btn-warning" onclick="playForward();"><span class="glyphicon glyphicon-play"></span> Legjobb szám utoljára</button>
			        <button class="btn btn-warning" onclick="playRandom();"><span class="glyphicon glyphicon-play"></span> Véletlenszerüen</button>
                <?php } else { ?>
                    <button class="btn btn-success" onclick="playRandom();"><span class="glyphicon glyphicon-play"></span> Legjobb szám elsőnek</button>
                <?php } ?>
            </div>
            <div class="music-filter" >
                <div style="vertical-align:top;display: inline-block;margin-top: 17px;">Keresés</div>
                <div style="display: inline-block">
                    <div class="input-group" style="width:300px;margin: 3px;display: inline-table;">
                    <span class="input-group-addon" style="width:30px" onclick="showMusic($('#searchText').val());"><span
                                class="glyphicon glyphicon-search"></span></span>
                    <input class="form-control" placeholder="Zene címe vagy együttes"
                           id="searchText" value="<?php echo getGetParam("srcText", "") ?>" onkeyup="searchMusic(this);" />
                    <span class="input-group-addon" style="width:30px" onclick="showMusic(null)"><span
                          class="glyphicon glyphicon-remove"></span></span>
                    </div>
                    <button id="showMusic" class="btn btn-default" style="display:none;margin-top: -29px" onclick="showMusic($('#searchText').val());">Mutasd</button><br/>
                    <div id="searchMusic" style="display: none"><table id="musicList"></table></div>
                </div>
            </div>
            <?php if (getParam("srcText","")=="") {?>
                <div class="music-filter" >
                    <div style="display: inline-block;margin-bottom: 3px;">Nyelv</div>
                    <div style="display: inline-block">
                        <div>Magyar   <input id="filter_hu" type="checkbox" name="filter_hu" onclick="musicFilter()" <?php echo $language["hu"]===true?"checked":"" ?>/></div>
                        <div>Angol    <input id="filter_en" type="checkbox" name="filter_en" onclick="musicFilter()" <?php echo $language["en"]?"checked":"" ?>/></div>
                        <div>Olasz    <input id="filter_it" type="checkbox" name="filter_it" onclick="musicFilter()" <?php echo $language["it"]?"checked":"" ?>/></div>
                        <div>Spanyol  <input id="filter_es" type="checkbox" name="filter_es" onclick="musicFilter()" <?php echo $language["es"]?"checked":"" ?>/></div>
                        <div>Portugál <input id="filter_pt" type="checkbox" name="filter_pt" onclick="musicFilter()" <?php echo $language["pt"]?"checked":"" ?>/></div>
                        <div>Francia  <input id="filter_fr" type="checkbox" name="filter_fr" onclick="musicFilter()" <?php echo $language["fr"]?"checked":"" ?>/></div>
                        <div>Nemzetközi <input id="filter_int" type="checkbox" name="filter_int" onclick="musicFilter()" <?php echo $language["int"]?"checked":"" ?>/></div>
                    </div>
                </div>
                <div class="music-filter" >
                    <div style="display: inline-block;margin-bottom: 3px;">Műfaj</div>
                    <div style="display: inline-block">
                        <div>Tánczene       <input id="filter_d" type="checkbox" name="filter_d" onclick="musicFilter()" <?php echo $genre["dance"]!==false?"checked":"" ?>/></div>
                        <div>Lassú tánczene <input id="filter_s" type="checkbox" name="filter_s" onclick="musicFilter()" <?php echo $genre["danceslow"]!==false?"checked":"" ?>/></div>
                        <div>Hardrock       <input id="filter_h" type="checkbox" name="filter_h" onclick="musicFilter()" <?php echo $genre["hardrock"]!==false?"checked":"" ?>/></div>
                        <div>Klassikus      <input id="filter_c" type="checkbox" name="filter_c" onclick="musicFilter()" <?php echo $genre["classic"]!==false?"checked":"" ?>/></div>
                        <div>Népzene/Nóta   <input id="filter_f" type="checkbox" name="filter_f" onclick="musicFilter()" <?php echo $genre["folk"]!==false?"checked":"" ?>/></div>
                        <div>Relax          <input id="filter_r" type="checkbox" name="filter_r" onclick="musicFilter()" <?php echo $genre["relax"]!==false?"checked":"" ?>/></div>
                        <div>Jazz           <input id="filter_j" type="checkbox" name="filter_j" onclick="musicFilter()" <?php echo $genre["jazz"]!==false?"checked":"" ?>/></div>
                    </div>
                </div>
                <div class="music-filter" >
                    <div style="display: inline-block;margin-bottom: 3px;">Évtízed</div>
                    <div style="display: inline-block">
                        <div>60-as   <input id="filter_60" type="checkbox" name="filter_60" onclick="musicFilter()" <?php echo $year["1960"]!==false?"checked":"" ?>/></div>
                        <div>70-es   <input id="filter_70" type="checkbox" name="filter_70" onclick="musicFilter()" <?php echo $year["1970"]!==false?"checked":"" ?>/></div>
                        <div>80-as   <input id="filter_80" type="checkbox" name="filter_80" onclick="musicFilter()" <?php echo $year["1980"]!==false?"checked":"" ?>/></div>
                        <div>90-es   <input id="filter_90" type="checkbox" name="filter_90" onclick="musicFilter()" <?php echo $year["1990"]!==false?"checked":"" ?>/></div>
                        <div>2000-es <input id="filter_00" type="checkbox" name="filter_00" onclick="musicFilter()" <?php echo $year["2000"]!==false?"checked":"" ?>/></div>
                        <div>2010-es <input id="filter_10" type="checkbox" name="filter_10" onclick="musicFilter()" <?php echo $year["2010"]!==false?"checked":"" ?>/></div>
                        <div>2020-as <input id="filter_20" type="checkbox" name="filter_20" onclick="musicFilter()" <?php echo $year["2020"]!==false?"checked":"" ?>/></div>
                    </div>
                </div>
            <?php  } ?>
		</div>
		<div class="form-group navbar-form navbar" >
            <?php
                for ($i=0;$i<sizeof($topList);$i++) {
                    $v = $topList[$i];
                    if (isUserAdmin() && getParam("check")=="true") {
                        $v["check"] = (getSongName($v['video']) !== "");
                    }
                    displayMusic($db, $v,"change",$v["changeUserID"],$v["changeDate"],false);
                }
            ?>
        </div>
	</div>
	<?php if (isUserAdmin()) :?>
		<button onclick="document.location='zenetoplista?check=true'" class="btn btn-danger">Youtube Link vizsgálata !</button>
	<?php endif;?>
</div>


<div class="col-sm-3">
	<div class="panel panel-default">
		<div class="panel-heading">
			<span id="dbDetails">Kedvenc zene jelölések száma:<?PHP echo($allVotesNoAnonymous); ?></span>
		</div>
		<div class="form-group navbar-form navbar">
			<table>
			  <?php foreach ($votersList as $voter) {
			     	if (intval($voter["count"])>0) {?>
			     		<tr>
			     			<td> <?php echo getPersonLinkAndPicture($voter) ?></td>
                            <td style="width: 40px">&nbsp;</td>
			     			<td style="text-align:right"><?php echo $voter["count"]?></td>
			     		</tr>
			     	<?php }
			  } ?>
			</table>
		</div>
	</div>
</div>


</div>
 
<script>
const songs = [<?php
    foreach($topList as $i=>$v) {
        echo(($i!=0?',"':'"').$v['video'].'"');
        if ($i>=99)
            break;
    }
?>];

function playBackward() {
    var url="zenePlayer?listdir=előre&link="+songs[0]+"&list=";
    for (var i=1;i<songs.length;i++) {
        url =url + songs[i]+",";
    }
    window.location.href=url;
}

function playForward() {
    var url="zenePlayer?listdir=visszafele&link="+songs[songs.length-1]+"&list=";
    for (var i=songs.length-2;i>=0;i--) {
        url+=songs[i]+",";
    }
    window.location.href=url;
}

function playRandom() {
    var idx=Math.floor(Math.random()*(songs.length));
    var url="zenePlayer?listdir=véletlenszerüen&link="+songs[idx]+"&list=";
    songs[idx]="";
    for (var i=1;i<songs.length;i++) {
		idx=Math.floor(Math.random()*(songs.length));
		while (songs[idx]==="") {
		    idx=Math.floor(Math.random()*(songs.length));
		}
		url+=songs[idx]+",";
	    songs[idx]="";
    }
    window.location.href=url;
}

function autoComplete (field, select, property, forcematch) {
	var found = false;
	for (var i = 0; i < select.options.length; i++) {
	    if (select.options[i][property].toUpperCase().indexOf(field.value.toUpperCase()) == 0) {
		    found=true; break;
		}
	}
	if (found) { select.selectedIndex = i; }
	else { select.selectedIndex = -1; }
	if (field.createTextRange) {
		if (forcematch && !found) {
			field.value=field.value.substring(0,field.value.length-1); 
			return;
			}
		var cursorKeys ="8;46;37;38;39;40;33;34;35;36;45;";
		if (cursorKeys.indexOf(event.keyCode+";") == -1) {
			var r1 = field.createTextRange();
			var oldValue = r1.text;
			var newValue = found ? select.options[i][property] : oldValue;
			if (newValue != field.value) {
				field.value = newValue;
				var rNew = field.createTextRange();
				rNew.moveStart('character', oldValue.length) ;
				rNew.select();
				}
			}
		}
	}
</script>
 
 <?php 

/**
 * Get the soung title using youtube API
 * @param string $youtubeId
 * @return string
 */
 function getSongName(string $youtubeId) {
    //https://console.developers.google.com/apis/api/youtubeoembed.googleapis.com/quotas?project=skilful-works-806
 	$apiPublicKey=encrypt_decrypt("decrypt","STRGZTdISFVONExKVVhkOE1Bay9ZOVhjMmVPQnZpUE5oNi84UlBBeDJ3OGZ6aDZyY3hWTGZkT3lEMHZUS0w4Ng==");
 	try {
 	    $url='https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $youtubeId . '&key=' . $apiPublicKey;
        $response = maierlabs\lpfw\htmlParser::loadUrl($url);
        $json = json_decode($response);
        if (is_object($json))
            return $json->title;
    } catch (Exception $e) {
 	    return "";
    }
	return "";
 }

/**
 * Save song vote as favorite and send status messages to the ui
 * @param dbDaOpinion $dbOpinion
 * @param int $songId
 */
 function saveVote(dbDaOpinion $dbOpinion,int $songId)
 {
     $oldOpinion = $dbOpinion->getOpinion($songId, "music", "favorite");
     if (sizeof($oldOpinion) > 0) {
         Appl::setMessage('Ezt a zenét már megjelölted mit a kedvenced!', 'warning');
         $psong = 0;
     } else {
         $ret = $dbOpinion->setOpinion($songId, getLoggedInUserId(), "music", "favorite");
         if ($ret >= 0) {
             Appl::setMessage('Zene és a szavazatod sikeresen kimentve.', 'success');
         } else {
             Appl::setMessage('Zene kimentése sikertelen! Elnézést kérünl.', 'warning');
         }
     }
 }

Appl::addJsScript('
    var cookieFilter;
    function musicFilter() {
        cookieFilter = {};
        $("input[type=checkbox]").each(function() {
            var name = $(this).attr("name");
            cookieFilter[name]=$(this).prop("checked");        
        });
        var jsonString=JSON.stringify(cookieFilter);
        Cookie("MUSIC_FILTER",jsonString );
        var url="zenetoplista?classId='.getParam("classid","all").'";
        document.location=url;
    }
    function searchMusic(o) {
        $.ajax({
            url: "ajax/getMusicByText?text="+$(o).val(),
            type:"GET",
            success:function(data){
                if (data.length>0) {
                    $("#searchMusic").show();$("#showMusic").show();
                    var buffer="";
                    $.each(data, function(index, item){ 
                        buffer+="<tr><td><a href=\"zenePlayer?id="+item.id+"\">"+item.interpretName+":"+item.name+"</a><td></td></tr>"; 
                    }); 
                    $("#musicList").html(buffer);
                    
                } else {
                    $("#searchMusic").hide();$("#showMusic").hide();
                }
            },
            error:function() {
                $("#searchMusic").hide();
            }
        });
    }
    function showMusic(src) {
        if (src!==null)
            document.location.href="zenetoplista?srcText="+src;
        else 
            document.location.href="zenetoplista";
    }
');


include "homefooter.inc.php";
