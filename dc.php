<?php
$su= explode("?",$_SERVER["REQUEST_URI"]);
$su = explode("/",$su[0]);

if (sizeof($su)>2) {
	$location ="Location: http://brassai.blue-l.de/".$su[1];
	for ($i=2;$i<sizeof($su);$location .="-".$su[$i++]);
	header($location);
	die();
}
include_once 'ltools.php';

if(getGetParam("p", "")=="") {
	$qs = explode("-",$_SERVER["REQUEST_URI"]) ;
} else {
	$qs = explode("-",getGetParam("p", ""));
}
/*
if  (($su[1]=='Levi') || ($su[1]=='levi'))  {
  header('Location:Maier_Levente');
}

else if  ($su[1]=='impressum') {
  header("status: 200"); 
  include_once('impressum.php');
}
*/
if (sizeof($qs)>1) {
	include_once("sessionManager.php");
	include_once ('userManager.php');
	include_once 'data.php';
	setAktUserId($qs[1]);
		$diak=$db->getPersonByID($qs[1]);
		if ($diak==null) {
			error();
			exit;
		} else {
			header("status: 200"); 
			include ("editDiak.php");
			exit();
		}
	}
} 
error();
exit;

function error() { 
	header("status: 404"); 
	$SiteTitle="A kolozsvári Brassai Sámuel líceum: Hiba oldal";
	$siteHeader="<link href='http://fonts.googleapis.com/css?family=Satisfy' rel='stylesheet' type='text/css'>";
	include_once("homemenu.php"); 
	?>
	<h2 class="sub_title">Sajnos ez az oldal nem létezik ezen a szerveren.</h2>
	<div style="background-image: url('images/kretatabla.jpg');background-size: cover;height: 600px;margin: 20px;border-radius: 30px;">	
		<div style="font-family: Satisfy;text-align: center;vertical-align:middle;color:white;padding-top:100px">
			<h1>Sajnos ez az oldal nem létezik ezen a szerveren.</h1>
			<h2>Keresett oldal: <?php echo $_SERVER["REQUEST_URI"]?></h2>
		</div>
	</div>
	<?php
		include_once("homefooter.php");
}	

