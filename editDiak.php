<?PHP 

include_once("sessionManager.php");
//********* Edit person ****************

 
//Change scool year and class if parameters are there 
if (isset($_GET['scoolYear'])) {
	$_SESSION['scoolYear']=$_GET['scoolYear'];
} 
if (isset($_GET['scoolClass']))  {
	$_SESSION['scoolClass']=$_GET['scoolClass'];	
}

if (isset($_GET["tabOpen"])) $tabOpen=$_GET["tabOpen"]; 
else if (isset($_POST["tabOpen"])) $tabOpen=$_POST["tabOpen"]; 
else $tabOpen=0;

//focus the person and get his data from the database
include_once("data.php");
$uid = 0;
if (isset($_GET["uid"]) || isset($_POST["uid"])) {
	if (isset($_GET["uid"])) $uid = $_GET["uid"];
	if (isset($_POST["uid"])) $uid = $_POST["uid"];
	$_SESSION['AktUID']=$uid;	//save actual person in case of tab changes 
}
else {
	if (isset($_GET["tabOpen"]) || isset($_POST["tabOpen"])) {
		$uid=$_SESSION['AktUID'];	//tabs are changed
	}
	else {
		if ( isset($_SESSION['UID']) && $_SESSION['UID']>0) { 
			$uid=$_SESSION['UID'];$_SESSION['AktUID']=$uid;
		}
	}
}
$diak = getPerson($uid);


$dataFieldNames 	=array("lastname","firstname","birthname","partner","address","zipcode","place","country","phone","mobil","email","skype","facebook","homepage","education","employer","function","children","facebookid");
$dataFieldCaption 	=array("Vezetéknév","Keresztnév","Diákkori név","Élettárs","Cím","Irányítószám","Helység","Ország","Telefon","Mobil","E-Mail","Skype","Facebook","Honoldal","Végzettség","Munkahely","Beosztás","Gyerekek","FacebookID");
$dataFieldLengths 	=array(40,40,40,40,	70,6,50,50,30,30,50,20,60,60,60,60,60,60,20,30);
$dataFieldVisible	=array(false,false,false,false,true,true,true,true,true,true,true,true,true,true,false,true,true, false,false);
	
$resultDBoperation="";

//Retrive changed data and save it 
if (($uid != 0) && isset($_GET["action"]) && ($_GET["action"]=="changediak") && isset($_SESSION['UID']) && $_SESSION['UID']>0 ) {
	$resultDBoperation='<div class="okay">Adatok sikeresen módósítva!</div>';
	for ($i=0;$i<sizeof($dataFieldNames);$i++) {
		$tilde="";
		if ($dataFieldVisible[$i]) {
			if (isset($_GET["cb_".$dataFieldNames[$i]])) $tilde="~";
		}
		//save the fields in the person array
		$diak[$dataFieldNames[$i]]=$tilde.$_GET[$dataFieldNames[$i]];
	}
	setPerson($uid,$diak);
	if (!userIsAdmin()) 
		saveLogInInfo("SaveData",$uid,$diak["user"],"",true);
}
//Save geo data
if (($uid != 0) && isset($_GET["action"]) && ($_GET["action"]=="changegeo")) {
	
	if (isset($_GET["geolat"])) $diak["geolat"]=$_GET["geolat"];
	if (isset($_GET["geolng"])) $diak["geolng"]=$_GET["geolng"];
	
	setPerson($uid,$diak);
	if (!userIsAdmin()) 
		saveLogInInfo("SaveGeo",$uid,$diak["user"],"",true);
	$resultDBoperation='<div class="okay">Geokoordináták sikeresen módósítva!</div>';
}


