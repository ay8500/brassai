<?PHP 
$SiteTitle="A véndiákok ezt hallgatják szívesen";
include("homemenu.php");
include("songdatabase.php");
include_once("userManager.php");
$resultDBoperation="";

//User can make changes in the toplist
$edit = (userIsLoggedOn() && getAktClass()==getLoggedInUserClassId()) || userIsAdmin();

//action  delete vote
$delVote = intval(getGetParam("delVote", "-1"));
if ($delVote>=0 && $edit) {
   	if (deleteVote(getAktDatabaseName(),getLoggedInUserId(),$delVote))
		$resultDBoperation='<div class="alert alert-success" >Zene sikeresen a szavazataidból törölve!</div>';
	else 
		$resultDBoperation='<div class="alert alert-warning" >Szavazat törlése nem sikerült.</div>';
   	$psong=0;$pinterpret=0;
}


 //Site Status 
 $siteStatus="Válaszd ki a kedvenc elöadód, ha nem találod a listában akkor írd be a lenti mezöbe.";

   //Parameter Interpret
   if (isset($_GET["interpret"])) $pinterpret = $_GET["interpret"]; else $pinterpret=0;
   if (isset($_GET["newinterpret"])) $pnewinterpret = $_GET["newinterpret"]; else $pnewinterpret="";
   if (($pinterpret=="0") && ($pnewinterpret<>"" )) {
   		$pinterpret=insertNewInterpret(getAktDatabaseName(),$pnewinterpret);
   		if ($pinterpret>=0) 
   			$resultDBoperation='<div class="alert alert-success" >Előadó sikeresen kimentve.</div>';
   		else
   			$resultDBoperation='<div class="alert alert-warning" >Előadó az adatbankban már létezik! Kimentés nem volt szükséges.</div>';
   }
   
   //Parameter Song
   $psong=intval(getGetParam("song", "0"));
   if (isset($_GET["newSong"])) $pnewSong = $_GET["newSong"]; else $pnewSong="";
   if (isset($_GET["newVideo"])) $pnewVideo = $_GET["newVideo"]; else $pnewVideo="";
   if (isset($_GET["newLink"])) $pnewLink = $_GET["newLink"]; else $pnewLink="";
   if (($psong=="0") && ($pnewSong<>"" && $edit )) {
   		$psong=insertNewSong(getAktDatabaseName(),$pinterpret, $pnewSong,$pnewVideo, $pnewLink);
   		if ($psong>=0) {
   			insertVote(getAktDatabaseName(),getLoggedInUserId(),$psong);
   			$resultDBoperation='<div class="alert alert-success" >Zene és a szavezatod sikeresen kimentve.</div>';
   			$psong=0;$pinterpret=0;
   		} else {
   			$resultDBoperation='<div class="alert alert-warning" >Zene már az adatbankban létezik, válassz újból!</div>';
   			$psong=0;
   		}
   } 
   if ($psong>0 && $edit) {
   		if (insertVote(getAktDatabaseName(),getLoggedInUserId(),$psong))
			$resultDBoperation='<div class="alert alert-success" >Zene sikeresen a szavazataidhoz hozzátéve.</div>';
		else 
			$resultDBoperation='<div class="alert alert-warning" >Szavazat nem sikerült.</div>';
   		$psong=0;$pinterpret=0;
   } 
	
   //Read voters List
	$votersList=readVotersList(getAktDatabaseName());
	$allVotes=0;
	$voteCount=0;
	foreach ($votersList as $voter) {
		if (trim($voter["Name"])!="")
			$allVotes +=$voter["VotesCount"];
		if (trim($voter["UID"])==getLoggedInUserId())
			$voteCount =$voter["VotesCount"];
	}
	
	//Check the maximal amout of vote
	if (userIsAdmin()) $maxVoteCount=500; else $maxVoteCount=25;
	if ($edit) {
		if ($voteCount<$maxVoteCount)  
			$voteStatus = " Még ".($maxVoteCount-$voteCount)." szavatot adhatsz"; 
		else 
			$voteStatus="A maximális szavazatok számát elérted. Ha szeretnél mégis más zenére szavazni, akkor törölj ki a szavazataidból.";
	} else {
		if (userIsLoggedOn())
			$voteStatus='Ez nem a te osztályod top 100-as listálya, ezért nem szavazhatsz. <a href="zenetoplista.php?scoolYear='.getUScoolYear().'&scoolClass='.getUScoolClass().'">An én osztályom toplistálya</a>';
		else
			$voteStatus="Jelentkezz be és szavazatoddal járulj hozzá az osztályod top 100-as zenelistályához.";
	}
