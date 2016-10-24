<?php
/**
 * Database engime for person data  
 */

include_once("userManager.php");
include_once 'dbDAO.class.php';

$db = new dbDAO;


$datafields = array("id","firstname","lastname","birthname","partner","address","zipcode","place","country","phone","mobil","email","skype","homepage","education","employer","function","children","picture","geolat","geolng","user","passw","role","date","ip","facebook","facebookid","twitter");
$PictureFields=array("id","visibleforall","date","title","comment","ip","uploadDate","lastIp","lastChangeDate","deleted");


//aktual used scoolyear and class
function getAktClass() {
	global $db;
	if (isset($_SESSION['aktClass'])) 
		return intval($_SESSION['aktClass']);
	else {
		$class =$db->getClassByText("1985 12A");
		if ($class!=null) {
			$_SESSION['aktClass']=$class["id"];
			return $class["id"];
		}
		else die("Default class not found!");
	}
}


function setAktClass($classId) {
	$_SESSION['aktClass']=$classId;
}


/**
 * The name of the aktual class
 */
function getAktClassFolder() {
	global $db;
	$class=$db->getClassById(getAktClass());
	if ($class!=null)
		return $class["name"].$class["graduationYear"];
	else
		return "";
}

/**
 * The name of the aktual class
 */
function getAktClassName() {
	global $db;
	$class=$db->getClassById(getAktClass());
	if ($class!=null)
		if ($class["id"]==0)
			return "";
		else
			return $class["text"];
	else
		return "";
}


/**
 * returns logged in person
 */
function getPersonLogedOn() {
	global $db;
	if ( null!=getLoggedInUserId())  { 
		return $db->getPersonByID(getLoggedInUserId());
	} else {
		return null;
	}
}

/**
 * returns aktual person
 */
function getAktPerson() {
	global $db;
	if ( null!=getAktUserId())  {
		return $db->getPersonByID(getAktUserId());
	} else {
		return null;
	}
}

/**
 * is the person a guest
 */
function isPersonGuest($person) {
	return (isset($person["role"]) && strstr($person["role"],"guest")!="");
}

function isPersonAdmint($person) {
	return (isset($person["role"]) && strstr($person["role"],"admin")!="");
}

function isPersonEditor($person) {
	return (isset($person["role"]) && strstr($person["role"],"editor")!="");
}

/**
 * returns an empty person
 */
function getPersonDummy() {
	$p = array();
	$p["firstname"]="";
	$p["lastname"]="";
	$p["picture"]="avatar.jpg";
	$p["geolat"]="46.7719";
	$p["geolng"]="23.5924";
	$p["user"]=createPassword(8);
	$p["passw"]=createPassword(8);
	$p["role"]="";
	/*
	$p["birthname"]=null;$p["partner"]=null;$p["email"]=null;$p["ip"]=null;
	$p["address"]=null;$p["zipcode"]=null;$p["place"]=null;$p["country"]=null;
	$p["phone"]=null;$p["mobil"]=null;$p["skype"]=null;$p["email"]=null;
	$p["employer"]=null;$p["function"]=null;$p["education"]=null;$p["children"]=null;
	$p["facebookid"]=null;$p["facebook"]=null;$p["homepage"]=null;$p["twitter"]=null;
	*/
	return $p;
}

/**
 * returns an empty person
 */
function createNewPerson($classID,$guest) {
	$p = getPersonDummy();
	$p["classID"]=$classID;
	if($guest)
		$p["role"]="guest";
	return $p;
}



/**
 * Compare classmates
 * "firstname","lastname","birthname"
 * @param unknown $a
 * @param unknown $b
 */
