<?php 
function displayPerson($db,$person,$showClass=false,$showDate=false) {
	$d=$person;
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
		<div style="display: inline-block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			<h4><?php echo getPersonName($d);?></h4>
			<?php if($showClass) {?>
				<?php if ($d["isTeacher"]==1) { ?>
					<h5>Tanár</h5>
				<?php } else { 
					$diakClass=$db->getClassById($d["classID"]);
					if (isPersonGuest($d)==1) {
						if ($d["classID"]!=0) 
							echo '<h5>Jó barát:<a href="hometable.php?classid='.$d["classID"].'">'.$diakClass["text"].'</a></h5>';
						else
							echo '<h5>Vendég:<a href="hometable.php?classid='.$d["classID"].'">'.$diakClass["text"].'</a></h5>';
					} else {
						echo '<h5>Véndiák:<a href="hometable.php?classid='.$d["classID"].'">'.$diakClass["text"].'</a></h5>';
					}
				} ?>
			<?php } ?>
			<div class="fields"> 
			<?php 
				if ($d["isTeacher"]==0) {
					if(showField($d,"partner")) 	echo "<div><div>Élettárs:</div><div>".$d["partner"]."</div></div>";
					if(showField($d,"education")) 	echo "<div><div>Végzettség:</div><div>".$d["education"]."</div></div>";
					if(showField($d,"employer")) 	{
						$fieldString = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "",	getFieldValue($d["employer"]));
						echo "<div><div>Munkahely:</div><div>".$fieldString ."</div></div>";
					}
					if(showField($d,"function")) 	echo "<div><div>Beosztás:</div><div>".getFieldValue($d["function"])."&nbsp;</div></div>";
				} else {
					if (isset($d["function"]))		echo "<div><div>Tantárgy:</div><div>".getFieldValue($d["function"])."&nbsp;</div></div>";
					if (showField($d,"children")) {	
						echo "<div><span>Osztályfőnök:</span>";
						$c = explode(",", getFieldValue($d["children"]));
						foreach ($c as $idx=>$cc) {
							$class= $db->getClassByText($cc);
							if ($idx!=0) echo(',');
							if ($class!=null) {
								echo(' <a href="hometable.php?classid='.$class["id"].'">'.$cc.'</a> ');
							} else {
								echo(' <a href="javascript:alert(\'Ennek az osztálynak még nincsenek bejegyzései ezen az oldalon. Szívesen bővitheted a véndiákok oldalát önmagad is. Hozd létre ezt az osztályt és egyenként írd be a diákoknak nevét és adatait. Előre is köszönjük!\')">'.$cc.'</a> ');
							}
						}
						echo "</div>";
					}
				}
				if(showField($d,"country")) 	echo "<div><div>Ország:</div><div>".getFieldValue($d["country"])."</div></div>";
				if(showField($d,"place")) 		echo "<div><div>Város:</div><div>".getFieldValue($d["place"])."</div></div>";
				echo('<div class="diakCardIcons">');
					if(showField($d,"email"))
						echo '<a href="mailto:'.getFieldValue($d["email"]).'" title="E-Mail"><img src="images/email.png" /></a>';
					if (isset($d["facebook"]) && strlen($d["facebook"])>8)
						echo '&nbsp;<a target="_new" href="'.getFieldValue($d["facebook"]).'" title="Facebook"><img src="images/facebook.png" /></a>';
					if (isset($d["twitter"]) && strlen($d["twitter"])>8)
						echo '&nbsp;<a target="_new" href="'.getFieldValue($d["twitter"]).'" title="Twitter"><img src="images/twitter.png" /></a>';
					if (isset($d["homepage"]) && strlen($d["homepage"])>8)
						echo '&nbsp;<a target="_new" href="'.getFieldValue($d["homepage"]).'" title="Honoldal"><img src="images/www.png" /></a>';
					if (sizeof($db->getListOfPictures($d["id"], "personID",0,userIsLoggedOn()?1:2))>0)
						echo '&nbsp;<a href="editDiak.php?tabOpen=1&uid='.$d["id"].'" title="Képek"><img src="images/picture.png" /></a>';
					if (isset($d["cv"]) && $d["cv"]!="")
						echo '&nbsp;<a href="editDiak.php?tabOpen=2&uid='.$d["id"].'" title="Életrajz"><img src="images/calendar.png" /></a>';
					if (isset($d["story"]) && $d["story"]!="")
						echo '&nbsp;<a href="editDiak.php?tabOpen=3&uid='.$d["id"].'" title="Diákkori történet"><img src="images/gradcap.png" /></a>';
					if (isset($d["aboutMe"]) && $d["aboutMe"]!="")
						echo '&nbsp;<a href="editDiak.php?tabOpen=4&uid='.$d["id"].'" title="Magamról szabadidőmben"><img src="images/info.gif" /></a>';
				?>
				</div>
				<?php  if ($showDate) {
					$changePerson=$db->getPersonByID($d["changeUserID"]);
				?>
				<div class="diakCardIcons">
					Módosította:<a href="editDiak.php?uid=<?php echo $d["changeUserID"] ?>"><?php echo $changePerson["lastname"]." ".$changePerson["firstname"]?></a><br/>
					Dátum:<?php echo date("Y.m.d H:i:s",strtotime($d["changeDate"]));?><br/>
				</div>
				<?php }?>
	  		</div>
		</div>
	</div>
<?php } 

function displayPicture($db,$picture,$showSchool=false) {
	$p=$picture;
	$person = $db->getPersonByID($picture["changeUserID"]);
	if (isset($picture["schoolID"])){
		$type="school";
		$typeid=$picture[$type."ID"];
		$school=$db->getSchoolById($typeid);
		$typeText="Iskolakép: ".$school["name"];
	}
	if (isset($picture["classID"])){
		$type="class";
		$typeid=$picture[$type."ID"];
		$class=$db->getClassById($typeid);
		$typeText="Osztálykép csoportkép: ".$class["text"];
	}
	if (isset($picture["personID"])){
		$type="person";
		$typeid=$picture[$type."ID"];
		$picturePerson=$db->getPersonByID($typeid);
		$typeText="Személyes kép: ".$person["lastname"]." ".$person["firstname"];
	}
	
	?>
	<div class="element">
		<div style="display: inline-block; ">
			<a href="picture.php?type=<?php echo $type?>&typeid=<?php echo $typeid?>&id=<?php echo $picture["id"]?>">
				<image src="convertImg.php?width=300&thumb=false&id=<?php echo $picture["id"]?>" />
			</a>
		</div><br/>
		<div style="display: inline-block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			<b><?php echo $picture["title"];?></b><br/>
			<?php echo $typeText;?><br/>
			Feltőltötte: <a href="editDiak.php?uid=<?php echo $picture["changeUserID"]?>" ><?php echo $person["lastname"]." ".$person["firstname"]?></a> <br/>
			Dátum:<?php echo date("Y.m.d H:i:s",strtotime($picture["uploadDate"]));?>
		</div>
	</div>
<?php } 


?>