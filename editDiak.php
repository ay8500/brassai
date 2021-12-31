<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaCandle.class.php';
include_once 'editPersonDataHelper.php';

use \maierlabs\lpfw\Appl as Appl;
global $db;
global $userDB;

Appl::addCss('css/chosen.css');

$tabOpen= getParam("tabOpen", "person");
$personid = getIntParam("uid",null);
if (getParam("type")=="personID" && getParam("typeid")!=null) {
    $personid=getIntParam("typeid");
}
if($personid!=null){
    $diak = $db->getPersonByID($personid);
	if ($diak!=null) {
		setActUserId($personid);	//save actual person in case of tab changes
		setActClass($diak["classID"],$diak["schoolID"]);
	} else {
		$diak=$db->getPersonByUser($personid);
		if ($diak!=null) {
			$personid=$diak["id"];
			setActUserId($diak["id"]);
		}
	}
}
else {
	$personid=getActUserId();
}

//Parameters
$action=getParam("action","");

$anonymousEditor=getParam("anonymousEditor")=="true";

//Edit or only view variant this page
$edit = (isUserEditor() || isUserSuperuser() || isAktUserTheLoggedInUser() || $anonymousEditor || $action=="changediak");

//Create new person 
$createNewPerson = $action=="newperson" || $action=="newguest" || $action=="newteacher" || $action=="savenewperson" || $action=="savenewguest" || $action=="savenewteacher";
if ( $createNewPerson ) {
	$diak = $db->getPersonDummy();
	$diak["id"] = -1;
	$diak["classID"] = getActClassId();
    if ($action=="newteacher" || $action=="savenewteacher" ) {
        $diak["schoolIdsAsTeacher"]=null;
        $diak["classID"] = null;
        $db->addActSchoolTeacher($diak);
    } else {
        $diak["schoolIdsAsTeacher"] = NULL;
    }
    ($action=="newguest"   || $action=="savenewguest" )? $diak["role"]="guest"	:	$diak["role"]="";
	$personid=-1;
}

//load person data from db in $diak
if ($personid!=null && $personid>=0) {
	$diak = $db->getPersonByID($personid);
	if ($diak!=null) {
        if ($diak["classID"]!=0) {
            $classId = $diak["classID"];
            $class = $db->getClassById($classId);
            setActClass($classId, $class["schoolID"]);
        } else {
            if ($diak["schoolIdsAsTeacher"]!=null) {
                $classId = null;
                $schoolId= intval(trim((explode(")",$diak["schoolIdsAsTeacher"]))[0],"("));
                if ($schoolId>0) {
                    setActSchool($schoolId);
                } else {
                    pageError("Személy adatai hibásak! <br/>Hiba: hiányzik a végzös osztály és a személy nem tanár");
                }
            } else {
                pageError("Személy adatai hibásak! <br/>Hiba: a személy ismeretlen iskolában tanár.");
            }
        }
        $firstPicture["file"] = "images/".$diak["picture"];
        \maierlabs\lpfw\Appl::setMember("firstPicture",$firstPicture);
    } else {
        pageError("Személy adatai hibásak! <br/>Személy nem létezik.");
	}
}

//GDPR person requested all data to be deleted exept name
if (!isUserAdmin() && $diak["gdpr"]==5) {
    include_once "homemenu.inc.php";
    ?>
        <div class="well">
            <h3><?php echo ($diak["lastname"].' '.$diak["firstname"]) ?></h3>
            Személyes adatok védve vannak. Módosítás vagy bővítés a személy kérésére nem lehetséges.
            <?php displayPerson($db,$diak,false,false); ?>
        </div>
    <?php
    include_once "homefooter.inc.php";
    die();
}

global $dataFieldNames, $dataFieldObl, $dataFieldCaption, $dataItemProp, $dataCheckFieldVisible;
setPersonFields($diak,isUserSuperuser(),isUserAdmin());

