<?php
include_once("userManager.php");

//** Database engime for person data **
$data = array();

//List of databases
$dataBase=Array("12A1985","12B1985");
$datafields = array("firstname","lastname","birthname","partner","address","zipcode","place","country","phone","mobil","email","skype","education","employer","function","children","picture","geolat","geolng","user","passw","admin","date","ip","facebook","facebookid");


//select the database
openDatabase( getScoolClass().getScoolYear() ) ;


function getScoolYear() {
	if (isset($_SESSION['scoolYear'])) 
		return $_SESSION['scoolYear'];
	else
		return "1985";
}

function getScoolClass() {
	if (isset($_SESSION['scoolClass']))
		return $_SESSION['scoolClass'];
	else
		return "12A";
}

/**
 * getDabaseName
 */
function getDatabaseName()
{
	return  getScoolClass().getScoolYear();
}


/**
 * open the specific database for year and class
 */
function openDatabase($name) {
	global $dataFileName;
	global $dataBase;
	global $data;

	
	if (getScoolClass().getScoolYear()==$name) {
		$ret=true;
		$dataFileName=$name."data.txt";
		ReadDB();
	}
	else {
		$ret=false;
		foreach($dataBase as $db) {
			if ($name==$db) {
				$ret=true;
				$_SESSION['scoolYear']=substr($name,3,4);
				$_SESSION['scoolClass']=substr($name,0,3);
				$dataFileName=$name."data.txt";
				ReadDB();
			}
		}
	}
}


/**
 * get person from database
 */
function getPerson($id) {
	if (isset($id) && ($id>0)  ) { 
		global $data;
		if (sizeof($data)==0) {
			readDB();
		}
		if ($id<=sizeof($data))
			return $data[$id];
		else
			return getPersonDummy();	
	}
	else 
		return getPersonDummy();
}

/**
 * returns user id from logged in user
 */
function getPersonLogedOn() {
	global $data;
	if (sizeof($data)==0) {
		readDB();
	}
	if ( isset($_SESSION['UID']) && $_SESSION['UID']>0 && ($_SESSION['UID']<sizeof($data)))  { 
		return $data[$_SESSION['UID']];
	} else {
		return getPersonDummy();
	}
}


/**
 * Set person data
 */
function setPerson($id,$person) {
	global $data;
	if (sizeof($data)==0) {
		readDB();
	}
	if (sizeof($person)>0) {
		reset($person);
		if (!userIsAdmin()) {
			$person['date']=date('d.m.Y H:i');
			$person['ip']=$_SERVER["REMOTE_ADDR"];
		}
		while (list($key, $val) = each($person)) {
		   $data[$id][$key]=$val;
		   //echo('Name='.$key.'Value='.$val);
		}
		saveDB();
	}
}

/**
 * Returns the count of persons in the database 
 * administrators, editors and viewers not included
 */
function getDataSize() {
	global $data;
	if (sizeof($data)==0) {
		readDB();
	}
	$ret=0;
	foreach($data as $person) {
		//count only persons whithout admin or editor rights
		if ($person['admin']=="") $ret++;
	}
	return $ret;
}

/**
 * returns an empty person
 */
function getPersonDummy() {
	global $datafields;
	$p = array();
	foreach ($datafields as $field) {
		$p[$field]="";
	}
	return $p;
}

/**
 * returns an empty authorisation 
 */
function getUserAuthDummy() {
	$datafields=array("id","user","passw","admin","scoolYear","scoolClass","facebookid");
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
	
	for ($i=0;$i<sizeof($data);$i++)
		unset( $data[$i]);			//delete old records
	
	global $dataFileName;
    if (file_exists($dataFileName)) {
		$file=fopen($dataFileName ,"r");
		$id=0;
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[0])&&isset($b[1])) {
				if(($b[0]!="")&&($b[1]!="")&&$b[0][0]!="#") {
					if ($b[0]=="id") {
						$id++;
						$data[$id]=getPersonDummy();
					} else {
						$data[$id][$b[0]]=chop($b[1]);
					}
				}
			}
		}
		fclose($file);
	}
	else 
		echo("Error:open database ".$dataFileName);
}


/**
 * Read in the userlist
 */
function readUserAuthDB()
{
	global $dataBase;
	$data=array();
	$id=0;
	foreach($dataBase as $db) {
		$scoolYear=substr($db,3,4);
		$scoolClass=substr($db,0,3);
		$dataFileName=$scoolClass.$scoolYear."data.txt";
	    if (file_exists($dataFileName)) {
			$file=fopen($dataFileName ,"r");
			$idx=1;
			while (!feof($file)) {
				$b = explode("=",fgets($file));
				if (isset($b[0])&&isset($b[1])) {
					if(($b[0]!="")&&($b[1]!="")&&$b[0][0]!="#") {
						if ($b[0]=="id") {
							$id++;
							$data[$id]=getUserAuthDummy();
							$data[$id]["scoolYear"]=$scoolYear;
							$data[$id]["scoolClass"]=$scoolClass;
							$data[$id]["id"]=$idx++;
						} else {
							if (($b[0] == "user") || ($b[0] == "passw") || ($b[0] == "facebookid") || ($b[0] == "admin")) {
	    						$data[$id][$b[0]]=chop($b[1]);
							}
						}
					}
				}
			}
			fclose($file);
		}
		else 
			echo("Error:open database ".$dataFileName);
	}
	return $data;

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
		fwrite($file,"id=".$i++."\r\n");
		while (list($key, $val) = each($person)) {
		   fwrite($file,$key."=".$val."\r\n");
		}
	}
	fclose($file);
}

