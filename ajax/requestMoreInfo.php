<?php
/*
 * Send a request mail to the actual user, to extend his user storys.
 */
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../config.class.php';
include_once __DIR__ . '/../sendMail.php';

$code = getParam("code", "+");

if (!isUserLoggedOn() && $code!=$_SESSION['SECURITY_CODE']) {
	http_response_code(400);
	echo ("Biztonságí kód nem helyes. Probáld még egyszer!");
	return;
}

if (!isUserLoggedOn() && getParam("name", "")=="") {
	http_response_code(400);
	echo ("Kérlek írd be a neved!");
	return;
}

$title = getParam("title", "");
$tab = getParam("tab", "");
$name = getParam("name", "");


if ( !isset($_SESSION["MoreRequestUid"]) || (isset($_SESSION["MoreRequestUid"]) && $_SESSION["MoreRequestUid"]!=getActUserId()) ) {

	$_SESSION["MoreRequestUid"]=getActUserId();

	$diak=$db->getPersonByID(getActUserId());
	$key=generateAktUserLoginKey();
	
	$text="Kedves ".$diak["lastname"]." ".$diak["firstname"].",<br /><br />";
	$text .="ezt az üzenetet a ".getActSchoolName()." végzős diakjainak honoldaláról azért kaptad, mert ".$name;
	$text .=" szertné ha többet olvashatna rólad az -".$title."- oldalon.<br /><br />";
	$text .="Légyszíves szakíts két perc időt és egészítsd ki az oldalt egy egyszerü kattintással a következö linkre.";
	$text .='<a href="'.Config::$siteUrl.'/editDiak?tabOpen='.$tab.'&key='.$key.'">Most szeretném vándiák oldalam kiegészíteni</a><br /><br />';
	$text .='Üdvözlettel '.$name;

	if (!\maierlabs\lpfw\Appl::sendHtmlMail(getFieldValue($diak["email"]),$text,"Kérés kiegészítésre.")) {
		http_response_code(400);
		echo ("Üzenet küldése sajnos nem sikerült. Probákozz késöbb újból.");
		return;
	}
	
}
else {
	http_response_code(400);
	echo ("Ennek a felhasználonak már küldtél üzenetet!");
	return;
}

echo($text);
?>