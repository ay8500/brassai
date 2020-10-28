<?php
/**
 * Business layer for the classmate database
 */

include_once 'config.class.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';
include_once Config::$lpfw.'mysqldbauh.class.php';
include_once Config::$lpfw.'htmlParser.class.php';

include_once 'dbChangeType.class.php';
include_once 'dbDAO.class.php';
include_once 'dbDaUser.class.php';


class dbBL extends dbDAO
{
    /**
     * Check the requester IP
     * User can't login more then a defined time per day. This is a safety function
     * to prevent automatic loging of password crack or to mutch anonymous changes and uploads
     * @param changeType $action
     */
    function checkRequesterIP($action) {
        return userIsLoggedOn() || $this->getCountOfRequest($action,24) < $action;
    }

    /**
     * handle the params that change the class or school
     * classId can be filled with the class id or the text e.g. 12A-1985, 1971 12A, 198112B
     * @return array NULL | school class
     */
    public function handleClassSchoolChange($classId = "all", $schoolId = null)
    {

        if ($schoolId != null) {
            unsetAktClass();
            setAktSchool($schoolId);
        }
        $class = null;
        if (null != $classId) {
            if ('all' == $classId) {
                unsetAktClass();
                return null;
            }
            $class = $this->getClassById(intval($classId));
            if ($class==null) {
                $classId = str_replace(" ", "", $classId);
                $classId = str_replace("-", "", $classId);
                if (strlen($classId) == 7) {
                    $class = $this->getClassByText(substr($classId, 0, 4) . ' ' . substr($classId, 4, 3));
                    if ($class == null)
                        $class = $this->getClassByText(substr($classId, 3, 4) . ' ' . substr($classId, 0, 3));
                }
            }
        }
        if ($class==null) {
            $class = getAktClass();
        } else {
            setAktClass($class["id"]);
            setAktSchool($class["schoolID"]);
        }
        return $class;
    }

    /**
     * The picture folder of the aktual persons class or school
     */
    public function getAktClassFolder()
    {
        $class = getAktClass();
        if ($class != null) {
            return $class["name"] . $class["graduationYear"];
        } else {
            return "school" . getAktSchoolId();
        }

    }

    /**
     * returns logged in person
     * @return object person or null
     */
    public function getPersonLogedOn() {
        if ( userIsLoggedOn())  {
            return $this->getPersonByID(getLoggedInUserId());
        } else {
            return null;
        }
    }

    /**
     * get the logged in user class id
     * @return integer or -1 if no user logged on
     */
    function getLoggedInUserClassId() {
        if (null==getLoggedInUserId())
            return -1;
        $loggedInUser=$this->getPersonByID(getLoggedInUserId());
        if ($loggedInUser!=null)
            return intval($loggedInUser["classID"]);

        return -1;
    }


    /**
     * returns an empty person
     * @return array
     */
    public function getPersonDummy() {
        return [
            "firstname"=>"",
            "lastname"=>"",
            "title"=>"",
            "user"=>createPassword(8),
            "passw"=>encrypt_decrypt("encrypt",createPassword(8)),
            "role"=>""
        ];
    }

    /**
     * get the first school admin person
     * @return array | null  person
     */
    public function getAktSchoolAdminPerson() {
        return $this->dataBase->getEntry('person',"role like '%admin%' and classID=".$this->getStafClassBySchoolId(getAktSchoolId())["id"]);
    }

    /**
     * Mark picture as deleted or delete it from the db and filesystem
     * @param integer $pictureId
     * @param boolean $unlink default false
     * @return boolean
     */
    function deletePicture($pictureId,$unlink=false) {
        $picture=$this->getPictureById($pictureId);
        if ($picture==null)
            return false;
        if ($unlink) {
            return $this->deletePictureEntry($pictureId);
        } else {
            $picture["isDeleted"]=1;
            return $this->savePicture($picture);
        }
    }

    /**
     * Mark picture as deleted or delete it from the db and filesystem
     * @param integer $personId
     * @return boolean
     */
    function unlinkPersonPicture($personId,$unlink=false) {
        $person=$this->getPersonByID($personId);
        if ($person==null)
            return false;
        $person["picture"]=null;
        return $this->savePerson($person);
    }

    /**
     * Get type text from a picture
     * @param $pict
     * @return string
     */
    public function getPictureTypeText($pict)
    {
        $type="";$typeid="";$typeText="";
        if (isset($pict["schoolID"])) {
            $type = "school";
            $typeid = $pict[$type . "ID"];
            $school = $this->getSchoolById($typeid);
            $typeText = '<b>Iskolakép:</b><br/><a href="picture?type=schoolID&typeid='.$typeid.'">' . html_entity_decode(html_entity_decode($school["name"])).'</a>';
        } elseif (isset($pict["classID"])) {
            $type = "class";
            $typeid = $pict[$type . "ID"];
            $class = $this->getClassById($typeid);
            $typeText = '<b>Osztálykép:</b><br/><a href="picture?type=classID&typeid='.$typeid.'">' . $class["text"].'</a>';
        } elseif (isset($pict["personID"])) {
            $type = "person";
            $typeid = $pict[$type . "ID"];
            $picturePerson = $this->getPersonByID($typeid);
            $typeText = '<b>Személyes kép:</b><br/><a href="editDiak?tabOpen=pictures&uid=' . $typeid .'">'. getPersonName($picturePerson).'</a>';
        }
        return array("type"=>$type,"typeId"=>$typeid,"text"=>$typeText);
    }


}


