<?PHP 
	$SiteTitle="A kolozsv�ri Brassai S�muel v�ndi�kok bejelentkez�si oldala";
	include("homemenu.php"); 
	include_once("logon.php"); 
?>
<div class="sub_title">Bejelentkez�s</div>
<?PHP 
if (isset($_SESSION['UID'])&&($_SESSION['UID']>0)) { 
	$person=getPersonLogedOn();
?>
<div style="text-align:left">
<table style="width:600px" class="pannel" align="center">
	<tr style="font-size:12px; font-weight:bold">
	<td>
		<div>Kedves <?PHP echo($person['lastname'].' '.$person['firstname']);?> sikeresen bejelentkezt�l a brassaista v�ndi�kok honlapj�ra.</div>
	</td></tr>
	<tr>
		<td><hr/></td>
	</tr>
	<tr style="font-size:12px; font-weight:bold">
		<td>Gyors linkek:</td>
	</tr>
	<tr><td>
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/myData.png" /><a href="editDiak.php">Az �n adataim </a> c�mem, foglalkoz�som, gyerekek, k�pek, be�ll�t�sok </div>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/address.png" /><a href="hometable.php">Oszt�lyt�rsak</a> di�kkori n�vsor szerint. </div>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/talalk.png" /><a href="zenetoplista.php">Zenetoplista</a>Zene a 25-�ves tal�lkoz�n </div>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/world.png" /><a href="worldmap.php">A vil�gt�rk�p</a> az oszt�lyt�rsakkal.</div>
	</td></tr><tr><td>
		<hr />	
	<tr style="font-size:12px; font-weight:bold">
		<td>�jdons�gok:</td>
	</tr>
	</td></tr><tr><td>	
		<div><img src="images/T.GIF" style="width:40px"/><img src="images/news.png" /></div>
	</td></tr><tr><td>	
	<table>
		<tr><td>M�jus 2010</td><td><a href="zenetoplista.php">Zenetoplista</a></td></tr>
		<tr><td>Junius 2010</td><td>K�pek: <a href="pictureGallery.php?gallery=SzepIdok">R�gi sz�p id�k</a> csak bejelentkezet oszt�lyt�rsak r�sz�re</td></tr>
		<tr><td>Julius 2010</td><td><a href="hometable.php?guests=true">Tan�rok, vend�gek �s j� bar�tok</a> adatainak a megtekint�si lehet�s�ge.</td></tr>
	</table>
	</td></tr>
</table>	
	
	<?PHP
}
else {
	if (isset($_GET["action"]) && ($_GET["action"]=="logon")) { 
	?>
	<div class="sub_title" style="color:red">Sajnos a bejelentkez�s nem siker�lt. </div>
	<div style="text-align:center">Lehets�ges rosszul �rtad be a beceneved vagy lejszavad. Prob�ld meg m�g egyszer!</div>
	<?PHP }
	include("lostPassw.php");
}
?>

<div>
</div>
</td></tr></table>
</body>
</html>
