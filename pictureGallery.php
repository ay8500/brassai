<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
use \maierlabs\lpfw\Appl as Appl;

if (isset($_GET["gallery"]))
	$gallery=$_GET["gallery"];
else
	$gallery="BALLAGAS";

Appl::setSiteTitle("Képtár: ".$gallery);
Appl::setSiteSubTitle('Emlékeik képekben');
Appl::addCss("ig/ig.css");

include("homemenu.inc.php");
$_SESSION['multipleGalleries']=0;
include ("ig/igframe.php"); 
include("homefooter.inc.php");
