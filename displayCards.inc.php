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
    //TODO user the function $db->getPersonWithInfo()
	if ($person==null)
		return;
    $dbOpinion = new dbDaOpinion($db);
    $dbFamily = new dbDaFamily($db);
	$d=$person;
	?>
	<div class="element">
        <?php
            displaySchool($d["schoolID"]);
            $personClass = displayPersonNameAndGetClass($db,$person,$showClass);
        ?>
        <?php if ($person["gdpr"]==100) {?>
            <img title="A személy jováhagyta a személyes adatainak a használatát kizárolag ezen az oldalon!" src="images/gdpr.png" style="position: absolute;width:88px;left:409px;top:3px" />
        <?php } ?>
        <?php if ($person["gdpr"]>0 && $person["gdpr"]<=5) {?>
            <img title="A személy tiltja részben vagy teljes mértékben a személyes adatainak a használatát!" src="images/gdpr.png" style="position: absolute;width:150px;left:335px;top:3px;filter: hue-rotate(90deg);" />
        <?php } ?>
        <?php displayPersonPictureAndHistory($db,$d);?>
		<div class="personboxc">
            <?php if ($showClass)
                echo($personClass);?>
            <?php if (strstr($d["role"],"jmlaureat")!==false)
                echo('<div><a href="search?type=jmlaureat">Juhász Máthé díjas</a></div>');?>
            <div class="fields"><?php
				if ($d["isTeacher"]==0) {
					if(showField($d,"partner")) 	echo "<div><div>Élettárs:</div><div>".getFieldValue($d,"partner")."</div></div>";
					if(showField($d,"education")) 	echo "<div><div>Végzettség:</div><div>".getFieldValue($d,"education")."</div></div>";
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
								echo(' <a href="hometable?classid='.$class["id"].'">'.$cc.'</a> ');
							} else {
								echo(' <a href="javascript:alert(\'Ennek az osztálynak még nincsenek bejegyzései ezen az oldalon. Szívesen bővitheted a véndiákok oldalát önmagad is. Hozd létre ezt az osztályt és egyenként írd be a diákoknak nevét és adatait. Előre is köszönjük!\')">'.$cc.'</a> ');
							}
						}
						echo "</div></div>";
					}
				}
				if(showField($d,"country")) 	echo "<div><div>Ország:</div><div>".getFieldValue($d["country"])."</div></div>";
				if(showField($d,"place")) 		echo "<div><div>Város:</div><div>".getFieldValue($d["place"])."</div></div>";
				?>
					<div class="diakCardIcons" style="margin-top:10px">
                    <?php
					    displayIcon($d,"phone","phone.png","Telefon","tel:");
                        displayIcon($d,"mobil","mobile.png","Mobil","tel:");
                        displayIcon($d,"email","email.png","E-Mail","mailto:");
                        displayIcon($d,"facebook","facebook.png","Facebook","");
                        //displayIcon($d,"twitter","twitter.png","Twitter","");
                        displayIcon($d,"homepage","www.png","Honoldal","");
                        $pictures=$db->getNrOfPersonPictures($d["id"]);
						if ($pictures>0)
							echo '<a href="editDiak?tabOpen=pictures&uid='.$d["id"].'" title="Képek"><img src="images/picture.png" /><span class="countTag">'.$pictures.'</span></a>';
						if (isset($d["cv"]) && $d["cv"]!="")
							echo '<a href="editDiak?tabOpen=cv&uid='.$d["id"].'" title="Életrajz"><img src="images/calendar.png" /></a>';
						if (isset($d["story"]) && $d["story"]!="")
							echo '<a href="editDiak?tabOpen=school&uid='.$d["id"].'" title="Diákkori történet"><img src="images/gradcap.png" /></a>';
						if (isset($d["aboutMe"]) && $d["aboutMe"]!="")
							echo '<a href="editDiak?tabOpen=hobbys&uid='.$d["id"].'" title="Magamról szabadidőmben"><img src="images/info.gif" /></a>';
						if (isset($d["geolat"]) && $d["geolat"]!="")
							echo '<a href="editDiak?tabOpen=geoplace&uid='.$d["id"].'" title="Itt vagyok otthon"><img style="width:25px" src="images/geolocation.png" /></a>';
						$relatives=$dbFamily->getPersonRelativesCountById($d["id"]);
						if ($relatives>0)
                            echo '<a href="editDiak?tabOpen=family&uid='.$d["id"].'" title="Családom"><img style="width:25px" src="images/relatives.png" /><span class="countTag">'.$relatives.'</span></a>';
					?>
				</div>
	  		</div>
		</div>
        <?php  if ($showDate) {
            if ($action!='change')
                $changePerson=$db->getPersonByID($changeUserID);
            else
                $changePerson=$db->getPersonByID($d["changeUserID"]);
            if ($action=='candle') $action="Gyertyát gyújtott ";
            if ($action=='change' || $action==null) $action="Módosította ";
            if ($action=='family' || $action==null) $action="Rokont jelölt ";
            if ($action=='opinion') $action="Vélemény ";
            if ($action=='easter') $action=$changePerson["gender"]=="m"?"Locsoló ":"Piros tojás";
            if ($changeDate==null)
                $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($d["changeDate"]);
            else
                $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($changeDate);
            ?><div class="diakCardIcons" style="margin-bottom: 5px">
                <?php echo $action. getPersonLinkAndPicture($changePerson) .' '. $changeDate;?><br/>
            </div>
        <?php }?>
    <?php
        /*Easter*/
        /*
        if ((!isset($person["deceasedYear"]) || $person["deceasedYear"]==null) && strtotime("now")<strtotime("2021-04-08")) {
            if (!isset($person["gender"]) || $person["gender"]=="f" && ($db->getPersonByID(getLoggedInUserId())["gender"]=="m" || !isUserLoggedOn())) {
                ?>
                <button style="margin-bottom: 5px" onclick="return saveEasterOpinion(<?php echo $person['id'] ?>,'person','easter',<?php echo getLoggedInUserId()!=null?getLoggedInUserId():"null" ?>)" title="Megszabad locsolni?" class="btn btn-success"><img src="images/easter.png" style="width: 26px"/> Szabad öntözni?</button>
                <?php
            }
        }
        */
        displayPersonOpinion($dbOpinion,$d["id"],$d["gender"],(isset($d["isTeacher"]) && $d["isTeacher"]==='1'),isset($d["deceasedYear"]));
    ?>
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
    if ($action=='change' || $action==null) $action="Módosította";
    if ($action=='opinion') $action="Vélemény";
    if ($action=='marked') $action="Személyt jelölt";
    if ($changeDate==null)
        $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($picture["changeDate"]);
    else
        $changeDate = maierlabs\lpfw\Appl::dateTimeAsStr($changeDate);

    $typeArray=$db->getPictureTypeText($picture);

	?>
	<div class="element">
        <div>
            <div style="display: inline-block; ">
                <?php displaySchool($p["schoolID"]); ?>
                <h4><?php echo $typeArray["text"];?></h4>
                <a href="picture?type=<?php echo $typeArray["type"]?>&typeid=<?php echo $typeArray["typeId"]?>&id=<?php echo $picture["id"]?>">
                    <img src="imageConvert?width=396&thumb=false&id=<?php echo $picture["id"]?>" title="<?php echo $picture["title"] ?>" />
                </a>
                <?php  if (isUserSuperuser()) {?>
                    <br/><a href="history?table=picture&id=<?php echo $picture["id"]?>" style="display:inline-block;position: relative;top:-30px; left:10px;">
                        <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("picture",$picture["id"]))?></span>
                    </a>
                <?php } ?>
            </div>
            <div style="vertical-align: top;margin-bottom:10px;">
                <?php if (isset($picture["title"])&&$picture["title"]!="") {?>
                    <b><?php echo $picture["title"];?></b>
                <?php } ?>
                <?php if (isset($picture["albumName"])&&$picture["albumName"]!="") {?>
                    <br/>Album:<?php echo $picture["albumName"]?>
                <?php }?>
                <?php if (isset($picture["tag"])&&$picture["tag"]!=""&&$picture["tag"]!="undefined") {?>
                    <br/>Tartalom:<?php echo $picture["tag"]?>
                <?php }?>
            </div>
        </div>
		<div style="margin-top:5px; margin-bottom: 5px">
            <?php echo $action .' '. getPersonLinkAndPicture($person) ?>
            <?php echo $changeDate?>
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
    if ($class["graduationYear"]==0)
        return;
	?>
	<div class="element">
		<div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
            <?php displaySchool($class["schoolID"]); ?>
            <h4><i class="material-icons" style="vertical-align: bottom;">group</i> Osztály: <a href="hometable?classid=<?php echo $class["id"]?>"><?php echo getSchoolClassName($class);?></h4></a><br/>
			<?php if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
			    $headTeacher = $db->getPersonByID($class["headTeacherID"]);?>
				Osztályfőnök: <a href="editDiak?uid=<?php echo $class["headTeacherID"]?>" ><?php echo $headTeacher["lastname"]." ".$headTeacher["firstname"]?></a> <br/>
			<?php } ?>
		</div>
		<?php if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
		    displayPersonPicture($headTeacher);
		} ?>

		<?php
            $pictureId = $db->getGroupPictureIdByClassId($class["id"]);
            if ($pictureId >0) {?>
			<div style="display: inline-block;vertical-align: top; ">
				<a href="picture?type=class&typeid=<?php  echo $class["id"]?>&id=<?php echo $pictureId?>" >
					<img src="imageConvert?width=300&thumb=false&id=<?php echo $pictureId?>" />
				</a>
			</div>
		<?php } ?>

        <?php  if (isUserSuperuser()) {?>
            <br/><a href="history?table=class&id=<?php echo $class["id"]?>" style="display:inline-block;">
                <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("class",$class["id"]))?></span>
            </a>
        <?php }?>
		<?php if ($showDate) {
		    $person = $db->getPersonByID($class["changeUserID"]);
		    ?>
            <br/><div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
				Módosította: <?php echo getPersonLinkAndPicture($person) ?>
				<?php echo maierlabs\lpfw\Appl::dateTimeAsStr($class["changeDate"]);?>
			</div>
		<?php }?>
	</div>
