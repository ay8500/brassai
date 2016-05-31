<?php
/**
 * Database engime for person data  
 */

include_once("userManager.php");

$data = array();

//List of databases are the subdirectorys in folder /data
$dataPath = "data/";
chdir($dataPath);
$dataBase = array_filter(glob('*'), 'is_dir');
chdir("..");
$datafields = array("id","firstname","lastname","birthname","partner","address","zipcode","place","country","phone","mobil","email","skype","education","employer","function","children","picture","geolat","geolng","user","passw","admin","date","ip","facebook","facebookid");
$openedDatebase=null;


//select the database
openDatabase( getAktDatabaseName() ) ;


/*
 * Database List
 */
function getDatabaseList() {
	$classes = Array();
	global $dataBase;
	foreach($dataBase as $db) {
		if ($db!="oooooo")
			array_push($classes, substr($db,3,4)." ".substr($db,0,3));
	}
	sort($classes);
	return $classes;
}

function getOpenedDatabase() {
	global $openedDatabase;
	return $openedDatabase;
}

function setOpenedDatabase($dataBaseName) {
	global $openedDatabase;
	$openedDatabase = $dataBaseName;
}

//aktual used scoolyear and class
function getAktScoolYear() {
	if (isset($_SESSION['aktScoolYear'])) 
		return $_SESSION['aktScoolYear'];
	else
		return "1985";
}

function getAKtScoolClass() {
	if (isset($_SESSION['aktScoolClass']))
		return $_SESSION['aktScoolClass'];
	else
		return "12A";
}

function setAktScoolYear($year) {
	$_SESSION['aktScoolYear']=$year;
}

function setAktScoolClass($class) {
	$_SESSION['aktScoolClass']=$class;
}

/**
 * get aktual DabaseName
 */
function getAktDatabaseName()
{
	if (null!=getAktScoolClass() && null!=getAktScoolYear())
		return  getAKtScoolClass().getAktScoolYear();
	else 
		return "12A1985";
}

function getUserDatabaseName() 
{
	if (null!=getUScoolClass() && null!=getUScoolYear())
		return  getUScoolClass().getUScoolYear();
	else 
		return "";
}

/**
 * open the specific database name = class+year eg. 12A1985
 * @return true if the database don't changed
 */
function openDatabase($name) {
	global $dataFileName;
	global $dataBase;
	global $data;
	global $dataPath;

	//no database change
	if (getOpenedDatabase()==$name) {
		$ret=true;
		//$dataFileName=$dataPath.$name."/data.txt";
		//ReadDB();
	}
	//database change
	else {
		$ret=false;
		foreach($dataBase as $db) {
			if ($name==$db) {
				$ret=true;
				$dataFileName=$dataPath.$name."/data.txt";
				setOpenedDatabase($name);
				ReadDB();
			}
		}
	}
}


/**
 * Get count of active persons<br />
 * @param $guest <b>true</b> if you want to count the entrys markt as guest
 */
function getCountOfActivePersons($guest) {
	$ret=0;
	global $data;
	foreach ($data as $l => $d)
	{
		
		if (isPersonActive($d) && isPersonGuest($d)==$guest) $ret++;
	}
	return $ret;
}

/**
 * Check if a person have the aktiv status
 */
function isPersonActive($d) {
	if (!isset($d["active"]))
		return true;
	
	return ($d["active"]=="true");
}

/**
 * get person from database
 * * @return Person
 */
function getPerson($id,$dataBase=null) {
	if (isset($id) && ($id>0)  ) { 
		global $data;
		if (sizeof($data)==0 || null==getOpenedDatabase() || (null!=$dataBase && getOpenedDatabase()!=$dataBase)) {
			openDatabase($dataBase);
		}
		foreach ($data as $l => $d) {	
			if ($d["id"]==$id)
				return $d;
		}
	}
	return null;
}

/**
 * Returns the person index in the data
 * @param person id
 * @return index:integer
 */
function getPersonIdx($id) {
	if (isset($id) && ($id>0)  ) { 
		global $data;
		if (sizeof($data)==0) {
			readDB();
		}
		foreach ($data as $l => $d) {	
			if ($d["id"]==$id)
				return $l;
		}
	}
	return -1;
}

