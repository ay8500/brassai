<?php 
include_once 'lpfw/sessionManager.php';
include_once 'lpfw/userManager.php';
include_once 'lpfw/ltools.php';
include_once 'lpfw/appl.class.php';
include_once 'dbBL.class.php';
include_once  'dbDaCandle.class.php';

use \maierlabs\lpfw\Appl as Appl;

$tabOpen= getParam("tabOpen", 0);

$personid = getParam("uid",null);
if($personid!=null){
    $diak = $db->getPersonByID($personid);
	if ($diak!=null) {
		setAktUserId($personid);	//save actual person in case of tab changes
		setAktClass($diak["classID"]);
	} else {
		$diak=$db->getPersonByUser($personid);
		if ($diak!=null) {
			$personid=$diak["id"];
			setAktUserId($diak["id"]);
		}
	}
}
else {
	$personid=getAktUserId();	
}

//Parameters
$action=getParam("action","");

$anonymousEditor=getParam("anonymousEditor")=="true";

//Edit or only view variant this page
$edit = (userIsAdmin() || userIsEditor() || userIsSuperuser() || isAktUserTheLoggedInUser() || $anonymousEditor || $action=="changediak");

//Create new person 
$createNewPerson = $action=="newperson" || $action=="newguest" || $action=="newteacher" || $action=="savenewperson" || $action=="savenewguest" || $action=="savenewteacher";
if ( $createNewPerson ) {
	$diak = $db->getPersonDummy();
	$diak["id"] = -1;
	$diak["classID"] = getAktClassId();
    ($action=="newteacher" || $action=="savenewteacher" )? $diak["isTeacher"]=1	:	$diak["isTeacher"]=0;
    ($action=="newguest"   || $action=="savenewguest" )? $diak["role"]="guest"	:	$diak["role"]="";
	$personid=-1;
}

if ($personid!=null && $personid>=0) {
	$diak = $db->getPersonByID($personid);
	if ($diak!=null) {
		$classId=$diak["classID"];
		$class=$db->getClassById($classId);
		setAktClass($classId);
	} else {
		header('Location:dc.php');
		exit;
	}
}

//GDPS person requested all data to be deleted exept name
if (!userIsAdmin() && getFieldChecked($diak,"place")!="") {
    include_once "homemenu.inc.php";
    ?>
        <div class="well">
            <h3><?php echo ($diak["lastname"].' '.$diak["firstname"]) ?></h3>
            Személyes adatok védve vannak. Módosítás vagy bővítés a személy kérésére nem lehetséges.
        </div>
    <?php
    include_once "homefooter.inc.php";
    die();
}

