<?PHP include("homemenu.php"); 
include_once("data.php");
include_once("userManager.php");

if (isset($_GET["action"]) && ($_GET["action"]=="sendMail")) {
	if ( userIsAdmin() ) {
		include("sendMail.php");
		for($uid=1;$uid<=getDataSize()+1;$uid++) {
			if (isset($_GET["D".$uid])) {
				SendMail($uid, $_GET["T"],isset($_GET["U"]) );
			}
		}
	}
}
?>

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
   
<p class="sub_title">Adminisztráció</p>
<?PHP
$tabsCaption=Array("Mail&nbsp;küldés","Diákok&nbsp;táblázatai","Administrátorok");
include("tabs.php");
?>

<?PHP if (userIsAdmin() || (userIsEditor()) ) { ?>
	<?PHP if ($tabOpen==0) {?>
		<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" name="mail">
		<table align="center" class="pannel" style="width:800px"><tr><td>
		<input type="checkbox" name="U"/> Bejelentkezési adatokat is küld? <br/>
		<textarea name="T" rows="10" cols="90" wrap="off" onchange="fieldChanged();">
<b>Kedves %%name%%</b><br/>
<p>
Ide kell írni a szöveget....
</p>
<p>
Üdvözlettel <?PHP $dd=getPerson($_SESSION["UID"]); echo($dd["lastname"]." ".$dd["firstname"]); ?>
</p>
<p>
Ezt az e-mailt <a href=http://brassai.blue-l.de/index.php?<?PHP echo('scoolYear='.$_SESSION['scoolYear'].'&scoolClass='.$_SESSION['scoolClass']);?>>A kolozsvári Brassai Sámuel líceum <?PHP echo($_SESSION['scoolYear']);?>-ben végzett diákjainak <?PHP echo($_SESSION['scoolClass']);?></a> honlapjáról kaptad.
</p>
		</textarea>
		<div style="color:red;font-weight:bold;text-align:right">Vigyázz! A beadott szöveg <a target="_blank" href="http://hu.wikipedia.org/wiki/HTML">HTML</a> formatumban kell legyen.</div>
		<input type="submit" class="submit2" value="E-Mail küldés!" />
		&nbsp;<a href="javascript:checkUncheckAll(true);">mindenkit megjelöl</a>
		&nbsp;<a href="javascript:checkUncheckAll(false);">megjelöléseket töröl</a>
		</td></tr></table>
		<table align="center" class="pannel" style="width:800px;" cellspacing="0" cellpadding="0">
		<tr>
		<?PHP
			foreach ($data as $l => $d) {
				echo('<td>');
				if (strlen($d["email"])>2) echo('<input type="checkbox" name="D'.$l.'" checked />');
				else echo('&nbsp;');
				echo('</td><td>'.$d["lastname"].'&nbsp;'.$d["firstname"].'</td>');
				if ($l % 3==0) echo('<tr></tr>'."\r\n");
			}
		?>
		</tr>
		</table>
		<input type="hidden" value="sendMail" name="action" />
		</form>
		
		<?PHP if (isset($sendMailMsg)) echo('<div style="text-align:center">'.$sendMailMsg.'</div>');?>
	<?PHP } ?>
	<?PHP if ($tabOpen==1) {?>
		<p style="text-align:center">
			<!---a href="getExcelData.php?data=Kontakt" target="excel">Kontaklista letöltése Excel formátumban</a--->
			&nbsp;|&nbsp;
			<a href="getExcelData.php?data=All" target="excel">Összes adatok letöltése Excel formátumban</a>
		</p>
		<table style="width:90%;border-collapse:collapse" align="center" >
		<tr style="text-align:center;font-weight:bold;"><td>Név</td><td>Becenév</td><td>E-Mail</td><td>Telefon</td><td>Mobiltelefon</td><td>Skype</td><td>IP</td><td>Datum</td></tr>
		<?PHP
		for ($l=1;$l<=getDataSize();$l++) {
			$d=getPerson($l);
			if (($l % 2) ==0) 
				echo '<tr style="background-color:#f8f8f8">';
			else
				echo '<tr style="background-color:#e8f0f0">';
			echo "<td valign=top>".$d["lastname"].' '.$d["firstname"]."</font></td><td>".getFieldValue($d["user"])."</td><td>";
			echo "<a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a>";
			echo "<td>".getFieldValue($d["phone"])."</td><td>".getFieldValue($d["mobil"])."</td><td>".getFieldValue($d["skype"])."</td><td>".getFieldValue($d["ip"])."</td><td>".getFieldValue($d["date"])."</td>";
			echo "</td></tr>";
		
		}
		?>
		</table>
	<?PHP } ?>
	<?PHP if ($tabOpen==2) {?>
		<table style="width:70%;border-collapse:collapse" align="center" >
		<tr style="text-align:center;font-weight:bold;"><td>Név</td><td>E-Mail</td><td>Telefon</td><td>Mobiltelefon</td><td>Skype</td><td>IP</td><td>Datum</td></tr>
		<?PHP
		foreach ($data as $idx => $d) {
			if ($d['admin']!="") {
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
</td></tr></table>
</body>
</html>
