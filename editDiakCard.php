<?php 
function editDiakCard($d) {
	global $db;
	if (userIsLoggedOn() || localhost()) {
		$personLink="editDiak.php?uid=".$d["id"];
	} else {
		$personLink=getPersonLink($d["lastname"],$d["firstname"])."-".$d["id"];
	}
	//Set the RIP value
	strstr($d["role"],"rip")!=""?$rip="rip":$rip="";
	?>
	<div class="element">
		<div style="display: inline-block; width:160px;">
			<a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>">
				<img src="images/<?php echo $d["picture"]?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="diak_image_medium <?php echo $rip?>" />
			</a>
		</div>
		<div style="display: inline-block;max-width:300px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			<h4><?php echo getPersonName($d);?></h4>
			<div class="fields"> 
			<?php 
				if ($d["isTeacher"]==0) {
					if(showField($d,"partner")) 	echo "<div><span>Élettárs:</span>".$d["partner"]."</div>";
					if(showField($d,"education")) 	echo "<div><span>Végzettség:</span>".$d["education"]."</div>";
					if(showField($d,"employer")) 	{
						$fieldString = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "",	getFieldValue($d["employer"]));
						echo "<div><span>Munkahely:</span>".$fieldString ."</div>";
					}
					if(showField($d,"function")) 	echo "<div><span>Beosztás:</span>".getFieldValue($d["function"])."</div>";
				} else {
					if (isset($d["function"]))		echo "<div><span>Tantárgy:</span>".getFieldValue($d["function"])."</div>";
					if (showField($d,"children")) {	
						echo "<div><span>Osztályfönök:</span>";
						$c = explode(",", getFieldValue($d["children"]));
						foreach ($c as $cc) {
							$class= $db->getClassByText($cc);
							if ($class!=null)
								echo(' <a href="hometable.php?classid='.$class["id"].'">'.$cc.'</a> ');
							else
								echo(' <a href="javascript:alert(\'Ennek az osztálynak még nincsenek bejegyzései ezen az oldalon. Szívesen bővitheted a véndiákok oldalát önmagad is. Hozd létre ezt az osztályt és egyenként írd be a diákoknak nevét és adatait. Előre is köszönjük!\')">'.$cc.'</a> ');
						}
						echo "</div>";
					}
				}
				if(showField($d,"country")) 	echo "<div><span>Ország:</span>".getFieldValue($d["country"])."</div>";
				if(showField($d,"place")) 		echo "<div><span>Város:</span>".getFieldValue($d["place"])."</div>";
				echo('<div style="margin-top:5px"><span></span>');
					if(showField($d,"email"))
						echo '<a href="mailto:'.getFieldValue($d["email"]).'"><img src="images/email.png" /></a>';
					if (isset($d["facebook"]) && strlen($d["facebook"])>8)
						echo '&nbsp;<a target="_new" href='.getFieldValue($d["facebook"]).'><img src="images/facebook.png" /></a>';
					if (isset($d["twitter"]) && strlen($d["twitter"])>8)
						echo '&nbsp;<a target="_new" href='.getFieldValue($d["twitter"]).'><img src="images/twitter.png" /></a>';
					if (isset($d["homepage"]) && strlen($d["homepage"])>8)
						echo '&nbsp;<a target="_new" href='.getFieldValue($d["homepage"]).'><img src="images/www.png" /></a>';
					if ($db->getListOfPictures($d["id"], "personID",0,userIsLoggedOn()?1:2))
						echo '&nbsp;<a target="_new" href="editDiak.php?tabOpen=1&uid='.$d["id"].'"><img src="images/picture.png" /></a>';
					if (isset($d["cv"]) && $d["cv"]!="")
						echo '&nbsp;<a target="_new" href="editDiak.php?tabOpen=2&uid='.$d["id"].'"><img src="images/info.gif" /></a>';
				echo("</div>");
			?>
	  		</div>
		</div>
	</div>
<?php } ?>