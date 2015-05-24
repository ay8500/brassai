<?php
include_once 'sessionManager.php';
include_once 'ltools.php';
include_once 'data.php';
include_once 'userManager.php';

$personId = getPostParam("id","" );
$type = getPostParam("type", "");
$story = getPostParam("story", "");
$privacy = getPostParam("privacy", "class");

$row = array();

if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() ) {
	saveTextData(getAktDatabaseName(), $personId, $type,$privacy,htmlspecialchars_decode(urldecode($story))) ;
	$row["database"] = getAktDatabaseName();
	$row["person"] = $personId;
	$row["type"] = $type;
	$row["privacy"] =$privacy;
	
	$row["story"] = substr($story,0,40)."...";
}
else
	$row["error"] = "Not authorized!";

header('Content-Type: application/json');
echo(json_encode($row));

?>