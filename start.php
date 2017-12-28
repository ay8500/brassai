<?php 
include_once 'tools/sessionManager.php';

if (!isset($siteHeader)) $siteHeader='';
$siteHeader .='<link rel="stylesheet" type="text/css" href="css/wrapper.css" /> ';

//Test facebook
/*
if (true) {
	$_SESSION['FacebookId']="965038893537235";
	$_SESSION["FacebookName"]="Peter Pán";
	$_SESSION["FacebookFirstName"]="Peter";
	$_SESSION["FacebookLastName"]="Pán";
	$_SESSION["FacebookEmail"]="pp@tonilne.de";
	$_SESSION["FacebookLink"]="https://www.facebook.com/rethy.levente";
} else {
	unset($_SESSION['FacebookId']);
}
*/

if (isset($_SESSION['FacebookId'])) {
	$file=fopen("facebooklogin.log","a");
	fwrite($file,$_SERVER["REMOTE_ADDR"]."\t".date('d.m.Y H:i')."\t".print_r($_SESSION,true)."\r\n");
}
$SiteTitle="A kolozsvári Brassai Sámuel véndiákok bejelentkezési oldala";

include_once 'tools/ltools.php';
include_once 'data.php';

$schoolClass=getParam("scoolClassFb","");

$userId=getIntParam("userId",-1);
if ($userId>=0) {
	$db->savePersonFacebookId($userId,$_SESSION["FacebookId"]);
}

include("homemenu.php");
include_once 'editDiakCard.php';
?>

<?php 
if (getParam("action","")=="lostpassw" || getParam("action","")=="newPassword") {
	include("lostPassw.php");
	include ("homefooter.php");
} elseif ((isset($_SESSION['FacebookId']) || getParam("action")=="newUser")&& !userIsLoggedOn()) { 
	include("signin.php");
	include ("homefooter.php");
} else { 
?>
<div class="sub_title">Újdonságok</div>
<div class="container-fluid">
	<div class="panel panel-default col-sm-12" style="margin-right:10px;">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-user"></span> Új személyek, frissitések </h4>
		</div>
		<div class="panel-body">
		<?php 
		$persons=$db->getRecentChangedPersonList(12);
		foreach ($persons as $person) {
			displayPerson($db,$person,true,true);
		}
	?>
		</div>
	</div>

	<div class="panel panel-default col-sm-12" style="margin-right:10px;">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-picture"></span> Új fényképek:</h4>
		</div>
		<div class="panel-body">
		<?php 
		$pictures=$db->getRecentPictureList(12);
		foreach ($pictures as $picture) {
			displayPicture($db,$picture);
		}
	?>
		</div>
	</div>

	<div class="panel panel-default col-sm-12">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-home"></span> Honoldal Újdonságok:</h4>
		</div>
		<div class="panel-body">
			<ul>
				<li>December 2017: <a href="start.php"></a> Ez az oldal az utólsó frissitéseket illetve bejegyzéseket is tartalmazza.</li>
				<li>December 2016: <a href="picture.php?type=tablo&typeid=tablo">Tablók</a> albumával bővült az oldal.</li>
				<li>Március 2016: <a href="hometable.php?classid=0 staf">Tanárok</a> listályával bővült az oldal.</li>
				<li>Junius 2015: Üzenőfal híreknek, véleményeknek, szervezésnek, újdonságoknak.</li>
				<li>Május 2015: Honoldal mobil készülékekkel is kompatibilis.</li>
				<li>Május 2015: A véndiákok életrajzzal, diákkori történetekkel és hobbikkal egészíthetik ki a profiljukat.</li>
				<li>Aprilis 2015: Bejelentkezés Facebook felhasználóval.</li>
				<li>Julius 2010:<a href="hometable.php?guests=true">Vendégekkel és jó barátokal</a> bővült az oldal.</li>
				<li>Junius 2010: Képek <a href="pictureGallery.php?gallery=SzepIdok">Régi szép idők</a></li>
				<li>Május 2010: Zene toplista <a href="zenetoplista.php?classid=0">Zenetoplista</a></li>
			</ul>
		</div>
	</div>
	<div id="wrapper"></div>
	<?php  include ("homefooter.php");?>
	<script type="text/javascript" src="js/wrapper.js"></script>

<?php } ?>
