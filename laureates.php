<?php
$SiteTitle="Brassai Sámuel díjas tanulói"; 
$SiteDescription="Kitüntetett díjazott diákok";
include("homemenu.php"); 
include_once 'editDiakCard.php';
?>
<h2 class="sub_title">Juhász Máté István díjasok</h2>
<div class="well">Az iskolánk legjelentősebb díját a Juhász Máté István Emlékdíjat az iskola egykori diákja, a műegyetem hallgatójaként rákban elhunyt kiváló tanuló emlékére alapította családja és ösztályfőnöke, Gáll Dénes.</div>
<?php 

$personList = array(
		$db->getPersonByID(2854), //Zsakó István
		$db->getPersonByID(1330), //Szűcs Zoltán
		$db->getPersonByID(3499), //Somay Antal-István
		$db->getPersonByID(3512), //Gábor Miklós
		$db->getPersonByID(2409), //Tálas András
		$db->getPersonByID(1457), //Karaki Dorottya
		$db->getPersonByID(2474), //Kántor Melinda
		createPerson("Csoma","Botond",1993),
		createPerson("Kovács","Ferenc",1994),
		createPerson("Ballon","Zsuzsa",1995),
		createPerson("Kopándi","Zoltán",1996),
		createPerson("Hosu","Andrea",1997),
		$db->getPersonByID(1701), //Máté Adél
		$db->getPersonByID(1547), //Fejér Ákos
		createPerson("Király","Tibor",2000),
		$db->getPersonByID(1461), //Jancsó Hajnal
		createPerson("Buchmüller","István",2011),
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