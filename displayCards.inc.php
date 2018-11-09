<?php
include_once 'displayOpinion.inc.php';

/**
 * Display a person including person picture class, education,ocupation,address and change date and username
 * @param dbDAO $db the database
 * @param array $person
 * @param bool $showClass
 * @param bool $showDate
 */
function displayPerson($db,$person,$showClass=false,$showDate=false) {
	if ($person==null)
		return;
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
					    displayIcon($d,"phone","phone.png","Telefon","tel:");
                        displayIcon($d,"mobil","mobile.png","Mobil","tel:");
                        displayIcon($d,"email","email.png","E-Mail","mailto:");
                        displayIcon($d,"facebook","facebook.png","Facebook","");
                        displayIcon($d,"twitter","twitter.png","Twitter","");
                        displayIcon($d,"homepage","www.png","Honoldal","");
						if ($db->getNrOfPictures($d["id"], "personID",0,userIsLoggedOn()?1:2)>0)
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
    <?php if(userIsAdmin() || userIsSuperuser()) displayPersonOpinion($db,$d["id"],(isset($d["isTeacher"]) && $d["isTeacher"]==='1')); ?>
	</div>
<?php
}


/**
 * Display a picture including change date and username
 * @param dbDAO $db the database
 * @param array $picture
 * @param bool $showSchool
 */
function displayPicture($db,$picture,$showSchool=false) {
	$p=$picture;
	$person = $db->getPersonByID($picture["changeUserID"]);
	if (isset($picture["schoolID"])){
		$type="school";
		$typeid=$picture[$type."ID"];
		$school=$db->getSchoolById($typeid);
		$typeText="<b>Iskolakép:</b><br/>".$school["name"];
	}
	if (isset($picture["classID"])){
		$type="class";
		$typeid=$picture[$type."ID"];
		$class=$db->getClassById($typeid);
		$typeText="<b>Osztálykép:</b><br/>".$class["text"];
	}
	if (isset($picture["personID"])){
		$type="person";
		$typeid=$picture[$type."ID"];
		$picturePerson=$db->getPersonByID($typeid);
		$typeText="<b>Személyes kép:</b><br/>".getPersonName($picturePerson);
	}
	
	?>
	<div class="element">
        <div>
            <div style="display: inline-block; ">
                <a href="picture.php?type=<?php echo $type?>&typeid=<?php echo $typeid?>&id=<?php echo $picture["id"]?>">
                    <image src="convertImg.php?width=300&thumb=false&id=<?php echo $picture["id"]?>" title="<?php echo $picture["title"] ?>" />
                </a>
                <?php  if (userIsAdmin() || userIsSuperuser()) {?>
                    <br/><a href="history.php?table=picture&id=<?php echo $picture["id"]?>" style="display:inline-block;position: relative;top:-30px; left:10px;">
                        <span class="badge"><?php echo sizeof($db->getHistoryInfo("picture",$picture["id"]))?></span>
                    </a>
                <?php } ?>
            </div>
            <div style="display: inline-block;max-width:160px;min-width:150px; vertical-align: top;margin-bottom:10px;">
                <?php echo $typeText;?><br/>
                <?php if (isset($picture["albumName"])&&$picture["albumName"]!="") {?>
                    Album:<?php echo $picture["albumName"]?><br/>
                <?php }?>
                <br/>
                <b>Kép címe:</b><br/>
                <?php echo $picture["title"];?>
            </div>
        </div>
		<div style="display: inline-block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
            Módosította: <a href="editDiak.php?uid=<?php echo $picture["changeUserID"]?>" ><?php echo $person["lastname"]." ".$person["firstname"]?></a> <br/>
			Dátum:<?php echo date("Y.m.d H:i:s",strtotime($picture["changeDate"]));?>
		</div>
        <?php if(userIsAdmin() || userIsSuperuser()) displayPictureOpinion($db,$picture["id"]); ?>
	</div>
<?php }


/**
 * Display a class
 * @param dbDAO $db the database
 * @param array $class
 * @param bool $showDate
 */
