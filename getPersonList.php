<?php
header('Content-Type: application/json');

include_once("tools/sessionManager.php");
include_once("data.php");
include_once("tools/ltools.php");


$class=$db->getClassByText(getParam("class"));
$guest = getParam("guest","")=="true";
$fid=getParam("fid","");

if ($class!=null) {
	$personList=$db->getPersonListByClassId(getRealId($class),$guest,strlen($fid)>5);
} else {
	$personList = array();
}

echo(json_encode($personList));
?>