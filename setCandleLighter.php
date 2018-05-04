<?php

include_once("tools/sessionManager.php");
include_once("tools/userManager.php");
include_once("data.php");
include_once("tools/ltools.php");


$id=getIntParam("id");	
$db->setCandleLighter($id,getLoggedInUserId());
?>