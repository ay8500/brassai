<?php
include_once 'sessionManager.php';
include_once 'ltools.php';
include_once 'data.php';
include_once 'sendMail.php';

$title = getParam("title", "");
$tab = getParam("tab", "");
$name = getParam("name", "");
$code = getParam("code", "");

if ($code!=$_SESSION['SECURITY_CODE']) {
	http_response_code(401);
	echo ("Biztonságí cód nem helyes. Probáld még egyszer!");
	return;
}

if ( !isset($_SESSION["MoreRequestUid"]) || (isset($_SESSION["MoreRequestUid"]) && $_SESSION["MoreRequestUid"]!=getAktUserId()) ) {

	//TODO
	//$_SESSION["MoreRequestUid"]=getAktUserId();
	$row = array();
	$row["userId"] = getAktUserId();
	$row["year"] = getAktScoolYear();
	$row["class"] = getAKtScoolClass();
	$row["title"] = $title;
	$row["tab"] = $tab;
	
	$diak=getPerson(getAktUserId(),getAktDatabaseName());
	$key=generateAktUserLoginKey();
	
	$text="Kedves ".$diak["lastname"]." ".$diak["firstname"].",<br /><br />";
	$text .="ezt az üzenetet Brassai Sámuel liceum végzős diakjai honoldaláról azért kaptad, mert ".$name;
	$text .="szertné ha többet olvashatna rólad a ".$title." oldalon.<br />< br/>";
	$text .="Légyszíves szakíts két perc időt és egészítsd ki az oldalt egy egyszerü kattintással a következö linkre.";
	$text .='<a href="http://brassai.blue-l.de/editDiak.php?tabOpen='.$tab.'&key='.$key.'">Most szeretném vándiák oldalam kiegészíteni</a><br /><br />'; 
	$text .='Üdvözlettel '.$name;
	
	//TODO
	//$diak["email"]
	if (!sendTheMail("levi@blue-l.de",$text,$title)) {
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
header('Content-Type: application/json');
echo(json_encode($row));
?>