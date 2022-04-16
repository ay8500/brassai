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
            $school = displaySchoolName(isset($d["schoolID"])?$d["schoolID"]:null);
            $personClass = displayPersonNameAndGetClass($db,$person,$showClass);
        ?>
        <?php if ($person["gdpr"]==100) {?>
            <img title="A személy jováhagyta a személyes adatainak a használatát kizárolag ezen az oldalon!" src="images/gdpr.png" style="position: absolute;width:58px;left:435px;top:3px" />
        <?php } ?>
        <?php if ($person["gdpr"]>0 && $person["gdpr"]<=5) {?>
            <img title="A személy tiltja részben vagy teljes mértékben a személyes adatainak a használatát!" src="images/gdpr.png" style="position: absolute;width:58px;left:435px;top:3px;filter: hue-rotate(90deg);" />
        <?php } ?>
        <?php displayPersonPictureAndHistory($db,$d);?>
		<div class="personboxc">
            <?php if (strstr($d["role"],"jmlaureat")!==false)
                echo('<div><a href="search?type=jmlaureat">'.$school['awardName'].' díjas</a></div>');?>
            <?php if ($showClass)
                echo($personClass);?>
            <div class="fields"><?php
				if ($d["schoolIdsAsTeacher"]===NULL) {
					if(showField($d,"partner")) echo "<div><div>Élettárs:&nbsp;</div><div>".getFieldValue($d,"partner")."</div></div>";
					if(showField($d,"education")) echo "<div><div>Végzettség:&nbsp;</div><div>".getFieldValue($d,"education")."</div></div>";
					if(showField($d,"employer")) 	{
						$fieldString = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "",	getFieldValue($d["employer"]));
						echo "<div><div>Munkahely:&nbsp;</div><div>".$fieldString ."</div></div>";
					}
					if(showField($d,"function")) echo "<div><div>Beosztás:&nbsp;</div><div>".getFieldValue($d["function"])."</div></div>";
				} else {
					if (isset($d["function"]))
					    echo "<div><div>Tantárgy:&nbsp;</div><div> ".getFieldValue($d["function"])."</div></div>";
                    echo "<div><div>Iskola:&nbsp;</div><div>";
                        $schools = explode(")",$d["schoolIdsAsTeacher"]);
                        foreach ($schools as $school) {
                            $school = intval(trim($school,"("));
                            if (($school=$db->getSchoolById($school,true))!=null) {
                                echo '<span><img src="images/school' . $school["id"] . '/logo.jpg" title="' . $school["name"] . '"/>&nbsp;';
                                echo $db->getTeacherPeriod($d, $school["id"]).'</span>';
                            }
                        }
                    echo "</div></div>";
				}
				if(showField($d,"country")) 	echo "<div><div>Ország:&nbsp;</div><div>".getFieldValue($d["country"])."</div></div>";
				if(showField($d,"place")) 		echo "<div><div>Város:&nbsp;</div><div>".getFieldValue($d["place"])."</div></div>";
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

        if ((!isset($person["deceasedYear"]) || $person["deceasedYear"]==null) ) {
            if (!isset($person["gender"]) || $person["gender"]=="f" && ($db->getPersonByID(getLoggedInUserId())["gender"]=="m" || !isUserLoggedOn())) {
                ?>
                <button style="margin-bottom: 5px" onclick="return saveEasterOpinion(<?php echo $person['id'] ?>,'person','easter',<?php echo getLoggedInUserId()!=null?getLoggedInUserId():"null" ?>)" title="Megszabad locsolni?" class="btn btn-success"><img src="images/easter.png" style="width: 26px"/> Szabad öntözni?</button>
                <?php
            }
        }

        displayPersonOpinion($dbOpinion,$d["id"],$d["gender"],(isset($d["schoolIdsAsTeacher"]) && $d["schoolIdsAsTeacher"]!=NULL),isset($d["deceasedYear"]));
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
                <?php displaySchoolName($p["schoolID"]); ?>
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
            <?php displaySchoolName($class["schoolID"]); ?>
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
 * Display a class
 * @param dbDAO $db the database
 * @param array $class
 * @param bool $showDate
 */
