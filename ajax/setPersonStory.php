<?php
include_once __DIR__ . '/../lpfw/sessionManager.php';
include_once __DIR__ . '/../lpfw/ltools.php';
include_once __DIR__ . '/../lpfw/userManager.php';
include_once __DIR__ . '/../dbBL.class.php';


$personId = getPostParam("id","" );
$type = getPostParam("type", "");
$text = getPostParam("story", "");
$privacy = getPostParam("privacy", "class");


if ( userIsAdmin() || userIsEditor() || userIsSuperuser() || isAktUserTheLoggedInUser() ) {
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
    $row = array();
	$row["classid"] = getAktClassId();
	$row["person"] = $personId;
	$row["type"] = $type;
	$row["privacy"] =$privacy;
	
	$row["story"] = substr($text,0,40)."...";
    header('Content-Type: application/json');
    echo(json_encode($row));
}
else {
    http_response_code(401);
    $row["error"] = "Not authorized!";
    die();
}

?>