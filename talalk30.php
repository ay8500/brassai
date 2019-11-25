<?PHP
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
use \maierlabs\lpfw\Appl as Appl;

$sTitle="30-éves véndiák találkozó";
Appl::setSiteTitle($sTitle,$sTitle);
include("homemenu.inc.php");
?>

<div class="container-fluid">
	<div class="well" style="margin-top: 20px;">
		<h1>30 éves éretségi találkozó</h1>
		
		<h3>Programajánlat:</h3>
		<h4>Augusztus 13 csütörtök:</h4> 
		<ul>
			<li>Délután  6-7 óra körül temetőlátogatás,	találkozás a Házsongárd előtt.</li>
		</ul>
		
		<h4>Augusztus 14 péntek:</h4> 
		<ul>
			<li>Délelött 10 órakkor osztályfőnöki óra a régi épület biológia laborjában.</li>
			<li>délután indulás Torockora.
			<a href="https://www.google.de/maps/place/46%C2%B028'18.8%22N+23%C2%B034'58.8%22E/@46.471876,23.58299,398m/data=!3m2!1e3!4b1!4m2!3m1!1s0x0:0x0"   target="_blank"">Panzió a térképen.</a></li> 
			<li>Este 7-kor előétel,</li>
			<li>majd 9 óra körül: finom főétel (sült, csirkecomb, petrezselymes krumpli, savanyúság),</li> 
			<li>utána: somodi kalács (torockói specialitás) <br />
			<a href="http://torocko.org/somodi_kalacs.html"  target="_blank"><img class="img-responsive" style="max-width: 200px" src="http://torocko.org/images/somodikalacs.jpg" /></a></li>
			<li>éjfél után: töltött káposzta.</li>
		</ul>
		 
		<h4>Augusztus 15 szombat:</h4>
		<ul> 
			<li>Reggelizés</li>
			<li> Reggel 10 óra körül indulás a 38,6km-re lévő Szolcsvai-búvópatakhoz.<a  target="_blank" href="https://www.google.de/maps/dir/Sub+Piatr%C4%83,+Rum%C3%A4nien/46.4718537,23.5829783/@46.3986645,23.5129027,19938m/data=!3m1!1e3!4m9!4m8!1m5!1m1!1s0x474948e358644b8f:0x78616929543288dc!2m2!1d23.4607753!2d46.3833121!1m0!3e0?hl=hu"><b>útleírás</b></a><br />
		Itt hagyjuk az autókat, elmegyünk a barlangszájig <a href="https://ssl.panoramio.com/photo/98918629"  target="_blank"><b>itt jön ki a patak a sziklából</b><br />
		<img class="img-responsive" src="https://static.panoramio.com.storage.googleapis.com/photos/small/98918629.jpg" /></a><br />
		egy lengőhídon átmegyünk, majd felsétálunk az erdőben, kb. 1,6 km, kiérünk egy nyeregbe, innen ereszkedünk le a <a  target="_blank" href="https://ssl.panoramio.com/photo/42505289"><b>Dilbina-vízeséshez</b></a>, itt gyűl össze 3 patak vize, és folyik be a szikla alá. 
		Könnyű túra, tényleg nem szabad kihagyni, gyönyörű kilátással. Nem olyan nehéz, mint a Székelykő, messziről sem. Persze, ezért még nem kötelező, csak ajánlott.</li>
		<li>Visszafelé megállunk az Aranyos partján valahol, tüzet rakunk, szalonnát sütünk. Délben tehát nincs ebéd.</li>
		<li>Délután közös gulyás lesz, mint a múltkor.</li>
		<li>Este bulizás, jókedv, éneklés, tánc, beszélgetés.</li>
		</ul>
		<h4>Augusztus 16 vasárnap:</h4>
		<ul> 
			<li>Reggelizés</li>
			<li>Csomagolás és hazaindulás.</li>
		</ul>
	</div>
</div>

<?PHP  include("homefooter.inc.php");?>