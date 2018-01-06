<?php 
include_once("tools/sessionManager.php");
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once("data.php");

$resultDBoperation="";
$tabOpen= getIntParam("tabOpen", 0);

$personid = getParam("uid",null);
if($personid!=null){
	if ($db->getPersonByID($personid)!=null) {
		setAktUserId($personid);	//save actual person in case of tab changes
	} else {
		$person=$db->getPersonByUser($personid);
		if ($person!=null) {
			$personid=$person["id"];
			setAktUserId($person["id"]);
		}
	}
}
else {
	$personid=getAktUserId();	
}

//Parameters
$action=getGetParam("action","");
$anonymousEditor=getParam("anonymousEditor")=="true";

//Edit or only view variant this page
$edit = (userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() || $anonymousEditor || $action=="changediak");

//Create new person 
$createNewPerson = $action=="newperson" || $action=="newguest" || $action=="newteacher" || $action=="savenewperson" || $action=="savenewguest" || $action=="savenewteacher";
if ( $createNewPerson ) {
	$diak = getPersonDummy();
	$diak["id"] = -1;
	$diak["classID"] = getAktClassId();
	$action=="newteacher" || $action=="savenewteacher" ? $diak["isTeacher"]=1	:	$diak["isTeacher"]=0;
	$action=="newguest"   || $action=="savenewguest" ? $diak["role"]="guest"	:	$diak["role"]="";
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

//preparation of the field to be edited and the itemprop characteristic
$dataFieldNames 	=array("lastname","firstname","email");
$dataFieldCaption 	=array("Családnév","Keresztnév","E-Mail");
$dataItemProp       =array("","","");
$dataCheckFieldVisible	=array(false,false,true);
$dataFieldObl			=array(true,true,true);
if(true)  { //Name
	array_push($dataFieldNames, "birthname","partner","address","zipcode","place","country");
	array_push($dataItemProp,"","","streetAddress","postalCode","addressLocality","addressCountry");
	array_push($dataFieldCaption, "Diákkori név","Élettárs","Cím","Irányítószám","Helység","Ország");
	array_push($dataCheckFieldVisible, false,false,true,true,false,false);
	array_push($dataFieldObl		, false,false,false,false,false,false);
}
if (true) { //Communication
	array_push($dataFieldNames, "phone","mobil","skype","facebook","twitter","homepage","education","employer","function","children");
	array_push($dataItemProp,"","","","","","","","","","","","");
	array_push($dataFieldCaption,"Telefon","Mobil","Skype","Facebook","Twitter","Honoldal","Végzettség","Munkahely","Beosztás","Gyerekek");
	array_push($dataCheckFieldVisible,true ,true ,true ,false,false,true ,true ,false,true ,true );
	array_push($dataFieldObl		, '+40 123 456789','+40 111 123456',false,'https://www.facebook.com/...',false,'http://',false,false,false,false);
}
if (userIsAdmin()) { //only for admin
	array_push($dataFieldNames, "facebookid","role","id", "user", "passw", "geolat", "geolng","changeIP","changeDate","changeUserID","changeForID","classID");
	array_push($dataItemProp,"","","","","","","","","","","","");
	array_push($dataFieldCaption, "FB-ID","Jogok","ID", "Felhasználó", "Jelszó", "X", "Y","IP","Dátum","User","changeForID","OsztályID");
	array_push($dataCheckFieldVisible, false,false,false,false,false,false,false,false,false,false,false,false);
	array_push($dataFieldObl	 	 , false,false,true,true,true,false,false,false,false,false,false,false);
}
if ((isset($classId) && $classId==0) || $action=="savenewteacher" || $action=="newteacher" ) { //Teachers
	$dataFieldCaption[17]="Tantárgy";
	$dataFieldCaption[18]="Osztályfönök";
	$dataFieldObl[18]="Év és osztály például: 1985 12A. Több osztály esetén vesszövel elválasztva. Például: 1985 12A,1989 12C";
}


if ($action=="changediak" || $action=="savenewperson" || $action=="savenewteacher" || $action=="savenewguest") {
	if (checkRequesterIP(changeType::personchange)) {
		if ($diak!=null) {
			$personid=-1;
			for ($i=0;$i<sizeof($dataFieldNames);$i++) {
				$tilde="";
				if ($dataCheckFieldVisible[$i]) {
					if (isset($_GET["cb_".$dataFieldNames[$i]]))
						$tilde="~";
				}
				//save the fields in the person array
				if (isset($_GET[$dataFieldNames[$i]]))
					$diak[$dataFieldNames[$i]]=$tilde.$_GET[$dataFieldNames[$i]];
			}
			//No dublicate email address is allowed
			if (checkUserEmailExists($diak["id"],$diak["email"])) {
				$resultDBoperation='<div class="alert alert-warning">E-Mail cím már létezik az adatbankban!<br/>Az adatok kimentése sikertelen.</div>';
				//Validate the mail address if no admin logged on
			} elseif (isset($diak["email"]) && $diak["email"]!="" && filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false && !userIsAdmin()) {
				$resultDBoperation='<div class="alert alert-warning">E-Mail cím nem helyes! <br/>Az adatok kimentése sikertelen.</div>';
			} elseif (($diak["lastname"]=="" || $diak["firstname"]=="" ) && !userIsAdmin()) {
				$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév üres! <br/>Az adatok kimentése sikertelen.</div>';
			} elseif ((strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) && !userIsAdmin()) {
				$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Az adatok kimentése sikertelen.</div>';
			} else {
				if($diak["id"]!=-1) {
					$oldDiakEntry=$db->getPersonByID($diak["id"]);
				}
				$personid = $db->savePerson($diak);
				setAktUserId($personid);	//save actual person in case of tab changes
				if ($personid>=0) {
					$resultDBoperation='<div class="alert alert-success" >Az adatok sikeresen módósítva!<br />Köszönük szépen a segítséged.</div>';
					$db->saveRequest(changeType::personchange);
					if (!userIsAdmin()) {
						sendHtmlMail(null, "Person is changed id:".$diak["id"].'<br/><br/>Entry<br/><br/>'.json_encode($diak), " Person is changed");
						saveLogInInfo("SaveData",$personid,$diak["user"],"",true);
					}
					
				} else {
					$resultDBoperation='<div class="alert alert-warning" >Az adatok kimentése nem sikerült! Hibakód:1631</div>';
				}
			}
		} else {
			$resultDBoperation='<div class="alert alert-warning" >Az adatok kimentése nem sikerült! Hibakód:1034</div>';
		}
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Az adatok módosítása anonim felhasználok részére korlatozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.</div>';
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
			$ret=$db->savePersonField(getAktUserId(), "passw", $newpwd1);
			if ($ret>=0) {
				if (!userIsAdmin()) 
					saveLogInInfo("SavePassw",$diak["id"],$diak["user"],"",true);
				$resultDBoperation='<div class="alert alert-success"">Jelszó módosíva!</div>';
			} else {
				$resultDBoperation='<div class="alert alert-warning">Jelszó kimentése nem sikerült!</div>';
			}
		}
		else $resultDBoperation='<div class="alert alert-warning">Jelszó ismétlése hibás!</div>';
	}
	else $resultDBoperation='<div class="alert alert-warning">Jelszó rövid, minimum 6 karakter!</div>';
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
				$resultDBoperation='<div class="alert alert-success">Becenév módosíva!</div>';
			} else {
				$resultDBoperation='<div class="alert alert-warning">Becenév módosítása nem sikerült!</div>';
			}
		}
		else
			$resultDBoperation='<div class="alert alert-warning">Becenév már létezik válassz egy másikat!</div>';
	}
	else
		$resultDBoperation='<div class="alert alert-warning"">Becenév rövid, minimum 3 karakter!</div>';
}

