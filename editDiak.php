<?PHP 

include_once("tools/sessionManager.php");
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once("data.php");

if (isset($_GET["tabOpen"])) $tabOpen=$_GET["tabOpen"]; 
else if (isset($_POST["tabOpen"])) $tabOpen=$_POST["tabOpen"]; 
else $tabOpen=0;

//focus the person and get his data from the database
//if user id is delivered over pos or get parameter
if (isset($_GET["uid"]) || isset($_POST["uid"])) {
	if (isset($_GET["uid"])) $personid = $_GET["uid"];
	if (isset($_POST["uid"])) $personid = $_POST["uid"];
	setAktUserId($personid);	//save actual person in case of tab changes
}
else {
	$personid=getAktUserId();	
}
if ($personid!=null && $personid>0) {
	$diak = $db->getPersonByID($personid);
	if ($diak!=null) {
		$classId=$diak["classID"];
		$class=$db->getClassById($classId);
		setAktClass($classId);
	}
}

$resultDBoperation="";

//Change password
if (getParam("action","")=="changepassw" && userIsLoggedOn()) {
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
if (getParam("action","")=="changeuser" && userIsLoggedOn()) {
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
if (getParam("action","")=="removefacebookconnection"  && userIsLoggedOn()) {
	$diak["facebookid"]="";
	$db->savePersonField($diak["id"], getLoggedInUserId(), "facebookid", "");
	saveLogInInfo("FacebookDelete",$personid,$diak["user"],"",true);
}

//Delete Picture
if (getParam("action","")=="deletePicture" && userIsLoggedOn()) {
	if (deletePicture(getParam("id", "")>=0)) {
		$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
		saveLogInInfo("PictureDelete",$uid,$diak["user"],getParam("id", ""),true);
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Kép törlés sikertelen!</div>';
	}
}


//Upload Image
if (isset($_POST["action"]) && ($_POST["action"]=="upload" || $_POST["action"]=="upload_diak") ) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = preg_split( "/[.]/", basename( $_FILES['userfile']['name']));
		$idx=$db->getNextPictureId("picture");
		if (checkRequesterIP("upload")) {
			if ($_POST["action"]=="upload_diak") {
				$pFileName=getAktClassFolder()."/d".$personid."-".$idx.".".strtolower($fileName[1]);
				$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/"."images/".$pFileName;
				$diak['picture']=$pFileName;
				$db->savePersonField($personid, "picture", $pFileName);
			} else {
				$uploadfile="./images/".getAktClassFolder()."/p".$personid."-".$idx.".".strtolower($fileName[1]);
				$picture = array();
				$picture["id"]=-1;
				$picture["personID"]=$personid;
				$picture["file"]=$uploadfile;
				$picture["isVisibleForAll"]=1;
				$picture["isDeleted"]=0;
				$picture["uploadDate"]=date("Y-m-d H:i:s");
				$db->savePicture($picture);
			}
			//JPG
			if (strcasecmp($fileName[1],"jpg")==0) {
				if ($_FILES['userfile']['size']<2000000) {
					if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
						resizeImage($uploadfile,1200,1024);
						$resultDBoperation='<div class="alert alert-success">'.$fileName[0].".".$fileName[1]." sikeresen feltöltve.</div>";
						saveLogInInfo("PictureUpload",$personid,$diak["user"],$idx,true);
					} else {
						$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
					}
				}
				else {
					$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." A kép file nagysága túlhaladja 2 MByteot.</div>";
					saveLogInInfo("PictureUpload",$personid,$diak["user"],"to big",false);			
				} 	
			}
			else {
				$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.</div>";
				saveLogInInfo("PictureUpload",$personid,$diak["user"],"only jpg",false);
			}
		} else {
			$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." Sajnáljuk, de tul sok képet probálsz feltölteni!<br/> Kérünk fordulj a rendszergazdához, ha tovább szeretnéd folytatni ezt az akciót.</div>";
		}
	}
}

if ($tabOpen==5) 
	$diakEditGeo = true;
if ($tabOpen==2 || $tabOpen==3 || $tabOpen==4)
	$diakEditStorys = true;

if ($personid!=null)
	$SiteTitle = "A kolozsvári Brassai Sámuel líceum vén diakja " .$diak["lastname"]." ".$diak["firstname"];
else 
	$SiteTitle = "A kolozsvári Brassai Sámuel líceum vén diakjai";
	
include("homemenu.php"); 

if (strstr(getParam("action"),"new")=="" ){?>
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
if (getAktClass()==0 && !userIsAdmin())
	$tabsCaption=Array("Semélyes&nbsp;adatok","Képek","Életrajz");
elseif ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() ) 
	$tabsCaption=Array("Semélyes&nbsp;adatok","Képek","Életrajzom","Diákkoromból","Szabadidőmben","Geokoordináta","Bejelentkezési&nbsp;adatok");
else
	$tabsCaption=Array("Semélyes&nbsp;adatok","Képek","Életrajzom","Diákkoromból","Szabadidőmben");
if (getParam("action","")=="newdiak" || getParam("action","")=="newguest" || getParam("action","")=="submit_newdiak" || getParam("action","")=="submit_newguest" || getParam("action","")=="submit_newdiak_save" || getParam("action","")=="submit_newguest_save")
	$tabsCaption=Array("Új személy adatai");
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
			include("editDiakPictures.php");
			return ;
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

