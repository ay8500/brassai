<?php
include_once 'tools/appl.class.php';

use \maierlabs\lpfw\Appl as Appl;

if (isset($_GET["gallery"]))
	$gallery=$_GET["gallery"];
else
	$gallery="BALLAGAS";

$SiteTitle="A kolozsvári Brassai Sámuel líceum véndikok képtára: ".$gallery;
Appl::$subTitle='Emlékeik képekben';
Appl::addCss("ig/ig.css");

include("homemenu.php");
$_SESSION['multipleGalleries']=0;
include ("ig/igframe.php"); 
include ("homefooter.php");
