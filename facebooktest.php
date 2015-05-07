<?php
session_start();
$_SESSION['FacebookId'] = "965038823537045";
$_SESSION['FacebookName'] = "Levente Maier";
$_SESSION['FacebookEmail'] =  "MailLevi";


//$urlName =strtr($_SESSION['FacebookName']," ",".");
//$res = json_decode(file_get_contents('https://graph.facebook.com/'.$urlName),true);
//$_SESSION['FacebookId'] = $res["id"];

session_write_close();


//print_r($_SESSION);

header("Location: start.php");
?>