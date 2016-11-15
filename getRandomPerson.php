<?php
header('Content-Type: application/json');

include_once("tools/sessionManager.php");
include_once("data.php");
include_once("tools/ltools.php");

$idList= getGetParam("ids", "");
$idArray = explode(",", $idList);


$p=getRandomPerson();
$i=0;
//Check only 10 times to get a different person
while (notUnique($idArray,$p["id"]) && $i++<10)		
	$p=getRandomPerson();

$person = Array();

$person["name"]=$p["lastname"]." ".$p["firstname"];
$person["id"]=$p["id"];
$person["classID"]=$p["classID"];
$person["classText"]=$p["classText"];
$person["image"]=$p["picture"];
if (isset($p["education"]) && showField($p,"education")) 
	$person["education"]=getFieldValue($p,"education");
if (isset($p["employer"]) && showField($p,"employer"))
	$person["employer"]=getFieldValue($p,"employer");
if (isset($p["country"]) && showField($p,"country"))
	$person["place"]=getFieldValue($p,"country");
else
	$person["place"]="";
if (isset($p["place"]) && showField($p,"place"))
	$person["place"] .=" ".getFieldValue($p,"place");
if (strlen($person["place"])<5)
	unset($person["place"]);
if (isset($p["facebook"]) && showField($p,"facebook"))
	$person["facebook"]=getFieldValue($p,"facebook");
if (isset($p["email"]) && showField($p,"email"))
	$person["email"]=getFieldValue($p,"email");
if (isset($p["twitter"]) && showField($p,"twitter"))
	$person["twitter"]=getFieldValue($p,"twitter");
if (isset($p["homepage"]) && showField($p,"homepage"))
	$person["homepage"]=getFieldValue($p,"homepage");
if (isset($p["function"]) && showField($p,"function"))
	$person["function"]=getFieldValue($p,"function");
if (isset($p["children"]) && showField($p,"children"))
	$person["children"]=getFieldValue($p,"children");
$person["isGuest"]= isPersonGuest($p)?1:0;
	

echo(json_encode($person));

/**
 * Get a random person  
 */
function getRandomPerson() {
	global $db;
	$personList=$db->getPersonIdListWithPicture();
	
	$idrow=$personList[rand(0,sizeof($personList)-1)];
	
	$p=$db->getPersonByID($idrow["id"]);
	$class=$db->getClassById($p["classID"]);
	$p["classText"]=$class["text"];
	
	return $p;
}



/**
 * Person identifikation is allready in the idArray
 * @param $idArray list of ids
 * @param Person id:  $class-$year-$id
 */
function notUnique($idArray,$id) {
	foreach ($idArray as $idtext) {
		if ($idtext==$id) {
			return true;
		}
			
	}
	return false;
}	

?>
