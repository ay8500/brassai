<?php
header('Content-Type: application/json');

include_once("sessionManager.php");
include_once("data.php");
include_once("ltools.php");

$idList= getGetParam("ids", "");
$idArray = explode(",", $idList);


$p=getRandomPerson();
while ($p["picture"]=="avatar.jpg" || notUnique($idArray,$p["class"],$p["year"],$p["id"])) {
	$p=getRandomPerson();
}

$person = Array();

$person["name"]=$p["firstname"]."&nbsp".$p["lastname"];
$person["id"]=$p["class"]."-".$p["year"]."-".$p["id"];
$person["image"]=$p["picture"];
if (isset($p["education"]) && showField($p,"education")) 
	$person["education"]=getFieldValue($p,"education");
if (isset($p["employer"]) && showField($p,"employer"))
	$person["employer"]=getFieldValue($p,"employer");



echo(json_encode($person));

function getRandomPerson() {
	global $data;
	$dblist = getDatabaseList();
	
	$dbidx=rand(0,sizeof($dblist)-1);
	
	openDatabase(substr($dblist[$dbidx],5,3).substr($dblist[$dbidx],0,4));
	
	$idx=rand(0,sizeof($data)-1);
	
	$p=$data[$idx];
	$p["class"]=substr($dblist[$dbidx],5,3);
	$p["year"]=substr($dblist[$dbidx],0,4);
	
	return $p;
}

function notUnique($idArray,$class,$year,$id) {
	foreach ($idArray as $idtext) {
		if ($idtext==$class."-".$year."-".$id) {
			return true;
		}
			
	}
	return false;
}	

?>