//Change password
if (($uid != 0) && isset($_GET["action"]) && ($_GET["action"]=="changepassw")) {
	
	if (isset($_GET["newpwd1"])) $newpwd1=$_GET["newpwd1"]; else $newpwd1="";
	if (isset($_GET["newpwd2"])) $newpwd2=$_GET["newpwd2"]; else $newpwd2="";
	if (strlen($newpwd1)>5) {
		if ($newpwd1==$newpwd2) {
			$diak['passw']=$newpwd1;
			setPerson($uid,$diak);
			if (!userIsAdmin()) 
				saveLogInInfo("SavePassw",$uid,$diak["user"],"",true);
			$resultDBoperation='<div class="okay">Jelszó módosíva!</div>';
		}
		else $resultDBoperation='<div class="error">Jelszó ismétlése hibás!</div>';
	}
	else $resultDBoperation='<div class="error">Jelszó rövid, minimum 6 karakter!</div>';
}

//Change user name
if (($uid != 0) && isset($_GET["action"]) && ($_GET["action"]=="changeuser")) {
	if (isset($_GET["user"]))  $user=$_GET["user"]; else $user="";
	if (strlen( $user)>2) { 
		if (!checkUserNameExists($uid,$user)) { 
			$diak["user"]=$user;
			$_SESSION["USER"]=$user;
			setPerson($uid,$diak);
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

//Upload Image
if (($uid != 0) && isset($_POST["action"]) && ($_POST["action"]=="upload")) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = explode( ".", basename( $_FILES['userfile']['name']));
		$idx=savePicture(getScoolClass().getScoolYear(),$uid,$_POST["title"], $_POST["content"], true);
		$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".getScoolClass().getScoolYear()."/p".$uid."-".$idx.".".$fileName[1];
		//JPG
		if (strcasecmp($fileName[1],"jpg")==0) {
			if ($_FILES['userfile']['size']<2000000) {
				if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
					resizeImage($uploadfile,1024,768);
					$resultDBoperation=$fileName[0].".".$fileName[1]." sikeresen feltöltve.";
				} else
					$resultDBoperation=$fileName[0].".".$fileName[1]." feltpötése sikertelen";
			}
			else {
				$resultDBoperation=$fileName[0].".".$fileName[1]."A kép file nagysága túlhaladja 2 MByteot.";
			} 	
		}
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
 <head>
   <title><?PHP echo($diak["lastname"]." ".$diak["firstname"]." ".$diak["birthname"]." ");?> A kolozsvari Brassai Sámuel líceum vén diakja</title>
   <meta http-equiv="content-type" content="text/html; charset=UTF8">
   <link rel="stylesheet" type="text/css" href="menu.css" />
   <link rel="stylesheet" href="lightbox.css" type="text/css" media="screen" />
   
   <meta name="robots" content="index,follow" />
   <meta name="geo.placename" content="<?PHP echo(getFieldValue($diak["place"]));?>" />
   <meta name="geo.position" content="<?PHP echo($diak["geolat"].";".$diak["geolng"]);?>" />
   <meta name="author" content="Levente Maier" />
   <meta name="description" content="<?PHP echo($diak["lastname"]." ".$diak["firstname"]." ".$diak["birthname"]." ");?> A kolozsvari Brassai Samuel líceum vén diakjai" />
   <meta name="keywords" content="<?PHP echo($diak["lastname"]." ".$diak["firstname"]." ".$diak["birthname"]." ");?>Brassai Sámuel iskola líceum Kolozsvár Cluj Klausenburg diák diákok <?PHP echo(getFieldValue($diak["place"]));?>" />

	<script type="text/javascript" src="http://www.blue-l.de/stat/track.php?mode=js"></script>
	<noscript><img src="http://www.blue-l.de/stat/track_noscript.php" border="0" alt="" width="1" height="1"></noscript>

	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-20252557-2']);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>

   
	<script type="text/javascript" src="js/prototype.js"></script>
	<script type="text/javascript" src="js/scriptaculous.js?load=effects,builder"></script>
	<script type="text/javascript" src="js/lightbox.js"></script>
   
   	<link rel="stylesheet" type="text/css" href="ddsmoothmenu.css" />
	<link rel="stylesheet" type="text/css" href="ddsmoothmenu-v.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript" src="js/ddsmoothmenu.js"></script>
	<script type="text/javascript">
	  ddsmoothmenu.init({	mainmenuid: 'smoothmenu', orientation: 'v', classname: 'ddsmoothmenu-v', contentsource: "markup" })
	</script>
   
   
   <?PHP if ($tabOpen==2) { ?>
      
   <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAAt_D9PjCp6KIewCC6DftsBTV4tYwmYR0tDWyEKlffNzbwkWE4hTrbEDIZOQBwqdYefOLpNQ7swehXg"  type="text/javascript"></script>
   <script type="text/javascript">
    var map = null;
    var geocoder = null;
    var marker = null;
    
    function initialize() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map_canvas"));
		map.enableScrollWheelZoom();
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
		<?PHP
			if ($diak["geolat"]!="") 
				echo('var center = new GLatLng('.$diak["geolat"].','.$diak["geolng"].');');
			else
				echo('var center = new GLatLng(46.771919,23.592248);');
		?>
        map.setCenter(center, 10);

        marker = new GMarker(center, {draggable: true});
		document.geo.geolat.value=center.lat();
		document.geo.geolng.value=center.lng();

        GEvent.addListener(marker, "dragstart", function() {
          map.closeInfoWindow();
        });

        GEvent.addListener(marker, "dragend", function() {
          marker.openInfoWindowHtml("<?PHP echo('<b>'.$diak["birthname"].' '.$diak["firstname"].'</b> '.$diak["lastname"].' <br/>'.getFieldValue($diak["address"]).'<br />'.getFieldValue($diak["zipcode"]).'&nbsp;'.getFieldValue($diak["place"]));?>");
		  var position = marker.getLatLng();
		  document.geo.geolat.value=position.lat();
		  document.geo.geolng.value=position.lng();
		  fieldChanged(); 
        });

        map.addOverlay(marker);
        
        geocoder = new GClientGeocoder();
       }
    }
    
    function doSearch() {
      var addres = document.getElementById("addres").value;
      if (geocoder) {
        geocoder.getLatLng(
          addres,
          function(point) {
            if (!point) {
              alert(addres + " Nem találtam semmit.");
            } else {
              map.setCenter(point, 13);
              marker.setLatLng(point);
			  document.geo.geolat.value=point.lat();
			  document.geo.geolng.value=point.lng();
            }
          }
        );
      }
    }
       
    </script>
    <?PHP } ?>
 </head>
