<?PHP
include_once '../config.class.php';
include_once  Config::$lpfw. 'sessionManager.php';
include_once  Config::$lpfw. 'appl.class.php';

include_once __DIR__ . '/../dbBL.class.php';
global $db;

$lat1=$_GET["lat1"];
$lng1=$_GET["lng1"];
$lat2=$_GET["lat2"];
$lng2=$_GET["lng2"];

$points = Array();
$classId=getRealId(getAktClass());
$where="geolat is not null and geolat <>'' ";

if($classId==null) {
	$classList=$db->getClassList(getRealId(getAktSchool()),true);
	$classIdList=array();
	foreach ($classList as $c) {
		array_push($classIdList,$c["id"]);
	}
	$where .=" and classID in (".implode(",",$classIdList).")";
} else {
	$where .=" and classID=".$classId;
}


if ( isUserLoggedOn() && getAktClassId()==$db->getLoggedInUserClassId() || isUserViewer()) {
	$xrandom=0;
	$yrandom=0;
} else {
	srand(levenshtein("123.34.56.011", $_SERVER["REMOTE_ADDR"]));
	$xrandom=rand(-5,5)/100;
	$yrandom=rand(-5,5)/100;
}

$i=0;
$persons = $db->getPersonList($where);
foreach ($persons as $d)  {
	if (!isUserGuest($d)) {
		if ( floatval($d["geolat"])+$xrandom>floatval($lat1) && 
			 floatval($d["geolat"])+$xrandom<floatval($lat2) && 
			 floatval($d["geolng"])+$yrandom>floatval($lng1) && 
			 floatval($d["geolng"])+$yrandom<floatval($lng2) ) {
			$points[$i]["name"]=$d["lastname"]." ".$d["firstname"];
			if (showField($d, "birthname")) $points[$i]["name"] = $points[$i]["name"]." (".$d["birthname"].")";
				$points[$i]["lat"]=floatval($d["geolat"])+$xrandom;
				$points[$i++]["lng"]=floatval($d["geolng"])+$yrandom;
		}
	}
}
 
foreach( $points as $p) { 
	echo($p["lat"].':'.$p["lng"].':'.($p["name"])."|");
}
?>
