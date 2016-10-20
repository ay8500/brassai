<?PHP 

include_once("sessionManager.php");
include_once ('userManager.php');
include_once 'ltools.php';
//********* Edit person ****************

 

if (isset($_GET["tabOpen"])) $tabOpen=$_GET["tabOpen"]; 
else if (isset($_POST["tabOpen"])) $tabOpen=$_POST["tabOpen"]; 
else $tabOpen=0;

//focus the person and get his data from the database
include_once("data.php");
//if user id is delivered over pos or get parameter
if (isset($_GET["uid"]) || isset($_POST["uid"])) {
	if (isset($_GET["uid"])) $personid = $_GET["uid"];
	if (isset($_POST["uid"])) $personid = $_POST["uid"];
	setAktUserId($personid);	//save actual person in case of tab changes
}
else {
	//tabs are changed
	if (isset($_GET["tabOpen"]) || isset($_POST["tabOpen"])) {
		$personid=getAktUserId();	
	}
	else {
		$personid=getAktUserId();
	}
}
$diak = $db->getPersonByID($personid);
$classId=$diak["classID"];
$class=$db->getClassById($classId);
setAktClass($class["id"]);

$resultDBoperation="";



//Change password
if (getParam("action","")=="changepassw" && userIsLoggedOn()) {
	
	if (isset($_GET["newpwd1"])) $newpwd1=$_GET["newpwd1"]; else $newpwd1="";
	if (isset($_GET["newpwd2"])) $newpwd2=$_GET["newpwd2"]; else $newpwd2="";
	if (strlen($newpwd1)>5) {
		if ($newpwd1==$newpwd2) {
			$diak['passw']=$newpwd1;
			savePerson($diak);
			if (!userIsAdmin()) 
				saveLogInInfo("SavePassw",$uid,$diak["user"],"",true);
			$resultDBoperation='<div class="alert alert-success"">Jelszó módosíva!</div>';
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
			$diak["user"]=$user;
			$_SESSION["USER"]=$user;
			savePerson($diak);
			if (!userIsAdmin()) 
				saveLogInInfo("SaveUsername",$personid,$diak["user"],"",true);
			$resultDBoperation='<div class="alert alert-success">Becenév módosíva!</div>';
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
	saveLogInInfo("FacebookDelete",$personid,$diak["user"],"",true);
	savePerson($diak);
}

//Delete Picture
if (getParam("action","")=="deletePicture" && userIsLoggedOn()) {
	deletePicture(getAktDatabaseName(), $personid,getParam("id", ""));
	$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
	saveLogInInfo("PictureDelete",$uid,$diak["user"],getParam("id", ""),true);
}


//Upload Image
if (isset($_POST["action"]) && ($_POST["action"]=="upload" || $_POST["action"]=="upload_diak") ) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = explode( ".", basename( $_FILES['userfile']['name']));
		$idx=getNextPictureId(getAktDatabaseName(),$personid,"", "", true);
		if ($_POST["action"]=="upload_diak") {
			$pFileName=getAktDatabaseName()."/d".$personid."-".$idx.".".strtolower($fileName[1]);
			$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/"."images/".$pFileName;
			$diak['picture']=$pFileName;
			savePerson($diak);
		} else {
			$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".getAktDatabaseName()."/p".$personid."-".$idx.".".strtolower($fileName[1]);
			setPictureAttributes(getAktDatabaseName(),$personid,$idx,"","","false");
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
	}
}

if ($tabOpen==5) 
	$diakEditGeo = true;
if ($tabOpen==2 || $tabOpen==3 || $tabOpen==4)
	$diakEditStorys = true;

$SiteTitle = "A kolozsvári Brassai Sámuel líceum vén diakja " .$diak["lastname"]." ".$diak["firstname"];


include("homemenu.php"); 

//Set person geo and data to be used in diakEditGeo.js
if ($tabOpen==5) {

}


if (strstr(getGetParam("action", ""),"new")=="" ){?>
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
if ($classId==0)
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
	function deleteDiak(db,id) {
		if (confirm("Személy végleges törölését kérem konfirmálni!")) {
			window.location.href="hometable.php?uid="+id+"&db="+db+"&action=delete_diak";
		}
	}
</script>

<?php include 'homefooter.php'; ?>