//Connect to the DB
$dbPropertys = \Config::getDatabasePropertys();
$dataBase = new \maierlabs\lpfw\MySqlDbAUH($dbPropertys->host,$dbPropertys->database,$dbPropertys->user,$dbPropertys->password,true);

/**
 * @var dbBL;
 */
$db = new dbBL($dataBase);

/**
 * $var dbDaUser;
 */
$userDB=new dbDaUser($db);


/**
 * Set aktual person class id
 * @param int $classId
 */
function setAktClass($classId) {
    $_SESSION['aktClass']=$classId;
}

function unsetAktClass() {
    unset($_SESSION['aktClass']);
}

/**
 * Set aktual person class id
 * @param unknown $classId
 */
function setAktSchool($schoolId) {
    $_SESSION['aktSchool']=$schoolId;
}

function unsetAktSchool() {
    unset($_SESSION['aktSchool']);
}


/**
 * The aktual person class id
 * @return integer or -1
 */
function getAktClassId() {
    if (isset($_SESSION['aktClass'])) {
        return intval($_SESSION['aktClass']);
    } else {
        return -1;
    }
}

/**
 * The aktual person class id
 * @return array|NULL
 */
function getAktClass() {
    global $db;
    if (isset($_SESSION['aktClass'])) {
        return $db->getClassById(intval($_SESSION['aktClass']));
    }
    return null;
}

/**
 * The aktual school id
 * @return array|NULL
 */
function getAktSchool() {
    global $db;
    return $db->getSchoolById(getAktSchoolId());
}

/**
 * The aktual school staf class id
 * @return boolean
 */
function isAktClassStaf() {
    global $db;
    return $db->getStafClassIdBySchoolId(getAktSchoolId())==getAktClassId();
}

/**
 * The aktual person school id
 * @return number|1
 */
function getAktSchoolId() {
    /*TODO
    if (isset($_SESSION['aktSchool']) && null!=$_SESSION['aktSchool'] && intval($_SESSION['aktSchool'])>0)
        return intval($_SESSION['aktSchool']);
    else
    */
    return 1;
}


/**
 * The name of the aktual school class
 * @param boolean $short short form without evening class text
 * @return string
 */
function getAktClassName($short=false) {
    if (isAktClassStaf())
        return "";
    $class=getAktClass();
    return getClassName($class,$short);
}


/**
 * The name of the school class
 * @param boolean $short short form without evening class text
 * @return string
 */
function getClassName($class,$short=false) {
    if (null==$class) return "";
    if (!isset($class["graduationYear"]) || $class["graduationYear"]===0) return "";

    $ret= str_replace(" ", "&nbsp;", $class["text"]);
    if (!$short) {
        $ret.= (intval($class["eveningClass"])!==0 && strpos($class["text"], "esti")===false)?" esti":"";
        $ret.= (intval($class["eveningClass"])===0)?"":"&nbsp;tagozat";
    }
    return $ret;
}

/**
 * The name of the aktual persons school
 */
function getAktSchoolName() {
    $school=getAktSchool();
    if ($school!=null) {
        if ($school["id"]==0)
            return "";
        else
            return html_entity_decode(html_entity_decode($school["name"]));
    } else
        return "";
}

/**
 *User is logged in and have the role of  editor
 */
