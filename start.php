<?PHP 
	include_once 'sessionManager.php';
	$facebook = isset($_SESSION['FacebookId']);
	if ($facebook) {
		$file=fopen("facebooklogin.log","a");
		fwrite($file,$_SERVER["REMOTE_ADDR"]."\t".date('d.m.Y H:i')."\t".print_r($_SESSION,true)."\r\n");
	}
	$SiteTitle="A kolozsvári Brassai Sámuel véndiákok bejelentkezési oldala";
	
	include_once 'ltools.php';
	include_once 'data.php';
	
	$scoolYear=getParam("scoolYearFb","");
	$scoolClass=getParam("scoolClassFb","");
	
	$userId=getIntParam("userId",-1);
	if ($userId>=0) {
		$person = getPerson($userId,getAktDatabaseName());
		$person["facebookid"]= $_SESSION["FacebookId"];
		savePerson($person);
	}

	include("homemenu.php");
?>

<div class="sub_title">Start</div>
	<?php if (userIsLoggedOn()) {?>
	<div class="container-fluid">
		<div class="panel panel-default">
			<div class="panel-heading"><h4>Véndiák Újdonságok:</h4></div>
			<div class="panel-body">
			<ul id="newData">
			<li>1985 12A Barabási Jenő Facebook felhasználó kapcsolatot létesített</li>
			<li>1985 12B Bodó Steven (István) Személyes adatait módósította. </li>
			<li>1985 12A Hinschütz Enikő (Bereczki) Személyes adatait módósította. </li>
			<li>1985 12B Berger Mária (Molnár) Személyes adatait módósította. </li>
			<li>1985 12A Kiss Edit Személyes adatait módósította. </li>
			<li>1985 12B Deák Attila Személyes adatait módósította. </li>
			<li>1985 12B Deák Attila Személyes adatait módósította. </li>
			<li>1985 12B Márton Åsgrim Anikó (Márton) Személyes adatait módósította. </li>
			<li>1985 12B Darai Gabriella (Pallos) Személyes adatait módósította. </li>
			<li>1985 12A Benedek Zsolt Személyes adatait módósította. </li>
			</ul>
			</div>
		</div>
	</div>
	<?php } elseif (getParam("action","")=="logon") { 
	?>
		<div class="sub_title" style="color:red">Sajnos a bejelentkezés nem sikerült. </div>
		<div style="text-align:center">Lehetséges rosszul írtad be a beceneved vagy lejszavad. Probálkozz még egyszer!</div>
		<?php 
		include("lostPassw.php");
		}
	elseif (getParam("action","")=="lostpassw" || getParam("action","")=="newUser" || getParam("action","")=="newPassword") {
		include("lostPassw.php");
	} elseif ($facebook) { 
		include("facebooklogin.php");
	} else {
	?>
	<div class="container-fluid">
		<div class="well">
			Nem vagy bejelentkezve, ezért nincs hozzáférésed minden oldalhoz.
			<button class="btn btn-default" onclick="handleLogon();" >Bejelentkezés</button>
		</div>
	</div>
	<?php 
	}

?>
<div class="container-fluid">
	<div class="panel panel-default">
	<div class="panel-heading"><h4>Honoldal Újdonságok:</h4></div>
		<div class="panel-body">
			<ul>
				<li>Május 2015: Honoldal mobil készülékekkel is kompatibilis.</li>
				<li>Május 2015: A véndiákok életrajzzal, diákkori történetekkel és hobbikkal egészíthetik ki a profiljukat.</li>
				<li>Aprilis 2015: Bejelentkezés Facebook felhasználóval.</li>
				<li>Julius 2010:<a href="hometable.php?guests=true">Tanárokal, vendégekkel és jó barátokal</a> bővült az oldal.</li>
				<li>Junius 2010: Képek <a href="pictureGallery.php?gallery=SzepIdok">Régi szép idők</a></li>
				<li>Május 2010: Zene toplista <a href="zenetoplista.php">Zenetoplista</a></li>
			</ul>
		</div>
	</div>
</div>

<?php include 'homefooter.php';?>

<script type="text/javascript">
	
</script>