//*********************[ Vote database]**************************************

$voteFields=array("date","class","cemetery","dinner","excursion","where");
$voteData = array();
$voteFileName = "vote.txt";

function setVote($uid, $vote) {
	global $voteData;
	$voteData[$uid] = $vote;
	saveVoteData();
}

function readVoteData() {
	global $voteData;
	global $voteFileName;

	for ($i=0;$i<sizeof($voteData);$i++)
		unset( $voteData[$i]);			//delete old records

    if (file_exists($_SESSION['scoolClass'].$_SESSION['scoolYear'].$voteFileName)) {
		$file=fopen($_SESSION['scoolClass'].$_SESSION['scoolYear'].$voteFileName ,"r");
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
   return $voteData[$uid];
}

function getVoteDummy() {
	global $voteFields;
	$p = array();
	foreach ($voteFields as $field) {
		$p[$field]="";
	}
	return $p;
}

function saveVoteData() {
	global $data;
	getDataSize();
	global $voteData;
	reset( $voteData);
	global $voteFileName;
	$file=fopen($_SESSION['scoolClass'].$_SESSION['scoolYear'].$voteFileName,"w");
	fwrite($file,"#Vote Database File\r\n");
	fwrite($file,"#Last IP:".$_SERVER["REMOTE_ADDR"]."\r\n");
	fwrite($file,"#Change Date:".date('d.m.Y H:i')."\r\n\r\n");
	$i=1;
	foreach ($data as $person) {
		reset($person);
		fwrite($file,"\r\n");
		fwrite($file,"id=".$i."\r\n");
		$vote=$voteData[$i];
		while (list($key, $val) = each($vote)) {
		   fwrite($file,$key."=".$val."\r\n");
		}
		$i++;
	}
	fclose($file);
}

//*********************[ Picture database]**************************************
//each person can have more than one picture with the following attributes:
// title, comment, visibleforall, date, uploaddate, ip
// the pictures are save in a folder named like the database
// picture name: the letter p plus personid-pictureid.jpg
// the pictures are saved in a maximal resolution of 1024x1024

$PictureFields=array("visibleforall","date","title","comment","ip","uploadDate","lastIp","lastChangeDate");
$pictures = array();
$pictureFolder = "./images/";

function getPictureDummy() {
	global $PictureFields;
	$p = array();
	foreach ($PictureFields as $field) {
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
	$directory = dir($pictureFolder.$database);	
	while ($file = $directory->read()) {
		if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif","png"))) {
			if (strpos($file,$personID.'-')==1) {
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
	//reset($images_array);
	return $images_array;
}


function savePicture($database,$personID,$title, $comment, $vorAll){
	//Next id
	global $pictureFolder;
	$nextId = 0;
	$directory = dir($pictureFolder.$database);	
	while ($file = $directory->read()) {
		if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif","png"))) {
			if (strpos($file,$personID.'-')==1) {
				$nextId++;
			}			
		}
	}
	$directory->close();
	
	setPictureAttributes($database,$personID,$nextId,$title,$comment,$vorAll);
	return $nextId;
}

function loadPictureAttributes($database,$personID,$pictureId) {
	global $pictureFolder;
	$picture=getPictureDummy();
	if (!file_exists($pictureFolder.$database."/".$personID."-".$pictureId.".txt")) {
		setPictureAttributes($database,$personID,$pictureId,"","","yes");
	}
    if (file_exists($pictureFolder.$database."/".$personID."-".$pictureId.".txt")) {
		$file=fopen($pictureFolder.$database."/".$personID."-".$pictureId.".txt" ,"r");
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
	return $picture;
}

function setPictureAttributes($database,$personID,$pictureId,$title,$comment,$visibleforall) {
	global $pictureFolder;
	$file=fopen($pictureFolder.$database."/".$personID."-".$pictureId.".txt","w");
	$picture=loadPictureAttributes($database,$personID,$pictureId);
	$picture["lastChangeDate"]=date('d.m.Y H:i');
	$picture["lastIp"]=$_SERVER["REMOTE_ADDR"];
	$picture["title"]=$title;
	$picture["comment"]=$comment;
	$picture["visibleforall"]=$visibleforall;
	
	fwrite($file,"#Picture Metafile\r\n");
	while (list($key, $val) = each($picture)) {
	   fwrite($file,$key."=".$val."\r\n");
	}
	fclose($file);
}

function setPictureVisibleForAll($database,$personID,$pictureId, $vorAll){
	
}

function deletePicture($database,$personID,$pictureId) {
	
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

//*************************************************************

function getFieldValue($field) {
  if ($field=="") 
  	return "";
  if ($field[0]=="~") return substr($field,1);
  else return $field;
}

function getFieldChecked($field) {
  if ($field=="") 
  	return "";
  if ($field[0]=="~") return "checked";
  else return "";

}

/*
 * is a field content printable
 */
function showField($field) {
  if ($field=="") 
  	return false;
  if (($field[0]!="~") || ( isset($_SESSION["UID"])) && ($_SESSION["UID"]>0) ) { 
  	if (ltrim($field,"~")!="") {
  		return true;
  	} else { 
  		return false;
  	}
  }
  else return false;

}


//generate normalised person link

function getPersonLink($fn,$ln) {
   return getNormalisedChars($fn).'_'.getNormalisedChars($ln);
}

function getNormalisedChars($s) {
  return strtr($s, " âäåáàéèíîöóòõúùüÅÁÄÉÖÜ", "-aaaaaeeiioooouuuAAAEOU");
}

?>
