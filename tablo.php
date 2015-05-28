<?php 
include_once("data.php");
$SiteTitle="Ballagási tabló ".getAktDatabaseName();
include("homemenu.php"); 
?>

<div class="container-fluid">

	<h2 class="sub_title">A tanárok és diákok együtt a ballagási Tablón.</h2>
	<div></div>
	<img class="img-responsive"style="margin: auto;" src="images/tablo<?PHP echo(getAktDatabaseName());?>.jpg" " />
</div>

<?php include 'homefooter.php'; ?> 