<?PHP 

//Change scool year and class if parameters are there 
if (isset($_GET['scoolYear'])) {
	$_SESSION['scoolYear']=$_GET['scoolYear'];
} 
if (isset($_GET['scoolClass']))  {
	$_SESSION['scoolClass']=$_GET['scoolClass'];	
}

include_once("data.php");
$SiteTitle="Ballagási tabló ".getDatabaseName();
include("homemenu.php"); ?>

<h2 class="sub_title">A tanárok és diákok együtt a ballagási Tablón.</h2>

  <table align="center" border="1">
    <td><IMG SRC="images/tablo<?PHP echo($_SESSION['scoolYear'].$_SESSION['scoolClass']);?>.jpg" ">
  </table>  

<?php include 'homefooter.php'; ?> 