function compareAlphabetical($a,$b) {
	if (strstr($a["lastname"],"Dr. ")!="")
		$a["lastname"]=substr($a["lastname"], 4);
	if (strstr($b["lastname"],"Dr. ")!="")
		$b["lastname"]=substr($b["lastname"], 4);
		if (isset($a["birthname"]) && $a["birthname"]!="") 
		$aa=$a["birthname"]." ".$a["firstname"];
	else
		$aa=$a["lastname"]." ".$a["firstname"];
	if (isset($b["birthname"]) && $b["birthname"]!="")
		$bb=$b["birthname"]." ".$b["firstname"];
	else
		$bb=$b["lastname"]." ".$b["firstname"];
	return strcmp(getNormalisedChars($aa), getNormalisedChars($bb));
}

function compairEmail($d1,$d2) {
	if (!isset($d1["email"]) || !isset($d2["email"]))
		return false;
	return getFieldValue($d1,"email")==getFieldValue($d2,"email");
}

function compairUser($d1,$d2) {
	if (!isset($d1["user"]) || !isset($d2["user"]))
		return false;
	return strtolower($d1["user"])==strtolower($d2["user"]);
}

function compairUserLink($d1,$d2) {
	if (isset($d1["lastname"]) && isset($d2["lastname"]) && isset($d1["firstname"]) && isset($d2["firstname"])) {
		return getPersonLink($d1["lastname"],$d1["firstname"])==getPersonLink($d2["lastname"],$d2["firstname"]);
	}
	else
		return false;
	}

function setPictureVisibleForAll($pictureId, $visibleforall){
	global $db;
	$picture=$db->getPictureById($pictureId);
	$picture["isVisibleForAll"]=$visibleforall?1:0;
	$db->savePicture($picture);
}

function deletePicture($pictureId) {
	global $db;
	$picture=$db->getPictureById($pictureId);
	$picture["isDeleted"]=1;
	return $db->savePicture($picture);
}

/* resize image
 * 
 */
function resizeImage($fileName,$maxWidth,$maxHight)
{ 
	$limitedext = array(".gif",".jpg",".png",".jpeg");		
	
	//check the file's extension
	$ext = strrchr($fileName,'.');
	$ext = strtolower($ext);

	//uh-oh! the file extension is not allowed!
	if (!in_array($ext,$limitedext)) {
		exit();
	}

	if($ext== ".jpeg" || $ext == ".jpg"){
		$new_img = imagecreatefromjpeg($fileName);
	}elseif($ext == ".png" ){
		$new_img = imagecreatefrompng($fileName);
	}elseif($ext == ".gif"){
		$new_img = imagecreatefromgif($fileName);
	}

	//list the width and height and keep the height ratio.
	list($width, $height) = getimagesize($fileName);

	//calculate the image ratio
	$imgratio=$width/$height;
	$newwidth = $width;
	$newheight = $height;

	//Image format -
	if ($imgratio>1){
		if ($width>$maxWidth) {
			$newwidth = $maxWidth;
			$newheight = $maxWidth/$imgratio;
		}
	//image format |
	}else{
		if ($height>$maxHight) {
			$newheight = $maxHight;
			$newwidth = $maxHight*$imgratio;
		}
	}

	//function for resize image.
	$resized_img = imagecreatetruecolor($newwidth,$newheight);

	//the resizing is going on here!
	imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	//finally, save the image
	ImageJpeg ($resized_img,$fileName,80);

	ImageDestroy ($resized_img);
	ImageDestroy ($new_img);
}


//****************************** Tools *********************

function getFieldAccessValue($field) {
	if (userIsAdmin() || isAktUserTheLoggedInUser())
		return getFieldValue($field);
	else if (userIsLoggedOn() && getFieldCheckedClass($field)=="checked" && getAktClass()==getLoggedInUserClassId())
		return getFieldValue($field);
	else if (userIsLoggedOn() && getFieldCheckedScool($field)=="checked")
		return getFieldValue($field);
	else if (getFieldCheckedWord($field))
		return getFieldValue($field);
	else {
		$len = strlen($field)/3;
		if ($len==0) {
			return null;	
		}
		else {
			if ($len>200) $len=200;
			$ret = substr(getFieldValue($field),0,$len)."...<br /><br />A szöveg többi része védve van.<br />";
			if (getFieldCheckedClass($field)=="checked") 
				$ret =$ret."A Teljes szöveget csak bejelentkezek osztálytársak láthatják.";
			if (getFieldCheckedScool($field)=="checked") 
				$ret =$ret."A Teljes szöveget csak bejelentkezek iskolatársak láthatják.";
			return $ret;
		}
	}
}

