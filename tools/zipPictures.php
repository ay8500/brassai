<?php
session_start();
//User is logged in and have the role of admin
if (!isset($_SESSION['uRole']) || strstr($_SESSION['uRole'],"admin")=="") 
	die("Only for admins");

echo("Zipping Pictures<br/>");

echo exec('zip -r '.$_SERVER["DOCUMENT_ROOT"].'/images/images.zip  '.$_SERVER["DOCUMENT_ROOT"].'/images/*');
echo("<br/>Done<br/>");
