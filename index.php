<?PHP 
include_once("sessionManager.php");
include_once("data.php");

if (!isset($siteHeader)) $siteHeader='';
$siteHeader .='<link rel="stylesheet" type="text/css" href="css/wrapper.css" /> ';

include("homemenu.php");
?>
<h2 class="sub_title">Szeretettel köszöntünk a Brassaista Véndiákok honlapján</h2>
<div class="container-fluid">
	<div  style="padding:15px;" class="col-sm-6" >
		<h4>Használd ezt az oldalt, hogy kapcsolatba lépj és maradj egykori Brassaista osztálytársaiddal és iskolatársaiddal!</h4>
		Lehetőségeid a véndiákok oldalán:
		<ul>
			<li><a href="message.php">Üzenetek</a> küldése volt osztálytársaknak, volt iskolatársaknak vagy az egész világnak.</li>
			<li><a href="hometable.php">Véndiákok</a> és <a href="hometable.php?scoolYear=teac&scoolClass=ooo">Tanáraink</a> névsorának ápolása</li>
			<li>Személyes <a href="editDiak.php?uid=21&tabOpen=0&scoolYear=1985&scoolClass=12A">adatok</a> beállítása, <a href="editDiak.php?uid=21&tabOpen=3&scoolYear=1985&scoolClass=12A">történetek</a> megosztása és <a href="editDiak.php?uid=21&tabOpen=1&scoolYear=1985&scoolClass=12A">képek</a> feltötése. Természetesen Te határozod meg ki láthatja ezeket az információkat.</li>
			<li><a href="worldmap.php?scoolYear=1985&scoolClass=12A">Térképen</a> látható szétszóródása az osztálytársaknak.</li>
			<li>Találkozók szervezésére alkalmas <a href="vote.php?scoolYear=1985&scoolClass=12A">szavazati lista</a>.</li>
			<li>Osztályfelelősők körlevelet (E-mail) küldhetnek volt osztálytársuknak.</li>
			<li><a href="zenetoplista.php?scoolYear=1985&scoolClass=12A">Zenetoplista</a></li> 
		 </ul>
	</div>
	<div  style="padding:15px;" class="col-sm-6" >
		<img src="images/BRASSAIS.JPG"  alt="Brassai Sámuel" /><br/>
		<div style="font-size:12px;font-weight:bold;height:20px">Brassai Sámuel (1800-1897)</div>
	 	<div>
		   	&quot;A tanító, mint a gazda, csak magvakat vet el,<br/>
		   	melyböl a tanítvány elméjében ismeretek teremnek,<br/>
		   	mint a gabona s más termék a földben.&quot;
		</div>
	</div>
	<div id="wrapper"></div>
	<div >  			
			Ez az oldal <B>1997. junius 11.</B>-e óta elérhető.	Utoljára módósítva <b>2016. április 23.</b>-án.
	</div>
</div>
<?php  include ("homefooter.php");?>
<script type="text/javascript" src="js/wrapper.js"></script>
