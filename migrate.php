
<h2>Bassai classmate database migration to MySQL</h2>
<?php
session_start();
$_SESSION["uId"]="0";
include_once 'data.php';
include_once 'songdatabase.php';

$dataBase=null;
$dataFileName="";
$data=array();
$pictureFolder = "./images/";
$dataPath = "data/";

$onlyError=true;
$migrateSongs=false;
$migratePictures=false;


//***********migrate song interprets ******************************
if($migrateSongs) {
	$interprets=readInterpretList(null);
	foreach ($interprets as $i=>$interpret) {
		$interpret["id"]=-1;
		$ret=$interprets[$i]["dbid"]=$db->saveInterpret($interpret);
		echores("Interpret:".$interpret["name"], $ret);
	}
	
	$songs=readSongList(null, 0);
	foreach ($songs as $s=>$song) {
		$interpretID=-1;
		foreach ($interprets as $i=>$interpret) {
			if ($interpret["id"]==$song["interpretID"]) {
				$interpretID=$interpret["dbid"];
			}
		}
		if ($interpretID!=-1) {
			$song["id"]=-1;
			$song["interpretID"]=$interpretID;
			$ret=$songs[$s]["dbid"]=$db->saveSong($song);
			echores("Song:".$song["name"], $ret);
		}
		else 
			die ("interpret not found");
	}
	
	$interpretsdb=$db->getInterpretList();
	$songsdb=$db->getSongList();
	echores("Interprets:",sizeof($interpretsdb),1);
	echores("Songs:",sizeof($songsdb),1);
}

//*********** migrate Clases **************************************
$dblist = getDatabaseList();


$nclass=array();
$nclass["id"]=0;
$nclass["schoolID"]=1;
$nclass["graduationYear"]=0;
$nclass["name"]="Staf";
$nclass["text"]=$nclass["graduationYear"]." ".$nclass["name"];
$res=$db->saveClass($nclass);
echores("Class:".$nclass["text"],$res,1);

foreach ($dblist as $class) {
	$nclass=array();
	$nclass["id"]=-1;
	$nclass["schoolID"]=1;
	$nclass["graduationYear"]=intval(substr($class, 0,4));
	$nclass["name"]=substr($class, 5);
	$nclass["text"]=$nclass["graduationYear"]." ".$nclass["name"];
	$res=$db->saveClass($nclass);
	echores("Class:".$nclass["text"],$res);
}

$classList=$db->getClassList();
echores("Classes in the DB:",sizeof($classList),2);

//*********** migrate Persons **************************************
$rec=array();
$rec["graduationYear"]="ooo";
$rec["name"]="ooo";
$rec["id"]="0";
array_push($classList, $rec);
$rec["graduationYear"]="teac";
$rec["name"]="ooo";
$rec["id"]="0";
array_push($classList, $rec);

foreach ($classList as $class) {
	openDatabase($class["name"].$class["graduationYear"]);
	echores($class["graduationYear"].$class["name"],$class["id"],1);
	foreach ($data as $l => $d) {
		if (isset($d["admin"])) {
			$d["role"]=$d["admin"];unset($d["admin"]);}
		$d["isTeacher"]=$class["graduationYear"]=="teac"?1:0;
		if (isset($d["ip"])) {
			$d["changeIP"]=$d["ip"];unset($d["ip"]);}
		if (isset($d["date"])) {
			$d["changeDate"]=$d["date"];unset($d["date"]);}
		if (isset($d["geolen"])) unset($d["geolen"]);
		if (isset($d["scoolYear"])) unset($d["scoolYear"]);
		if (isset($d["scoolClass"])) unset($d["scoolClass"]);
		$d["classID"]=$class["id"];
		$personid=$d["id"];
		$d["id"]=-1;
		$ret = $db->savePerson($d," user='".$d["user"]."'");
		$data["$l"]["dbid"]=$ret;
		echores("Name:".$d["lastname"]." ".$d["firstname"],$ret);
		//Textfiles
		$person=$db->getPersonByUser($d["user"]);
		$privacy="class";
		$person["story"]=loadTextData($class["name"].$class["graduationYear"], $d["id"], "story");
		$person["cv"]=$text=loadTextData($class["name"].$class["graduationYear"], $d["id"], "cv");
		$person["aboutMe"]=$text=loadTextData($class["name"].$class["graduationYear"], $d["id"], "spare");
		//Pictures
		if($migratePictures) {
			$pictures = getListofPictures($class["name"].$class["graduationYear"],$personid ); 
			foreach ($pictures as $picture) {
				$p=array();
				$p["id"]=-1;
				$p["personID"]=$person["id"];
				$p["file"]=$pictureFolder.$class["name"].$class["graduationYear"].'/'.$picture["File"];
				$p["isVisibleForAll"]=$picture["visibleforall"]=="true"?1:0;
				$p["title"]=$picture["title"];
				$p["comment"]=$picture["comment"];
				$p["isDeleted"]=$picture["deleted"]=="true"?1:0;
				$ret =$db->savePicture($p," file='".$p["file"]."'");
				echores("&nbsp;&nbsp;&nbsp;&nbsp;Picture:".$picture["File"],$ret);
			}
		}
		//SongVotes
		if($migrateSongs) {
			$votes=readVoteList($class["name"].$class["graduationYear"], $personid);
			foreach ($votes as $vote) {
				if($vote["voted"]=="true") {
					$v=array();
					$v["id"]=-1;
					$v["personID"]=$person["id"];
					$v["songID"]=getNewSongId($vote["song"]["id"]);
					$ret = $db->saveSongVote($v);
					echores("&nbsp;&nbsp;&nbsp;&nbsp;Songvote:".$person["lastname"], $ret);
				}
			}
		}
	}
}