//save changes
if ($action=="changediak" || $action=="savenewperson" || $action=="savenewteacher" || $action=="savenewguest") {
	if ($db->checkRequesterIP(changeType::personchange)) {
		if ($diak!=null) {
			for ($i=0;$i<sizeof($dataFieldNames);$i++) {
				$tilde="";
				if ($dataCheckFieldVisible[$i]) {
					if (getParam("cb_".$dataFieldNames[$i])!=null)
						$tilde="~";
				}
				//save the fields in the person array
				$fvalue =trim(getParam($dataFieldNames[$i]));
                if ($fvalue!==null) {
                    $diak[$dataFieldNames[$i]] = $tilde . $fvalue;
                }
			}
			//ClassID
			if (getIntParam("classID",-1)>-1) {
				$diak["classID"]=getIntParam("classID");
			}
            //person is taecher as well
            if (getParam("schoolIdsAsTeacher","")!="") {
                $diak["schoolIdsAsTeacher"]=getParam("schoolIdsAsTeacher");
                if (getParam("teacherPeriod","")!="") {
                    $diak["employer"]=getParam("teacherPeriod");
                }
                if (getParam("field")!=null) {
                    $diak["function"]=getParam("field");
                }
            } else {
                $diak["schoolIdsAsTeacher"]=null;
            }
			//No dublicate email address is allowed
			if (isset($diak["email"]) && checkUserEmailExists($userDB,$diak["id"],$diak["email"])) {
				Appl::setMessage("E-Mail cím már létezik az adatbankban!<br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tE-Mail exists".getActUserId(),\maierlabs\lpfw\LoggerLevel::error);
			} elseif ((isset($diak["classID"]) && $diak["classID"]==="-1") || !isset($diak["classID"])) {
				Appl::setMessage("Osztály nincs kiválasztva!","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tClass not selected".getActUserId(),\maierlabs\lpfw\LoggerLevel::error);
			} elseif (checkUserNameExists($userDB,$diak["id"], $diak["user"])) {
				Appl::setMessage("Felhasználó név már létezik!<br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tUsername exists".getActUserId(),\maierlabs\lpfw\LoggerLevel::error);
                //Validate the mail address if no admin logged on
			} elseif (isset($diak["email"]) && $diak["email"]!="" && filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false && !isUserAdmin()) {
				Appl::setMessage("E-Mail cím nem helyes! <br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tE-Mail wrong syntax".getActUserId(),\maierlabs\lpfw\LoggerLevel::error);
            } elseif (($diak["lastname"]=="" || $diak["firstname"]=="" ) && !isUserAdmin()) {
				Appl::setMessage("Családnév vagy Keresztnév üres! <br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tEmpty name fields".getActUserId(),\maierlabs\lpfw\LoggerLevel::error);
            } elseif ((strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) && !isUserAdmin()) {
				Appl::setMessage("Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tName too short".getActUserId(),\maierlabs\lpfw\LoggerLevel::error);
            } else {
				$personid = $db->savePerson($diak);
				if ($personid>=0) {
					setActUserId($personid);		//set actual person in case of tab changes
					setActClass($diak["classID"]);	//set actual class in case of class changes
                    \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getActUserId(),\maierlabs\lpfw\LoggerLevel::info);
					$db->saveRequest(changeType::personchange);
					header("location:hometable?class=".$diak["classID"]."&action=saveok");
				} else {
					Appl::setMessage("Az adatok kimentése nem sikerült! Hibakód:1631","warning");
                    \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getActUserId()."\tError:1631",\maierlabs\lpfw\LoggerLevel::error);
				}
			}
		} else {
			Appl::setMessage("Az adatok kimentése nem sikerült! Hibakód:1034","warning");
            \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getActUserId()."\tError:1034",\maierlabs\lpfw\LoggerLevel::error);
		}
	} else {
		Appl::setMessage("Az adatok módosítása anonim látogatók részére korlátozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.","warning");
        \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getActUserId()."\tError: too many",\maierlabs\lpfw\LoggerLevel::error);
	}
	if ($personid==-1) {
		if ($action=="savenewteacher") $action="newteacher";
		else if ($action=="savenewperson") $action="newperson";
		else if ($action=="savenewguest") $action="newguest";
	}
}

