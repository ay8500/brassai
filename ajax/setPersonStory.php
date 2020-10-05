<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';


$personId = getPostParam("id","" );
$type = getPostParam("type", "");
$text = getPostParam("story", "");
$privacy = getPostParam("privacy", "class");


if (userIsEditor() || userIsSuperuser() || isAktUserTheLoggedInUser() ) {
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
	
	if ($db->savePerson($p)>=0) {
        $row = array();
        $row["classid"] = getAktClassId();
        $row["person"] = $personId;
        $row["type"] = $type;
        $row["privacy"] = $privacy;

        $row["story"] = substr($text, 0, 40) . "...";
        header('Content-Type: application/json');
        $r = json_encode($row,JSON_PARTIAL_OUTPUT_ON_ERROR);
        if ($r!==false) {
            echo($r);
        } else {
            http_response_code(400);
            $row["error"] = "JSON Error!";
            echo(json_encode($row));
        }
    } else {
        http_response_code(400);
        $row["error"] = "Database Error!";
        echo(json_encode($row));
    }
}
else {
    http_response_code(401);
    $row["error"] = "Not authorized!";
    echo(json_encode($row));
}

?>