<body  <?PHP if ($tabOpen==2) { ?>onload="initialize()" onunload="GUnload()" <?PHP } ?> >

<?PHP 
//******************************Save Data**************************************
$googleMap = true;
include("homemenu.php"); 
include_once("userManager.php"); 



?>
<div itemscope itemtype="http://schema.org/Person">
<h2 class="sub_title" >
	
	<?PHP 
		echo('<span itemprop="name">'.$diak["lastname"].' '.$diak["firstname"].'</span> ');
		if ($diak["birthname"]!="") echo('('.$diak["birthname"].')');
	?>
	
</h2>

<?PHP
global $SCRIPT_NAME;

//initialize tabs
if ( isset($_SESSION['UID']) && $_SESSION['UID']>0) 
	$tabsCaption=Array("Semélyes&nbsp;adatok","Képek","Geokoordináta","Bejelentkezési&nbsp;adatok","Beállítások");
else
	$tabsCaption=Array("Semélyes&nbsp;adatok","Képek");
if (isset($_SESSION["FacebookId"]))
	array_push($tabsCaption,"Facebook");
include("tabs.php");
?>

<?PHP if ($tabOpen==0) { 
	$diak = getPerson($uid);
	//Edit variant of this page
	echo('<table class="editpagetable" >');
	if ( isset($_SESSION['UID']) && $_SESSION['UID']>0) {
		//person data fields
		echo('<tr><td colspan="3" style="text-align:center">'.$resultDBoperation.'</td></tr>');
		echo('<tr><td></td><td class="highlight"></td><td class="highlight">Ha azt szeretnéd, hogy az adataidat csak mi az osztálytársak láthassuk, akkor jelöld meg öket!</td></tr>');
		echo('<form action="'.$SCRIPT_NAME.'" method="get">');
		echo('');
		$fieldCountToBeEdited = sizeof($dataFieldNames);
		if (!userIsAdmin()) $fieldCountToBeEdited--;
		for ($i=0;$i<$fieldCountToBeEdited;$i++) {
			echo('<tr><td class="caption1">'.$dataFieldCaption[$i].'</td>'."\r\n");
			if ($dataFieldVisible[$i]) 
				echo('<td class="highlight"><input type="checkbox" name="cb_'.$dataFieldNames[$i].'" '.getFieldChecked($diak[$dataFieldNames[$i]]).' title="Jelöld meg és akkor csak a bejelentkezett osztálytársak lássák!" /></td>');
			else 
				echo('<td class="highlight">&nbsp;</td>');
			echo('<td><input type="text" value="'.getFieldValue($diak[$dataFieldNames[$i]]).'" name="'.$dataFieldNames[$i].'" size="'.$dataFieldLengths[$i].'" class="input2" onchange="fieldChanged();" /></td></tr>'."\r\n");
		}
		echo('<tr><td colspan="3"> </td></tr>');
		echo('<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="submit2" value="Kiment!" title="Adatok kimentése" /></td></tr>');
		echo('<input type="hidden" value="changediak" name="action" />');
		echo('<input type="hidden" value="'.$uid.'" name="uid" />');
		echo('<input type="hidden" value="'.$tabOpen.'" name="tabOpen" />');
		echo('</form>');
	
	}
	//Show read only data
	else {
		$d=$diak;
		echo "<table border=\"1\" ><tr><td width=150>\r\n" ;
		echo "<img src=\"images/".$d["picture"]."\" border=\"0\" alt=\"\" itemprop=\"image\" />";
		echo "</td><td valign=top>";
		echo "<table>\r\n";
		echo "<tr><td valign=top align=right>Élettárs:</td><td>".$d["partner"]."</td></tr>";
		echo "<tr><td valign=top align=right>Végzettség:</td><td>".$d["education"]."</td></tr>";
		if(showField($d["employer"])) 
			echo "<tr><td valign=top align=right>Munkahely:</td><td><div itemprop=\"worksFor\" itemscope itemtype=\"http://schema.org/Organization\"><span itemprop=\"Name\">".getFieldValue($d["employer"])."</span></div></td></tr>";
		if(showField($d["function"])) 
			echo "<tr><td valign=top align=right>Beosztás:</td><td><span itemprop=\"jobTitle\">".getFieldValue($d["function"])."</span></td></tr>";
		echo "<tr><td valign=top align=right>Gyerekek:</td><td>".$d["children"]."</td></tr>";
		if(showField($d["address"])||showField($d["place"])||showField($d["zipcode"])) { 
			echo ("<tr><td valign=top align=right>Cím:</td><td><div itemprop=\"address\" itemscope itemtype=\"http://schema.org/PostalAddress\">");
			if(showField($d["address"])) echo('<span itemprop="streetAddress">'.getFieldValue($d["address"])."</span>, ");
			if(showField($d["zipcode"])) echo('<span itemprop="postalCode">'.getFieldValue($d["zipcode"])."</span> ");
			if(showField($d["place"]))   echo('<span itemprop="addressLocality">'.getFieldValue($d["place"]).'</span>');
			echo("</div></td></tr>");
		}
		if(showField($d["country"])) echo "<tr><td valign=top align=right>Ország:</td><td>".getFieldValue($d["country"])."</td></tr>";
		if(showField($d["phone"])) echo "<tr><td valign=top align=right>Telefon:</td><td>".getFieldValue($d["phone"])."</td></tr>";
		if(showField($d["mobil"])) echo "<tr><td valign=top align=right>Mobil:</td><td>".getFieldValue($d["mobil"])."</td></tr>";
		if(showField($d["email"])) echo "<tr><td valign=top align=right>E-Mail:</td><td><a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a></td></tr>";
		if(showField($d["skype"])) echo "<tr><td valign=top align=right>Skype:</td><td>".getFieldValue($d["skype"])."</td></tr>";
		if(showField($d["facebook"])) echo "<tr><td valign=top align=right>Facebook:</td><td>".getFieldValue($d["facebook"])."</td></tr>";
		if(showField($d["homepage"])) echo "<tr><td valign=top align=right>Honoldal:</td><td>".getFieldValue($d["honoldal"])."</td></tr>";
		echo "</table>";
	}
	echo('</table>');
}

