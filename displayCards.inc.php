<?php
include_once 'dbDaOpinion.class.php';
include_once 'displayOpinion.inc.php';
include_once 'dbDaFamily.class.php';

/**
 * Display a person including person picture class, education,ocupation,address and change date and username
 * @param dbDAO $db the database
 * @param array $person
 * @param bool $showClass
 * @param bool $showDate
 * @param string $action change action change,opinion,candle, if null then default will be change
 * @param int $changeUserID the user that made the changes, if null then user will be taken from person
 * @param string $changeDate, if null then date will be taken from person
 * @return void
 */
function displayPerson($db,$person,$showClass=false,$showDate=false,$action=null,$changeUserID=null, $changeDate=null) {
	if ($person==null)
		return;
    $dbOpinion = new dbDaOpinion($db);
    $dbFamily = new dbDaFamily($db);
	$d=$person;
	?>
	<div class="element">
		<div style="display: inline-block; ">
			<?php $personLink=displayPersonPicture($d); ?>
			<?php  if (userIsAdmin() || userIsSuperuser()) {?>
			<br/><a href="history.php?table=person&id=<?php echo $d["id"]?>" title="módosítások" style="position: relative;top: -37px;left: 10px;display:inline-block;">
				<span class="badge"><?php echo sizeof($db->getHistoryInfo("person",$d["id"]))?></span>
			</a>
			<?php }?>
		</div>
		<div class="personboxc">
			<a href="<?php echo $personLink?>"><h4><?php echo getPersonName($d);?></h4></a>
            <?php if (strstr($d["role"],"jmlaureat")!==false)
                echo('<div><a href="search.php?type=jmlaureat">Juhász Máthé díjas</a></div>');?>
			<?php if($showClass) {
			    if ($d["isTeacher"]==1) {
					if ($d["gender"]=='f') echo('<h5>Tanárnő</h5>');
                    elseif ($d["gender"]=='m') echo('<h5>Tanár úr</h5>');
                    else echo('<h5>Tanár</h5>');
				} else {
				    if (!isset($d["classText"])) {
					    $diakClass = $db->getClassById($d["classID"]);
					    $d["classText"] = getClassName($diakClass);
					}
					if (isPersonGuest($d)==1) {
						if (strstr($d["classText"],"staf")!==false)
							echo '<h5>Jó barát:<a href="hometable.php?classid='.$d["classID"].'">'.$d["classText"].'</a></h5>';
						else
							echo '<h5>Vendég:<a href="hometable.php?classid='.$d["classID"].'">'.$d["classText"].'</a></h5>';
					} else {
							echo '<h5>Véndiák:<a href="hometable.php?classid='.$d["classID"].'">'.$d["classText"].'</a></h5>';
					}
				} ?>
			<?php } ?>
			<div class="fields"><?php
				if ($d["isTeacher"]==0) {
					if(showField($d,"partner")) 	echo "<div><div>Élettárs:</div><div>".$d["partner"]."</div></div>";
					if(showField($d,"education")) 	echo "<div><div>Végzettség:</div><div>".getFieldValue($d["education"])."</div></div>";
					if(showField($d,"employer")) 	{
						$fieldString = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "",	getFieldValue($d["employer"]));
						echo "<div><div>Munkahely:</div><div>".$fieldString ."</div></div>";
					}
					if(showField($d,"function")) 	echo "<div><div>Beosztás:</div><div>".getFieldValue($d["function"])."&nbsp;</div></div>";
				} else {
					if (isset($d["function"]))
					    echo "<div><div>Tantárgy:</div><div>".getFieldValue($d["function"])."&nbsp;</div></div>";
                    if (isset($d["employer"]) && $d["employer"]!="")
                        echo "<div><div>Mettől meddig:</div><div>".getFieldValue($d["employer"])."&nbsp;</div></div>";
					if (showField($d,"children")) {
						echo "<div><div>Osztályfőnök:</div><div>";
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
						echo "</div></div>";
					}
				}
				if(showField($d,"country")) 	echo "<div><div>Ország:</div><div>".getFieldValue($d["country"])."</div></div>";
				if(showField($d,"place")) 		echo "<div><div>Város:</div><div>".getFieldValue($d["place"])."</div></div>";
					echo('<div class="diakCardIcons" style="margin-top:10px">');
					    displayIcon($d,"phone","phone.png","Telefon","tel:");
                        displayIcon($d,"mobil","mobile.png","Mobil","tel:");
                        displayIcon($d,"email","email.png","E-Mail","mailto:");
                        displayIcon($d,"facebook","facebook.png","Facebook","");
                        //displayIcon($d,"twitter","twitter.png","Twitter","");
                        displayIcon($d,"homepage","www.png","Honoldal","");
                        $pictures=$db->getNrOfPersonPictures($d["id"]);
						if ($pictures>0)
							echo '<a href="editDiak.php?tabOpen=pictures&uid='.$d["id"].'" title="Képek"><img src="images/picture.png" /><span class="countTag">'.$pictures.'</span></a>';
						if (isset($d["cv"]) && $d["cv"]!="")
							echo '<a href="editDiak.php?tabOpen=cv&uid='.$d["id"].'" title="Életrajz"><img src="images/calendar.png" /></a>';
						if (isset($d["story"]) && $d["story"]!="")
							echo '<a href="editDiak.php?tabOpen=school&uid='.$d["id"].'" title="Diákkori történet"><img src="images/gradcap.png" /></a>';
						if (isset($d["aboutMe"]) && $d["aboutMe"]!="")
							echo '<a href="editDiak.php?tabOpen=hobbys&uid='.$d["id"].'" title="Magamról szabadidőmben"><img src="images/info.gif" /></a>';
						if (isset($d["geolat"]) && $d["geolat"]!="")
							echo '<a href="editDiak.php?tabOpen=geoplace&uid='.$d["id"].'" title="Itt vagyok otthon"><img style="width:25px" src="images/geolocation.png" /></a>';
						$relatives=$dbFamily->getPersonRelativesCountById($d["id"]);
						if ($relatives>0) {
                            echo '<a href="editDiak.php?tabOpen=family&uid='.$d["id"].'" title="Családom"><img style="width:25px" src="images/relatives.png" /><span class="countTag">'.$relatives.'</span></a>';
                        }
						?>
					</div>
				<?php  if ($showDate) {
				    if ($action!='change')
                        $changePerson=$db->getPersonByID($changeUserID);
				    else
					    $changePerson=$db->getPersonByID($d["changeUserID"]);
				    if ($action=='candle') $action="Gyertyát gyújtott: ";
				    if ($action=='change' || $action==null) $action="Módósította: ";
                    if ($action=='family' || $action==null) $action="Rokont jelölt: ";
                    if ($action=='opinion') $action="Vélemény: ";
                    if ($changeDate==null)
                        $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($d["changeDate"]);
                    else
                        $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($changeDate);
				?>
                    <div class="diakCardIcons">
                        <?php echo $action ?>
                        <?php if($changePerson!=null) {?>
                              <a href="editDiak.php?uid=<?php echo $changePerson["id"] ?>"><?php echo $changePerson["lastname"]." ".$changePerson["firstname"]?></a>
                        <?php } else {echo ('Anonim látogató');} ?>
                        <br/>Dátum:<?php echo $changeDate;?><br/>
                    </div>
				<?php }?>
	  		</div>
		</div>
    <?php displayPersonOpinion($dbOpinion,$d["id"],(isset($d["isTeacher"]) && $d["isTeacher"]==='1'),isset($d["deceasedYear"])); ?>
	</div>
