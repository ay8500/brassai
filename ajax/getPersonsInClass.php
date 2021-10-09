<?php
/*
Used by sign in procedure (singnin)
*/
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';

header('Content-Type: application/json');

global $db;
$class=$db->getClassByText(getParam("class"));
if (null==$class) {
	$class=$db->getClassById(getParam("classid"));
}

$guest = getParam("guest","")=="true";
$fid=getParam("fid","");

$personList = array();

if ($class!=null) {
	$persons=$db->getPersonListByClassId(getRealId($class),$guest,strlen($fid)>5,false,true);
	foreach ($persons as $p) {
		$n=array();
		$n["id"]=$p["id"];
		$n["firstname"]=$p["firstname"];
		$n["lastname"]=$p["lastname"];
		$n["birthname"]=$p["birthname"];
        $n["title"]=$p["title"];
        $n["gender"]=$p["gender"];
		if (showField($p,"email"))
			$n["email"]= getFieldValueNull($p,"email");
		else
			$n["email"]="";
		array_push($personList, $n);		
	}
}

echo(json_encode($personList));
?>