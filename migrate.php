
<h2>Database Migration to MySQL</h2>
<?php
include_once 'data.php';

$dataBase=null;
$dataFileName="";

//Clases
$dblist = getDatabaseList();

foreach ($dblist as $class) {
	$res=$db->saveClass(null, null, 1, substr($class, 5), intval(substr($class, 0,4)));
	echo(substr($class, 0,4).'-'.substr($class, 5)." res=".$res."<br/>");
}

$classList=$db->getClassList();
echo("<br/>Classes in the DB:".sizeof($classList)."<br/>");

//Persons
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
	echo($class["graduationYear"].'-'.$class["name"]."<br/>");
	foreach ($data as $l => $d) {
		$ret = $db->savePerson(null, null, $class["id"],0, $class["graduationYear"]=="teac"?1:0, $d["firstname"], $d["lastname"], $d["picture"], $d["geolat"], $d["geolng"], $d["user"], $d["passw"], $d["admin"], $d["birthname"], $d["partner"], $d["address"], $d["zipcode"], $d["place"], $d["country"], $d["phone"], $d["mobil"], $d["email"], $d["skype"], $d["education"], $d["employer"], $d["function"], $d["children"], $d["ip"], $d["facebook"], $d["homepage"], $d["facebookid"], $d["twitter"]);
		echo("Name:".$d["lastname"]." Ret:".$ret."<br/>");
		$person=$db->getPersonByUser($d["user"]);
		$privacy="class";
		$text=loadTextData($class["name"].$class["graduationYear"], $d["id"], "story");
		$ret=$db->savePersonTextData($person["id"], null, 0, "story", $privacy, $text);
		$text=loadTextData($class["name"].$class["graduationYear"], $d["id"], "cv");
		$ret=$db->savePersonTextData($person["id"], null, 0, "cv", $privacy, $text);
		$text=loadTextData($class["name"].$class["graduationYear"], $d["id"], "spare");
		$ret=$db->savePersonTextData($person["id"], null, 0, "aboutMe", $privacy, $text);
	}
}


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

?>