/**
 * Returns the nex free id in the aktual database
 */
function getNextFreeId() {
	$ret = 0;
	global $data;
	foreach ($data as $l => $d) {
		if (intval($d["id"])>$ret)
			$ret=intval($d["id"]);
	}
	return $ret+1;
	
}

/**
 * returns logged in person
 */
function getPersonLogedOn() {
	if ( null!=getLoggedInUserId())  { 
		return getPerson(getLoggedInUserId(),getUserDatabaseName());
	} else {
		return getPersonDummy();
	}
}

/**
 * returns aktual person
 */
function getAktPerson() {
	if ( null!=getAktUserId())  {
		return getPerson(getAktUserId(),getAktDatabaseName());
	} else {
		return getPersonDummy();
	}
}


/**
 * Set person data
 */
function savePerson($person) {
	global $data;
	if (sizeof($data)==0 || getOpenedDatabase()!=getAktDatabaseName()) {
		openDatabase(getAktDatabaseName());
	}
	if (sizeof($person)>0) {
		if (!userIsAdmin()) {
			$person['date']=date('d.m.Y H:i');
			$person['ip']=$_SERVER["REMOTE_ADDR"];
		}
		reset($person);
		$idx=getPersonIdx($person["id"]);
		if ($idx!=-1) {
			while (list($key, $val) = each($person)) {
			   $data[$idx][$key]=$val;
			   //echo('Name='.$key.'Value='.$val);
			}
			
		}
		else {
			array_push($data,$person);
		}
		saveDB();
	}
}



/**
 * is the person a guest
 */
function isPersonGuest($person) {
	return (isset($person["admin"]) && strstr($person["admin"],"guest")!="");
}

function isPersonAdmint($person) {
	return (isset($person["admin"]) && strstr($person["admin"],"admin")!="");
}

function isPersonEditor($person) {
	return (isset($person["admin"]) && strstr($person["admin"],"editor")!="");
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
	$p["admin"]="";
	return $p;
}

/**
 * returns an empty person
 */
function createNewPerson($db,$guest) {
	openDatabase($db);
	$p = getPersonDummy();
	$p["id"]=getNextFreeId();
	if($guest)
		$p["admin"]="guest";
	global $data;
	array_push($data, $p);
	return $p;
}

/**
 * returns an empty authorisation 
 */
function getUserAuthDummy() {
	$datafields=array("id","user","passw","admin","scoolYear","scoolClass","facebookid","email");
	$p = array();
	foreach ($datafields as $field) {
		$p[$field]="";
	}
	return $p;
}

/**
 * Read the Database into the Memory
 */
function readDB()
{
	global $data;
	
	while (count($data)>0) array_pop($data);  //delete old records
	
	global $dataFileName;
    if (file_exists($dataFileName)) {
		$file=fopen($dataFileName ,"r");
		$person = NULL;
		$id=0;
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[0])&&isset($b[1])) {
				if(($b[0]!="")&&($b[1]!="")&&$b[0][0]!="#") {
					if ($b[0]=="id") {
						if (isset($person)) {
							while (list($key, $val) = each($person)) 
								$data[$id][$key]=$val;
							$id++;
							$person=getPersonDummy();
						}
						else 
							$person=getPersonDummy();
					}
					$person[$b[0]]=chop($b[1]);
				}
			}
		}
		while (list($key, $val) = each($person)) 
			$data[$id][$key]=$val;
		fclose($file);
	}
	usort($data, "compareAlphabetical");
}

/**
 * Compare classmates
 * "firstname","lastname","birthname"
 * @param unknown $a
 * @param unknown $b
 */
function compareAlphabetical($a,$b) {
	if (isset($a["birthname"]) && $a["birthname"]!="") 
		$aa=$a["birthname"]." ".$a["firstname"];
	else
		$aa=$a["lastname"]." ".$a["firstname"];
	if (isset($b["birthname"]) && $b["birthname"]!="")
		$bb=$b["birthname"]." ".$b["firstname"];
	else
		$bb=$b["lastname"]." ".$b["firstname"];
	return strcmp($aa, $bb);
}


