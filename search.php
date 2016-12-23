<?PHP
$SiteTitle="Brassaista Véndiákok"; 
$SiteDescription="Brassaista Véndiákok keresés";
include("homemenu.php");
include_once 'editDiakCard.php';


$name=trim(html_entity_decode(getGetParam("srcText", "")));
$personList=$db->searchForPerson($name);

?>

<div class="container-fluid">
	<h2 style="text-align: center;"  class="sub_title" >Találatok a véndiákok adatbankjában</h2>
	<div class="well">
		Találatok száma:<?php echo sizeof($personList)?> Keresett szó:"<?php echo $name?>"
	</div>
	<?php
	foreach ($personList as $d)	{
		editDiakCard($d,true);
	} 
	?>
</div>


<?php include ("homefooter.php");?>

<script type="text/javascript">
	$( document ).ready(function() {
		if ("<?php echo getGetParam("srcText", "")?>"!="") {
		    showSearchBox(true);
		}
	});
</script>
