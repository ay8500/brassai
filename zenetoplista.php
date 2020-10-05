<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once Config::$lpfw.'htmlParser.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaSongVote.class.php';
include_once 'displayCards.inc.php';

$dbSongVote = new dbDaSongVote($db);
$dbOpinion = new dbDaOpinion($db);
$db->handleClassSchoolChange(getParam("classid"),getParam("schoolid"));

use \maierlabs\lpfw\Appl as Appl;

Appl::setSiteTitle("A véndiákok ezt hallgatják szívesen");
if (getAktClassId()==-1) {
    Appl::setSiteSubTitle('Zene toplista. Ezt hallgatják az iskola véndiákjai szívesen.');
} else {
    Appl::setSiteSubTitle('A mi osztályunk zenetoplistája. Ezt hallgatjuk mi szívesen.');
} 

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
if (($psong=="0") && ($pnewSong<>"" && userIsLoggedOn() )) {
    if (getSongName($pnewVideo)!="") {
        $psong=$dbSongVote->saveSong([
                'id'=>-1,
                'interpretID'=>$pinterpret,
                'name'=>$pnewSong,
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
        Appl::setMessage('Videó nem létezik a youtubeon! Írd be a youtoube linkből a videó anzonosítót. Lásd a pédából a sárgán megjelöt azonosítót:<br/>https://www.youtube.com/watch?v=<b style="background-color:yellow;color:black">VjBefVAKmIM</b>&list=PLigfHYFbRfpKkCJjJGhf-0WB83q0eP_fT&index=51'.'warning');
        $psong=0;
    }
}
if ($psong>0 && userIsLoggedOn()) {
    saveVote($dbOpinion,$psong);
    $psong=0;$pinterpret=0;
}
	
//Read voters List by ClassID
if (getAktClassId()!=-1)
    $votersList=$dbSongVote->getVotersListByClassId(getRealId(getAktClass()));
else
    $votersList=$dbSongVote->getVotersListBySchoolId(getRealId(getAktSchool()));
usort($votersList, "compareAlphabetical");

$allVotesNoAnonymous=0;
foreach ($votersList as $voter) {
    if (trim($voter["firstname"])!="")
        $allVotesNoAnonymous +=$voter["count"];
}
	
include("homemenu.inc.php");
?>

<div class="container-fluid">
	<?php if ( userIsLoggedOn() ) { ?>
	<form action="zenetoplista">
	<div class="panel panel-default">
		<?php if (!($pinterpret>0)) { ?>
			<div class="panel-heading">
				<label id="dbDetails">Szavazat: Válaszd ki az előadót</label> 
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
	    	   	<label style="min-width:300px;" for="newinterpret" id="search_left">vagy írj be egy újat <br />Például: ABBA, Hungaria, Vangelis stb.</label>
	    	   	<input name="newinterpret" id="newinterpret" type="text" size="50" onkeyup="autoComplete(this,this.form.interpret,'text',false)" class="form-control" />
	    	</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" ></label>
	    		<button class="btn btn-default"><span class="glyphicon glyphicon-arrow-right"></span> Tovább</button>
	    	</div>
			<?php } else {?> 
			<div class="panel-heading">
				<label id="dbDetails">Szavazat: Válaszd ki az éneket</label> 
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
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="newSong" >vagy írj be egy újat<br/>Például: Létezem, A Kör,Csókkirály  stb.</label>
	    	   	<input name="newSong" id="newSong" type="text" size="50"  onkeyup="autoComplete(this,this.form.song,'text',false)" class="form-control" value="<?php echo $pnewSong; ?>" />
	    	</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="newVideo" >Youtube link vagy cód</label>
		    	<input name="newVideo" id="newVideo" type="text" size="50" class="form-control" value="<?php echo $pnewVideo; ?>" />
		    </div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" ></label>
	    		<button class="btn btn-default"><span class="glyphicon glyphicon-ok"></span> Ez az én kedvencem!</button>
	    		<button class="btn btn-default" onclick="document.location.href='zenetoplista?reload';return false;"><span class="glyphicon glyphicon-remove"></span> Újból előadót választok</button>
	    	</div>
	    	 <input name="interpret" type="hidden" value="<?PHP echo($pinterpret); ?>" />	
			<?php } ?>
		</div>
		</form><?php
	}

  	 	$topList= $dbSongVote->readTopList (getRealId(getAktClass()),getLoggedInUserId());
		
  	 	if (sizeof($topList)<25)
  	 		$listLength=sizeof($topList);
  	 	else if (userIsAdmin() || getAktClassId()==-1)
  	 		$listLength=sizeof($topList);
  	 	else if (userIsLoggedOn())
  	 		$listLength=100;
  	 	else
  	 		$listLength=25;
?>
<div class="col-sm-9">
	<div class="panel panel-default">
		<div class="panel-heading" style="background-image: url(images/tenor.gif);background-size: contain;background-blend-mode: difference;">

			<label id="dbDetails">Top <?php echo $listLength>100?100:$listLength?> zenelista lejátszó</label><br/>
			<button class="btn btn-default" onclick="playBackward();"><span class="glyphicon glyphicon-sort-by-order"></span> Legjobb szám elsőnek</button>
			<button class="btn btn-default" onclick="playForward();"><span class="glyphicon glyphicon-sort-by-order-alt"></span> Legjobb szám utoljára</button>
			<button class="btn btn-default" onclick="playRandom();"><span class="glyphicon glyphicon-transfer"></span> Véletlenszerüen</button>
		</div>
		<div class="form-group navbar-form navbar" >
            <?php
                for ($i=0;$i<$listLength;$i++) {
                    $v = $topList[$i];
                    if (userIsAdmin() && getParam("check")=="true") {
                        $v["check"] = (getSongName($v['video']) !== "");
                    }
                    displayMusic($db, $v,"change",$v["changeUserID"],$v["changeDate"]);
                }
            ?>
        </div>
	</div>
	<?php if (userIsAdmin()) :?>
		<button onclick="document.location='zenetoplista?check=true'" class="btn btn-default">Youtube Link vizsgálata</button>
	<?php endif;?>
</div>


<div class="col-sm-3">
	<div class="panel panel-default">
		<div class="panel-heading">
			<label id="dbDetails">Szavazatok száma:<?PHP echo($allVotesNoAnonymous); ?></label>
		</div>
		<div class="form-group navbar-form navbar">
			<table>
			  <?php foreach ($votersList as $voter) {
			     	if (intval($voter["count"])>0) {?>
			     		<tr>
			     			<td><img src="<?php echo getPersonPicture($voter) ?>" class="diak_image_sicon" style="margin:2px;"/></td>
			     			<td><?php echo $voter["lastname"]." ".$voter["firstname"]?></td>
			     			<td>&nbsp;</td>
			     			<td style="padding-left:15px;"><?php echo $voter["count"]?></td>
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
        if ($i>100)
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
 include "homefooter.inc.php";

/**
 * Get the soung title using youtube API
 * @param string $youtubeId
 * @return string
 */
 function getSongName(string $youtubeId) {
 	$apiPublicKey=encrypt_decrypt("decrypt","aXg2Zk9QMEp6eGtsMlRkMDR1MGN3LzdPd2pqMUhNRG5LWDl5bU9yMGpDVTlXUzY1YWJ3dFVGL3pxZGhEcUFyRg==");
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


?>