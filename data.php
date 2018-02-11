<?php
/**
 * Business layer for the classmate database 
 */

include_once "tools/userManager.php";
include_once 'dbDAO.class.php';

$db = new dbDAO;

/**
 * The folder of the aktual persons class
 */
function getAktClassFolder() {
	global $db;
	$class=getAktClass();
	if ($class!=null)
		return $class["name"].$class["graduationYear"];
	else
		return "";
}

/**
 * The name of the aktual school class
 * @param boolean $short short form without evening class text
 * @return string
 */
function getAktClassName($short=false) {
	$class=getAktClass();
	return getClassName($class,$short);
}


/**
 * The name of the school class 
 * @param boolean $short short form without evening class text
 * @return string
 */
function getClassName($class,$short=false) {
	if ($class!=null) {
		if ($class["id"]==0)
			return "";
			else {
				$ret= str_replace(" ", "&nbsp;", $class["text"]);
				if (!$short) {
					$ret.= (intval($class["eveningClass"])==0)?"":"&nbsp;tagozat";
				}
				return $ret;
			}
	}
	return "";
}

/**
 * The name of the aktual persons school
 */
function getAktSchoolName() {
	global $db;
	$school=getAktSchool();
	if ($school!=null) {
		if ($school["id"]==0)
			return "";
		else
			return $school["name"];
	} else
		return "";
}


/**
 * returns aktual person
 * @return object person
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
 * returns logged in person
 * @return object person
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
 * is the person a guest
 * @return boolean*/
function isPersonGuest($person) {
	return (isset($person["role"]) && strstr($person["role"],"guest")!="");
}

/**
 * is the person a admin
 * @return boolean*/
function isPersonAdmin($person) {
	return (isset($person["role"]) && strstr($person["role"],"admin")!="");
}

/**
 * is the person a editor
 * @return boolean
 */
function isPersonEditor($person) {
	return (isset($person["role"]) && strstr($person["role"],"editor")!="");
}

/**
 * returns an empty person
 */
function getPersonDummy() {
	return [
		"firstname"=>"",
		"lastname"=>"",
		"user"=>createPassword(8),
		"passw"=>createPassword(8),
		"role"=>""
	];
}

function getPersonPicture($person) {
	if (null==$person || !isset($person["picture"]) || $person["picture"]=="") {
		return "images/avatar.jpg";
	} else {
		return "images/".$person["picture"];
	}
}

function writePersonLinkAndPicture($person) {
	?>	
		<img src="<?php echo getPersonPicture($person) ?>"  class="diak_image_sicon"/>
		<a href="editDiak.php?uid=<?php echo($person["id"]);?>"><?php echo $person["lastname"]." ".$person["firstname"] ?></a>
	<?php		
}

/**
 * Compare classmates by firstname,lastname,birthname
 * @param person $a
 * @param person $b
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


/**
 * Compare classmates by picture,firstname,lastname,birthname
 * @param person $a
 * @param person $b
 */
function compareAlphabeticalPicture($a,$b) {
	$c = strcmp(isset($a["picture"])?'1':'0',isset($b["picture"])?'1':'0');
	if ($c!=0) {
		return $c;
	}
	return compareAlphabetical($a, $b);
}

/**
 * Compare classmates by email
 * @param person $a
 * @param person $b
 */
function compairEmail($d1,$d2) {
	if (!isset($d1["email"]) || !isset($d2["email"]))
		return false;
	return getFieldValue($d1,"email")==getFieldValue($d2,"email");
}

/**
 * Compare classmates by username
 * @param person $a
 * @param person $b
 */
function compairUser($d1,$d2) {
	if (!isset($d1["user"]) || !isset($d2["user"]))
		return false;
	return strtolower($d1["user"])==strtolower($d2["user"]);
}

/**
 * Compare classmates by username link
 * @param person $a
 * @param person $b
 */
function compairUserLink($d1,$d2) {
	if (isset($d1["lastname"]) && isset($d2["lastname"]) && isset($d1["firstname"]) && isset($d2["firstname"])) {
		return getPersonLink($d1["lastname"],$d1["firstname"])==getPersonLink($d2["lastname"],$d2["firstname"]);
	}
	else
		return false;
	}