//Migrate Messages
$messages=readSiteMessages();
foreach ($messages as $message) {
	if(isset($message["uid"])) {
		$m=array();
		$m["id"]=-1;
		$m["text"]=$message["text"];
		$m["privacy"]=$message["privacy"];
		$_SESSION["uId"]=array_search($message["uid"], array_column($data, "dbid"));
		if (isset($message["deleted"]))
			$m["isDeleted"]=$message["deleted"]=="true"?1:0;
		else
			$m["isDeleted"]=0;
		$ret=$db->saveMessage($m);
		echores("Message:", $ret);
	}
}

session_destroy();

function echores($text,$ret,$newLine=0) {
	global $onlyError;
	$bb="";$be="";
	if ($newLine>0 && $onlyError==false) {
		for($i=0;$i<$newLine;$i++) {
			echo("<br/>");
		}
		$bb="<b>";$be="</b>";
	}
	if ($ret>=0 && ($onlyError==false || $newLine>0)) {
		echo("<div>".$bb.$text.$be." = ".$ret."</div>");
	}
	else if ($ret<0){
		echo('<div style="background-color:yellow">'.$bb.$text.$be." = ".$ret."</div>");
	}
}


function getNewSongId($id) {
	global $songs;
	foreach ($songs as $song) {
		if($id==$song["id"])
			return $song["dbid"];
	}
	return -1;
}
//***********The old Databas functions *****************************

/*
 * Database List
 */
function getDatabaseList() {
	global $dataBase;
	
	$dataPath = "data/";
	chdir($dataPath);
	$dataBase = array_filter(glob('*'), 'is_dir');
	chdir("..");
	
	$classes = Array();
	foreach($dataBase as $db) {
		if (strstr($db,"ooo")=="")
			array_push($classes, substr($db,3,4)." ".substr($db,0,3));
	}
	sort($classes);
	return $classes;
}

/**
 * open the specific database name = class+year eg. 12A1985
 * @return true if the database don't changed
 */
function openDatabase($name) {
	global $dataFileName;
	global $dataBase;
	$dataPath = "data/";
	
	$ret=false;
	foreach($dataBase as $db) {
		if ($name==$db) {
			$ret=true;
			$dataFileName=$dataPath.$name."/data.txt";
			ReadDB();
		}
	}
	return $ret;
}

/**
 * Read the Database into the Memory
 */
function readDB()
{
	global $data;
	global $dataFileName;
	
	while (count($data)>0) array_pop($data);  //delete old records

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
 * load text data
 * @param unknown $database
 * @param unknown $personId
 * @param unknown $type cv; story; spare
 * @return unknown
 */
function loadTextData($database, $personId, $type) {
	global $dataPath;
	$fileName =$dataPath."/".$database."/".$personId."-".$type.".txt";
	$ret=null;
	if (file_exists($fileName)) {
		$file=fopen($fileName,"r");
		while (!feof($file)) {
			$ret .= fgets($file);
		}
		fclose($file);
	}
	return $ret;
}

/* getListofPictures
 * The filen name structure: the letter "p".DataBaseRecord."-".ImgageIndex eg. p12-4.jpf
 */
function getListofPictures($database,$personID ) {
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
	return $images_array;
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

function readSiteMessages() {
	$ar_privacy = array ("class","scool","world");
	$ret = array();
	$file=fopen("data/message.json","r");
	while (!feof($file)) {
		$b = fgets($file);
		$json = json_decode($b,true);
		array_push($ret, $json);
	}
	return $ret;
}

?>

