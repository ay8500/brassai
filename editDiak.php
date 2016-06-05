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
$uid = 0;
//if user id is delivered over pos or get parameter
if (isset($_GET["uid"]) || isset($_POST["uid"])) {
	if (isset($_GET["uid"])) $uid = $_GET["uid"];
	if (isset($_POST["uid"])) $uid = $_POST["uid"];
	setAktUserId($uid);	//save actual person in case of tab changes 
}
else {
	//tabs are changed
	if (isset($_GET["tabOpen"]) || isset($_POST["tabOpen"])) {
		$uid=getAktUserId();	
	}
	else {
		$uid=getAktUserId();
	}
}
$diak = getPerson($uid,getAktDatabaseName());


$resultDBoperation="";



//Change password
if (($uid != 0) && getParam("action","")=="changepassw" && userIsLoggedOn()) {
	
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
if (($uid != 0) && getParam("action","")=="changeuser" && userIsLoggedOn()) {
	if (isset($_GET["user"]))  $user=$_GET["user"]; else $user="";
	if (strlen( $user)>2) { 
		if (!checkUserNameExists($uid,$user)) { 
			$diak["user"]=$user;
			$_SESSION["USER"]=$user;
			savePerson($diak);
			if (!userIsAdmin()) 
				saveLogInInfo("SaveUsername",$uid,$diak["user"],"",true);
			$resultDBoperation='<div class="alert alert-success">Becenév módosíva!</div>';
		}
		else
			$resultDBoperation='<div class="alert alert-warning">Becenév már létezik válassz egy másikat!</div>';
	}
	else
		$resultDBoperation='<div class="alert alert-warning"">Becenév rövid, minimum 3 karakter!</div>';
}

//Remove Facebook connection
if (($uid != 0) && getParam("action","")=="removefacebookconnection"  && userIsLoggedOn()) {
	$diak["facebookid"]="";
	saveLogInInfo("FacebookDelete",$uid,$diak["user"],"",true);
	savePerson($diak);
}

//Delete Picture
if (($uid != 0) && getParam("action","")=="deletePicture" && userIsLoggedOn()) {
	deletePicture(getAktDatabaseName(), $uid,getParam("id", ""));
	$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
	saveLogInInfo("PictureDelete",$uid,$diak["user"],getParam("id", ""),true);
}


//Upload Image
if (($uid != 0) && isset($_POST["action"]) && ($_POST["action"]=="upload" || $_POST["action"]=="upload_diak") ) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = explode( ".", basename( $_FILES['userfile']['name']));
		$idx=getNextPictureId(getAktDatabaseName(),$uid,"", "", true);
		if ($_POST["action"]=="upload_diak") {
			$pFileName=getAktDatabaseName()."/d".$uid."-".$idx.".".strtolower($fileName[1]);
			$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/"."images/".$pFileName;
			$diak['picture']=$pFileName;
			savePerson($diak);
		} else {
			$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".getAktDatabaseName()."/p".$uid."-".$idx.".".strtolower($fileName[1]);
			setPictureAttributes(getAktDatabaseName(),$uid,$idx,"","","false");
		}
		//JPG
		if (strcasecmp($fileName[1],"jpg")==0) {
			if ($_FILES['userfile']['size']<2000000) {
				if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
					resizeImage($uploadfile,1200,1024);
					$resultDBoperation='<div class="alert alert-success">'.$fileName[0].".".$fileName[1]." sikeresen feltöltve.</div>";
					saveLogInInfo("PictureUpload",$uid,$diak["user"],$idx,true);
				} else {
					$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
				}
			}
			else {
				$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." A kép file nagysága túlhaladja 2 MByteot.</div>";
				saveLogInInfo("PictureUpload",$uid,$diak["user"],"to big",false);			
			} 	
		}
		else {
			$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.</div>";
			saveLogInInfo("PictureUpload",$uid,$diak["user"],"only jpg",false);
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
	echo('<script language="JavaScript" type="text/javascript">'."\r\n");
	echo("\t".'var diak="<b>');
	if (showField($diak, "birthname")) echo($diak["birthname"].' ');
	echo($diak["firstname"].'</b> '.$diak["lastname"].' <br/>');
	if (showField($diak, "address")) 	echo( getFieldValue($diak["address"]).'<br />');
	if (showField($diak, "zipcode")) echo( getFieldValue($diak["zipcode"]).'&nbsp;');
	if (showField($diak, "place")) echo( getFieldValue($diak["place"]));
	echo('";'."\r\n");
	if ($diak["geolat"]!="")
		echo("\t".'var centerx = '.$diak["geolat"].'; var centery ='.$diak["geolng"].";"."\r\n");
	else
		echo("\tvar centerx =46.771919; var centery = 23.592248;"."\r\n");
	echo("</script>"."\r\n");

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
if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() ) 
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
		if (confirm("Biztos kiakarod véglegesen törölni ezt a véndiákot?")) {
			window.location.href="hometable.php?uid="+id+"&db="+db+"&action=delete_diak";
		}
	}
</script>

<?php include 'homefooter.php'; ?>

