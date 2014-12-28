<?php

//Options
$csDateFormat='d.m.y. H:i:s';
$ciEntrysPerPage=5;
$ciGbWidth=600;
 
//The XML Database File
define("CFileNameXmlData", "data.xml");

$CFG["EMailFrom"]='Guestbook<code@blue-l.de>';
$CFG["EMailSubject"]='Új vendégköny bejegyzés';
$CFG["EMailSendTo"]=array('<code@blue-l.de>');
$CFG["EMailCC"]=array('');
$CFG["EMailBCC"]=array('');

$SupportedLang = array("hu"); //First language ist the default language

//*********************************************************************************
// Set languge include file
if (!isset($_SESSION['LANG'])) $_SESSION['LANG']=$SupportedLang[0];
// Change language
if (isset($_GET["language"]))  {
	$_SESSION['LANG']=$_GET["language"];
}

    $LangFile = "gbLang_".$_SESSION['LANG'].".php";
    if(file_exists($LangFile))
        include $LangFile;
    else
        include "gbLang_".$SupportedLang[0].".php";
?>
