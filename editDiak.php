<?php 
include_once("tools/sessionManager.php");
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once("dbBL.class.php");

use \maierlabs\lpfw\Appl as Appl;

$tabOpen= getIntParam("tabOpen", 0);

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
$dataFieldNames 	=array("lastname","firstname","email","birthname","deceasedYear");
$dataFieldCaption 	=array("Családnév","Keresztnév","E-Mail","Diákkori név","† elhunyt");
$dataItemProp       =array("","","","","");
$dataCheckFieldVisible	=array(false,false,true,false,false);
$dataFieldObl			=array(true,true,"fontos mező","leánykori családnév","csak az évszámot kell beírni, ha nem tudod pontosan akkor 0-t írj ebbe a mezőbe. Kimentés után beadhatod a sírhelyet.");
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
    $dataFieldObl[16+$offset] = "Évszám mettől meddig pl: 1961-1987";
    $dataFieldCaption[16+$offset] = "Mettől meddig";
    $dataFieldObl[17+$offset] = "Leadott tantárgy, maximum kettő pl: matematika, angol nyelv";
    $dataFieldCaption[17+$offset] = "Tantárgy";
    $dataFieldCaption[18+$offset] = "Osztályfönök";
    $dataFieldObl[18+$offset] = "Év és osztály például: 1985 12A. Több osztály esetén vesszövel elválasztva. Például: 1985 12A,1989 12C";
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
			if (isset($diak["email"]) && checkUserEmailExists($diak["id"],$diak["email"])) {
				Appl::$resultDbOperation='<div class="alert alert-warning">E-Mail cím már létezik az adatbankban!<br/>Az adatok kimentése sikertelen.</div>';
			} elseif ((isset($diak["classID"]) && $diak["classID"]==="-1") || !isset($diak["classID"])) {
				Appl::$resultDbOperation='<div class="alert alert-warning">Osztály nincs kiválasztva!</div>';
			} elseif (checkUserNameExists($diak["id"], $diak["user"])) {
				Appl::$resultDbOperation='<div class="alert alert-warning">Felhasználó név már létezik!<br/>Az adatok kimentése sikertelen.</div>';
				//Validate the mail address if no admin logged on
			} elseif (isset($diak["email"]) && $diak["email"]!="" && filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false && !userIsAdmin()) {
				Appl::$resultDbOperation='<div class="alert alert-warning">E-Mail cím nem helyes! <br/>Az adatok kimentése sikertelen.</div>';
			} elseif (($diak["lastname"]=="" || $diak["firstname"]=="" ) && !userIsAdmin()) {
				Appl::$resultDbOperation='<div class="alert alert-warning">Családnév vagy Keresztnév üres! <br/>Az adatok kimentése sikertelen.</div>';
			} elseif ((strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) && !userIsAdmin()) {
				Appl::$resultDbOperation='<div class="alert alert-warning">Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Az adatok kimentése sikertelen.</div>';
			} else {
				$personid = $db->savePerson($diak);
				if ($personid>=0) {
					setAktUserId($personid);		//set actual person in case of tab changes
					setAktClass($diak["classID"]);	//set actual class in case of class changes
					Appl::$resultDbOperation='<div class="alert alert-success" >Az adatok sikeresen módósítva!<br />Köszönük szépen a segítséged.</div>';
					$db->saveRequest(changeType::personchange);
					header("location:hometable.php?class=".$diak["classID"]."&action=saveok");
				} else {
					Appl::$resultDbOperation='<div class="alert alert-warning" >Az adatok kimentése nem sikerült! Hibakód:1631</div>';
				}
			}
		} else {
			Appl::$resultDbOperation='<div class="alert alert-warning" >Az adatok kimentése nem sikerült! Hibakód:1034</div>';
		}
	} else {
		Appl::$resultDbOperation='<div class="alert alert-warning" >Az adatok módosítása anonim felhasználok részére korlátozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.</div>';
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
					saveLogInInfo("SavePassw",$diak["id"],$diak["user"],"",true);
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
		if (!checkUserNameExists($personid,$user)) { 
			$ret=$db->savePersonField(getAktUserId(),'user', $user);
			if ($ret>=0) {
				$_SESSION["USER"]=$user;
				if (!userIsAdmin()) 
					saveLogInInfo("SaveUsername",$personid,$diak["user"],"",true);
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
        saveLogInInfo("FacebookDelete",$diak["id"],$diak["user"],"",true);
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
								Appl::$resultDbOperation='<div class="alert alert-success">'.$fileName[1]." sikeresen feltöltve.</div>";
								saveLogInInfo("PictureUpload",$personid,$diak["user"],$idx,true);
							} else {
								Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
							}
						} else {
							resizeImage($uploadfile,400,400,"o");
							Appl::$resultDbOperation='<div class="alert alert-success">Kép sikeresen kicserélve</div>';
						}
					} else {
						Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[1]." feltötése sikertelen. Probálkozz újra. Hibakód:4091</div>";
					}
				}
				else {
					Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[1]." A kép nagysága túlhaladja 3 MByteot.<br />Probáld a képet kissebb formátumba konvertálni, és töltsd fel újra.</div>";
					saveLogInInfo("PictureUpload",$personid,$diak["user"],"to big",false);			
				} 	
			}
			else {
				Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.<br />Probáld a képet jpg formátumba konvertálni, és töltsd fel újra.</div>";
				saveLogInInfo("PictureUpload",$personid,$diak["user"],"only jpg",false);
			}
		} else {
			Appl::$resultDbOperation='<div class="alert alert-warning">'."Sajnáljuk, de tul sok képet probálsz feltölteni!<br/>Az adatok módosítása anonim felhasználok részére korlatozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.</div>";
		}
	}
}