<?php }

/**
 * Display a message
 * @param dbDAO $db the database
 * @param array $message
 * @param bool $showDate
 */
function displayMessage($db,$message,$showDate=true) {
    $dbOpinion = new dbDaOpinion($db);
    ?>
    <div class="element">
        <div style="display: block;min-width:300px; vertical-align: top;margin-bottom:10px;">
            <?php displaySchool(($db->getPersonByID($message["changeUserID"]))["schoolID"]); ?>
            <h4><i class="material-icons" style="vertical-align: bottom">chat</i> Üzenet</h4>
            <div style="max-height: 300px; overflow-y: scroll;margin-top: 10px">
                <?php
                    echo(htmlspecialchars_decode($message["text"]));
                    if (isset($message["comment"]) && $message["comment"]!=null)
                        echo("Kommentár: ".$message["comment"]);
                ?>
            </div><div style="margin-top: 5px;">
                Üzenetet írta
                <?php if (isset($message["changeUserID"]) && $message["changeUserID"]!=null) {
                    echo getPersonLinkAndPicture($db->getPersonByID($message["changeUserID"])).' '.maierlabs\lpfw\Appl::dateTimeAsStr($message["changeDate"]);
                } else {
                    echo $message["name"].' '.maierlabs\lpfw\Appl::dateTimeAsStr($message["changeDate"]);
                } ?>
            </div>
        </div>
        <?php  displayMessageOpinion($dbOpinion,$message["id"]); ?>
    </div>
<?php }