<?php
}


/**
 * Display a picture including change date and username
 * @param dbDAO $db the database
 * @param array $picture
 * @param bool $showSchool show school name
 * @param string $action change action change,opinion, if null then default will be change
 * @param int $changeUserID the user that made the changes, if null then user will be taken from picture
 * @param string $changeDate, if null then date will be taken from picture
 * @return void
 */
function displayPicture($db,$picture,$showSchool=false,$action=null,$changeUserID=null, $changeDate=null) {
    $dbOpinion = new dbDaOpinion($db);
	$p=$picture;
    if ($action!='change')
        $person=$db->getPersonByID($changeUserID);
    else
        $person = $db->getPersonByID($picture["changeUserID"]);
    if ($action=='change' || $action==null) $action="Módósította: ";
    if ($action=='opinion') $action="Vélemény: ";
    if ($action=='marked') $action="Személyt megjelölt:";
    if ($changeDate==null)
        $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($picture["changeDate"]);
    else
        $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($changeDate);

    if (isset($picture["schoolID"])){
		$type="school";
		$typeid=$picture[$type."ID"];
		$school=$db->getSchoolById($typeid);
		$typeText="<b>Iskolakép:</b><br/>".$school["name"];
	} elseif (isset($picture["classID"])){
		$type="class";
		$typeid=$picture[$type."ID"];
		$class=$db->getClassById($typeid);
		$typeText="<b>Osztálykép:</b><br/>".$class["text"];
	} elseif (isset($picture["personID"])){
		$type="person";
		$typeid=$picture[$type."ID"];
		$picturePerson=$db->getPersonByID($typeid);
		$typeText="<b>Személyes kép:</b><br/>".getPersonName($picturePerson);
	} else {
        return;
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
            <?php echo $action?>
            <?php if($person!=null) {?>
                <a href="editDiak.php?uid=<?php echo $person["id"]?>" ><?php echo $person["lastname"]." ".$person["firstname"]?></a>
            <?php } else {echo ('Anonim látogató');} ?>
            <br/>Dátum:<?php echo $changeDate?>
		</div>
        <?php  displayPictureOpinion($dbOpinion,$picture["id"]); ?>
	</div>
<?php }


/**
 * Display a class
 * @param dbDAO $db the database
 * @param array $class
 * @param bool $showDate
 */
function displayClass($db,$class,$showDate=false) {
	?>
	<div class="element">
		<div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
            <h4>Osztály: <a href="hometable.php?classid=<?php echo $class["id"]?>"><?php echo getClassName($class);?></h4></a><br/>
			<?php if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
			    $headTeacher = $db->getPersonByID($class["headTeacherID"]);?>
				Osztályfőnök: <a href="editDiak.php?uid=<?php echo $class["headTeacherID"]?>" ><?php echo $headTeacher["lastname"]." ".$headTeacher["firstname"]?></a> <br/>
			<?php } ?>
		</div>
		<?php if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
		    displayPersonPicture($headTeacher);
		} ?>

		<?php
            $pictureId = $db->getGroupPictureIdByClassId($class["id"]);
            if ($pictureId >0) {?>
			<div style="display: inline-block;vertical-align: top; ">
				<a href="picture.php?type=class&typeid=<?php  echo $class["id"]?>&id=<?php echo $pictureId?>" >
					<image src="convertImg.php?width=300&thumb=false&id=<?php echo $pictureId?>" />
				</a>
			</div>
		<?php } ?>

        <?php  if (userIsAdmin() || userIsSuperuser()) {?>
            <br/><a href="history.php?table=class&id=<?php echo $class["id"]?>" style="display:inline-block;">
                <span class="badge"><?php echo sizeof($db->getHistoryInfo("class",$class["id"]))?></span>
            </a>
        <?php }?>
		<?php if ($showDate) {
		    $c = $db->getPersonByID($class["changeUserID"]);
		    ?>
            <br/><div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
				Módosította: <a href="editDiak.php?uid=<?php echo $class["changeUserID"]?>" ><?php echo $c["lastname"]." ".$c["firstname"]?></a> <br/>
				Dátum:<?php echo maierlabs\lpfw\Appl::dateTimeAsStr($class["changeDate"]);?>
			</div>
		<?php }?>
	</div>
