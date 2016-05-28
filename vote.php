<?PHP include("homemenu.php"); 
include_once("data.php");
openDatabase(getAktDatabaseName());

$classMeetingCount=date("Y")-intval(getAktScoolYear());

if (($classMeetingCount % 5)!=0) $classMeetingCount += 5 - ($classMeetingCount % 5);
if ($classMeetingCount<10) $classMeetingCount=10;
$classMeetingYear = intval(getAktScoolYear())+$classMeetingCount;

$resultDBoperation="";

readVoteData(getAktDatabaseName(),$classMeetingCount);


//Vote data structure 
$fields=array("date","class","cemetery","dinner","excursion","where");
//Save vote data 
if (isset($_GET["action"]) && ($_GET["action"]=="vote")) {
	//Save all data for admins
	if ( userIsAdmin() || userIsEditor() ) {
		foreach ($data as $l => $d)	
		{ 
			if ( isPersonActive($d)) {
				$vote=getVoteDummy();
				for ($i=0;$i<sizeof($fields);$i++) {
					$field=$fields[$i]."_".$d["id"];
					if (getGetParam($field, "")!="") {
						$vote[$fields[$i]]=getGetParam($field, "");
					} 
				}
				setVote($d["id"],$vote);
			}
		}
	}
	else {
		//Save only one record
		if (userIsLoggedOn())  {
			$vote=getVoteDummy();
			for ($i=0;$i<sizeof($fields);$i++) {
				$field=$fields[$i]."_".getLoggedInUserId();
				if (getGetParam($field, "")!="") {
					$vote[$fields[$i]]=substr(getGetParam($field, ""),0,255);
				} 
			}
			setVote(getLoggedInUserId(),$vote);
		}
	}
	saveVoteData(getAktDatabaseName(), $classMeetingCount);
	$resultDBoperation='<div class="alert alert-success">Sikeresen kimentve. Köszönjük bejegyzésed.</div>';
}
?>
<h4 class="sub_title" >A következő találkozónk</h4>

<div class="container-fluid">
<div class="well well-lg">
	<b>A következő <?php echo $classMeetingCount?> éves talákozonk <?php echo $classMeetingYear?> ben lesz megtartva.</b>
	Légyszíves
	<?PHP if (!userIsLoggedOn()) echo(' jelenkezz be '); ?> 
	és töltsd ki a táblázatot egyszerübb organizáció miatt.
</div>

<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>

<form action="<?PHP echo($SCRIPT_NAME);?>" method="get"">
<table align="center" class="pannel" style="width:auto">
<tr style="font-weight:bold">
	<td></td>
	<td style="min-width:133px"  class="hidden-xs hidden-sm">Név</td>
	<td>Dátum javaslat</td>
	<td style="width: 80px;text-align: center;" class="hidden-xs">Osztály-<br>főnöki</td>
	<td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-education"></span></td>
	<td style="width: 80px;text-align: center;" class="hidden-xs">Temető</td>
	<td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-plus"></span></td>
	<td style="width: 80px;text-align: center;" class="hidden-xs">Vacsora</td>
	<td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-cutlery"></span></td>
	<td style="width: 80px;text-align: center;" class="hidden-xs">Kirán-<br>dulás</td>
	<td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-tree-conifer"></span></td>
	<td class="hidden-xs">Kivándulás hova?</td></tr>
<?php
	$k=false;
	foreach ($data as $d)	
	{ 
		if ( isPersonActive($d)) {
			$l=$d["id"];
			$vote=getVote($d["id"]);
			if ($k) { $k=false; echo('<tr class="disabled" >'); } else { $k=true; echo('<tr class="disabled" style="background-color:#dddddd">');}?>
			<td>
				<img src="images/<?php echo $d["picture"] ?>" style="height:30px; border-radius:3px; margin:2px;" />
			</td>
			<td class="hidden-xs hidden-sm">
				<?php echo $d["lastname"].' '.$d["firstname"];
				if (showField($d,"birthname")) echo(' ('.$d["birthname"].')');?>
			</td>
			<?php 
			if ( userIsAdmin() || userIsEditor() || ($d["id"]==getLoggedInUserId() && getAktDatabaseName()==getUserDatabaseName()) ) { 
				$dis="";$ro="";
			} else {
				$dis="disabled";$ro="readonly";
			}
			?>
			<td><input style="text" class="form-control" <?php echo $ro?> size="10" name="date_<?php echo $l?>" value="<?php echo $vote["date"]?>" /></td>
			<td><input type="checkbox" size="4" <?php echo $dis?> name="class_<?php echo $l?>" <?php echo $vote["class"]=="on"?"checked":""?> /></td>
			<td><input type="checkbox" size="4" <?php echo $dis?> name="cemetery_<?php echo $l?>" <?php echo $vote["cemetery"]=="on"?"checked":""?> /></td>
			<td><input type="checkbox" size="4" <?php echo $dis?> name="dinner_<?php echo $l?>" <?php echo $vote["dinner"]=="on"?"checked":""?> /></td>
			<td><input type="checkbox" size="4" <?php echo $dis?> name="excursion_<?php echo $l?>" <?php echo $vote["excursion"]=="on"?"checked":""?> /></td>
			<td class="hidden-xs"><input class="form-control" <?php echo $ro?>  style="text" size="40" name="where_<?php echo $l?>" value="<?php echo $vote["where"]?>" /></td>
			
			<td>
				<?php if ($dis=="") :?>
					<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-save"></span> Kiment</button>
				<?php endif; ?>
			</td>
			<input type="hidden" value="vote" name="action" />
			</tr>
		<?php 
		}
	}
?>
</table>
</form>
</div>
<div>&nbsp;</div>
<?php include 'homefooter.php';?>