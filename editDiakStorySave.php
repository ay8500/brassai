<?php
include_once 'tools/sessionManager.php';
include_once 'tools/ltools.php';
include_once 'data.php';
include_once 'tools/userManager.php';

$personId = getPostParam("id","" );
$type = getPostParam("type", "");
$text = getPostParam("story", "");
$privacy = getPostParam("privacy", "class");

$row = array();

if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() ) {
	if ($privacy=="world") $text="~~".$text;
	if ($privacy=="scool") $text="~".$text;
	
	$text=htmlspecialchars_decode(urldecode($text));
	
	$p=$db->getPersonByID($personId);

	if ($type=="story") 
		$p["story"]=$text;	
	else if ($type=="cv") 
		$p["cv"]=$text;	
	else if ($type=="spare") 
		$p["aboutMe"]=$text;
	
	$db->savePerson($p);
	$row["classid"] = getAktClassId();
	$row["person"] = $personId;
	$row["type"] = $type;
	$row["privacy"] =$privacy;
	
	$row["story"] = substr($text,0,40)."...";
	saveLogInInfo("SaveStory",$personId,"",$type,true);
}
else
	$row["error"] = "Not authorized!";

header('Content-Type: application/json');
echo(json_encode($row));

?>