//Change password
if ($action=="changepassw" && isUserLoggedOn()) {
	if (isset($_GET["newpwd1"])) $newpwd1=$_GET["newpwd1"]; else $newpwd1="";
	if (isset($_GET["newpwd2"])) $newpwd2=$_GET["newpwd2"]; else $newpwd2="";
	if (strlen($newpwd1)>5) {
		if ($newpwd1==$newpwd2) {
            $ret=$userDB->setUserPassword(getActUserId(),encrypt_decrypt("encrypt",$newpwd1));
			if ($ret>=0) {
				if (!isUserAdmin())
                    \maierlabs\lpfw\Logger::_("SavePassw\t".getLoggedInUserId());
					Appl::setMessage("Jelszó módosíva!", "success");
			} else {
				Appl::setMessage("Jelszó kimentése nem sikerült!", "warning");
			}
		}
		else Appl::setMessage("Jelszó ismétlése hibás!", "warning");
	}
	else Appl::setMessage("Jelszó rövid, minimum 6 karakter!", "warning");
}

//Change user name
if ($action=="changeuser" && isUserLoggedOn()) {
	if (isset($_GET["user"]))  $user=$_GET["user"]; else $user="";
	if (strlen( $user)>2) { 
		if (!checkUserNameExists($userDB,$personid,$user)) {
			$ret=$db->savePersonField(getActUserId(),'user', $user);
			if ($ret>=0) {
				$_SESSION["USER"]=$user;
				if (!isUserAdmin())
                    \maierlabs\lpfw\Logger::_("SaveDataname\t".getLoggedInUserId());
					Appl::setMessage("Becenév módosíva!", "success");
			} else {
				Appl::setMessage("Becenév módosítása nem sikerült!", "warning");
			}
		}
		else
			Appl::setMessage("Becenév már létezik válassz egy másikat!", "warning");
	}
	else
		Appl::setMessage("Becenév rövid, minimum 3 karakter!", "warning");
}

//Remove Facebook connection
if ($action=="removefacebookconnection"  && isUserLoggedOn()) {
	$ret = $db->savePersonField(getLoggedInUserId(), "facebookid", 0);
	$diak=$db->getPersonByID(getLoggedInUserId());
	if ((!isset($diak["facebookid"]) || $diak["facebookid"]=null) && $ret>=0) {
        Appl::setMessage("Facebook kapcsolat törlése sikerült","success");
        unset($_SESSION['FacebookId']);
        \maierlabs\lpfw\Logger::_("FacebookDelete\t".getLoggedInUserId());
    } else {
        Appl::setMessage("Facebook kapcsolat törlése nem sikerült","warning");
    }
}

//Delete Picture
if (getIntParam("deletePersonPicture",-1)>=0 && (isUserSuperuser() || getLoggedInUserId()==getRealId($diak))) {
	if ($db->unlinkPersonPicture(getIntParam("deletePersonPicture"))) {
		Appl::setMessage("Kép sikeresen törölve","success");
		$diak["picture"]=null;
	} else {
		Appl::setMessage("Kép törlés sikertelen!","warning");
	}
}