function compairUserPassw($d1,$d2) {
	if (isset($d1["user"]) && isset($d2["user"]) && isset($d1["passw"]) && isset($d2["passw"])) {
		return strtolower($d1["user"])==strtolower($d2["user"]) && $d1["passw"]==$d2["passw"];
	}
	else 
		return false;
}

function compairEmailPassw($d1,$d2) {
	if (isset($d1["email"]) && isset($d2["email"]) && isset($d1["passw"]) && isset($d2["passw"])) {
		return strtolower(getFieldValue($d2,"email"))==strtolower(getFieldValue($d1,"email")) && $d1["passw"]==$d2["passw"];
	}
	else 
		return false;
}

function compairFacebookId($d1,$d2) {
	if (isset($d1["facebookid"]) && isset($d2["facebookid"]) ) {
		return strtolower($d1["facebookid"])==strtolower($d2["facebookid"]);
	}
	else
		return false;
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

/**
 * Search for person lastname and firstname 
 * @param unknown $name
 */
function searchForPerson($name) {
	$ret = array();
	if( strlen($name)>1) {
		global $dataBase;
		global $dataPath;
		global $data;
		foreach($dataBase as $db) {
			if ($db!="oooooo") 
			{
				openDatabase($db);
				foreach ($data as $person) {
					if (stristr(html_entity_decode($person["lastname"]), $name)!="" ||
						stristr(html_entity_decode($person["firstname"]), $name)!="" ||
						(isset($person["birthname"]) && stristr(html_entity_decode($person["birthname"]), $name)!="")) {
						$person["scoolYear"]=substr($db,3,4);
						$person["scoolClass"]=substr($db,0,3);
						array_push($ret, $person);
					}
				}
			}
		}
		usort($ret, "compareAlphabetical");
	}
	return $ret;
}

/**
 * Read user in all Databases using comparator
  */
function getGlobalUser($diak,$compair, $scoolYear=null, $scoolClass=null) {
	global $dataBase;
	global $dataPath;
	global $data;
	foreach($dataBase as $db) {
		if ((null==$scoolClass && null==$scoolYear) ||
			($scoolClass.$scoolYear==$db)) 
		{
			openDatabase($db);
			foreach ($data as $person) {
				if ($compair($person,$diak)) {
					$person["scoolYear"]=substr($db,3,4);
					$person["scoolClass"]=substr($db,0,3);
					return $person;
				}
			}
		}
	}
	return null;
}

/**
 * Read in the userlist
 */
function readUserAuthDB()
{
	global $dataBase;
	global $dataPath;
	$data=array();
	$id=-1;
	foreach($dataBase as $db) {
		$scoolYear=substr($db,3,4);$scoolClass=substr($db,0,3);
		$dataFileName=$dataPath.$db."/data.txt";
	    if (file_exists($dataFileName)) {
			$file=fopen($dataFileName ,"r");
			while (!feof($file)) {
				$b = explode("=",fgets($file));
				if (isset($b[0])&&isset($b[1])) {
					if(($b[0]!="")&&($b[1]!="")&&$b[0][0]!="#") {
						if ($b[0]=="id") {
							$id++;
							$data[$id]=getUserAuthDummy();
							$data[$id]["scoolYear"]=$scoolYear;
							$data[$id]["scoolClass"]=$scoolClass;
							$data[$id]["id"]=chop($b[1]);
						} else {
							if (($b[0] == "user") || ($b[0] == "passw") || ($b[0] == "email") || ($b[0] == "facebookid") || ($b[0] == "admin")) {
	    						$data[$id][$b[0]]=getFieldValue($b,1);
							}
						}
					}
				}
			}
			fclose($file);
		}
	}
	return $data;
}


function deleteDiak($id,$db) {
	$ret = Array();
	if (isset($id) && ($id>0)  ) {
		//Database
		openDatabase($db);
		global $data;

		foreach ($data as $l => $d) {
			if ($d["id"]!=$id)
				array_push($ret, $d);
		}
		$data = $ret;
		saveDB();
		
		//Pictures 
		//TODO
		$pl=getListofPictures($db, $id, true);
		foreach ($pl as $p) {
			deletePicture($db, $id, $p["id"]);
		}
	}
	
	
}

/**
 *Save the databe to file 
 */
function saveDB() {
	global $data;
	reset( $data);
	global $dataFileName;
	$file=fopen($dataFileName,"w");
	fwrite($file,"#Database File\r\n");
	fwrite($file,"#Last IP:".$_SERVER["REMOTE_ADDR"]."\r\n");
	fwrite($file,"#Change Date:".date('d.m.Y H:i')."\r\n\r\n");
	$i=1;
	foreach ($data as $person) {
		reset($person);
		if (strlen($person['user'])==0) $person['user']=getPersonLink($person['lastname'],$person['firstname']);
		if (strlen($person['passw'])==0) $person['passw']=createPassword(8);
		fwrite($file,"\r\n");
		fwrite($file,"id=".$person["id"]."\r\n");     //id is the first element
		while (list($key, $val) = each($person)) {
			if (null!=$val && $val!="" && $key!="id")
		   		fwrite($file,$key."=".$val."\r\n");
		}
	}
	fclose($file);
	
	//Just for fun save the data as json file
	$json = $data;
	$file=fopen($dataFileName.".json","w");
	$json["lastIp"]=$_SERVER["REMOTE_ADDR"];
	$json["changeDate"]=date('d.m.Y H:i');
	$json["changeUserId"]=getLoggedInUserId();
	$json["changeUserDb"]=getUserDatabaseName();
	fwrite($file,json_encode($json));
	fclose($file);
}

//*********************[ Vote database]**************************************

$voteFields=array("date","class","cemetery","dinner","excursion","where");
$voteData = array();

function setVote($uid, $vote) {
	global $voteData;
	$voteData[$uid] = $vote;
}

function readVoteData($db,$years) {
	global $voteData;
	global $dataPath;

	for ($i=0;$i<sizeof($voteData);$i++)
		unset( $voteData[$i]);			//delete old records

	$fileName=$dataPath.$db.'/'.$years.'vote.txt';
    if (file_exists($fileName)) {
		$file=fopen($fileName ,"r");
		$id=0;
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[0])&&isset($b[1])) {
				if(($b[0]!="")&&($b[1]!="")&&$b[0][0]!="#") {
					if ($b[0]=="id") {
						$id =chop($b[1]);
						$voteData[$id]=getVoteDummy();
					} else {
						$voteData[$id][$b[0]]=chop($b[1]);
					}
				}
			}
		}
		fclose($file);
	}
}

