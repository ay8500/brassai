<?PHP 
	include_once 'sessionManager.php';
	$facebook = isset($_SESSION['FacebookId']);
	if ($facebook) {
		$file=fopen("facebooklogin.log","a");
		fwrite($file,$_SERVER["REMOTE_ADDR"]."\t".date('d.m.Y H:i')."\t".print_r($_SESSION,true)."\r\n");
	}
	if (isset($_GET["action"]) && ($_GET["action"]=="logoff")) { 
		header("Location: index.php");
	}	
	$SiteTitle="A kolozsvári Brassai Sámuel véndiákok bejelentkezési oldala";
	include("homemenu.php"); 
	include_once("logon.php"); 
?>
<div class="sub_title">Bejelentkezés</div>
<?PHP 
if (isset($_SESSION['UID'])&&($_SESSION['UID']>0)) { 
	$person=getPersonLogedOn();
?>
<div style="text-align:left">
<table style="width:600px" class="pannel" align="center">
	<tr style="font-size:12px; font-weight:bold">
	<td>
		<div>Kedves <?PHP echo($person['lastname'].' '.$person['firstname']);?> sikeresen bejelentkeztél a brassaista véndiákok honlapjára.</div>
	</td></tr>
	<tr>
		<td><hr/></td>
	</tr>
	<tr style="font-size:12px; font-weight:bold">
		<td>Gyors linkek:</td>
	</tr>
	<tr><td>
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/myData.png" /><a href="editDiak.php">Az én adataim </a> címem, foglalkozásom, gyerekek, képek, beállítások </div>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/address.png" /><a href="hometable.php">Osztálytársak</a> diákkori névsor szerint. </div>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/talalk.png" /><a href="zenetoplista.php">Zenetoplista</a>Zene a 25-éves találkozón </div>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/world.png" /><a href="worldmap.php">A világtérkép</a> az osztálytársakkal.</div>
	</td></tr><tr><td>
		<hr />	
	<tr style="font-size:12px; font-weight:bold">
		<td>Újdonságok:</td>
	</tr>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/news.png" /></div>
	</td></tr><tr><td>	
	<table>
		<tr><td>Május 2010</td><td><a href="zenetoplista.php">Zenetoplista</a></td></tr>
		<tr><td>Junius 2010</td><td>Képek: <a href="pictureGallery.php?gallery=SzepIdok">Régi szép idők</a> csak bejelentkezet osztálytársak részére</td></tr>
		<tr><td>Julius 2010</td><td><a href="hometable.php?guests=true">Tanárok, vendégek és jó barátok</a> adatainak a megtekintési lehetősége.</td></tr>
	</table>
	</td></tr>
</table>	
	
	<?PHP
}
else {
	if (isset($_GET["action"]) && ($_GET["action"]=="logon")) { 
	?>
		<div class="sub_title" style="color:red">Sajnos a bejelentkezés nem sikerült. </div>
		<div style="text-align:center">Lehetséges rosszul írtad be a beceneved vagy lejszavad. Probálkozz még egyszer!</div>
	<?php }
	elseif (isset($_GET["action"]) && ($_GET["action"]=="lostpassw")) {
		include("lostPassw.php");
	} elseif ($facebook) { ?>
		<div class="sub_title" style="color:red">Sajnos a bejelentkezés Facebookon keresztül nem sikerült. </div>
		<div style="text-align:center">Probálkozz még egyszer!</div>
	<?php 
	} 
}
?>

<div>
</div>
</td></tr></table>
</body>
</html>