//Upload Image
if (isset($_POST["action"]) && $_POST["action"]=="upload_diak" ) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = preg_split( "/[.]/", basename( $_FILES['userfile']['name']));
		if ($db->checkRequesterIP(changeType::personupload)) {
			//Only jpg
			if (strcasecmp($fileName[1],"jpg")==0) {
				//Create folder is doesn't exists
				$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".$db->getActClassFolder();
				if (!file_exists($fileFolder)) {
 	   				mkdir($fileFolder, 0777, true);
				}
				//The max size of e picture 
				if ($_FILES['userfile']['size']<3100000) {
					if (isUserAdmin() && null!=getParam("overwriteFileName")) {
						//Overwrite an existing file
						$pFileName='/'.basename(getParam("overwriteFileName"));
						unlink($fileFolder.$pFileName);
						$overwrite=true;
					} else {
						$idx=rand(234567,999999);
						$pFileName="/d".$personid."-".$idx.".".strtolower($fileName[1]);
						$overwrite=false;
					}
					$uploadfile=$fileFolder.$pFileName;
					if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
						if (!$overwrite) {
							$diak['picture']=$db->getActClassFolder().$pFileName;
							if ($db->savePersonField($personid, "picture", $db->getActClassFolder().$pFileName)>=0) {
								$db->saveRequest(changeType::personupload);
								resizeImage($uploadfile,400,400,"o");
								Appl::setMessage($fileName[1]." Profilkép sikeresen feltöltve.","succes");
                                \maierlabs\lpfw\Logger::_("UserPicture\t".getLoggedInUserId()."\t".$idx);
							} else {
								Appl::setMessage($fileName[1]." Profilkép feltötése sikertelen. Probálkozz újra","warning");
                                \maierlabs\lpfw\Logger::_("UserPicture\t".getLoggedInUserId()."\t".$idx,\maierlabs\lpfw\LoggerLevel::error);
							}
						} else {
							resizeImage($uploadfile,400,400,"o");
							Appl::setMessage("Kép sikeresen kicserélve","success");
						}
					} else {
						Appl::setMessage($fileName[1]." feltötése sikertelen. Probálkozz újra. Hibakód:4091","warning");
                        \maierlabs\lpfw\Logger::_("UserPicture\t".getLoggedInUserId()."Error:4091",\maierlabs\lpfw\LoggerLevel::error);
					}
				}
				else {
					Appl::setMessage($fileName[1]." A kép nagysága túlhaladja 3 MByteot.<br />Probáld a képet kissebb formátumba konvertálni, és töltsd fel újra.","warning");
                    \maierlabs\lpfw\Logger::_("UserPicture\t".getLoggedInUserId()."\tError: to big",\maierlabs\lpfw\LoggerLevel::error);
				} 	
			}
			else {
				Appl::setMessage($fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.<br />Probáld a képet jpg formátumba konvertálni, és töltsd fel újra.","warning");
                \maierlabs\lpfw\Logger::_("UserPicture\t".getLoggedInUserId()."\tError: only jpg",\maierlabs\lpfw\LoggerLevel::error);

            }
		} else {
			Appl::setMessage("Sajnáljuk, de tul sok képet probálsz feltölteni!<br/>Az adatok módosítása anonim felhasználok részére korlatozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.","warning");
		}
	}
}

// Title an subtitle of the page schoolmate or guests
if ($diak["schoolIdsAsTeacher"]!=NULL) {
    Appl::setSiteSubTitle("Tanári kar");
    Appl::$title = getPersonName($diak) . ' ' . Appl::$subTitle;
} else if (isActClassStaf()) {
    Appl::setSiteSubTitle(" Barátaink");
    Appl::$title=getPersonName($diak).' '.Appl::$subTitle;
} else {
    if (isUserGuest($diak)) {
        Appl::setSiteSubTitle(getActSchoolClassName()." Vendég jó barát");
        Appl::$title=getPersonName($diak).' '.Appl::$subTitle;
    } else {
        if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
            $headTeacher=$db->getPersonByID($class["headTeacherID"]);
            Appl::setSiteSubTitle(getActSchoolClassName()." Osztályfőnök: ".getPersonLinkAndPicture($headTeacher));
        } else {
            Appl::setSiteSubTitle("Osztály ".getActSchoolClassName());
        }
        Appl::$title=getPersonName($diak)." ".getActSchoolClassName();
    }
}

include("homemenu.inc.php");
if (isActionParam("newperson") && getActSchoolId()==null) {
    Appl::setMessage("Vállasssz egy iskolát vagy egy osztályt az új személy részére!","warning");
    include "homefooter.inc.php";
    die();
}
?>

<?php if (isActionParam("new") && isset($diak)){?>
	<div itemscope itemtype="http://schema.org/Person">
	<h2 class="sub_title" style="text-align: left;margin-left:20px">
		<img src="<?php echo getPersonPicture($diak) ?>" class="diak_image_icon" />
        <span style="font-size: 22px">
	        <span itemprop="name"><?php echo($diak["title"])." ".$diak["lastname"]." ".$diak["firstname"] ?></span>
	        <?php if (showField($diak,"birthname")) echo('('.$diak["birthname"].')');?>
        </span>
	</h2>
	</div>
<?php } else { ?>
	<div style="margin-bottom: 15px">&nbsp;</div>
<?php }

