<?PHP 
$diakEditStorys=true;
include("homemenu.php"); 
include_once("data.php");
include_once("userManager.php");


if (isset($_GET["action"]) && ($_GET["action"]=="sendMail")) {
	openDatabase(getAktDatabaseName());
	if ( userIsAdmin() ) {
		include_once ("sendMail.php");
		for($i=0;$i<sizeof($data);$i++) {
			$uid=$data[$i]["id"];
			if (isset($_GET["D".$uid])) {
				SendMail($uid, $_GET["T"],isset($_GET["U"]) );
			}
		}
	}
}
?>

<div class="container-fluid">   
<h2 class="sub_title" >Adminisztráció</h2>
<?PHP
$tabsCaption=Array("Mail&nbsp;küldés","Diákok&nbsp;táblázatai","Administrátorok");
include("tabs.php");
?>

<?PHP if (userIsAdmin() || (userIsEditor()) ) { ?>
	<?PHP if ($tabOpen==0) {?>
	<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" name="mail">
		<textarea id="story" name="T" style="width:95%;height:300px" wrap="off" onchange="fieldChanged();">
<b>Kedves %%name%%</b><br/>
<p>
Ide kell írni a szöveget....
</p>
<p>
Üdvözlettel <?php $dd=getPersonLogedOn(); echo($dd["lastname"]." ".$dd["firstname"]); ?>
</p>
<p>
Ezt az e-mailt <a href=http://brassai.blue-l.de/index.php?<?PHP echo('scoolYear='.getAktScoolYear().'&scoolClass='.getAktScoolClass());?>>A kolozsvári Brassai Sámuel líceum <?PHP echo(getAktScoolYear());?>-ben végzett diákjainak <?PHP echo(getAktScoolClass());?></a> honlapjáról kaptad.
</p>
		</textarea>
		<input type="checkbox" name="U"/> Bejelentkezési adatokat is elküld.<br/>
		<button class="btn btn-default" type="submit" ><span class="glyphicon glyphicon-envelope"></span> E-Mail küldés!</button>
		<button type="button" class="btn btn-default" onclick="checkUncheckAll(true);"><span class="glyphicon glyphicon-check"></span> Mindenkit megjelöl</button>
		<button type="button" class="btn btn-default" onclick="checkUncheckAll(false);"><span class="glyphicon glyphicon-unchecked"></span> Megjelöléseket töröl</button>
		<p>
		<?php
			openDatabase(getAktDatabaseName());
			foreach ($data as $l => $d) {
				echo('<div style="display:inline-block; margin-right:10px">');
				if (isset($d["email"]) && strlen($d["email"])>2) 
					echo('<input type="checkbox" name="D'.$d["id"].'" checked />');
				echo($d["lastname"].'&nbsp;'.$d["firstname"].' ');
				echo("</div>");
			}
		?>
		</p>
		<input type="hidden" value="sendMail" name="action" />
		</form>
		
		<?PHP if (isset($sendMailMsg)) echo('<div style="text-align:center">'.$sendMailMsg.'</div>');?>
	<?PHP } ?>
	
	
	<?php if ($tabOpen==1) { ?>
		<p style="text-align:center">
			<!---a href="getExcelData.php?data=Kontakt" target="excel">Kontaklista letöltése Excel formátumban</a>
			&nbsp;|&nbsp;-->
			<a href="getExcelData.php?data=All" target="excel">Összes adatok letöltése Excel formátumban</a>
		</p>
		<div>
		<table  class="table-sp" >
		<tr style="text-align:center;font-weight:bold;">
			<td style="min-width:160px">Név</td><td id="o1024" >Becenév</td><td>E-Mail</td><td id="o400">Telefon</td><td  id="o480">Mobiltelefon</td><td  id="o1024">Skype</td><td  id="o1024">IP</td><td  id="o1024">Datum</td>
		</tr>
		
		<?PHP
		openDatabase(getAktDatabaseName());
		for ($l=0;$l<sizeof($data);$l++) {
			$d=$data[$l];
			if (!isPersonGuest($d)) {
				if (($l % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top>".$d["lastname"].' '.$d["firstname"]."</font>".'</td><td  id="o1024">'.getFieldValue($d,"user")."</td><td>";
				echo "<a href=mailto:".getFieldValue($d,"email").">".getFieldValue($d,"email")."</a>";
				echo '<td  id="o400">'.getFieldValue($d,"phone").'</td><td  id="o480">'.getFieldValue($d,"mobil").'</td><td  id="o1024">'.getFieldValue($d,"skype").'</td><td  id="o1024">'.getFieldValue($d,"ip").'</td><td  id="o1024">'.getFieldValue($d,"date")."</td>";
				echo "</tr>";
			}
		
		}
		echo("<tr><td>Vendégek, Tanárok</td></tr>");
		for ($l=0;$l<sizeof($data);$l++) {
			$d=$data[$l];
					if (isPersonGuest($d)) {
				if (($l % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top>".$d["lastname"].' '.$d["firstname"]."</font>".'</td><td  id="o1024">'.getFieldValue($d,"user")."</td><td>";
				echo "<a href=mailto:".getFieldValue($d,"email").">".getFieldValue($d,"email")."</a>";
				echo '<td  id="o400">'.getFieldValue($d,"phone").'</td><td  id="o480">'.getFieldValue($d,"mobil").'</td><td  id="o1024">'.getFieldValue($d,"skype").'</td><td  id="o1024">'.getFieldValue($d,"ip").'</td><td  id="o1024">'.getFieldValue($d,"date")."</td>";
				echo "</tr>";
			}		
		}
		?>
		</table></div>
	<?PHP } ?>
	
	
	<?PHP if ($tabOpen==2) {?>
		<table class="table-sp"  >
		<tr style="text-align:center;font-weight:bold;"><td>Név</td><td>E-Mail</td><td id="o400">Telefon</td><td  id="o480">Mobiltelefon</td><td  id="o1024">Skype</td><td  id="o1024">IP</td><td  id="o1024">Datum</td></tr>
		<?PHP
		openDatabase(getAktDatabaseName());
		foreach ($data as $idx => $d) {
			if (isPersonAdmint($d) || isPersonEditor($d))  {
				if (($idx % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top align=right>".$d["lastname"].' '.$d["firstname"]."</font></td><td>";
				echo "<a href=mailto:".getFieldValue($d,"email").">".getFieldValue($d,"email")."</a>";
				echo '<td  id="o400">'.getFieldValue($d,"phone").'</td><td  id="o480">'.getFieldValue($d,"mobil").'</td><td  id="o1024">'.getFieldValue($d,"skype").'</td><td  id="o1024">'.getFieldValue($d,"ip").'</td><td  id="o1024">'.getFieldValue($d,"date")."</td>";
				echo "</td></tr>";
			}
		}
		?>
		</table>
	<?PHP } ?>
<?PHP } 
else
	echo '<div style="margin:40px;">Adat hozzáférési jog hiányzik!</div>';
?>
</div>
<script language="JavaScript" type="text/javascript">
	function checkUncheckAll(state) {
		for(var z=0; z < document.mail.elements.length; z++) {
			if (document.mail.elements[z].type == 'checkbox') {
				if (document.mail.elements[z].name != 'U') 
					document.mail.elements[z].checked = state;
	  		}
     	}
    }
</script>
<?php include 'homefooter.php'; ?>
