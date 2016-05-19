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

$dataFieldNames 	=array("lastname","firstname","birthname","partner","address","zipcode","place","country","phone","mobil","email","skype","facebook","homepage","education","employer","function","children","facebookid","admin");
$dataFieldCaption 	=array("Családnév","Keresztnév","Diákkori név","Élettárs","Cím","Irányítószám","Helység","Ország","Telefon","Mobil","E-Mail","Skype","Facebook","Honoldal","Végzettség","Munkahely","Beosztás","Gyerekek","FB-ID","Jogok");
$dataFieldLengths 	=array(40,40,40,40,	70,6,50,50,30,30,50,20,60,60,60,60,60,60,20,30,60,40,40,40,40,40,40);
$dataFieldVisible	=array(false,false,false,false,true,true,true,true,true,true,true,true,true,true,false,true,true, false,false,false,false,false,false,false,false,false);
if (userIsAdmin()) {
	array_push($dataFieldNames, "id", "user", "passw", "geolat", "geolng");
	array_push($dataFieldCaption, "ID", "Felhasználó", "Jelszó", "X", "Y");
}

$resultDBoperation="";

//create new diak
if (($uid != 0) && (getParam("action","")=="newdiak" || getParam("action","")=="newguest") &&  userIsLoggedOn() ) {
	$diak = getPersonDummy();	
}


//Retrive changed data and save it 
if (($uid != 0) && getParam("action","")=="changediak" &&  userIsLoggedOn() ) {
	$diak = getPerson($uid,getAktDatabaseName());
	for ($i=0;$i<sizeof($dataFieldNames);$i++) {
		$tilde="";
		if ($dataFieldVisible[$i]) {
			if (isset($_GET["cb_".$dataFieldNames[$i]])) $tilde="~";
		}
		//save the fields in the person array
		if (isset($_GET[$dataFieldNames[$i]]))
			$diak[$dataFieldNames[$i]]=$tilde.$_GET[$dataFieldNames[$i]];
	}
	if (checkUserEmailExists($diak["id"],$diak["email"])) {
		$resultDBoperation='<div class="error">E-Mail cím már létezik az adatbankban!</div>';
	} else {
		savePerson($diak);
		$resultDBoperation='<div class="okay">Adatok sikeresen módósítva!</div>';
		if (!userIsAdmin()) 
			saveLogInInfo("SaveData",$uid,$diak["user"],"",true);
	}
}
//Save geo data
if (($uid != 0) && getParam("action","")=="changegeo" && userIsLoggedOn()) {
	
	if (isset($_GET["geolat"])) $diak["geolat"]=$_GET["geolat"];
	if (isset($_GET["geolng"])) $diak["geolng"]=$_GET["geolng"];
	
	savePerson($diak);
	if (!userIsAdmin()) 
		saveLogInInfo("SaveGeo",$uid,$diak["user"],"",true);
	$resultDBoperation='<div class="okay">Geokoordináták sikeresen módósítva!</div>';
}


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
			$resultDBoperation='<div class="okay">Jelszó módosíva!</div>';
		}
		else $resultDBoperation='<div class="error">Jelszó ismétlése hibás!</div>';
	}
	else $resultDBoperation='<div class="error">Jelszó rövid, minimum 6 karakter!</div>';
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
			$resultDBoperation='<div class="okay">Becenév módosíva!</div>';
		}
		else
			$resultDBoperation='<div class="error">Becenév már létezik válassz egy másikat!</div>';
	}
	else
		$resultDBoperation='<div class="error">Becenév rövid, minimum 3 karakter!</div>';
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
					$resultDBoperation=$fileName[0].".".$fileName[1]." sikeresen feltöltve.";
					saveLogInInfo("PictureUpload",$uid,$diak["user"],$idx,true);
				} else {
					$resultDBoperation=$fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.";
				}
			}
			else {
				$resultDBoperation=$fileName[0].".".$fileName[1]." A kép file nagysága túlhaladja 2 MByteot.";
				saveLogInInfo("PictureUpload",$uid,$diak["user"],"to big",false);			
			} 	
		}
		else {
			$resultDBoperation=$fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.";
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
include_once("userManager.php"); 

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


?>
<div itemscope itemtype="http://schema.org/Person">
<h2 class="sub_title" style="text-align: left;margin-left:20px">
		<img src="images/<?php echo $diak["picture"] ?>" style="height:30px; border-radius:3px;" />
			<span itemprop="name"><?php  echo $diak["lastname"] ?>  <?php echo $diak["firstname"] ?></span>
			<?php if (showField($diak,"birthname")) echo('('.$diak["birthname"].')');?>
</h2>

<?php

//initialize tabs
if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() ) 
	$tabsCaption=Array("Semélyes&nbsp;adatok","Képek","Életrajzom","Diákkoromból","Szabadidőmben","Geokoordináta","Bejelentkezési&nbsp;adatok");
