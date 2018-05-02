<?php
include_once 'tools/logger.class.php';

setLoggerType(loggerType::file);

if (!isset($SiteTitle))
	$SiteTitle="A kolozsvári Brassai Sámuel líceum véndiakjai";

$webAppVersion="20180502";  //Used to load the actual css und js files.

$SupportedLang = array("hu"); //First language ist the default language.

// Set languge include file
if (!isset($_SESSION['LANG'])) $_SESSION['LANG']=$SupportedLang[0];
// Change language
if (isset($_GET["language"]))  {
	$_SESSION['LANG']=$_GET["language"];
}

$LangFile = "Lang_".$_SESSION['LANG'].".php";
if(file_exists($LangFile))
	include $LangFile;
else
	include "Lang_".$SupportedLang[0].".php";


function getTextRes($index) {
	global $TXT;
	if (isset($TXT[$index]))
		return  $TXT[$index];
	else {
		return "#".$index."#";
	}
}
?>