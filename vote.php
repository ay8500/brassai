<?PHP include("homemenu.php"); 
include_once("data.php");

$class=$db->getClassById(getAktClass());
$classMeetingCount=date("Y")-intval($class["graduationYear"]);

if (($classMeetingCount % 5)!=0) $classMeetingCount += 5 - ($classMeetingCount % 5);
if ($classMeetingCount<10) $classMeetingCount=10;
$classMeetingYear = intval($class["graduationYear"])+$classMeetingCount;

$resultDBoperation="";

$data=$db->getPersonListByClassId(getAktClass());

//Vote data structure 
$fields=array("personID","classID","meetAfterYear", "eventDay","isSchool","isCemetery","isDinner","isExcursion","place");
//Save vote data 
if (isset($_GET["action"]) && ($_GET["action"]=="vote")) {
	//Save all data for admins
	if ( userIsAdmin() || userIsEditor() ) {
		foreach ($data as $l => $d)	
		{ 
			$vote=array();
			$vote["classID"]=getAktClass();
			$vote["meetAfterYear"]=$classMeetingCount;
			for ($i=0;$i<sizeof($fields);$i++) {
				$field=$fields[$i]."_".$d["id"];
				if (preg_match('/\\Ais/', $field)) {
					$vote[$fields[$i]]=getGetParam($field, "")=="on"?1:0;
				} else {
					$vote[$fields[$i]]=substr(getGetParam($field, ""),0,200);
				}
			}
			$vote["id"]=-1;
			$ret=$db->saveVote($vote);
		}
		if ($ret>=0)
			$resultDBoperation='<div class="alert alert-success">Sikeresen kimentve. Köszönjük bejegyzésed.</div>';
		else
			$resultDBoperation='<div class="alert alert-warning">Szavazat kimentése nem sikerült!</div>';
	}
	else {
		//Save only one record
		if (userIsLoggedOn())  {
			$vote=array();
			$vote["classID"]=getAktClass();
			$vote["meetAfterYear"]=$classMeetingCount;
			for ($i=0;$i<sizeof($fields);$i++) {
				$field=$fields[$i]."_".getLoggedInUserId();
				if (preg_match('/\\Ais/', $field)) {
					$vote[$fields[$i]]=getGetParam($field, "")=="on"?1:0;
				} else {
					$vote[$fields[$i]]=substr(getGetParam($field, ""),0,200);
				}
			}
			$vote["id"]=-1;
			$ret=$db->saveVote($vote);
			if ($ret>=0)
				$resultDBoperation='<div class="alert alert-success">Sikeresen kimentve. Köszönjük bejegyzésed.</div>';
			else
				$resultDBoperation='<div class="alert alert-warning">Szavazat kimentése nem sikerült!</div>';
		}
	}
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
		$l=$d["id"];
		$vote=$db->getVote(getPersonId($d),$classMeetingCount);
		if ($k) { $k=false; echo('<tr class="disabled" >'); } else { $k=true; echo('<tr class="disabled" style="background-color:#dddddd">');}?>
		<td>
			<img src="images/<?php echo $d["picture"] ?>" style="height:30px; border-radius:3px; margin:2px;" />
		</td>
		<td class="hidden-xs hidden-sm">
			<?php echo $d["lastname"].' '.$d["firstname"];
			if (showField($d,"birthname")) echo(' ('.$d["birthname"].')');?>
		</td>
		<?php 
		if ( userIsAdmin() || userIsEditor() || ($d["id"]==getLoggedInUserId() && getAktClass()==getLoggedInUserClassId()) ) { 
			$dis="";$ro="";
		} else {
			$dis="disabled";$ro="readonly";
		}
		?>
		<td><input style="text" class="form-control" <?php echo $ro?> size="10" name="eventDay_<?php echo $l?>" value="<?php echo $vote["eventDay"]?>" /></td>
		<td><input type="checkbox" size="4" <?php echo $dis?> name="isSchool_<?php echo $l?>" <?php echo $vote["isSchool"]==1?"checked":""?> /></td>
		<td><input type="checkbox" size="4" <?php echo $dis?> name="isCemetery_<?php echo $l?>" <?php echo $vote["isCemetery"]==1?"checked":""?> /></td>
		<td><input type="checkbox" size="4" <?php echo $dis?> name="isDinner_<?php echo $l?>" <?php echo $vote["isDinner"]==1?"checked":""?> /></td>
		<td><input type="checkbox" size="4" <?php echo $dis?> name="isExcursion_<?php echo $l?>" <?php echo $vote["isExcursion"]==1?"checked":""?> /></td>
		<td class="hidden-xs"><input class="form-control" <?php echo $ro?>  style="text" size="40" name="place_<?php echo $l?>" value="<?php echo $vote["place"]?>" /></td>
		
		<td>
			<?php if ($dis=="") :?>
				<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-save"></span> Kiment</button>
			<?php endif; ?>
		</td>
		<input type="hidden" value="vote" name="action" />
		<input type="hidden" value="<?php echo getPersonId($d)?>" name="personID_<?php echo $l?>" />
		<input type="hidden" value="<?php echo getAktClass()?>" name="classID_<?php echo $l?>" />
		<input type="hidden" value="<?php echo $classMeetingCount?>" name="meetAfterYear_<?php echo $l?>" />
		</tr>
	<?php 
	}
?>
</table>
</form>
</div>
<div>&nbsp;</div>
<?php include 'homefooter.php';?>