else
	$tabsCaption=Array("Semélyes&nbsp;adatok","Képek","Életrajzom","Diákkoromból","Szabadidőmben");
if (getParam("action","")=="newdiak" || getParam("action","")=="newguest")
	$tabsCaption=Array("Semélyes&nbsp;adatok");
include("tabs.php");
?>


<?PHP if ($tabOpen==0) { 
	//Edit or only view variant this page
	$edit = (userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser());
	//person data fields
	?>
	<div class="container-fluid">
		<div class="well">
		<div class="diak_picture" style="display: inline-block;">
			<img src="images/<?php echo($diak["picture"]);?>" border="0" alt="" itemprop="image" class="diak_image" />
		</div>
		<?php if ($edit && getParam("action","")!="newdiak" && getParam("action","")!="newguest") {   //Change Profile Image?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<form enctype="multipart/form-data" action="editDiak.php" method="post">
				<span>Válassz egy új képet max. 2MByte</span>
				<input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" />	
				<button style="margin-top:5px;" type="submit" class="btn btn-default" title="Feltölti a kivásztott képet" ><span class="glyphicon glyphicon-save"></span> Feltölt</button>
				<input type="hidden" value="upload_diak" name="action" />
				<input type="hidden" value="<?PHP echo($uid) ?>" name="uid" />
				<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
			</form>
		</div>
		<?php if (getLoggedInUserId()<>$diak["id"]) {  //Don't delete myself?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;"">
			<button onclick="deleteDiak(<?php echo("'".getAktDatabaseName()."','".$diak["id"]."'");?>);" class="btn btn-default"><span class="glyphicon glyphicon glyphicon-remove-circle"></span> Diákot véglegesen kitöröl!</button>
		</div>
		<?php } }?>
	</div>
	<?php 
	echo('<div style="text-align:center">'.$resultDBoperation.'</div>');
	if ($edit) {
		echo('<div style="min-height:30px" class="input-group">');
      	echo('<span style="min-width:110px;" class="input-group-addon" >&nbsp;</span>');
      	echo('<span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>');
		echo('<input type="text" readonly  id="highlight" class="form-control" value="Ha azt szeretnéd, hogy az adataidat csak az osztálytársaid lássák, akkor jelöld meg öket!" />');
   		echo('</div>');	
		echo('<form action="'.$SCRIPT_NAME.'" method="get">');
	}
	$fieldCountToBeEdited = sizeof($dataFieldNames);
	if (!userIsAdmin()) $fieldCountToBeEdited -=2;
	for ($i=0;$i<$fieldCountToBeEdited;$i++) {
		if ($edit || (!$edit && showField($diak,$dataFieldNames[$i]))) {
			echo('<div class="input-group">');
	      	echo('<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">'.$dataFieldCaption[$i].'</span>');
			if ($edit) {
	      		echo('<span style="width:40px" id="highlight" class="input-group-addon">');
	      			if ($dataFieldVisible[$i])
	        			echo('<input type="checkbox" name="cb_'.$dataFieldNames[$i].'" '.getFieldChecked($diak,$dataFieldNames[$i]).' title="A megjelölt mezöket csak az osztálytásaid látják." >');
	      		echo('</span>');
			}
	      	echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'" />');
	    	echo('</div>');	
		}
	}
	if ($edit) {
		echo('<button style="margin-top:5px;margin-bottom:5px;" type="submit" class="btn btn-default" title="Adatok kimentése" ><span class="glyphicon glyphicon-save"></span>'.getTextRes("Save").'</button>');
		echo('<input type="hidden" value="changediak" name="action" />');
		echo('<input type="hidden" value="'.$diak["id"].'" name="uid" />');
		echo('<input type="hidden" value="'.$tabOpen.'" name="tabOpen" />');
		echo('</form>');
	}
	echo('</div>');
