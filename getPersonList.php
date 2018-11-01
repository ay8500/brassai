<?php
/*
Used by sign in procedure (singnin.php)
*/
header('Content-Type: application/json');

include_once("tools/sessionManager.php");
include_once("dbBL.class.php");
include_once("tools/ltools.php");


$class=$db->getClassByText(getParam("class"));
if (null==$class) {
	$class=$db->getClassById(getParam("classid"));
}

$guest = getParam("guest","")=="true";
$fid=getParam("fid","");

$personList = array();

if ($class!=null) {
	$persons=$db->getPersonListByClassId(getRealId($class),$guest,strlen($fid)>5);
	foreach ($persons as $p) {
		$n=array();
		$n["id"]=$p["id"];
		$n["firstname"]=$p["firstname"];
		$n["lastname"]=$p["lastname"];
		$n["birthname"]=$p["birthname"];
		if (showField($p,"email"))
			$n["email"]= getFieldValueNull($p,"email");
		else
			$n["email"]="";
		array_push($personList, $n);		
	}
}


echo(json_encode($personList));
?>