function getFieldValue($person,$field=null) {
	if (null==$field) {
 		if ($person=="") 
  			return "";
		$ret = ltrim($person,"~");
	}
	else {
  		if (!isset($person[$field]) || $person[$field]=="") 
  			return "";
		  $ret = ltrim($person[$field],"~");
	}
  $ret = trim($ret);
  $ret= str_replace("%3D", "=", $ret); 
  return  $ret;
}

function getFieldValueNull($diak,$field) {
	if ( !isset($diak[$field]) || $diak[$field]=="")
		return "";
	$ret = ltrim($diak[$field],"~");
	$ret = trim($ret);
	$ret= str_replace("%3D", "=", $ret); 
	return  $ret;
}

function getFieldCheckedWord($field) {
	if (strlen($field)>1 && $field[0]=="~" && $field[1]=="~") #
		return "checked";
	else 
		return "";
}

function getFieldCheckedScool($field) {
	if (strlen($field)==1 && $field[0]=="~" )
		return "checked";
	if (strlen($field)>1 && $field[0]=="~" && $field[1]!="~" )
		return "checked";
	else
		return "";
}

function getFieldCheckedClass($field) {
	if ($field=="")
		return "checked";
	if (strlen($field)>0 && $field[0]!="~")
		return "checked";
	else 
		return "";
}

function getFieldChecked($diak,$field) {
  if (null== $field || null==$diak || !isset($diak[$field]) || $diak[$field]=="" ) 
  	return "";
  $c=$diak[$field];
  if ($c[0]=="~") 
  	return "checked";
  return "";
}

/*
 * is a field content printable
 */
function showField($diak,$field) {
  if (!isset($diak[$field]) || $diak[$field]=="") 
  	return false;
  if (($diak[$field][0]!="~") ||  userIsLoggedOn()) { 
  	if (ltrim($diak[$field],"~")!="") {
  		return true;
  	} else { 
  		return false;
  	}
  }
  return false;

}


//generate normalised person link

function getPersonLink($ln,$fn) {
   return getNormalisedChars($ln).'_'.getNormalisedChars($fn);
}

function getNormalisedChars($s) {
  $trans = array (
  	" "=>"_","-"=>"_",
  	"â"=>"a", "ä"=>"a","â"=>"a", "á"=>"a", "à"=>"a",
  	"é"=>"e", "è"=>"e", 
  	"í "=>"i", "ì"=>"i", "Í"=>"I","Ì"=>"I",
  	"ó"=>"o", "ò"=>"o", "ö"=>"o","ő"=>"o", "õ"=>"o",
  	"ú"=>"u", "ù"=>"u", "ü"=>"u","ű"=>"u",
  	"Á"=>"A", "À"=>"A", "Ä"=>"A","Å"=>"A",
  	"É"=>"E", "È"=>"E",
	"Ó"=>"O", "Ò"=>"O", "Ö"=>"O","Ő"=>"O",
  	"ș"=>"s","Ș"=>"S","Ț"=>"T","ț"=>"t",
	"Ú"=>"U", "Ù"=>"U", "Ü"=>"U","Ű"=>"U"
  );
  //return strtr($s, " âäåáàéèíîöóòõőúùüűÅÁÄÉÖŐÜŰ", "-aaaaaeeiiooooouuuuAAAEOOUU");
  return strtr($s, $trans);
}

?>