//preparation of the field to be edited and the itemprop characteristic
$offset=0;
$dataFieldNames 	=array("gender","title","lastname","firstname","email","birthname","deceasedYear");
$dataFieldCaption 	=array("Megszólítás","Akad.titulus","Családnév","Keresztnév","E-Mail","Diákkori név","† elhunyt");
$dataItemProp       =array("gender","title","","","","","");
$dataCheckFieldVisible	=array(false,false,false,false,true,false,false);
$dataFieldObl			=array("Hölgy/Úr","Akadémia titulus pl: Dr. Dr.Prof. ",true,true,"fontos mező","leánykori családnév","csak az évszámot kell beírni, ha nem tudod pontosan akkor 0-t írj ebbe a mezőbe. Kimentés után beadhatod a sírhelyet.");
if (isset($diak["deceasedYear"])){
    array_push($dataFieldNames ,"cementery","gravestone");
    array_push($dataFieldCaption,"Temető","Sírhely");
    array_push($dataItemProp,"","");
    array_push($dataCheckFieldVisible,false,false);
    array_push($dataFieldObl,"Temető neve, helyiség nélkül","");
    $offset=2;
}
if(true)  { //Address
	array_push($dataFieldNames, "partner","address","zipcode","place","country");
	array_push($dataItemProp,"","streetAddress","postalCode","addressLocality","addressCountry");
	array_push($dataFieldCaption, "Élettárs","Cím","Irányítószám","Helység","Ország");
	array_push($dataCheckFieldVisible, true,true,true,false,false);
	array_push($dataFieldObl		, "ha külömbőzik akkor a családneve is","útca, házszám, épület, emelet, apartament",false,"fontos mező","fontos mező");
}
if (true) { //Communication
	array_push($dataFieldNames, "phone","mobil","skype","facebook","homepage","education","employer","function","children");
	array_push($dataItemProp,"","","","","","","","","");
	array_push($dataFieldCaption,"Telefon","Mobil","Skype","Facebook","Honoldal","Végzettség","Munkahely","Beosztás","Gyerekek");
	array_push($dataCheckFieldVisible,true ,true ,true ,true,true ,true ,true,true ,true );
	array_push($dataFieldObl		, '+40 123 456789','+40 111 123456',false,'https://www.facebook.com/...','http://',false,false,false,"nevük és születési évük pl: Éva 1991, Tamás 2002");
}
if (userIsAdmin() || userIsSuperuser() ) {
    array_push($dataFieldNames, "role");
    array_push($dataItemProp,"role");
    array_push($dataFieldCaption, "Opciók");
    array_push($dataCheckFieldVisible, true);
    array_push($dataFieldObl, false);
}
if (userIsAdmin()) { //only for admin
	array_push($dataFieldNames, "facebookid","id", "user", "passw", "geolat", "geolng","userLastLogin","changeIP","changeDate","changeUserID","changeForID");
	array_push($dataItemProp,"","","","","","","","","","","");
	array_push($dataFieldCaption, "FB-ID","ID", "Felhasználó", "Jelszó", "X", "Y","Utolsó login","IP","Dátum","User","changeForID");
	array_push($dataCheckFieldVisible, false,false,false,false,false,false,false,false,false,false,false);
	array_push($dataFieldObl	 	 , false,true,true,true,false,false,'2000-01-01',false,'2000-01-01',false,false);
}
if ( isAktClassStaf() || $action=="savenewteacher" || $action=="newteacher" ) { //Teachers
    $dataFieldObl[18+$offset] = "Évszám mettől meddig pl: 1961-1987";
    $dataFieldCaption[18+$offset] = "Mettől meddig";
    $dataFieldObl[19+$offset] = "Leadott tantárgy, maximum kettő pl: matematika, angol nyelv";
    $dataFieldCaption[19+$offset] = "Tantárgy";
    $dataFieldCaption[20+$offset] = "Osztályfönök";
    $dataFieldObl[20+$offset] = "Év és osztály például: 1985 12A. Több osztály esetén vesszövel elválasztva. Például: 1985 12A,1989 12C";
}

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
			//No dublicate email address is allowed
			if (isset($diak["email"]) && checkUserEmailExists($userDB,$diak["id"],$diak["email"])) {
				Appl::setMessage("E-Mail cím már létezik az adatbankban!<br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tE-Mail exists".getAktUserId(),\maierlabs\lpfw\LoggerLevel::error);
			} elseif ((isset($diak["classID"]) && $diak["classID"]==="-1") || !isset($diak["classID"])) {
				Appl::setMessage("Osztály nincs kiválasztva!","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tClass not selected".getAktUserId(),\maierlabs\lpfw\LoggerLevel::error);
			} elseif (checkUserNameExists($userDB,$diak["id"], $diak["user"])) {
				Appl::setMessage("Felhasználó név már létezik!<br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tUsername exists".getAktUserId(),\maierlabs\lpfw\LoggerLevel::error);
                //Validate the mail address if no admin logged on
			} elseif (isset($diak["email"]) && $diak["email"]!="" && filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false && !userIsAdmin()) {
				Appl::setMessage("E-Mail cím nem helyes! <br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tE-Mail wrong syntax".getAktUserId(),\maierlabs\lpfw\LoggerLevel::error);
            } elseif (($diak["lastname"]=="" || $diak["firstname"]=="" ) && !userIsAdmin()) {
				Appl::setMessage("Családnév vagy Keresztnév üres! <br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tEmpty name fields".getAktUserId(),\maierlabs\lpfw\LoggerLevel::error);
            } elseif ((strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) && !userIsAdmin()) {
				Appl::setMessage("Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Az adatok kimentése sikertelen.","warning");
                \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\tName too short".getAktUserId(),\maierlabs\lpfw\LoggerLevel::error);
            } else {
				$personid = $db->savePerson($diak);
				if ($personid>=0) {
					setAktUserId($personid);		//set actual person in case of tab changes
					setAktClass($diak["classID"]);	//set actual class in case of class changes
                    \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getAktUserId(),\maierlabs\lpfw\LoggerLevel::info);
					$db->saveRequest(changeType::personchange);
					header("location:hometable.php?class=".$diak["classID"]."&action=saveok");
				} else {
					Appl::setMessage("Az adatok kimentése nem sikerült! Hibakód:1631","warning");
                    \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getAktUserId()."\tError:1631",\maierlabs\lpfw\LoggerLevel::error);
				}
			}
		} else {
			Appl::setMessage("Az adatok kimentése nem sikerült! Hibakód:1034","warning");
            \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getAktUserId()."\tError:1034",\maierlabs\lpfw\LoggerLevel::error);
		}
	} else {
		Appl::setMessage("Az adatok módosítása anonim felhasználok részére korlátozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.","warning");
        \maierlabs\lpfw\Logger::_("SaveData\t".getLoggedInUserId()."\t".getAktUserId()."\tError: too many",\maierlabs\lpfw\LoggerLevel::error);
	}
	if ($personid==-1) {
		if ($action=="savenewteacher") $action="newteacher";
		else if ($action=="savenewperson") $action="newperson";
		else if ($action=="savenewguest") $action="newguest";
	}
}

