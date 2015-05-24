<?php 
include_once("data.php");
$SiteTitle="Ballagási tabló ".getAktDatabaseName();
include("homemenu.php"); 
?>

<h2 class="sub_title">A tanárok és diákok együtt a ballagási Tablón.</h2>

  <table align="center" border="1">
    <td><img src="images/tablo<?PHP echo(getAktDatabaseName());?>.jpg" ">
  </table>  

<?php include 'homefooter.php'; ?> 