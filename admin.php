<?PHP 
$diakEditStorys=true;
include("homemenu.php"); 
include_once("data.php");
include_once("userManager.php");

if (isset($_GET["action"]) && ($_GET["action"]=="sendMail")) {
	if ( userIsAdmin() ) {
		include_once ("sendMail.php");
		for($uid=1;$uid<=getDataSize()+1;$uid++) {
			if (isset($_GET["D".$uid])) {
				SendMail($uid, $_GET["T"],isset($_GET["U"]) );
			}
		}
	}
}
?>

   
<p class="sub_title">Adminisztráció</p>
<?PHP
$tabsCaption=Array("Mail&nbsp;küldés","Diákok&nbsp;táblázatai","Administrátorok");
include("tabs.php");
?>

<?PHP if (userIsAdmin() || (userIsEditor()) ) { ?>
	<?PHP if ($tabOpen==0) {?>
	<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" name="mail">
		<input type="checkbox" name="U"/> Bejelentkezési adatokat is küld? <br/>
		<textarea id="story" name="T" style="width:95%;height:300px" wrap="off" onchange="fieldChanged();">
<b>Kedves %%name%%</b><br/>
<p>
Ide kell írni a szöveget....
</p>
<p>
Üdvözlettel <?php $dd=getPersonLogedOn(); echo($dd["lastname"]." ".$dd["firstname"]); ?>
</p>
<p>
Ezt az e-mailt <a href=http://brassai.blue-l.de/index.php?<?PHP echo('scoolYear='.getUScoolYear().'&scoolClass='.getUScoolClass());?>>A kolozsvári Brassai Sámuel líceum <?PHP echo(getUScoolYear());?>-ben végzett diákjainak <?PHP echo(getUScoolClass());?></a> honlapjáról kaptad.
</p>
		</textarea>
		<input type="submit" class="submit2" value="E-Mail küldés!" />
		<input type="button" class="submit2" value="Mindenkit megjelöl" onclick="checkUncheckAll(true);"/>
		<input type="button" class="submit2" value="Megjelöléseket töröl" onclick="checkUncheckAll(false);"/>
		<p>
		<?PHP
			foreach ($data as $l => $d) {
				echo('<div style="display:inline-block; margin-right:10px">');
				if (strlen($d["email"])>2) 
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
	
	
	<?PHP if ($tabOpen==1) {?>
		<p style="text-align:center">
			<!---a href="getExcelData.php?data=Kontakt" target="excel">Kontaklista letöltése Excel formátumban</a>
			&nbsp;|&nbsp;-->
			<a href="getExcelData.php?data=All" target="excel">Összes adatok letöltése Excel formátumban</a>
		</p>
		<div><table >
		<tr style="text-align:center;font-weight:bold;"><td>Név</td><td>Becenév</td><td>E-Mail</td><td>Telefon</td><td>Mobiltelefon</td><td>Skype</td><td>Datum</td></tr>
		<?PHP
		for ($l=0;$l<sizeof($data);$l++) {
			$d=$data[$l];
			if (!(strpos($d["admin"],"guest")===0)) {
				if (($l % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top>".$d["lastname"].' '.$d["firstname"]."</font></td><td>".getFieldValue($d["user"])."</td><td>";
				echo "<a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a>";
				echo "<td>".getFieldValue($d["phone"])."</td><td>".getFieldValue($d["mobil"])."</td><td>".getFieldValue($d["skype"])."</td><td>".getFieldValue($d["date"])."</td>";
				echo "</tr>";
			}
		
		}
		echo("<tr><td>Vendégek, Tanárok</td></tr>");
		for ($l=0;$l<sizeof($data);$l++) {
			$d=$data[$l];
					if ((strpos($d["admin"],"guest")===0)) {
				if (($l % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top>".$d["lastname"].' '.$d["firstname"]."</font></td><td>".getFieldValue($d["user"])."</td><td>";
				echo "<a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a>";
				echo "<td>".getFieldValue($d["phone"])."</td><td>".getFieldValue($d["mobil"])."</td><td>".getFieldValue($d["skype"])."</td><td>".getFieldValue($d["date"])."</td>";
				echo "</tr>";
			}		
		}
		?>
		</table></div>
	<?PHP } ?>
	
	
	<?PHP if ($tabOpen==2) {?>
		<table   >
		<tr style="text-align:center;font-weight:bold;"><td>Név</td><td>E-Mail</td><td>Telefon</td><td>Mobiltelefon</td><td>Skype</td><td>IP</td><td>Datum</td></tr>
		<?PHP
		foreach ($data as $idx => $d) {
			if ($d['admin']=="admin" || $d['admin']=="editor") {
				if (($idx % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top align=right>".$d["lastname"].' '.$d["firstname"]."</font></td><td>";
				echo "<a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a>";
				echo "<td>".getFieldValue($d["phone"])."</td><td>".getFieldValue($d["mobil"])."</td><td>".getFieldValue($d["skype"])."</td><td>".getFieldValue($d["ip"])."</td><td>".getFieldValue($d["date"])."</td>";
				echo "</td></tr>";
			}
		}
		?>
		</table>
	<?PHP } ?>
<?PHP } 
else
	echo "<div>Adat hozzáférési jog hiányzik!</div>";
?>
</td></tr></table>
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
<?php
include 'homefooter.php';
?>
