<?php 
/**
 * Display a person div including person picture class, education,ocupation,address and change date an user 
 * @param object $db
 * @param array $person
 * @param bool $showClass
 * @param bool $showDate
 */
function displayPerson($db,$person,$showClass=false,$showDate=false) {
	$d=$person;
	if ($d["id"]!=-1) {
		if (userIsLoggedOn() || isLocalhost()) {
			$personLink="editDiak.php?uid=".$d["id"];
		} else {
			$personLink=getPersonLink($d["lastname"],$d["firstname"])."-".$d["id"];
		}
	} else {
		$personLink="javascript:alert('Sajnos erről a személyről nincsenek adatok.');";
	}
	//mini icon
	if (isset($person["picture"]) && $person["picture"]!="") 
		$rstyle=' diak_image_medium';
	else {
		$rstyle=' diak_image_empty';
	}
	?>
	<div class="element">
		<div style="display: inline-block; ">
			<a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>" style="display:inline-block;">
				<div>
					<img src="<?php echo getPersonPicture($d)?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="<?php echo $rstyle?>" />
					<?php if (isset($d["deceasedYear"]) && intval($d["deceasedYear"])>=0) {?>
						<div style="background-color: black;color: white;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">
							<?php echo intval($d["deceasedYear"])==0?"†":"† ".intval($d["deceasedYear"]); ?>
						</div>
					<?php }?>
				</div>
			</a>
			<?php  if (userIsAdmin() || userIsSuperuser()) {?>
			<br/><a href="history.php?table=person&id=<?php echo $d["id"]?>" title="módosítások" style="position: relative;top: -37px;left: 10px;display:inline-block;">
				<span class="badge"><?php echo sizeof($db->getHistoryInfo("person",$d["id"]))?></span>
			</a>
			<?php }?>
		</div>
		<div style="display: inline-block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			<a href="<?php echo $personLink?>"><h4><?php echo getPersonName($d);?></h4></a>
			<?php if($showClass) {?>
				<?php if ($d["isTeacher"]==1) { ?>
					<h5>Tanár</h5>
				<?php } else { 
					$diakClass = $db->getClassById($d["classID"]);
					$classText = getClassName($diakClass);
					if (isPersonGuest($d)==1) {
						if ($d["classID"]!=0) 
							echo '<h5>Jó barát:<a href="hometable.php?classid='.$d["classID"].'">'.$classText.'</a></h5>';
						else
							echo '<h5>Vendég:<a href="hometable.php?classid='.$d["classID"].'">'.$classText.'</a></h5>';
					} else {
						if (null!=$diakClass)
							echo '<h5>Véndiák:<a href="hometable.php?classid='.$d["classID"].'">'.$classText.'</a></h5>';
						else 
							echo('<h5>Véndiák:'.-1 * $d["classID"].'</h5>'); //Graduation year for laurates that are not in the db
					}
				} ?>
			<?php } ?>
			<div class="fields"> 
			<?php 
				if ($d["isTeacher"]==0) {
					if(showField($d,"partner")) 	echo "<div><div>Élettárs:</div><div>".$d["partner"]."</div></div>";
					if(showField($d,"education")) 	echo "<div><div>Végzettség:</div><div>".getFieldValue($d["education"])."</div></div>";
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
					echo('<div class="xxx_diakCardIcons" style="margin-top:10px">');
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
						if (isset($d["geolat"]) && $d["geolat"]!="")
							echo '&nbsp;<a href="editDiak.php?tabOpen=5&uid='.$d["id"].'" title="Itt vagyok otthon"><img style="width:25px" src="images/geolocation.png" /></a>';
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
		$typeText="Személyes kép: ".getPersonName($picturePerson);
	}
	
	?>
	<div class="element">
		<div style="display: inline-block; ">
			<a href="picture.php?type=<?php echo $type?>&typeid=<?php echo $typeid?>&id=<?php echo $picture["id"]?>">
				<image src="convertImg.php?width=300&thumb=false&id=<?php echo $picture["id"]?>" />
			</a>
		</div>
		<?php  if (userIsAdmin() || userIsSuperuser()) {?>
			<a href="history.php?table=picture&id=<?php echo $picture["id"]?>" style="display:inline-block;">
				<span class="badge"><?php echo sizeof($db->getHistoryInfo("picture",$picture["id"]))?></span>
			</a>
		<?php } ?>
		<br/>
		<div style="display: inline-block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			<b><?php echo $picture["title"];?></b><br/>
			<?php echo $typeText;?><br/>
			<?php if (isset($picture["albumName"])&&$picture["albumName"]!="") {?>
				Album:<?php echo $picture["albumName"]?><br/>
			<?php }?>
			Feltőltötte: <a href="editDiak.php?uid=<?php echo $picture["changeUserID"]?>" ><?php echo $person["lastname"]." ".$person["firstname"]?></a> <br/>
			Dátum:<?php echo date("Y.m.d H:i:s",strtotime($picture["uploadDate"]));?>
		</div>
	</div>
<?php } 

function displayClass($db,$d,$showDate=false) { 
	if (userIsLoggedOn() || isLocalhost()) {
		$personLink="editDiak.php?uid=".$d["headTeacherID"];
	} else {
		$personLink=getPersonLink($d["tlname"],$d["tfname"])."-".$d["headTeacherID"];
	}
	//Set the RIP value
	strstr($d["role"],"rip")!=""?$rip="rip":$rip=""
	?>
	<div class="element">
		<div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			Osztály: <a href="hometable.php?classid=<?php echo $d["id"]?>"><b><?php echo getClassName($d);?></b></a><br/>
			<?php if (isset($d["headTeacherID"]) && $d["headTeacherID"]>=0) {?>
				Osztályfőnök: <a href="editDiak.php?uid=<?php echo $d["headTeacherID"]?>" ><?php echo $d["tlname"]." ".$d["tfname"]?></a> <br/>
			<?php } ?>
		</div>
		<?php if (isset($d["headTeacherID"]) && $d["headTeacherID"]>=0) { ;?>
			<div style="display: inline-block; vertical-align: top;width:160px;">
				<a href="<?php echo $personLink?>" title="<?php echo ($d["tlname"]." ".$d["tfname"])?>">
					<img src="<?php echo getPersonPicture($d)?>" border="0" title="<?php echo $d["tlname"].' '.$d["tfname"]?>" class="diak_image_medium <?php echo $rip?>" />
				</a>
			</div>
		<?php } ?>
		<?php if ($d["classPictureID"]!==false) {?>
			<div style="display: inline-block;vertical-align: top; ">
				<a href="picture.php?type=class&typeid=<?php  echo $d["id"]?>&id=<?php echo $d["classPictureID"]?>" >
					<image src="convertImg.php?width=300&thumb=false&id=<?php echo $d["classPictureID"]?>" />
				</a>
			</div>
		<?php } ?>
		<br/>
		<?php if ($showDate) {?>
			<div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
				Módosítva: <a href="editDiak.php?uid=<?php echo $d["changeUserID"]?>" ><?php echo $d["clname"]." ".$d["cfname"]?></a> <br/>
				Dátum:<?php echo date("Y.m.d H:i:s",strtotime($d["changeDate"]));?>
			</div>
		<?php }?>
		<?php  if (userIsAdmin() || userIsSuperuser()) {?>
			<br/><a href="history.php?table=class&id=<?php echo $d["id"]?>" style="display:inline-block;">
				<span class="badge"><?php echo sizeof($db->getHistoryInfo("class",$d["id"]))?></span>
			</a>
		<?php }?>
	</div>
<?php }
?>