if ($tabOpen=="geoplace") {
	Appl::addJs("//maps.googleapis.com/maps/api/js?key=AIzaSyCuHI1e-fFiQz3-LfVSE2rZbHo5q8aqCOY",false,false);
	Appl::addJs("js/diakEditGeo.js");
}
if (in_array($tabOpen,array("cv","hobbys","school"))) {
	Appl::addCss('editor/ui/trumbowyg.min.css');
	Appl::addJs('editor/trumbowyg.min.js');
	Appl::addJs('editor/langs/hu.min.js');
	Appl::addJsScript("
	$( document ).ready(function() {
		$('#story').trumbowyg({
			fullscreenable: false,
			closable: false,
			lang: 'hu',
			btns: ['formatting','btnGrp-design','|', 'link', 'insertImage','btnGrp-lists'],
			removeformatPasted: true,
			autogrow: true
		});
	});
	");
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
	<span itemprop="name"><?php  echo $diak["lastname"] ?>  <?php echo $diak["firstname"] ?></span>
	<?php if (showField($diak,"birthname")) echo('('.$diak["birthname"].')');?>
	</h2>
	</div>
<?php } else { ?>
	<div style="margin-bottom: 15px">&nbsp;</div>
<?php }

//initialize tabs
if ($action=="newperson") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új diák adatai"));
} else if ($action=="newguest") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új barát vagy vendég adatai"));
} else if ($action=="newteacher") {
    $tabsCaption = array(array("id" => "person", "caption" => "Új tanárnő vagy tanár adatai"));
} else {
    $tabsCaption = array(array("id" => "person", "caption" => "Személyes&nbsp;adatok"));
}
if (isset($diak["deceasedYear"])) {
    array_push($tabsCaption ,array("id" => "candles", "caption" => "Gyertyák"));
}
array_push($tabsCaption ,array("id" => "pictures", "caption" => "Képek"));
array_push($tabsCaption ,array("id" => "cv", "caption" => "Életrajz"));
array_push($tabsCaption ,array("id" => "hobbys", "caption" => "Szabadidőmben"));
if ($diak["isTeacher"]==0) {
    array_push($tabsCaption ,array("id" => "school", "caption" => "Diákkoromból"));
}
array_push($tabsCaption ,array("id" => "geoplace", "caption" => "Térkép"));
if(userIsLoggedOn() || userIsAdmin()) {
    if (getLoggedInUserId()==$diak["id"])
        array_push($tabsCaption ,array("id" => "user", "caption" => "Bejelentkezési&nbsp;adatok"));
    array_push($tabsCaption ,array("id" => "info", "caption" => "Infók"));
}

$tabUrl="editDiak.php";
?>
<?php if (null!=getAktClass()) {?>
<div class="container-fluid">
	<?php  include('tabs.inc.php'); ?>
	<div class="well">

		<?php
		//Personal Data
		if ($tabOpen=="person") {
			include("editDiakPersonData.php");
		}
        //Candles
        if ($tabOpen=="candles") {
            include("rip.inc.php");
            $personList=array();
            $personList[0]=$diak;
            \maierlabs\lpfw\Appl::addJs('js/candles.js',true);
            displayRipPerson($db,$diak);
        }
		//Pictures
		if ($tabOpen=="pictures") {
			$type="personID";
			$typeId=getRealId($diak);
			include("picture.inc.php");
		}
		//Change storys cv, scool trory, sparetime
		if (in_array($tabOpen,array("cv","hobbys","school"))) {
			include("editDiakStorys.php");
		}
		//Change geo place
		if ($tabOpen=="geoplace") {
			include("editDiakPickGeoPlace.php");
		}
		//Change password, usename, facebook
		if ($tabOpen=="user") {
			include("editDiakUserPassword.php");
		}
		//Activities
		if ($tabOpen=="info") {
			include("editDiakActivities.php");
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