function displaySchool($db,$school,$showDate=false) {
    ?>
    <div class="element" style="text-align: left">
        <div style="display: block;max-width:400px;min-width:300px; vertical-align: top;margin-bottom:10px;">
            <h4><i class="material-icons" style="vertical-align: bottom;">school</i> Iskola: <a href="start?schoolid=<?php echo $school["id"]?>"><?php echo $school["name"];?></h4></a>
        </div>

        <div style="display: inline-block;vertical-align: top; ">
            <a href="school?schoolid=<?php echo $school["id"]?>" >
                <img style="height: 65px" src="images/school<?php echo $school["id"]?>/logo.jpg" />
            </a>
        </div>
        <div style="display: inline-block;vertical-align: top;margin-left: 30px ">
            <div><span><?php echo $school["addressZipCode"]." ".$school["addressStreet"] ?></span></div>
            <div><span><a href="<?php echo $school["homepage"] ?>" target="_blank"><?php echo $school["homepage"] ?></a></span></div>
            <div><span><a href="mailto:<?php echo $school["mail"] ?>" target="_blank"><?php echo $school["mail"] ?></a></span></div>
        </div>

        <?php if (isset($school["directorId"])) {
            displayPersonPicture($school["directorId"]);
        } ?>

        <?php
        $pictureId = 0;
        if ($pictureId >0) {?>
            <div style="display: inline-block;vertical-align: top; ">
                <a href="picture?id=<?php echo $pictureId?>" >
                    <img src="imageConvert?width=300&thumb=false&id=<?php echo $pictureId?>" />
                </a>
            </div>
        <?php } ?>

        <?php  if (isUserSuperuser()) {?>
            <a href="history?table=school&id=<?php echo $school["id"]?>" style="position: absolute; left: 15px;top: 55px;">
                <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("school",$school["id"]))?></span>
            </a>
        <?php }?>
        <?php if ($showDate) {
            $person = $db->getPersonByID($school["changeUserID"]);
            ?>
            <br/><div style="display: block;max-width:350px;min-width:300px; vertical-align: top;margin-bottom:10px;">
                Módosította: <?php echo getPersonLinkAndPicture($person) ?>
                <?php echo maierlabs\lpfw\Appl::dateTimeAsStr($school["changeDate"]);?>
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
            <?php displaySchoolName(($db->getPersonByID($message["changeUserID"]))["schoolID"]); ?>
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
            <?php displaySchoolName($d["schoolID"]); ?>
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
            <?php displaySchoolName($article["schoolID"]); ?>
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
    if ($d["schoolIdsAsTeacher"]!=NULL) {
        $icon = '<i style="vertical-align: bottom" class="material-icons">school</i> ';
    } else {
        $icon = '<i style="vertical-align: bottom" class="material-icons">person</i> ';
    }
    echo '<a href="'.getPersonLink($d["lastname"],$d["firstname"]).'-'. $d["id"] .'"><h4>'.$icon. getPersonName($d).'</h4></a>';
    $personClass ="";
    if ( isset($d["classID"]) && $d["classID"]>0) {
        if (!isset($d["classText"])) {
            $diakClass = $db->getClassById($d["classID"]);
            $d["classText"] = getSchoolClassName($diakClass);
        }
        if (isUserGuest($d)) {
            if (strpos($d["classText"],"Tanár")!==false)
                $personClass .= '<h5>Jó barát:<a href="hometable?classid=' . $d["classID"] . '">' . $d["classText"] . '</a></h5>';
            else
                $personClass .= '<h5>Vendég:<a href="hometable?classid=' . $d["classID"] . '">' . $d["classText"] . '</a></h5>';
        } else {
            if (strpos($d["classText"],"Tanár")===false)
                $personClass .= '<h5>Véndiák:<a href="hometable?classid=' . $d["classID"] . '">' . $d["classText"] . '</a></h5>';
        }
    }
    if ($d["schoolIdsAsTeacher"]!=NULL) {
        if ($d["gender"]=='f') $personClass .='<h5>Tanárnő</h5>';
        elseif ($d["gender"]=='m') $personClass .='<h5>Tanár úr</h5>';
        else $personClass = '<h5>Tanár</h5>';
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

function displaySchoolName($id) {
    global $schoolList;
    $school = $schoolList[array_search($id,array_column($schoolList,"id"))];
    if (getActSchoolId()==null || getActSchoolId()!=$id) {
        echo( '<div class="schoolname" style="margin-bottom: -15px;">'. $school["name"] . "</div>");

    }
    return $school;
}

\maierlabs\lpfw\Appl::addJsScript("
	function hiddenData(title) {
		showModalMessage(title,'Személyes adat védve!<br/>Csak iskola vagy osztálytárs tekintheti meg ezt az informácíót.');
	}
");



