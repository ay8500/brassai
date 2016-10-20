<?php
header('Content-Type: application/json');

include_once("sessionManager.php");
include_once("data.php");
include_once("ltools.php");

$idList= getGetParam("ids", "");
$idArray = explode(",", $idList);


$p=getRandomPerson();
$i=0;
while (($p["picture"]=="avatar.jpg" || 								//No Empty pictures
		notUnique($idArray,$p["class"],$p["year"],$p["id"]) ||		//Unique entrys
		sizeof($data)<8												//More than 8 entrys in the db
		) && 
		$i++<10)													//Check only 10 times
{
	$p=getRandomPerson();
}

$person = Array();

$person["name"]=$p["lastname"]." ".$p["firstname"];
$person["id"]=$p["class"]."-".$p["year"]."-".$p["id"];
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
	

echo(json_encode($person));

/**
 * Get a random person  
 */
function getRandomPerson() {
	global $db;
	$classList=$db->getClassList();
	$rec=array();
	$rec["graduationYear"]="teac";
	$rec["name"]="ooo";
	$rec["id"]="0";
	array_push($classList, $rec);
	
	$class=$classList[rand(0,sizeof($classList)-1)];
	
	
	$data=$db->getPersonListByClassId($class["id"]);
	
	$idx=rand(0,sizeof($data)-1);
	
	$p=$data[$idx];
	$p["class"]=$class["name"];
	$p["year"]=$class["graduationYear"];
	
	return $p;
}


/**
 * Get a random picture
 */
/*
function getRandomPicture() {
	global $data;
	$dblist = getDatabaseList();
	
	$allPictures = Array();
	foreach ($dblist as $dbname) {
		$pictures = getListofPictures($dbName.'group','all', false) ;
		array_push($allPictures, $pictures);
	}
	$dbidx=rand(0,sizeof($allPictures)-1);

	openDatabase(substr($dblist[$dbidx],5,3).substr($dblist[$dbidx],0,4));

	$idx=rand(0,sizeof($data)-1);

	$p=$data[$idx];
	$p["class"]=substr($dblist[$dbidx],5,3);
	$p["year"]=substr($dblist[$dbidx],0,4);

	return $p;
}
*/

/**
 * Person identifikation is allready in the idArray
 * @param $idArray list of ids
 * @param Person id:  $class-$year-$id
 */
function notUnique($idArray,$class,$year,$id) {
	foreach ($idArray as $idtext) {
		if ($idtext==$class."-".$year."-".$id) {
			return true;
		}
			
	}
	return false;
}	

?>