/**
 * Display a music
 * @param dbDAO $db the database
 * @param array $musicVote
 * @param bool $showDate
 */
function displayMusic($db,$music,$action,$userId,$date,$showVideo=false) {
    $dbOpinion = new dbDaOpinion($db);
    if ($action=="change")
        $actionText = "Zenét kiválasztotta";
    else
        $actionText = "Mejelőlte mint kendvenc zenéje";
    $d = $db->getPersonByID($userId);
    ?>
    <div class="element">
        <div style="display: block;min-width:300px; vertical-align: top;margin-bottom:10px;">
            <?php displaySchool($d["schoolID"]); ?>
            <div style="">
                <a href="zenePlayer?link=<?php echo $music["video"]?>&id=<?php echo $music['id']?>"><h4><span class="glyphicon glyphicon-film"></span> <?php echo(htmlspecialchars_decode($music["interpretName"]))?> - <?php echo(htmlspecialchars_decode($music["name"]))?></h4></a>
            </div>
            <?php if ($showVideo) {?>
                <object  class="embed-responsive embed-responsive-16by9">
                    <embed src="https://www.youtube.com/v/<?php echo $music["video"]?>&hl=de_DE&enablejsapi=0&fs=1&rel=0&border=1&autoplay=0&showinfo=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"  />
                </object>
            <?php }?>
            <br/>Megjelenési év:<?php echo $music["year"] ?><br/>
            <?php echo $actionText.' '.getPersonLinkAndPicture($d)?>
            <?php echo maierlabs\lpfw\Appl::dateTimeAsStr($date);?>
            <?php
                if (isset($music["check"])) {
                    echo("<br/>Check:".$music["check"]?"Ok":"ERROR");
                }
            ?>
        </div>
        <?php  displayMusicOpinion($dbOpinion,$music["id"]); ?>
    </div>
<?php }