function userIsEditor() {
    if (null==getLoggedInUserId())              //No logged in user
        return false;
    if (getLoggedInUserId()==getAktUserId())    //Logged in user views his entry
        return true;
    global $db;
    //User is editor in his own class
    if (isset($_SESSION['uRole']) && getAktClassId()==$db->getLoggedInUserClassId()) {
        return strstr($_SESSION['uRole'],"editor")!="";
    } else {
        $p=$db->getPersonByID(getLoggedInUserId());
        //User is teacher and editor then return editor right for all classes where the teacher is head teacher
        if ($p["isTeacher"]==1) {
            if (strstr($_SESSION['uRole'],"editor")!="") {
                if (isset($p["children"])) {
                    $c=explode(",", $p["children"]);
                    $ret = false;
                    $class = getAktClass();
                    if (null!=$class) {
                        foreach ($c as $cc) {
                            if (substr($cc,0,3)==$class["name"] && substr($cc,3,4)==$class["graduationYear"])
                                $ret=true;
                        }
                    }
                    return $ret;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}


function getPersonPicture($person) {
    if (null==$person || !isset($person["picture"]) || $person["picture"]=="") {
        if (isset($person["gender"]) && $person["gender"]=="f")
            return "images/woman.png";
        else
            return "images/man.png";
    } else {
        return "images/".$person["picture"];
    }
}

function getPersonLinkAndPicture($person,$fullLink=false) {
    if (isset($person["id"])) {
        if ($fullLink)
            $pict = '';
        else
            $pict = '<img src="' . getPersonPicture($person) . '"  class="diak_image_sicon" />';
        $ret = ' <a href="'.($fullLink?Config::$siteUrl.'/':'') .'editDiak?uid=' . $person["id"] . '">' .$pict. $person["lastname"] . " " . $person["firstname"] . '</a>';
    } else {
        $ret = 'Anonim felhasználó';
    }
    return $ret;
}


function writePersonLinkAndPicture($person) {
    echo(getPersonLinkAndPicture($person));
}

function writePersonName($person) {
    echo($person["lastname"]." ".$person["firstname"]);
}


/**
 * Compare classmates by firstname,lastname,birthname
 * @param person $a
 * @param person $b
 * @return int
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
 * Compare persons by isTeacher,firstname,lastname,birthname
 * @param person $a
 * @param person $b
 * @return int
 */
function compareAlphabeticalTeacher($a,$b) {
    $c = strcmp($a["isTeacher"]?'0':'1',$b["isTeacher"]?'0':'1');
    if ($c!=0) {
        return $c;
    }
    return compareAlphabetical($a, $b);
}

/**
 * Compare classmates by picture,firstname,lastname,birthname
 * @param person $a
 * @param person $b
 * @return int
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
 * @return int
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
 * @return string
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
 * @return boolean
 */
function compairUserLink($d1,$d2) {
    if (isset($d1["lastname"]) && isset($d2["lastname"]) && isset($d1["firstname"]) && isset($d2["firstname"])) {
        return getPersonLink($d1["lastname"],$d1["firstname"])==getPersonLink($d2["lastname"],$d2["firstname"]);
    }
    else
        return false;
}




/**
 * Resize image, if originalFileSufix not set the original file will be owewrited
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
    global $db;
    if (userIsAdmin() || isAktUserTheLoggedInUser())
        return getFieldValue($field);
    else if (userIsLoggedOn() && getFieldCheckedClass($field)=="checked" && getAktClassId()==$db->getLoggedInUserClassId())
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
        if (!showField($person, $field))
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
    if ($c[0]==="~")
        return "checked";
    return "";
}

/*
 * is a field content allowed to print
 */
function showField($diak,$field) {
    if (!isset($diak[$field]) || $diak[$field]=="")
        return false;
    if (($diak[$field][0]!="~") || userIsLoggedOn()) {
        if (ltrim($diak[$field],"~")!="") {
            return true;
        } else {
            return false;
        }
    }
    return false;

}

/**
 * Concatenate lastname firstname
 * @param array $user
 * @return string
 */
function getPersonShortName($user) {
    if ($user!=null) {
        $ret ="";
        if (isset($user["title"]))
            $ret = $user["title"].' ';
        $ret .= $user["lastname"]." ".$user["firstname"];
        return $ret;
    }
    return '';
}


/**
 * Concatenate lastname firstname and birtname
 * @param array $user
 * @return string
 */
function getPersonName($user) {
    if ($user==null || !isset($user["lastname"]) || !isset($user["firstname"]) )
        return '';
    $ret ="";
    if (isset($user["title"]))
        $ret = $user["title"].' ';
    $ret .= $user["lastname"]." ".$user["firstname"];
    if (isset($user["birthname"]) && trim($user["birthname"])!="")
        $ret .= " (".trim($user["birthname"]).")";
    return $ret;
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
 * The real object id
 * @param array $entry
 * @return int | null
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
 * get gender by addressok database
 * @param $firstname
 * @return string "f" or "m"
 */
function getGender($firstname) {
    $url="https://addressok.blue-l.de/ajax/jsonCheckName.php?name=".$firstname;
    try {
        $ret = maierlabs\lpfw\htmlParser::loadUrl($url);
        $ret = json_decode($ret);
    } catch (Exception $e) {
        \maierlabs\lpfw\Logger::_("Get gender for $firstname ".$e->getMessage(),\maierlabs\lpfw\LoggerLevel::error);
    }

    if (isset($ret->countAll) && $ret->countAll>0) {
        $genderM=0;
        $genderF=0;
        $genderN=0;
        foreach ($ret as $r) {
            if (is_object($r)) {
                if ($r->gender=='m') $genderM++;
                if ($r->gender=='n') $genderN++;
                if ($r->gender=='f') $genderF++;
            }
        }
    } else {
        return "";
    }

    if ($genderM==0 && $genderF==0)
        return "";

    return $genderM>$genderF?'m':'f';
}

?>
