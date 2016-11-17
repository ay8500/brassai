<?PHP
$SiteTitle="Brassaista Véndiákok"; 
$SiteDescription="Brassaista Véndiákok találata";
include("homemenu.php");


$name=trim(html_entity_decode(getGetParam("srcText", "")));
$personList=$db->searchForPerson($name);

?>
<style>
      .fields div span { font-weight: bold;width: 90px;text-align: right;display: inline-block; }
      .fields {vertical-align: text-top;}
      .element{display: inline-block; background-color: #E5E9EA;  padding: 10px; border-radius: 7px; margin-bottom: 10px; vertical-align: top;}
</style>

<div class="container-fluid">
	<h2 style="text-align: center;"  class="sub_title" >Találatok a véndiákok adatbankjában</h2>
	<div class="well">
		Véndiákok száma:<?php echo sizeof($personList)?> Keresett szó:"<?php echo $name?>"
	</div>
<?php 
foreach ($personList as $d)	
{ 
	$personLink="editDiak.php?uid=".$d["id"];?>
		
	<div class="element">
		
		<div style="display: inline-block; width:160px;">
			<a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>">
				<img src="images/<?php echo $d["picture"]?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="diak_image_medium" />
			</a>
		</div>
		<div style="display: inline-block;max-width:300px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			<h4>
				<?php echo $d["lastname"].' '.$d["firstname"];
					if(showField($d,"birthname")) echo("&nbsp;(".$d["birthname"].")");
				?>
			</h4>
			<div class="fields"> 
				<?php
				if ($d["classID"]!=0) {
					echo "<div><span>Ballagási év:</span>".$d["scoolYear"]."</div>";
					echo "<div><span>Osztály:</span>".$d["scoolClass"]."</div>";
				} else {
					echo "<div><span>Tanár:</span>".$d["function"]."</div>";
				}
				echo "<div>&nbsp;</div>";
				if(showField($d,"partner")) 	echo "<div><span>Élettárs:</span>".$d["partner"]."</div>";
				if(showField($d,"children")) 	echo "<div><span>Gyerekek:</span>".$d["children"]."</div>";
				if(showField($d,"country")) 	echo "<div><span>Ország:</span>".getFieldValue($d["country"])."</div>";
				if(showField($d,"email")) 		echo "<div><span>E-Mail:</span><a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a></div>";
				if($d["changeForID"]!=null)		echo "<div><span>.</span></div>";
				?>
	  		</div>
		</div>
	</div>
	<?php } ?>
</div>


<?PHP  include ("homefooter.php");?>

<script type="text/javascript">
	$( document ).ready(function() {
		if ("<?php echo getGetParam("srcText", "")?>"!="") {
		    showSearchBox(true);
		}
	});

</script>
