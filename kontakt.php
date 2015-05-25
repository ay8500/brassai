<?PHP include("homemenu.php"); ?>

<h4 style="text-align:center"><font face="Arial">Az osztálytársak kontakt adatai:</font></h4>
<?php 
include_once("UserManager.php");

if (userIsAdmin()) {
	include_once("data.php");
	openDatabase(getAktDatabaseName());
?>
<p style="text-align:center">
	<a href="getExcelData.php?data=Kontakt">Kontaklista letöltése Excel formátumban</a>&nbsp;|&nbsp;
	<a href="getExcelData.php?data=All">Összes adatok letöltése Excel formátumban</a>
</p>
<table style="width:70%;border-collapse:collapse" align="center" >
	<tr style="text-align:center;font-weight:bold;"><td>Név</td><td>E-Mail</td><td>Telefon</td><td>Mobiltelefon</td><td>Skype</td></tr>
	<?PHP
	for ($l=0;$l<sizeof($data);$l++) {
		$d=getPerson($l);
		if (($l % 2) ==0) 
			echo '<tr style="background-color:#f8f8f8">';
		else
			echo '<tr style="background-color:#e8f0f0">';
		echo "<td valign=top align=right>".$d["lastname"].' '.$d["firstname"]."</font></td><td>";
		echo "<a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a>";
		echo "<td>".getFieldValue($d["phone"])."</td><td>".getFieldValue($d["mobil"])."</td><td>".getFieldValue($d["skype"])."</td>";
		echo "</td></tr>";
	
	}
}
else
	echo "<div>Adat hozzáférési jog hiányzik!</div>";

?>

</table>


	</td>
</tr>
</table>
</body>
</html>
