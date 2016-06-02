<?php
include_once 'ltools.php';

//$su = explode("/",$_SERVER["REDIRECT_REDIRECT_SCRIPT_URL"]);

if(getGetParam("p", "")!="") {
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
if (sizeof($qs)>2) {
	include_once("sessionManager.php");
	include_once ('userManager.php');
	setAktScoolYear(substr($qs[1],3,4));
	setAktScoolClass(substr($qs[1],0,3));
	setAktUserId($qs[2]);
	$diak=getPerson($qs[2],$qs[1]);
	if ($diak==null) {
		error();
		exit;
	} else {
		include ("editDiak.php");
		exit();
	}
} else {
	error();
	exit;
}

function error() { 
	header("status: 404"); 
	$SiteTitle="A kolozsvári Brassai Sámuel líceum: Hiba oldal";
	//echo(include_once("homemenu.php")); 
	?>
	<h2 class="sub_title">Sajnos ez az oldal nem létezik ezen a szerveren.</h2>
	<?php
		//echo("Keresett oldal: ".$su[1]." ");echo($su[2]." ");echo($su[3]." ");
		//include_once("homefooter.php");
}	