/*	
		if(showField($d,"address")||showField($d,"place")||showField($d,"zipcode")) { 
			<div itemprop=\"address\" itemscope itemtype=\"http://schema.org/PostalAddress\">");
			<span itemprop="streetAddress">'.getFieldValue($d["address"])."</span>, ");
			<span itemprop="postalCode">'.getFieldValue($d["zipcode"])."</span> ");
			<span itemprop="addressLocality">'.getFieldValue($d["place"]).'</span>');
*/
}

//Change password, usename, facebook
if ($tabOpen==6) {
	if ( userIsAdmin() || isAktUserTheLoggedInUser()) {
?> 
	<table style="width:90%" class="editpagetable">
		<tr><td colspan="3" style="text-align:center"><b><?php echo $resultDBoperation; ?></b></td></tr>
		<form action="editDiak.php" method="get">
			<tr><td colspan="3"><p style="text-align:left" ><h3>Becenév módosítása</h3> A becenév minimum 6 karakter hosszú kell legyen. </p></td></tr>
			<tr><td class="caption1">Becenév</td><td>&nbsp;</td><td><input type="text" class="input2" name="user" value="<?php  echo $diak["user"] ?>" /></td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="submit2" value="Új becenév!" title="Új becenév kimentése" /></td></tr>
			<input type="hidden" value="changeuser" name="action" />
			<input type="hidden" value="<?php echo $uid; ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>
		<tr><td colspan="3"><hr/> </td></tr>
			<form action="editDiak.php" method="get">
			<tr><td colspan="3"><p style="text-align:left"><h3>Jelszó módosítása</h3> A jelszó minimum 6 karakter hosszú kell legyen. </p></td></tr>
			<tr><td class="caption1">Jelszó</td><td>&nbsp;</td><td><input type="password" class="input2" name="newpwd1" value="" />&nbsp;jelszó ismétlése:&nbsp;<input type="password" class="input2" name="newpwd2" value="" /></td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="submit2" value="Új jelszó!" title="Új jelszó kimentése" /></td></tr>
			<input type="hidden" value="changepassw" name="action" />
			<input type="hidden" value="<?php echo $uid; ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>
		<?php if (isset($_SESSION['FacebookId'])) : ?>		
		<tr><td colspan="3"><hr/> </td></tr>
		<tr><td colspan="3">
			<h3>Facebook</h3>Jelenleg Facebook kapcsolat létezik közötted és a "<?php echo $_SESSION["FacebookName"] ?>" Facebook felhasználóval.<br />
			<div style="border-style: solid; border-width: 1px; width: 250px;" >
				Facebook kép: <img src="https://graph.facebook.com/<?php echo $_SESSION['FacebookId']; ?>/picture" />
			</div> 
			<br />
			<form action="editDiak.php" method="get">
				<input type="hidden" value="removefacebookconnection" name="action" />
				<input type="hidden" value="<?php echo $uid ?>" name="uid" />
				<input type="hidden" value="<?php echo $tabOpen ?>" name="tabOpen" />
				<input type="submit" value="Facebook kapcsolatot töröl" />
			</form>
		</td></tr>
		<?php endif ?>
	</table>
	<?php 
	}	
}

//************** pictures
if ($tabOpen==1) { 
	include("editDiakPictures.php");
	return ;
}

//*************** change geo place
if ($tabOpen==5) { 
	include("editDiakPickGeoPlace.php");
}
//************** change storys cv, scool trory, sparetime
if ($tabOpen==2 || $tabOpen==3 || $tabOpen==4) { 
	include("editDiakStorys.php");
}
?>
</div>

<script type="text/javascript">
	function deleteDiak(db,id) {
		if (confirm("Biztos kiakarod véglegesen törölni ezt a véndiákot?")) {
			window.location.href="hometable.php?uid="+id+"&db="+db+"&action=delete_diak";
		}
	}
</script>

<?php include 'homefooter.php'; ?>