?>


<div class="sub_title">A mi osztályunk zenetoplistája. Ezt hallgatjuk mi szívesen.</div>
<div class="container-fluid">
	<div class="well">
		<?php echo $voteStatus?>
	</div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>

	<?php if ( $voteCount<$maxVoteCount && $edit ) { ?>
	<form action="zenetoplista.php">
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
				<select name="interpret" size="0" onChange="this.form.newinterpret.value=this.options[this.selectedIndex].text" class="form-control">
					<option value="0">...válassz!...</option>
					<?php
						$interpretList= readInterpretList(getAktDatabaseName()); 
						foreach ($interpretList as $interpret)	{
							if ($interpret['id']==$pinterpret) $def="selected"; else $def="";
							echo('<option value='.$interpret['id'].' '.$def.' >'.$interpret['name'].'</option>');
						} 
					?>	 
				</select>
			</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="newinterpret" id="search_left">vagy írj be egy újat <br />Például: ABBA, Hungaria, Vangelis stb.</label>
	    	   	<input name="newinterpret" type="text" size="50" onkeyup="autoComplete(this,this.form.interpret,'text',false)" class="form-control" />
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
	    	   	<input readonly class="form-control" value="<?php echo (readInterpret(getAktDatabaseName(),$pinterpret)["name"]);?>"/>
	    	</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="interpret" id="search_left">Az adatbázisból </label>
				<select name="song" size="0" onChange="this.form.newSong.value='';this.form.newVideo.value='';this.form.newLink.value='';" class="form-control" />
					<option value="0">...válassz!...</option>
				  	 <?php
				  	 	$songList= readSongList(getAktDatabaseName(),$pinterpret);
						foreach ($songList as $song) 
						{
							if ($song['id']==$psong) $def="selected"; else $def="";
							echo('<option value='.$song['id'].' '.$def.' >'.$song['name'].'</option>');
						} 
					 ?>	 
				</select>
			</div> 	 		
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="interpret" >vagy írj be egy újat<br/>Például: Létezem, A Kör,Csókkirály  stb.</label>
	    	   	<input name="newSong" type="text" size="50"  onkeyup="autoComplete(this,this.form.song,'text',false)" class="form-control"/>
	    	</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="interpret" >Youtube link vagy cód</label>
		    	<input name="newVideo" type="text" size="50" class="form-control" />
		    </div>
  			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" for="interpret" >Honoldal<br/>Például: Létezem, A Kör,Csókkirály  stb.</label>
  				<input name="newLink" type="text" size="50" class="form-control" />
  			</div>
			<div class="form-group navbar-form navbar">
	    	   	<label style="min-width:300px;" ></label>
	    		<button class="btn btn-default"><span class="glyphicon glyphicon-ok"></span> Ez az én kedvencem!</button>
	    		<button class="btn btn-default" onclick="document.location.href='zenetoplista.php?reload';return false;"><span class="glyphicon glyphicon-remove"></span> Újból előadót választok</button>
	    	</div>
	    	 <input name="interpret" type="hidden" value="<?PHP echo($pinterpret); ?>" />	
			<?php } ?>
		</div>
		</form>
	<?php } ?>

	
<div class="col-sm-3">	
	<div class="panel panel-default">
		<div class="panel-heading">
			<label id="dbDetails">Szavazatok száma:<?PHP echo($allVotes); ?></label> 
		</div>
		<div class="form-group navbar-form navbar">
			<table>
			  <?php foreach ($votersList as $voter) {
			     	if (trim($voter["Name"])!="" && intval($voter["VotesCount"])>0) { ?>
			     		<tr>
			     			<td><img src="images/<?php echo getPerson($voter["UID"])["picture"] ?>" style="height:30px; border-radius:3px; margin:2px;" /></td>
			     			<td><?php echo $voter["Name"]?></td>
			     			<td>&nbsp;</td>
			     			<td style="padding-left:15px;"><?php echo $voter["VotesCount"]?></td>
			     		</tr>
			     	<?php }
			  } ?>
			</table>
		</div>
	</div>