//Remove Facebook connection
if ($action=="removefacebookconnection"  && userIsLoggedOn()) {
	unset($diak["facebookid"]);
	$db->savePersonField(getLoggedInUserId(), "facebookid", null);
	saveLogInInfo("FacebookDelete",$personid,$diak["user"],"",true);
}

//Delete Picture
if ($action=="deletePicture" && userIsLoggedOn()) {
	if (deletePicture(getParam("id", ""))>=0) {
		$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
		saveLogInInfo("PictureDelete",getLoggedInUserId(),$diak["user"],getParam("id", ""),true);
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Kép törlés sikertelen!</div>';
	}
}


//Upload Image
if (isset($_POST["action"]) && $_POST["action"]=="upload_diak" ) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = preg_split( "/[.]/", basename( $_FILES['userfile']['name']));
		if (checkRequesterIP(changeType::personupload)) {
			//Only jpg
			if (strcasecmp($fileName[1],"jpg")==0) {
				//Create folder is doesn't exists
				$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".getAktClassFolder();
				if (!file_exists($fileFolder)) {
 	   				mkdir($fileFolder, 0777, true);
				}
				//The max size of e picture 
				if ($_FILES['userfile']['size']<3100000) {
					$idx=rand(234567,999999);
					$pFileName="/d".$personid."-".$idx.".".strtolower($fileName[1]);
					$uploadfile=$fileFolder.$pFileName;
					if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
						$diak['picture']=getAktClassFolder().$pFileName;
						if ($db->savePersonField($personid, "picture", getAktClassFolder().$pFileName)>=0) {
							$db->saveRequest(changeType::personupload);
							resizeImage($uploadfile,400,400);
							$resultDBoperation='<div class="alert alert-success">'.$fileName[1]." sikeresen feltöltve.</div>";
							saveLogInInfo("PictureUpload",$personid,$diak["user"],$idx,true);
						} else {
							$resultDBoperation='<div class="alert alert-warning">'.$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
						}
					} else {
						$resultDBoperation='<div class="alert alert-warning">'.$fileName[1]." feltötése sikertelen. Probálkozz újra. Hibakód:4091</div>";
					}
				}
				else {
					$resultDBoperation='<div class="alert alert-warning">'.$fileName[1]." A kép nagysága túlhaladja 3 MByteot.<br />Probáld a képet kissebb formátumba konvertálni, és töltsd fel újra.</div>";
					saveLogInInfo("PictureUpload",$personid,$diak["user"],"to big",false);			
				} 	
			}
			else {
				$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.<br />Probáld a képet jpg formátumba konvertálni, és töltsd fel újra.</div>";
				saveLogInInfo("PictureUpload",$personid,$diak["user"],"only jpg",false);
			}
		} else {
			$resultDBoperation='<div class="alert alert-warning">'."Sajnáljuk, de tul sok képet probálsz feltölteni!<br/>Az adatok módosítása anonim felhasználok részére korlatozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.</div>";
		}
	}
}

