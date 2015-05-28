<?PHP include("homemenu.php"); 
include_once("data.php");
openDatabase(getAktDatabaseName());
readVoteData();

//Vote data structure 
$fields=array("date","class","cemetery","dinner","excursion","where");
//Save vote data 
if (isset($_GET["action"]) && ($_GET["action"]=="vote")) {
	//Save all data for admins
	if ( userIsAdmin() || userIsEditor() ) {
		for($uid=0;$uid<sizeof($data);$uid++) {
			for ($i=0;$i<sizeof($fields);$i++) {
				$vote[$fields[$i]]=$_GET[$fields[$i]."_".$uid];
			}
			setVote($uid,$vote);
		}
	}
	else {
		//Save only one record
		if (userIsLoggedOn())  {
			for ($i=0;$i<sizeof($fields);$i++) {
				$vote[$fields[$i]]=$_GET[$fields[$i]."_".getLoggedInUserId()];
			}
			setVote(getLoggedInUserId(),$vote);
		}
	}
}
?>

<div class="container-fluid">
<h4>30 éves Találkozó</h4>
<div call="well well-lg">
	<b>A 30 éves talákozonk 2015 ben lesz megtartva.</b>
	Légyszíves
	<?PHP if (!userIsLoggedOn()) echo(' jelenkezz be '); ?> 
	és töltsd ki a táblázatot egyszerübb organizáció miatt.
</div>
	
<form action="<?PHP echo($SCRIPT_NAME);?>" method="get"">
<table align="center" class="pannel" style="width:850px">
<tr style="font-weight:bold"><td style="min-width:133px">Név</td><td>Dátum javaslat</td><td>Osztályfőnöki</td><td>Temető</td><td>Vacsora</td><td>Kirándulás</td><td>Hova?</td></tr>
<?php
	$k=false;
	for ($l=0;$l<sizeof($data);$l++) {
		$d=$data[$l];
		$vote=getVote($l);
		if ($k) { $k=false; echo('<tr>'); } else { $k=true; echo('<tr style="background-color:#eedddd">');}
		echo('<td>'.$d["lastname"].' '.$d["firstname"]);if ($d["birthname"]) echo(' ('.$d["birthname"].')'); echo('</td>');
		if ( userIsAdmin() || userIsEditor() || ($d["id"]==getLoggedInUserId() && getAktDatabaseName()==getUserDatabaseName()) ) {
			echo("\r\n".'<td><input style="text" size="19" name="date_'.$l.'" value="'.$vote["date"].'"</td>');
			echo('<td><input style="text" size="4" name="class_'.$l.'" value="'.$vote["class"].'"</td>');
			echo('<td><input style="text" size="4" name="cemetery_'.$l.'" value="'.$vote["cemetery"].'"</td>');
			echo('<td><input style="text" size="4" name="dinner_'.$l.'" value="'.$vote["dinner"].'"</td>');
			echo('<td><input style="text" size="4" name="excursion_'.$l.'" value="'.$vote["excursion"].'"</td>');
			echo('<td><input style="text" size="40" name="where_'.$l.'" value="'.$vote["where"].'"</td>');
			echo('<td><input type="submit" value="Elküld" class="submit2" /></td>');
			echo('<input type="hidden" value="vote" name="action" />');
		} else {
			echo("\r\n".'<td>'.$vote["date"].'</td>');
			echo('<td>'.$vote["class"].'</td>');
			echo('<td>'.$vote["cemetery"].'</td>');
			echo('<td>'.$vote["dinner"].'</td>');
			echo('<td>'.$vote["excursion"].'</td>');
			echo('<td>'.$vote["where"].'</td>');
			echo('<td>&nbsp;</td>');
		}
		echo('</tr>');
	}
	
?>
</table>
</form>
</div>
<?php include 'homefooter.php';?>