/**
 * Set picture visibility 
 * @param int $pictureId
 * @param int $visibleforall
 * @return integer >=0 ok,
 */
function setPictureVisibleForAll($pictureId, $visibleforall){
	global $db;
	$picture=$db->getPictureById($pictureId);
	$picture["isVisibleForAll"]=$visibleforall?1:0;
	return $db->savePicture($picture);
}

/**
 * Mark picture as deleted or delete it from the db and filesystem
 * @param integer $pictureId
 * @param boolean $unlink default false
 * @return boolean
 */
function deletePicture($pictureId,$unlink=false) {
	global $db;
	$picture=$db->getPictureById($pictureId);
	if ($picture==null)
		return false;
	if ($unlink) {
		$db->deletePictureEntry($pictureId);
	} else {
		$picture["isDeleted"]=1;
		return $db->savePicture($picture);
	}
}

/**
 * Resize image, if originalFileSufix not set the original file well be owewrited
 * @param string $fileName
 * @param int $maxWidth
 * @param int $maxHight
 * @param string $originalFileSufix
 */
function resizeImage($fileName,$maxWidth,$maxHight,$originalFileSufix="")
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
	if ($originalFileSufix!="") {
		$path_parts=pathinfo($fileName);
		rename($fileName,$path_parts["dirname"].DIRECTORY_SEPARATOR.$path_parts["filename"]."_".$originalFileSufix.".".$path_parts["extension"]);
	}
	ImageJpeg ($resized_img,$fileName,80);

	ImageDestroy ($resized_img);
	ImageDestroy ($new_img);
}


//****************************** Tools *********************

function getFieldAccessValue($field) {
	if (userIsAdmin() || isAktUserTheLoggedInUser())
		return getFieldValue($field);
	else if (userIsLoggedOn() && getFieldCheckedClass($field)=="checked" && getAktClassId()==getLoggedInUserClassId())
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

/**
 * Concatenate lastname firstname and birtname
 * @param array $person
 * @return string
 */
function getPersonName($person) {
	if ($person!=null) {
		$ret = $person["lastname"]." ".$person["firstname"];
		if (isset($person["birthname"]) && trim($person["birthname"])!="")
			$ret .= " (".trim($person["birthname"]).")";
		return $ret;
	}
	return '';
}

/**
 * try to get a person by normalised name in one class
 * @param unknown $personLink
 * @param unknown $classId
 */
function getPersonByNormalisedName($personLink,$classId=null) {
	$personLink = trim($personLink,"//");
	global $db;
	if ($classId!=null) {
		$personlist = $db->getPersonListByClassId($classId);
		foreach ($personlist as $person) {
			if (getPersonLink($person["lastname"], $person["firstname"])==$personLink) { 
				return $person;
				exit;
			}
		}
	}
	$personlist=$db->getPersonList();
	foreach ($personlist as $person) {
		if (getPersonLink($person["lastname"], $person["firstname"])==$personLink) {
			return $person;
			exit;
		}
	}
	return null;
}

/**
 * The real person id
 * @param unknown $person
 * @deprecated use getRealId
 */
function getPersonId($person) {
	if (isset($person["changeForID"]))
		return $person["changeForID"];
	else
		return $person["id"];
}

/**
 * The real class id
 * @param object $entry 
 */
function getRealId($entry) {
	if(null==$entry)
		return null;
	if (isset($entry["changeForID"])) {
		return $entry["changeForID"];
	} else {
		if (isset($entry["id"])) {
			return $entry["id"];
		}
	}
	return null;
}


/**
 * generate normalised person link
 * @param string $lastname
 * @param string $firstname
 * @return string
 */
function getPersonLink($lastname,$firstname) {
   return getNormalisedChars($lastname).'_'.getNormalisedChars($firstname);
}

/**
 * Translate special chars in normal chars eg. á->a
 * @param unknown $s
 */
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