//initialize tabs
$tabsTranslate["search"] = array(".php","/dc?","/brassai/dc?");
$tabsTranslate["replace"] = array("","/editDiak?","/brassai/editDiak?");
if ($action=="newperson") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új diák adatai", "glyphicon" => "*person"));
} else if ($action=="newguest") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új barát vagy vendég adatai", "glyphicon" => "*person"));
} else if ($action=="newteacher") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új tanárnő vagy tanár adatai", "glyphicon" => "*person"));
} else {
    $tabsCaption = array(array("id" => "person", "caption" => "Személyes&nbsp;adatok", "glyphicon" => "*person"));
}
if (isset($diak["deceasedYear"])) {
    array_push($tabsCaption ,array("id" => "candles", "caption" => "Gyertyák", "glyphicon" => "*whatshot"));
}
array_push($tabsCaption ,array("id" => "pictures", "caption" => "Képek", "glyphicon" => "*photo_camera"));
array_push($tabsCaption ,array("id" => "family", "caption" => "Családtagok", "glyphicon" => "*device_hub"));

array_push($tabsCaption ,array("id" => "cv", "caption" => "Életrajz", "glyphicon" => "*description"));
array_push($tabsCaption ,array("id" => "hobbys", "caption" => "Szabadidőmben", "glyphicon" => "*access_time"));
if ($diak["schoolIdsAsTeacher"]==NULL) {
    array_push($tabsCaption ,array("id" => "school", "caption" => "Diákkoromból", "glyphicon" => "*folder_shared"));
} else {
    array_push($tabsCaption ,array("id" => "classes", "caption" => "Tanítványok", "glyphicon" => "*folder_shared"));
}
array_push($tabsCaption ,array("id" => "geoplace", "caption" => "Térkép", "glyphicon" => "*public"));
if(isUserLoggedOn() || isUserAdmin()) {
    if (getLoggedInUserId()==$diak["id"] || isUserAdmin())
        array_push($tabsCaption ,array("id" => "user", "caption" => "Bejelentkezési&nbsp;adatok", "glyphicon" => "*vpn_key"));
    array_push($tabsCaption ,array("id" => "info", "caption" => "Infók", "glyphicon" => "info-sign"));
}

$tabUrl="editDiak";
?>
<div class="container-fluid"><?php
    include Config::$lpfw.'view/tabs.inc.php';?>
	<div class="well"><?php
		//Personal Data
		if ($tabOpen=="person") {
			include("editPersonData.php");
		}
        //Teachers classes
        if ($tabOpen=="classes") {
            include("editPersonClasses.php");
        }
        //Candles
        if ($tabOpen=="candles") {
            if (isset($diak["deceasedYear"]) && $diak["deceasedYear"]!=null) {
                include("rip.inc.php");
                $personList = array();
                $personList[0] = $diak;
                \maierlabs\lpfw\Appl::addJs('js/candles.js', true);
                displayRipPerson(new dbDaCandle($db), $diak);
            }
        }
		//Pictures
		if ($tabOpen=="pictures") {
			$type="personID";
			$typeId=getRealId($diak);
			include_once "picture.inc.php";
		}
		//Change storys cv, scool trory, sparetime
		if (in_array($tabOpen,array("cv","hobbys","school"))) {
			include("editPersonStorys.php");
		}
		//Change geo place
		if ($tabOpen=="geoplace") {
			include("editPersonGeoPlace.php");
		}
        //Family
        if ($tabOpen=="family") {
            include("editPersonFamily.inc.php");
        }
		//Change password, usename, facebook
		if ($tabOpen=="user") {
			include("editPersonUserData.php");
		}
		//Activities
		if ($tabOpen=="info") {
			include("editPersonActivities.php");
		}
		?>
	</div>
</div>

<script type="text/javascript">
	function deleteDiak(id) {
		if (confirm("Személy végleges törölését kérem konfirmálni!")) {
			window.location.href="hometable?uid="+id+"&action=delete_diak";
		}
	}
</script>
<?php
Appl::addJs('js/chosen.jquery.js');
Appl::addJsScript('
    $(document).ready(function(){
        $(".chosen").chosen({width:"100%",no_results_text:"Ilyen opció nincs!"});
    });
');
include 'homefooter.inc.php';

function pageError($text) {
    include "homemenu.inc.php";
    Appl::setMessage($text,"danger");
    include "homefooter.inc.php";
    die();
}

