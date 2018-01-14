<?php
header('Content-Type: application/json');

include_once("tools/sessionManager.php");
include_once("data.php");
include_once("tools/ltools.php");


$class=$db->getClassByText(getParam("class"));
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