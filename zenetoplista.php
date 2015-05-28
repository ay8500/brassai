<?PHP 


$SiteTitle="A véndiákok ezt hallgatják szívesen";
include("homemenu.php");
include("songdatabase.php");
include_once("userManager.php"); 
?>

<?PHP
   //Check the maximal amout of vote
   $voteCount=getUserSongVoteCount(getAktDatabaseName(),getLoggedInUserId());
   if (userIsAdmin()) $maxVoteCount=500; else $maxVoteCount=25;
   if ($voteCount<$maxVoteCount)  
	 $voteStatus = " Még ".($maxVoteCount-$voteCount)." szavatot csinálhatsz"; 
   else 
   	 $voteStatus="A maximális szavazatok számát elérted. Ha szeretnél mégis más zenére szavazni, akkor törölj ki a szavazataidból.";

   //Site Status 
   $siteStatus="Válaszd ki a kedvenc elöadód, ha nem találod a listában akkor írd be a lenti mezöbe.";

   //Parameter Interpret
   if (isset($_GET["interpret"])) $pinterpret = $_GET["interpret"]; else $pinterpret=0;
   if (isset($_GET["newinterpret"])) $pnewinterpret = $_GET["newinterpret"]; else $pnewinterpret="";
   if (($pinterpret=="0") && ($pnewinterpret<>"" )) {
   		$pinterpret=insertNewInterpret(getAktDatabaseName(),$pnewinterpret);
	    $siteStatus="Az elöadó kimentve, most írd be a kendvenc zenéd címét.";
   }
   
   //Site Status 
   if ($pinterpret>0) {
	   $siteStatus="Válaszd ki a kedvenc énkedet, ha nem találod a listában akkor írd be a lenti mezöbe.";
   }
   //Parameter Song
   if (isset($_GET["song"])) $psong = $_GET["song"]; else $psong=0;
   if (isset($_GET["newSong"])) $pnewSong = $_GET["newSong"]; else $pnewSong="";
   if (isset($_GET["newVideo"])) $pnewVideo = $_GET["newVideo"]; else $pnewVideo="";
   if (isset($_GET["newLink"])) $pnewLink = $_GET["newLink"]; else $pnewLink="";
   if (($psong=="0") && ($pnewSong<>"" )) {
   		$psong=insertNewSong(getAktDatabaseName(),$pinterpret, $pnewSong,$pnewVideo, $pnewLink);
   		insertVote(getAktDatabaseName(),getLoggedInUserId(),$psong);
	    $siteStatus="A zene és a szavazatod kimentve.";
   		$psong=0;$pinterpret=0;
   } 
   if ($psong>0) {
   		insertVote(getAktDatabaseName(),getLoggedInUserId(),$psong);
	    $siteStatus="A szavazatod kimentve.";
   		$psong=0;$pinterpret=0;
   } 

   //Parameter delete Vote
   if (isset($_GET["delVote"])) $delVote = $_GET["delVote"]; else $delVote=0;
   if ($delVote>0) {
   		deleteVote(getAktDatabaseName(),getLoggedInUserId(),$delVote);
   		$psong=0;$pinterpret=0;
   }
   //TODO VoteCount twice in code
   $voteCount=getUserSongVoteCount(getAktDatabaseName(),getLoggedInUserId());
   
   //The list of Songs
   //	if (userIsAdmin()) 
		$topList= readTopList (getAktDatabaseName(),getLoggedInUserId());
	//else
	//	$topList = readVoteList(getDatabaseName(),getUserID()); 
   
?>

<script language="JavaScript1.2">
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

<div class="sub_title">A 25-éves Találkozó zene toplistája<!---a target="wikipedia" href="http://hu.wikipedia.org/wiki/A_szoftverkiad%C3%A1s_%C3%A9letciklusa#B.C3.A9ta">(beta)</a---></div>
<table class="editpagetable"><tr><td width="250px">
<!-- img src="images/music.jpg" /--->
</td>
<td>