function getVote($uid) {
	global $voteData;
	if (isset($voteData[$uid]))
   		return $voteData[$uid];
	else
		return  getVoteDummy();
}

function getVoteDummy() {
	global $voteFields;
	$p = array();
	foreach ($voteFields as $field) {
		$p[$field]="";
	}
	return $p;
}

function saveVoteData($db,$years) {
	global $data;
	global  $dataPath;
	global $voteData;
	reset( $voteData);
	$fileName=$dataPath.$db.'/'.$years.'vote';
	
	$file=fopen($fileName.".txt","w");
	fwrite($file,"#Vote Database File\r\n");
	fwrite($file,"#Last IP:".$_SERVER["REMOTE_ADDR"]."\r\n");
	fwrite($file,"#Change Date:".date('d.m.Y H:i')."\r\n\r\n");
	foreach ($data as $person) {
		reset($person);
		fwrite($file,"\r\n");
		fwrite($file,"id=".$person["id"]."\r\n");
		$vote=getVote($person["id"]);
		while (list($key, $val) = each($vote)) {
		   fwrite($file,$key."=".$val."\r\n");
		}
	}
	fclose($file);
	
	//Just for fun save the data as json file
	$json = $voteData;
	$file=fopen($fileName.".json","w");
	$json["lastIp"]=$_SERVER["REMOTE_ADDR"];
	$json["changeDate"]=date('d.m.Y H:i');
	$json["changeUserId"]=getLoggedInUserId();
	$json["changeUserDb"]=getUserDatabaseName();
	fwrite($file,json_encode($json));
	fclose($file);
	
}