/**
 * Display an article class
 * @param dbDAO $db the database
 * @param array $article
 */
function displayArticle($db,$article,$showDate=true) {
    ?>
    <div class="element">
        <div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
            <h4><?php echo \maierlabs\lpfw\Appl::_($article["category"]) ?></h4><br/>
        </div>


        <?php  if (isUserSuperuser()) {?>
            <br/><a href="history?table=article&id=<?php echo $article["id"]?>" style="display:inline-block;">
                <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("article",$article["id"]))?></span>
            </a>
        <?php }?>
        <?php if ($showDate) {
            $c = $db->getPersonByID($article["changeUserID"]);
            ?>
            <br/><div style="display: block;max-width:310px;min-width:300px; vertical-align: top;margin-bottom:10px;">
                Módosította: <a href="editDiak?uid=<?php echo $article["changeUserID"]?>" ><?php echo $c["lastname"]." ".$c["firstname"]?></a> <br/>
                Dátum:<?php echo maierlabs\lpfw\Appl::dateTimeAsStr($article["changeDate"]);?>
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
        $person["id"]=0;$person["classID"]=-1;$person["isTeacher"]=null;$person["lastname"]="anonim látogató";
    }
    $d=$person;
    ?>
    <div class="element">
        <div style="display: inline-block; ">
            <a href="rip<?php echo $person["id"]!=0?'?id='.$person["id"]:'' ?>" style="display:inline-block;">
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
            <a href="rip<?php echo $person["id"]!=0?'?id='.$person["id"]:'' ?>"><h4><?php echo getPersonName($d);?><br/>gyertyát gyújtott</h4></a>
            <?php if(true) {?>
                <?php if ($d["isTeacher"]==1) { ?>
                    <h5>Tanár</h5>
                <?php } else {
                    $diakClass = $db->getClassById($d["classID"]);
                    if ($diakClass!=null) {
                        $classText = getSchoolClassName($diakClass);
                        if (isUserGuest($d)) {
                            if ($d["classID"] != 0)
                                echo '<h5>Jó barát:<a href="hometable?classid=' . $d["classID"] . '">' . $classText . '</a></h5>';
                            else
                                echo '<h5>Vendég:<a href="hometable?classid=' . $d["classID"] . '">' . $classText . '</a></h5>';
                        } else {
                            if (null != $diakClass)
                                echo '<h5>Véndiák:<a href="hometable?classid=' . $d["classID"] . '">' . $classText . '</a></h5>';
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

function displayPersonPictureAndHistory($db,$d) {
    ?>
    <div style="display: inline-block;position: relative;">
        <?php if (isUserSuperuser()) { ?>
            <a href="history?table=person&id=<?php echo $d["id"] ?>" title="módosítások"
                    style="position: absolute;top:2px;left:2px;z-index:10;">
                <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("person", $d["id"])) ?></span>
            </a>
        <?php } ?>
        <?php $personLink = displayPersonPicture($d); ?>
    </div> <?php
    return $personLink;
}

function displayPersonNameAndGetClass ($db,$d) {
    if ($d["isTeacher"]==1) {
        $icon = '<i style="vertical-align: bottom" class="material-icons">school</i> ';
    } else {
        $icon = '<i style="vertical-align: bottom" class="material-icons">person</i> ';
    }
    echo '<a href="'.getPersonLink($d["lastname"],$d["firstname"]).'-'. $d["id"] .'"><h4>'.$icon. getPersonName($d).'</h4></a>';
    if ($d["isTeacher"]==1) {
        if ($d["gender"]=='f') $personClass='<h5>Tanárnő</h5>';
        elseif ($d["gender"]=='m') $personClass='<h5>Tanár úr</h5>';
        else $personClass = '<h5>Tanár</h5>';
    } else {
        if (!isset($d["classText"])) {
            $diakClass = $db->getClassById($d["classID"]);
            $d["classText"] = getSchoolClassName($diakClass);
        }
        if (isUserGuest($d)) {
            if (strstr($d["classText"],"staf")!==false)
                $personClass = '<h5>Jó barát:<a href="hometable?classid='.$d["classID"].'">'.$d["classText"].'</a></h5>';
            else
                $personClass = '<h5>Vendég:<a href="hometable?classid='.$d["classID"].'">'.$d["classText"].'</a></h5>';
        } else {
            $personClass = '<h5>Véndiák:<a href="hometable?classid='.$d["classID"].'">'.$d["classText"].'</a></h5>';
        }
    }
    return $personClass;
}

function displayPersonPicture($d)
{
    //mini icon
    if (isset($d["picture"]) && $d["picture"] != "") {
        $rstyle = ' diak_image_medium';
    } else {
        $rstyle = ' diak_image_empty';
    }
    if ($d["id"]!=-1) {
        //if (isUserLoggedOn() || isLocalhost()) {
        //     $personLink="editDiak?uid=".$d["id"];
        //} else {
            $personLink=getPersonLink($d["lastname"],$d["firstname"])."-".$d["id"];
        //}
    } else {
        $personLink="javascript:alert('Sajnos erről a személyről nincsenek adatok.');";
    }
    ?>
    <a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>" style="display:inline-block;">
        <div>
            <img src="<?php echo getPersonPicture($d)?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="<?php echo $rstyle?>" style="position: relative" />
            <?php if ((isset($d["deceasedYear"]) && intval($d["deceasedYear"])>=0) || isset($d["birthyear"])) {?>
                <?php if (isset($d["deceasedYear"])) {?>
                    <div style="background-color: black;color: white;hight:20px;text-align: center;border-radius: 0px 0px 5px 5px;position: relative;top: -8px;">
                <?php } else { ?>
                    <div style="background-color: lightgray;color: black;hight:20px;text-align: center;border-radius: 0px 0px 5px 5px;position: relative;top: -8px;">
                <?php }?>
                <?php if (isset($d["birthyear"]) && intval($d["birthyear"])>1800) {?>
                    <?php echo "* ".intval($d["birthyear"]).'&nbsp;&nbsp;'; ?>
                <?php } ?>
                <?php if (isset($d["deceasedYear"])) {?>
                    <?php echo intval($d["deceasedYear"])==0?"†":"† ".intval($d["deceasedYear"]); ?>
                <?php } ?>
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
    if (isset($d[$field]) && strlen($d[$field])>6)
        if(showField($d,$field))
            echo '<a target="_blank" href="'.$appl.getFieldValue($d[$field]).'" title="'.$title.'"><img src="images/'.$image.'" /></a>';
        else
            echo '<a href="#" onclick="hiddenData(\''.$title.'\');" title="'.$title.'"><img src="images/'.$image.'" /></a>';
}

function displaySchool($id) {
    if (getActSchoolId()==null) {
        global $schoolList;
        echo( '<div style="margin-bottom: -15px;">'. $schoolList[array_search($id,array_column($schoolList,"id"))]["name"] . "</div>");

    }
}

\maierlabs\lpfw\Appl::addJsScript("
	function hiddenData(title) {
		showModalMessage(title,'Személyes adat védve!<br/>Csak iskola vagy osztálytárs tekintheti meg ezt az informácíót.');
	}
");