</div>

<?php 
  	 	$topList= readTopList (getAktDatabaseName(),getLoggedInUserId());
		
  	 	if (sizeof($topList)<25)
  	 		$listLength=sizeof($topList);
  	 	else if (userIsLoggedOn())
  	 		$listLength=100;
  	 	else if (userIsAdmin())
  	 		$listLength=sizeof($topList)-1;
  	 	else
  	 		$listLength=25;
?>
<div class="col-sm-9">	
	<div class="panel panel-default">
		<div class="panel-heading">
			<label id="dbDetails">Top <?php echo $listLength?> zenelista</label> 
		</div>
		<div class="form-group navbar-form navbar">
			<table>
			   <tr class="zenecaption">
				<?php if (userIsAdmin()) :?>
				   	<td>&nbsp;</td>
				<?php  endif;?>
			   	<td>&nbsp;</td>
			   	<td style="padding-left:5px;padding-right:5px;"><span class="glyphicon glyphicon-thumbs-up" title="Nekem tetszik"></span></td>
			   	<td class="hidden-xs">Elöadó</td>
			   	<td style="padding-left:5px;">Ének</td>
			   	<?php if ($edit) :?>
			   		<td style="padding-left:5px;padding-right:5px;">Szavaz</td>
			   	<?php endif;?>
			   	<td class="hidden-xs">Youtube</td>
			   	<td class="visible-xs" style="padding-left:5px;padding-right:5px;"><span class="glyphicon glyphicon-film"></span></td>
			   	<td class="hidden-xs" style="padding-left:10px;">Honoldal</td>
			  </tr>
			   <?php
				for ($i=0;$i<$listLength;$i++) {
					$v=$topList[$i];
					$dh='&nbsp;';
					if  ($v['voted']) {
						$voted='<a href="zenetoplista.php?delVote='.$v['song']['id'].'" title="Törlöm"><span style="color:red" class="glyphicon glyphicon-remove-circle"></span></a>';
						$dh='<span class="glyphicon glyphicon-thumbs-up" title="Nekem tetszik"></span>';
					} else {
						if (($voteCount<$maxVoteCount)&&(getLoggedInUserId()>0))
							$voted='<a href="zenetoplista.php?song='.$v['song']['id'].'" title="Bejelölöm mert tetszik nekem!"><span style="color:green" class="glyphicon glyphicon-ok-circle"></span></a>';
						else
							$voted='';
					}
					if (strlen($v['song']['video'])>5) 
						$YouTubeLink='<a href="zenePlayer.php?link='.$v['song']['video'].'"><span class="glyphicon glyphicon-film"></span></a>';
					else 
						$YouTubeLink="&nbsp;";
					if (strlen($v['song']['link'])>5) 
						$wwwLink='<a target="song" href="'.$v['song']['link'].'" title="Honoldal"><span class="glyphicon glyphicon-link"></span></a>';
					else 
						$wwwLink='<a target="song" href="http://www.google.de/search?q='.$v['interpret']['name'].' '.$v['song']['name'].'" title="Megkeresem"><span class="glyphicon glyphicon-search"></span></a>';
					?>
					<tr>
						<?php if (userIsAdmin()) :?>
							<td><?php echo --$v["votes"]?></td>
						<?php endif;?>
						<td><?php echo $i+1?></td>
						<td style="padding-left:5px;padding-right:5px;"><?php echo $dh?></td>
						<td class="hidden-xs"><?php echo $v['interpret']['name']?></td>
						<td style="padding-left:5px;"><?php echo $v['song']['name']?></td>
						<?php if ($edit) :?>
							<td style="text-align: center;"><?php echo $voted?></td>
						<?php endif;?>
						<td style="text-align: center;"><?php echo $YouTubeLink?></td>
						<td class="hidden-xs " style="text-align: center;"><?php echo$wwwLink?></td>
					</tr>
			<?php }?> 
			</table>
		</div>
	</div>
</div>

</div>
 <?PHP  include "homefooter.php" ?>

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
 