//*********************[ Picture database]**************************************
//each person can have more than one picture with the following attributes:
// title, comment, visibleforall, date, uploaddate, ip
// the pictures are save in a folder named like the database
// picture name: the letter p plus personid-pictureid.jpg
// the pictures are saved in a maximal resolution of 1200x1024

$PictureFields=array("id","visibleforall","date","title","comment","ip","uploadDate","lastIp","lastChangeDate","deleted");
$pictures = array();
$pictureFolder = "./images/";

function getPictureDummy() {
	global $PictureFields;
	$p = array();
	foreach ($PictureFields as $field) {
		if($field=="visibleforall")
			$p[$field]="false";
		if($field=="deleted")
			$p[$field]="false";
		elseif($field=="date" || $field=="lastChangeDate")
		$p["lastChangeDate"]=date('d.m.Y H:i');
		else
			$p[$field]="";
	}
	return $p;
}

/* getListofPictures
* The filen name structure: the letter "p".DataBaseRecord."-".ImgageIndex eg. p12-4.jpf
*/ 
function getListofPictures($database,$personID, $vorAll) {
	global $pictureFolder;
	$idx = 0;
	$images_array = array();
	//Check if directory exists and create if not	
	if (!file_exists($pictureFolder.$database)) {
    	mkdir($pictureFolder.$database, 0777, true);
	}
	$directory = dir($pictureFolder.$database);
	while ($file = $directory->read()) {
		if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif","png"))) {
			if (substr($file,0,1)=="p" && strpos($file,$personID.'-')==1) {
				//get the file id from file name
				$fileSplit = split('[.-]',$file);
				$idx=$fileSplit[1];
				$images_array[$idx]["File"] = $file;	
				$picture = loadPictureAttributes($database,$personID,$idx);
				while (list($key, $val) = each($picture)) {
	   				$images_array[$idx][$key]=$val;
				}
				$idx++;
			}
		}
		
	}
	$directory->close();
	/*
	$directory = glob($pictureFolder.$database."/p".$personID."-*.jpg");	
	usort($directory, function($a, $b) {
		return filemtime($a) < filemtime($b);
	});
	foreach ($directory as $file) {
		$fileSplit = split('[.-]',basename($file));
		echo($fileSplit[1]);
		$idx=$fileSplit[1];
		$images_array[$idx]["File"] = $file;
		$picture = loadPictureAttributes($database,$personID,$idx);
		while (list($key, $val) = each($picture)) {
			$images_array[$idx][$key]=$val;
		}
		$idx++;
		
	}
	*/
	return $images_array;
}


/**
 * returns the nex id for the name of a picture 
 * @param unknown $database
 * @param unknown $personID
 * @return number
 */
function getNextPictureId($database,$personID){
	//Next id
	global $pictureFolder;
	$nextId = 0;
	if (!file_exists($pictureFolder.$database)) {
		mkdir($pictureFolder.$database, 0777, true);
	}
	$directory = dir($pictureFolder.$database);	
	while ($file = $directory->read()) {
		if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif","png"))) {
			if (strpos($file,$personID.'-')==1) {
				$nextId++;
			}			
		}
	}
	$directory->close();
	return $nextId;
}

function loadPictureAttributes($database,$personId,$pictureId) {
	global $pictureFolder;
	$picture=getPictureDummy();
	$fileName =$pictureFolder.$database."/".$personId."-".$pictureId.".txt";
	//if (!file_exists($fileName)) {
	//	setPictureAttributes($database,$personId,$pictureId,"","","false");
	//}
    if (file_exists($fileName)) {
		$file=fopen($fileName,"r");
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[0])&&isset($b[1])) {
				if(($b[0]!="")&&($b[1]!="")&&$b[0][0]!="#") {
					$picture[$b[0]]=chop($b[1]);
				}
			}
		}
		fclose($file);
	}
	$picture["id"]=$pictureId;
	return $picture;
}

function setPictureAttributes($database,$personID,$pictureId,$title,$comment, $visibleforall=null) {
	$picture=loadPictureAttributes($database,$personID,$pictureId);
	$picture["title"]=$title;
	$picture["comment"]=$comment;
	if ($visibleforall!=null)
		$picture["visibleforall"]=$visibleforall;
	savePictureAttributes($database,$personID,$pictureId,$picture);
}

