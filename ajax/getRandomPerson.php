<?php
header('Content-Type: application/json');
include_once '../config.class.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';

$idList= getGetParam("ids", "");
$idArray = explode(",", $idList);

$p=getRandomPerson();
$i=0;
//Check only 10 times to get a different person
while (notUnique($idArray,$p["id"]) && $i++<10)		
	$p=getRandomPerson();

$person = Array();

$person["name"]=(isset($p["title"])?$p["title"]." ":"").$p["lastname"]." ".$p["firstname"];
$person["id"]=$p["id"];
$person["isTeacher"]=$p["isTeacher"];
if (isset($p["deceasedYear"]))
	$person["deceasedYear"]=$p["deceasedYear"];
$person["classID"]=$p["classID"];
$person["classText"]=$p["classText"];
$person["classText"].=(intval($p["classEvening"])==0)?"":" esti tagozat";
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

if (isset($p["facebook"]) && strlen($p["facebook"])>8)
	$person["facebook"]	=showField($p,"facebook") 	? getFieldValue($p,"facebook")	: 'javascript:hiddenData("Facebook");';
if (isset($p["email"]) && strlen($p["email"])>8)
	$person["email"]	=showField($p,"email") 		? 'mailto:'.getFieldValue($p,"email")	: 'javascript:hiddenData("E-Mail");';
if (isset($p["twitter"]) && strlen($p["twitter"])>8 )
	$person["twitter"]	= showField($p,"twitter") 	? getFieldValue($p,"twitter")	: 'javascript:hiddenData("Twitter");';
if (isset($p["homepage"]) && strlen($p["homepage"])>8 )
	$person["homepage"]	= showField($p,"homepage")	? getFieldValue($p,"homepage") 	: 'javascript:hiddenData("Honoldal");';

if (isset($p["function"]) && showField($p,"function"))
	$person["function"]=getFieldValue($p,"function");
if (isset($p["children"]) && showField($p,"children"))
	$person["children"]=getFieldValue($p,"children");

$person["geolocation"] = (isset($p["geolat"]) && $p["geolat"]!="")?1:0;
$person["isGuest"] = isUserGuest($p)?1:0;
	

echo(json_encode($person));

/**
 * Get a random person  
 */
function getRandomPerson() {
	global $db;
	$personList=$db->getPersonIdListWithPicture();
	
	$idrow=$personList[rand(0,sizeof($personList)-1)];
	
	$p=$db->getPersonByID($idrow["id"]);
	// Testperson
	// $p=$db->getPersonByID(700);
	$class=$db->getClassById($p["classID"]);
	$p["classText"]=$class["text"];
	$p["classEvening"]=$class["eveningClass"];
	
	return $p;
}


/**
 * Person identification is allready in the idArray
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