function displayClass($db,$class,$showDate=false) {
	if (userIsLoggedOn() || isLocalhost()) {
		$personLink="editDiak.php?uid=".$class["headTeacherID"];
	} else {
		$personLink=getPersonLink($class["tlname"],$class["tfname"])."-".$class["headTeacherID"];
	}
	//Set the RIP value
	strstr($class["role"],"rip")!=""?$rip="rip":$rip=""
	?>
	<div class="element">
		<div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			Osztály: <a href="hometable.php?classid=<?php echo $class["id"]?>"><b><?php echo getClassName($class);?></b></a><br/>
			<?php if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {?>
				Osztályfőnök: <a href="editDiak.php?uid=<?php echo $class["headTeacherID"]?>" ><?php echo $class["tlname"]." ".$class["tfname"]?></a> <br/>
			<?php } ?>
		</div>
		<?php if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) { ;?>
			<div style="display: inline-block; vertical-align: top;width:160px;">
				<a href="<?php echo $personLink?>" title="<?php echo ($class["tlname"]." ".$class["tfname"])?>">
					<img src="<?php echo getPersonPicture($class)?>" border="0" title="<?php echo $class["tlname"].' '.$class["tfname"]?>" class="diak_image_medium <?php echo $rip?>" />
				</a>
			</div>
		<?php } ?>
		<?php if ($class["classPictureID"]!==false) {?>
			<div style="display: inline-block;vertical-align: top; ">
				<a href="picture.php?type=class&typeid=<?php  echo $class["id"]?>&id=<?php echo $class["classPictureID"]?>" >
					<image src="convertImg.php?width=300&thumb=false&id=<?php echo $class["classPictureID"]?>" />
				</a>
			</div>
		<?php } ?>
		<br/>
		<?php if ($showDate) {?>
			<div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
				Módosítva: <a href="editDiak.php?uid=<?php echo $class["changeUserID"]?>" ><?php echo $class["clname"]." ".$class["cfname"]?></a> <br/>
				Dátum:<?php echo date("Y.m.d H:i:s",strtotime($class["changeDate"]));?>
			</div>
		<?php }?>
		<?php  if (userIsAdmin() || userIsSuperuser()) {?>
			<br/><a href="history.php?table=class&id=<?php echo $class["id"]?>" style="display:inline-block;">
				<span class="badge"><?php echo sizeof($db->getHistoryInfo("class",$class["id"]))?></span>
			</a>
		<?php }?>
	</div>
<?php }

/**
 * Display a lighted candle for a person
 * @param dbDAO $db the database
 * @param array $person
*/
function displayPersonCandle($db,$person,$date) {
    if ($person==null) {
        $person = $db->getPersonDummy();
        $person["id"]=0;$person["classID"]=-1;$person["isTeacher"]=null;$person["lastname"]="Anonim látogató";
    }
    $d=$person;
    ?>
    <div class="element">
        <div style="display: inline-block; ">
            <a href="rip.php<?php echo $person["id"]!=0?'?id='.$person["id"]:'' ?>" style="display:inline-block;">
                <div>
                    <img src="images/candle2.gif" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="diak_image_medium" />
                    <?php if (isset($d["deceasedYear"]) && intval($d["deceasedYear"])>=0) {?>
                        <div style="background-color: black;color: white;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">
                            <?php echo intval($d["deceasedYear"])==0?"†":"† ".intval($d["deceasedYear"]); ?>
                        </div>
                    <?php }?>
                </div>
            </a>
        </div>
        <div style="display: inline-block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
            <a href="rip.php<?php echo $person["id"]!=0?'?id='.$person["id"]:'' ?>"><h4><?php echo getPersonName($d);?><br/>gyertyát gyújtott</h4></a>
            <?php if(true) {?>
                <?php if ($d["isTeacher"]==1) { ?>
                    <h5>Tanár</h5>
                <?php } else {
                    $diakClass = $db->getClassById($d["classID"]);
                    if ($diakClass!=null) {
                        $classText = getClassName($diakClass);
                        if (isPersonGuest($d) == 1) {
                            if ($d["classID"] != 0)
                                echo '<h5>Jó barát:<a href="hometable.php?classid=' . $d["classID"] . '">' . $classText . '</a></h5>';
                            else
                                echo '<h5>Vendég:<a href="hometable.php?classid=' . $d["classID"] . '">' . $classText . '</a></h5>';
                        } else {
                            if (null != $diakClass)
                                echo '<h5>Véndiák:<a href="hometable.php?classid=' . $d["classID"] . '">' . $classText . '</a></h5>';
                            else
                                echo('<h5>Véndiák:' . -1 * $d["classID"] . '</h5>'); //Graduation year for laurates that are not in the db
                        }
                    }
                } ?>
            <?php } ?>
            <div class="diakCardIcons">
                 Dátum:<?php echo date("Y.m.d H:i:s",strtotime($date));?><br/>
             </div>
        </div>
    </div>
<?php }

/**
 * Display icons for person
 * @param array $d
 * @param string $field
 * @param string $image
 * @param string $title
 * @param string $appl
 */
function displayIcon($d,$field,$image,$title,$appl) {
    if (isset($d[$field]) && strlen($d[$field])>8)
        if(showField($d,$field))
            echo '&nbsp;<a target="_blank" href="'.$appl.getFieldValue($d[$field]).'" title="'.$title.'"><img src="images/'.$image.'" /></a>';
        else
            echo '&nbsp;<a href="#" onclick="hiddenData(\''.$title.'\');" title="'.$title.'"><img src="images/'.$image.'" /></a>';
}


\maierlabs\lpfw\Appl::addJsScript("
	function hiddenData(title) {
		showModalMessage(title,'Személyes adat védve!<br/>Csak iskola vagy osztálytárs tekintheti meg ezt az informácíót.');
	}
");