//************************Change Password

if ($tabOpen==3) { 
	echo('<table style="width:500px" class="editpagetable">');
	if ( isset($_SESSION['UID']) && $_SESSION['UID']>0) {
		//change password
		echo('<tr><td colspan="3" style="text-align:center">'.$resultDBoperation.'</td></tr>');
		echo('<form action="'.$SCRIPT_NAME.'" method="get">');
		echo('<tr><td colspan="3"><p style="text-align:left" ><b>Becenév módosítása</b><br/> A becenév minimum 3 karakter hosszú kell legyen. </p></td></tr>');
		echo('<tr><td class="caption1">Becenév</td><td>&nbsp;</td><td><input type="text" class="input2" name="user" value="'.$diak["user"].'" /></td></tr>');
		echo('<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="submit2" value="Új becenév!" title="Új becenév kimentése" /></td></tr>');
		echo('<input type="hidden" value="changeuser" name="action" />');
		echo('<input type="hidden" value="'.$uid.'" name="uid" />');
		echo('<input type="hidden" value="'.$tabOpen.'" name="tabOpen" />');
		echo('</form>');
		echo('<tr><td colspan="3"><hr/> </td></tr>');

		echo('<form action="'.$SCRIPT_NAME.'" method="get">');
		echo('<tr><td colspan="3"><p style="text-align:left" ><b>Jelszó módosítása</b><br/> A jelszó minimum 6 karakter hosszú kell legyen. </p></td></tr>');
		echo('<tr><td class="caption1">Jelszó</td><td>&nbsp;</td><td><input type="password" class="input2" name="newpwd1" value="" />&nbsp;jelszó ismétlése:&nbsp;<input type="password" class="input2" name="newpwd2" value="" /></td></tr>');
		echo('<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="submit2" value="Új jelszó!" title="Új jelszó kimentése" /></td></tr>');
		echo('<input type="hidden" value="changepassw" name="action" />');
		echo('<input type="hidden" value="'.$uid.'" name="uid" />');
		echo('<input type="hidden" value="'.$tabOpen.'" name="tabOpen" />');
		echo('</form>');
	}
	echo('</table>');
}

//************** pictures
if ($tabOpen==1) { 
	include("editDiakPictures.php");
}

//*************** change geo place
if ($tabOpen==2) { 
	include("editDiakPickGeoPlace.php");
}
//************** change options
if ($tabOpen==4) { 
	include("editDiakOptions.php");
}
//************** facebook
if ($tabOpen==5) { 
	?>
	<p></p>
	Jelenleg Facebook kapcsolat létezik a "<?php echo $_SESSION["FacebookName"] ?>" felhasználóval.<br />
	Kép: <img src="https://graph.facebook.com/<?php echo $_SESSION['FacebookId']; ?>/picture" /> 
	<br />
	Hamarosan itt lehetséges lessz a Facebook kapcsolatot megszakítani. 
	<?php 
}
echo('</div>');

?>
</td></tr></table>
</td></tr></table>
</body>
</html>
