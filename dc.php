<?php
$su = explode("/",$_SERVER["REDIRECT_REDIRECT_SCRIPT_URL"]);

$qs = explode("-",$_SERVER["REQUEST_URI"]) ;


if  (($su[1]=='Levi') || ($su[1]=='levi'))  {
  header('Location:Maier_Levente');
}

else if  ($su[1]=='impressum') {
  header("status: 200"); 
  include_once('impressum.php');
}
else if (sizeof($qs)>1) {
	print_r($qs);
}
else {
	$nameList = explode("_", $su[1]);
	$diak["lastname"]=$nameList[0];
	$diak["firstname"]=$nameList[1];
	//TODO
	$scoolYear=null;
	$scoolClass=null;
	include_once('data.php');
	$usr =getGlobalUser($diak,"compairUserLink",$scoolYear,$scoolClass);
	if (null != $usr) {
		setAktUserId($usr["id"]);
		include ("editDiak.php");
		exit();
	}
	header("status: 404"); 
	$SiteTitle="A kolozsvári Brassai Sámuel líceum: Hiba oldal";

	//echo(include_once("homemenu.php")); 
	?>
	
	<h2 class="sub_title">Sajnos ez az oldal nem létezik ezen a szerveren.</h2>
	Keresett oldal:
	<?PHP
	echo($su[1]." ");echo($su[2]." ");echo($su[3]." ");
	
	//include_once("homefooter.php");
 }
?>