if ($tabOpen==5) 
	$diakEditGeo = true;
if ($tabOpen==2 || $tabOpen==3 || $tabOpen==4)
	$diakEditStorys = true;

if ($personid!=null && $personid>=0)
	$SiteTitle = "A kolozsvári Brassai Sámuel líceum vén diakja " .$diak["lastname"]." ".$diak["firstname"];
else 
	$SiteTitle = "A kolozsvári Brassai Sámuel líceum vén diakjai";
	
include("homemenu.php"); 
?>

<?php if (strstr($action,"new")=="" ){?>
	<div itemscope itemtype="http://schema.org/Person">
	<h2 class="sub_title" style="text-align: left;margin-left:20px">
	<img src="images/<?php echo $diak["picture"] ?>" class="diak_image_icon" />
	<span itemprop="name"><?php  echo $diak["lastname"] ?>  <?php echo $diak["firstname"] ?></span>
	<?php if (showField($diak,"birthname")) echo('('.$diak["birthname"].')');?>
	</h2>
	</div>
<?php } else { ?>
	<div style="margin-bottom: 15px">&nbsp;</div>
<?php }

//initialize tabs
if (getAktClassId()==0 && !userIsAdmin()) {
	$tabsCaption=Array("Személyes&nbsp;adatok","Képek","Életrajz");
} else if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() ) { 
	$tabsCaption=Array("Személyes&nbsp;adatok","Képek","Életrajzom","Diákkoromból","Szabadidőmben","Geokoordináta","Bejelentkezési&nbsp;adatok");
} else {
	$tabsCaption=Array("Személyes&nbsp;adatok","Képek","Életrajzom","Diákkoromból","Szabadidőmben");
}
if ($action=="newperson") { 
	$tabsCaption=Array("Új diák adatai");
} else if ($action=="newguest") {
	$tabsCaption=Array("Új barát vagy vendég adatai");
} else if ($action=="newteacher") {
	$tabsCaption=Array("Új tanárnő vagy tanár adatai");
}
$tabUrl="editDiak.php";
?>

<div class="container-fluid">
	<?php  include("tabs.php"); ?>
	<div class="well">

		<?php
		//Personal Data
		if ($tabOpen==0) {
			include("editDiakPersonData.php");
		}
		//Pictures
		if ($tabOpen==1) { 
			$type="personID";
			$typeId=$personid;
			include("pictureinc.php");
		}
		//Change storys cv, scool trory, sparetime
		if ($tabOpen==2 || $tabOpen==3 || $tabOpen==4) {
			include("editDiakStorys.php");
		}
		//Change geo place
		if ($tabOpen==5) { 
			include("editDiakPickGeoPlace.php");
		}
		//Change password, usename, facebook
		if ($tabOpen==6) {
			include("editDiakUserPassword.php");
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

<?php include 'homefooter.php'; ?>