function setPictureVisibleForAll($database,$personID,$pictureId, $visibleforall){
	$picture=loadPictureAttributes($database,$personID,$pictureId);
	$picture["visibleforall"]=$visibleforall;
	savePictureAttributes($database,$personID,$pictureId,$picture);
}

function deletePicture($database,$personID,$pictureId) {
	$picture=loadPictureAttributes($database,$personID,$pictureId);
	$picture["deleted"]="true";
	savePictureAttributes($database,$personID,$pictureId,$picture);
}

function savePictureAttributes($database,$personID,$pictureId,$picture) {
	global $pictureFolder;
	$file=fopen($pictureFolder.$database."/".$personID."-".$pictureId.".txt","w");
	$picture["lastChangeDate"]=date('d.m.Y H:i');
	$picture["lastIp"]=$_SERVER["REMOTE_ADDR"];
	fwrite($file,"#Picture Metafile\r\n");
	while (list($key, $val) = each($picture)) {
		fwrite($file,$key."=".$val."\r\n");
	}
	fclose($file);
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

//****************************** TextData *********************

/**
 * load text data 
 * @param unknown $database
 * @param unknown $personId
 * @param unknown $type cv; story; spare
 * @return unknown
 */
function loadTextData($database, $personId, $type) {
	global $dataPath;
	$fileName =$dataPath."/".$database."/".$personId."-".$type.".txt";
	$ret="";
	if (file_exists($fileName)) {
		$file=fopen($fileName,"r");
		while (!feof($file)) {
			$ret .= fgets($file);
		}
		fclose($file);
	}
	return $ret;
}

function getTextDataDate($database, $personId, $type) {
	global $dataPath;
	$fileName =$dataPath."/".$database."/".$personId."-".$type.".txt";
	$ret="";
	if (file_exists($fileName)) {
		// if ($_SESSION['LANG']=="hu"
		$ret=date ("Y.m.d. H:i:s.",filemtime($fileName));
	}
	return $ret;
}
/**
 * Save text data
 * @param unknown $database
 * @param unknown $personId
 * @param unknown $type
 * @param unknown $text
 */
function saveTextData($database, $personId, $type, $privacy, $text) {
	global $dataPath;
	$fileName =$dataPath."/".$database."/".$personId."-".$type.".txt";
	$file=fopen($fileName,"w");
	if ($privacy=="world") fwrite($file,"~~"); 
	if ($privacy=="scool") fwrite($file,"~"); 
	//if ($privacy=="class") fwrite($file,"");
	fwrite($file,$text);
	fclose($file);
}


//****************************** Tools *********************

function getFieldAccessValue($field) {
	if (userIsAdmin() || isAktUserTheLoggedInUser())
		return getFieldValue($field);
	else if (userIsLoggedOn() && getFieldCheckedClass($field)=="checked" && getAktDatabaseName()==getUserDatabaseName())
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
			
function getFieldValueNull($diak,$field) {
	if ( !isset($diak[$field]) || $diak[$field]=="")
		return "";
	$ret = ltrim($diak[$field],"~");
	$ret = trim($ret);
	return  $ret;
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
  	" "=>"-", "â"=>"a", "ä"=>"a","â"=>"a", "á"=>"a", "à"=>"a",
  	"é"=>"e", "è"=>"e", 
  	"í "=>"i", "ì"=>"i", "Í"=>"I","Ì"=>"I",
  	"ó"=>"o", "ò"=>"o", "ö"=>"o","ő"=>"o", "õ"=>"o",
  	"ú"=>"u", "ù"=>"u", "ü"=>"u","ű"=>"u",
  	"Á"=>"A", "À"=>"A", "Ä"=>"A","Å"=>"A",
  	"É"=>"E", "È"=>"E",
	"Ó"=>"O", "Ò"=>"O", "Ö"=>"O","Ő"=>"O",
	"Ú"=>"U", "Ù"=>"U", "Ü"=>"U","Ű"=>"U"
  );
  //return strtr($s, " âäåáàéèíîöóòõőúùüűÅÁÄÉÖŐÜŰ", "-aaaaaeeiiooooouuuuAAAEOOUU");
  return strtr($s, $trans);
}

?>
