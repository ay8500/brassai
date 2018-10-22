<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/appl.class.php';
use \maierlabs\lpfw\Appl as Appl;

Appl::setSiteTitle("Díjas tanulók");
Appl::setSiteTitle("Kitüntetett díjazott diákok");
include("homemenu.php"); 
include_once 'editDiakCard.php';
?>
<h2 class="sub_title">Juhász Máté István díjasok</h2>
<div class="well">Az iskolánk legjelentősebb díját a Juhász Máté István Emlékdíjat az iskola egykori diákja, a műegyetem hallgatójaként rákban elhunyt kiváló tanuló emlékére alapította családja és ösztályfőnöke, Gáll Dénes.</div>
<?php 

$personList = array(
		$db->getPersonByID(2854), //Zsakó István 1971
		$db->getPersonByID(1330), //Szűcs Zoltán 1973
		$db->getPersonByID(3499), //Somay Antal-István 1974
		$db->getPersonByID(3512), //Gábor Miklós 1975
		$db->getPersonByID(2409), //Tálas András 1976
		//
		$db->getPersonByID(1457), //Karaki Dorottya 1982
		//
		$db->getPersonByID(645), //Biró Katalin 1985
		//
		$db->getPersonByID(1508), //Sallai Zsuzsa 1991
		$db->getPersonByID(2474), //Kántor Melinda 1992
		$db->getPersonByID(4992), //Csoma Botond 1993
		$db->getPersonByID(4956),//"Kovács","Ferenc",1994),
		createPerson("Ballon","Zsuzsa",1995),
		$db->getPersonByID(4957), //("Kopándi","Zoltán",1996),
		$db->getPersonByID(4958), //("Hosu","Andrea",1997),
		$db->getPersonByID(1701), //Máté Adél 1998
		$db->getPersonByID(1547), //Fejér Ákos 1999
		createPerson("Király","Tibor",2000),
		$db->getPersonByID(2503),	//Dobri Réka 2001
		$db->getPersonByID(5616),	//("Farkas","Mónika",2002),
		createPerson("Kassai","Réka",2003),
		//2004
		$db->getPersonByID(2332), //Székely Zsófia 2005
		$db->getPersonByID(2860), //Kiss Réka 2006
		$db->getPersonByID(5137), //Azzola Katalin 2007
		$db->getPersonByID(1883), //("Bekő","Levente",2008),
		$db->getPersonByID(1461), //Jancsó Hajnal 2009
		//2010
		$db->getPersonByID(5556),	//("Buchmüller","István",2011),
		createPerson("Jakab","Mátyás",2012),
		$db->getPersonByID(5622),	//("Kászoni","Noémi Éva",2013), 
		$db->getPersonByID(5176),	//("Mezei","Tímea",2014),
		$db->getPersonByID(5621),	//("Bagaméri","Lilla",2015), 
		$db->getPersonByID(5620),	//("Kovács","Mirjám",2016), 
		$db->getPersonByID(5619),	//("Szallós-Kis","Csaba",2017),
		$db->getPersonByID(6011),	//Szabó Norbert 2018,
);

foreach ($personList as $d) {
	//$d = $db->getPersonByID($id);
	displayPerson($db,$d,true);
}

function createPerson($lastname,$firstname,$year) {
	$p=array();
	$p["id"]=-1;
	$p["firstname"]=$firstname;
	$p["lastname"]=$lastname;
	$p["role"]="";
	$p["isTeacher"]="0";
	$p["classID"]=-1*$year;
	return $p;
}

?>

<?php include ("homefooter.php");?>