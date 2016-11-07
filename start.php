<?php 
include_once 'tools/sessionManager.php';

if (!isset($siteHeader)) $siteHeader='';
$siteHeader .='<link rel="stylesheet" type="text/css" href="css/wrapper.css" /> ';

//Test facebook
/*
$_SESSION['FacebookId']="965038823537235";
$_SESSION["FacebookName"]="Teszt";
*/
$facebook = isset($_SESSION['FacebookId']);
if ($facebook) {
	$file=fopen("facebooklogin.log","a");
	fwrite($file,$_SERVER["REMOTE_ADDR"]."\t".date('d.m.Y H:i')."\t".print_r($_SESSION,true)."\r\n");
}
$SiteTitle="A kolozsvári Brassai Sámuel véndiákok bejelentkezési oldala";

include_once 'tools/ltools.php';
include_once 'data.php';

$scoolClass=getParam("scoolClassFb","");
if ($scoolClass!="")
	openDatabase(substr($scoolClass,5,3).substr($scoolClass,0,4));

$userId=getIntParam("userId",-1);
if ($userId>=0) {
	$person = $db->getPersonByID($userId);
	$person["facebookid"]= $_SESSION["FacebookId"];
	$db->savePerson($person);
}

include("homemenu.php");
include_once 'history.php';
?>

<?php 
if (getParam("action","")=="lostpassw" || getParam("action","")=="newUser" || getParam("action","")=="newPassword") {
	include("lostPassw.php");
} elseif ($facebook && !userIsLoggedOn()) { 
	include("facebooklogin.php");
} else { 
?>
<div class="sub_title">Újdonságok</div>
<div class="container-fluid">
	<?php if(userIsAdmin()) :?>
	<div class="panel panel-default col-sm-6" style="margin-right:10px;">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-user"></span> Véndiák Újdonságok:</h4></div>
		<div class="panel-body">
		<ul id="newData">
		<?php echo (readHistoryList(20)); ?>
		</ul>
		</div>
	</div>
	<?php endif;?>
	<div class="panel panel-default col-sm-5">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-home"></span> Honoldal Újdonságok:</h4></div>
			<div class="panel-body">
				<ul>
					<li>Március 2016: <a href="hometable.php?scoolYear=teac&scoolClass=ooo">Tanárok</a> listályával bővült az oldal.</li>
					<li>Junius 2015: Üzenőfal híreknek, véleményeknek, szervezésnek, újdonságoknak.</li>
					<li>Május 2015: Honoldal mobil készülékekkel is kompatibilis.</li>
					<li>Május 2015: A véndiákok életrajzzal, diákkori történetekkel és hobbikkal egészíthetik ki a profiljukat.</li>
					<li>Aprilis 2015: Bejelentkezés Facebook felhasználóval.</li>
					<li>Julius 2010:<a href="hometable.php?guests=true">Vendégekkel és jó barátokal</a> bővült az oldal.</li>
					<li>Junius 2010: Képek <a href="pictureGallery.php?gallery=SzepIdok">Régi szép idők</a></li>
					<li>Május 2010: Zene toplista <a href="zenetoplista.php">Zenetoplista</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div id="wrapper"></div>
</div>
<?php } ?>
<?php  include ("homefooter.php");?>
<script type="text/javascript" src="js/wrapper.js"></script>
