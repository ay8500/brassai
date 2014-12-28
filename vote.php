<?PHP include("homemenu.php"); 
include_once("data.php");
readVoteData();

//Vote data structure 
$fields=array("date","class","cemetery","dinner","excursion","where");
//Save vote data 
if (isset($_GET["action"]) && ($_GET["action"]=="vote")) {
	//Save all data for admins
	if ( userIsAdmin() || userIsEditor() ) {
		for($uid=1;$uid<=getDataSize();$uid++) {
			for ($i=0;$i<sizeof($fields);$i++) {
				$vote[$fields[$i]]=$_GET[$fields[$i]."_".$uid];
			}
			setVote($uid,$vote);
		}
	}
	else {
		//Save only one record
		if (isset($_SESSION['UID']) && $_SESSION['UID']>0)  {
			for ($i=0;$i<sizeof($fields);$i++) {
				$vote[$fields[$i]]=$_GET[$fields[$i]."_".$_SESSION['UID']];
			}
			setVote($_SESSION['UID'],$vote);
		}
	}
}
?>

<div class="sub_title">30 éves Találkozó</div>
<div style="text-align:center;">
	<b>A 30 éves talákozonk 2015 ben lesz megtartva.</b>
	Légyszíves
	<?PHP if (!((isset($_SESSION['UID']))&&($_SESSION['UID']>0))) echo(' jelenkezz be '); ?> 
	és töltsd ki a táblázatot egyszerübb organizáció miatt.
</div>
	
<form action="<?PHP echo($SCRIPT_NAME);?>" method="get"">
<table align="center" class="pannel" style="width:850px">
<tr style="font-weight:bold"><td>Név</td><td>Dátum javaslat</td><td>Osztályfönöki</td><td>Temetö</td><td>Vacsora</td><td>Kirándulás</td><td>Hova?</td></tr>
<?php
	$k=false;
	for ($l=1;$l<=getDataSize();$l++) {
		$d=getPerson($l);
		$vote=getVote($l);
		if ($k) { $k=false; echo('<tr>'); } else { $k=true; echo('<tr style="background-color:#eedddd">');}
		echo('<td>'.$d["lastname"].' '.$d["firstname"]);if ($d["birthname"]) echo(' ('.$d["birthname"].')'); echo('</td>');
		if ( userIsAdmin() || userIsEditor() || (isset($_SESSION['UID']) && ($_SESSION['UID']==$l)) ) {
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

</td></tr></table>
</body>
</html>