//Change password
if ($action=="changepassw" && userIsLoggedOn()) {
	if (isset($_GET["newpwd1"])) $newpwd1=$_GET["newpwd1"]; else $newpwd1="";
	if (isset($_GET["newpwd2"])) $newpwd2=$_GET["newpwd2"]; else $newpwd2="";
	if (strlen($newpwd1)>5) {
		if ($newpwd1==$newpwd2) {
			$ret=$db->savePersonField(getAktUserId(), "passw", encrypt_decrypt("encrypt",$newpwd1));
			if ($ret>=0) {
				if (!userIsAdmin()) 
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
if ($action=="changeuser" && userIsLoggedOn()) {
	if (isset($_GET["user"]))  $user=$_GET["user"]; else $user="";
	if (strlen( $user)>2) { 
		if (!checkUserNameExists($userDB,$personid,$user)) {
			$ret=$db->savePersonField(getAktUserId(),'user', $user);
			if ($ret>=0) {
				$_SESSION["USER"]=$user;
				if (!userIsAdmin()) 
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
if ($action=="removefacebookconnection"  && userIsLoggedOn()) {
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
if (getIntParam("deletePersonPicture",-1)>=0 && (userIsAdmin() || userIsSuperuser() || getLoggedInUserId()==getRealId($diak))) {
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
				$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".$db->getAktClassFolder();
				if (!file_exists($fileFolder)) {
 	   				mkdir($fileFolder, 0777, true);
				}
				//The max size of e picture 
				if ($_FILES['userfile']['size']<3100000) {
					if (userIsAdmin() && null!=getParam("overwriteFileName")) {
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
							$diak['picture']=$db->getAktClassFolder().$pFileName;
							if ($db->savePersonField($personid, "picture", $db->getAktClassFolder().$pFileName)>=0) {
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
$guests = isPersonGuest($diak);
if (isAktClassStaf()) {
    if (intval($diak["isTeacher"])==1)
        Appl::setSiteSubTitle("Tanári kar");
    else
        Appl::setSiteSubTitle(" Barátaink");
    Appl::setSiteTitle(Appl::$subTitle.' '.getPersonName($diak));
} else {
    if ($guests) {
        Appl::setSiteSubTitle(getAktClassName()." Vendég jó barát");
        Appl::setSiteTitle(Appl::$subTitle.' '.getPersonName($diak));
    } else {
        if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
            $headTeacher=$db->getPersonByID($class["headTeacherID"]);
            Appl::setSiteSubTitle(getAktClassName()." Osztályfőnök: ".getPersonLinkAndPicture($headTeacher));
            Appl::setSiteTitle(getAktClassName()." Osztályfőnök ".getPersonName($headTeacher));
        } else {
            Appl::setSiteSubTitle("Osztály ".getAktClassName());
            Appl::setSiteTitle(Appl::$subTitle.' '.getPersonName($diak));
        }
    }
}
\maierlabs\lpfw\Appl::addCss('css/chosen.css');

include("homemenu.inc.php");
?>

<?php if (isActionParam("new")=="" && isset($diak)){?>
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
if ($action=="newperson") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új diák adatai", "glyphicon" => "user"));
} else if ($action=="newguest") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új barát vagy vendég adatai", "glyphicon" => "user"));
} else if ($action=="newteacher") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új tanárnő vagy tanár adatai", "glyphicon" => "user"));
} else {
    $tabsCaption = array(array("id" => "person", "caption" => "Személyes&nbsp;adatok", "glyphicon" => "user"));
}
if (isset($diak["deceasedYear"])) {
    array_push($tabsCaption ,array("id" => "candles", "caption" => "Gyertyák", "glyphicon" => "plus"));
}
array_push($tabsCaption ,array("id" => "pictures", "caption" => "Képek", "glyphicon" => "picture"));
array_push($tabsCaption ,array("id" => "family", "caption" => "Családtagok", "glyphicon" => "heart"));

array_push($tabsCaption ,array("id" => "cv", "caption" => "Életrajz", "glyphicon" => "calendar"));
array_push($tabsCaption ,array("id" => "hobbys", "caption" => "Szabadidőmben", "glyphicon" => "dashboard"));
if ($diak["isTeacher"]==0) {
    array_push($tabsCaption ,array("id" => "school", "caption" => "Diákkoromból", "glyphicon" => "education"));
} else {
    array_push($tabsCaption ,array("id" => "classes", "caption" => "Tanítványok", "glyphicon" => "education"));
}
array_push($tabsCaption ,array("id" => "geoplace", "caption" => "Térkép", "glyphicon" => "globe"));
if(userIsLoggedOn() || userIsAdmin()) {
    if (getLoggedInUserId()==$diak["id"] || userIsAdmin())
        array_push($tabsCaption ,array("id" => "user", "caption" => "Bejelentkezési&nbsp;adatok", "glyphicon" => "envelope"));
    array_push($tabsCaption ,array("id" => "info", "caption" => "Infók", "glyphicon" => "info-sign"));
}

$tabUrl="editDiak.php";
?>
<?php if (null!=getAktClass()) {?>
    <div class="container-fluid"><?php
    include 'lpfw/view/tabs.inc.php';?>
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
            include("rip.inc.php");
            $personList=array();
            $personList[0]=$diak;
            \maierlabs\lpfw\Appl::addJs('js/candles.js',true);
            displayRipPerson(new dbDaCandle($db),$diak);
        }
		//Pictures
		if ($tabOpen=="pictures") {
			$type="personID";
			$typeId=getRealId($diak);
			include("picture.inc.php");
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
			window.location.href="hometable.php?uid="+id+"&action=delete_diak";
		}
	}
</script>
<?php } else { ?>
<div class="alert alert-info" >
	<b>Osztály névsór</b>
	<br/><br/><br/>
	<p>Osztály névsórának a módosótásához elöbször válassz ki egy osztályt az "Osztályok" menü segítségével!</p>
</div>
<?php
}
Appl::addJs('js/chosen.jquery.js');
Appl::addJsScript('
    $(document).ready(function(){
        $(".chosen").chosen();
    });
');
include 'homefooter.inc.php';
?>