<?PHP 
	//if ( ($voteCount<$maxVoteCount) && (getUserID()>0)  ) 
	//if ( (getUserID()>0)  )
	if (false) 
	{
?>
<div><form action="zenetoplista.php">
<table class="zene">
  <?PHP if (!($pinterpret>0)) { ?>
  <tr><td class="zenetab">Válaszd ki az elöadot</td><td class="zenetabout">&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><?PHP echo($siteStatus); ?></td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td class="zenetext">Az adatbázisból:</td><td>
    <?PHP $interpretList= readInterpretList(getAktDatabaseName()); ?>
  <select name="interpret" size="0" onChange="this.form.newinterpret.value=this.options[this.selectedIndex].text" >
    <option value="0">...válassz!...</option>
  	 <?PHP
		foreach ($interpretList as $interpret) 
		{
			if ($interpret['id']==$pinterpret) $def="selected"; else $def="";
			echo('<option value='.$interpret['id'].' '.$def.' >'.$interpret['name'].'</option>');
		} 
	 ?>	 
  </select>
  </td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td class="zenetext">vagy írj be egy újat:<br/>Például ABBA, Hungaria, Vangelis stb.</td>
      <td><input name="newinterpret" type="text" size="50" onkeyup="autoComplete(this,this.form.interpret,'text',false)">
  </td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><input type="submit" value="tovább" /></td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<?PHP } else { ?>
  <tr><td class="zenetab">Válaszd ki az éneket</td><td class="zenetabout">&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><?PHP echo($siteStatus); ?></td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td class="zenetext">Elöadó:</td><td style="font-weight: bold"><?PHP $i=readInterpret(getAktDatabaseName(),$pinterpret); echo($i['name']); ?></td></tr>
  <tr><td class="zenetext">Az adatbázisból:</td><td>
  <?PHP $songList= readSongList(getAktDatabaseName(),$pinterpret); ?>
  <select name="song" size="0" onChange="this.form.newSong.value='';this.form.newVideo.value='';this.form.newLink.value='';">
    <option value="0">...válassz!...</option>
  	 <?PHP
		foreach ($songList as $song) 
		{
			if ($song['id']==$psong) $def="selected"; else $def="";
			echo('<option value='.$song['id'].' '.$def.' >'.$song['name'].'</option>');
		} 
	 ?>	 
  </select>
  </td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td class="zenetext">vagy írj be egy újat<br/>Például: Létezem, A Kör stb.</td>
      <td><input name="newSong" type="text" size="50"  onkeyup="autoComplete(this,this.form.song,'text',false)">
  </td></tr>
  <tr><td class="zenetext">Youtube</td> <td><input name="newVideo" type="text" size="50" />
  <tr><td class="zenetext">Honoldal</td> <td><input name="newLink" type="text" size="50" />
  </td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td>  <input type="submit" value="Ez az én kedvencem!" /></td></tr>
  <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  <input name="interpret" type="hidden" value="<?PHP echo($pinterpret); ?>" />	
<?PHP } ?>
</table>
</form></div>
<?PHP } else { ?>

<?PHP } ?>	
</td></tr>

<?PHP
    $votersList=readVotersList(getAktDatabaseName());
	$allVotes=0;
	foreach ($votersList as $voter) {
		$allVotes +=$voter["VotesCount"];
	}
?>
<tr><td valign="top">
<table  class="zenevoters" >
  <tr><td colspan="2"><b>Szavazatok száma:<?PHP echo($allVotes); ?></b><hr/></td></tr>
  <?PHP
     foreach ($votersList as $voter) {
     	echo("<tr><td>".$voter["Name"]."</td><td>".$voter["VotesCount"]."</td></tr>");
     }
  ?>
</table>
</td><td>
<table  class="zene" width="750px" align="center">
   <tr class="zenecaption"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>Elöadó</td><td>Ének</td><td>Bejelölés</td><td>Youtube Link</td><td>Honoldal</td></tr>
  	 <?PHP
		$i=1;
		foreach ($topList as $v) 
		{	
			$dh='&nbsp;';
			if  ($v['voted']) {
				$voted='<a href="zenetoplista.php?delVote='.$v['song']['id'].'"><img border="0"  src="images/delete.gif" /> Mégsem tetszik törlöm.</a>';
				$dh='<img src="images/DaumenHoch.png" title="Nekem tetszik :-)" />';
			} else {
				if (($voteCount<$maxVoteCount)&&(getLoggedInUserId()>0))
					$voted='<a href="zenetoplista.php?song='.$v['song']['id'].'"><img border="0" src="images/ok.gif" /> Ez is tetszik</a>';
				else
					$voted='';
			}
			if (strlen($v['song']['video'])>5) 
				$YouTubeLink='<a href="zenePlayer.php?link='.$v['song']['video'].'">YouTube</a>';
			else 
				$YouTubeLink="&nbsp;";
			if (strlen($v['song']['link'])>5) 
				$wwwLink='<a target="song" href="'.$v['song']['link'].'" >Honoldal...</a>';
			else 
				$wwwLink='<a target="song" href="http://www.google.de/search?q='.$v['interpret']['name'].' '.$v['song']['name'].'">Megkeresem...</a>';
			if (userIsAdmin())
				$rank=--$v["votes"]; 
			else
				$rank="&nbsp;";
			echo('<tr><td>'.$rank.'</td><td>'.$i++.'</td><td>'.$dh.'</td><td>'.$v['interpret']['name'].'</td><td>'.$v['song']['name'].'</td><td>'. $voted .'</td><td>'.$YouTubeLink.'</td><td>'.$wwwLink.'</td></tr>');
		} 
	 ?>	 
</table>

</td></tr></table>

 <?PHP  //include ?>
</td></tr></table></body></html>