<?php }

/**
 * Display a lighted candle for a person
 * @param dbDAO $db the database
 * @param array $person
 * @deprecated
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

function displayPersonPicture($d)
{
    //mini icon
    if (isset($d["picture"]) && $d["picture"] != "") {
        $rstyle = ' diak_image_medium';
    } else {
        $rstyle = ' diak_image_empty';
    }
    if ($d["id"]!=-1) {
        if (userIsLoggedOn() || isLocalhost()) {
            $personLink="editDiak.php?uid=".$d["id"];
        } else {
            $personLink=getPersonLink($d["lastname"],$d["firstname"])."-".$d["id"];
        }
    } else {
        $personLink="javascript:alert('Sajnos erről a személyről nincsenek adatok.');";
    }
    ?>
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
    <?php
    return $personLink;
}

/**
 * Display icons for person
 * @param array $d
 * @param string $field the fielname with the content
 * @param string $image the icon filename
 * @param string $title the title
 * @param string $appl application name mailto, phoneto
 */
function displayIcon($d,$field,$image,$title,$appl) {
    if (isset($d[$field]) && strlen($d[$field])>8)
        if(showField($d,$field))
            echo '<a target="_blank" href="'.$appl.getFieldValue($d[$field]).'" title="'.$title.'"><img src="images/'.$image.'" /></a>';
        else
            echo '<a href="#" onclick="hiddenData(\''.$title.'\');" title="'.$title.'"><img src="images/'.$image.'" /></a>';
}


\maierlabs\lpfw\Appl::addJsScript("
	function hiddenData(title) {
		showModalMessage(title,'Személyes adat védve!<br/>Csak iskola vagy osztálytárs tekintheti meg ezt az informácíót.');
	}
");



