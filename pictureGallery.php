<?php
include_once 'tools/appl.class.php';

	if (isset($_GET["gallery"])) 
		$gallery=$_GET["gallery"];
	else
		$gallery="BALLAGAS";
	$SiteTitle="A kolozsvári Brassai Sámuel líceum véndikok képtára: ".$gallery;
	Appl::$subTitle='Emlékeik képekben';
	include("homemenu.php"); 
?>
<?PHP /*
	<table style="width:100%;height:100%;">
		<tr style="width:100%;height:100%">
			<td style="width:100%;height:100%;text-align:center;">
    			<iframe name="inhalt" src="ig/ig.php?gallery=<?PHP echo($gallery);?>" width=100% height=800px marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=yes>
			</td>
		</tr>
	</table>
*/?>
<link rel="stylesheet" type="text/css" href="ig/ig.css" />
<?PHP 
$_SESSION['multipleGalleries']=0;
include ("ig/igframe.php"); 
include ("homefooter.php");
?>