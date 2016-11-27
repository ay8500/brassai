<?PHP
include_once("tools/sessionManager.php");
include_once("data.php");
$lat1=$_GET["lat1"];
$lng1=$_GET["lng1"];
$lat2=$_GET["lat2"];
$lng2=$_GET["lng2"];

$points = Array();

$i=0;
$classId=getAktClassId();
if($classId==0) {
	$classList=$db->getClassList(1);
} else {
	$classList = array($db->getClassById($classId));
}
$dummy=getPersonDummy();

foreach ($classList as $class) {
	$persons=$db->getPersonListByClassId(getRealId($class));
	foreach ($persons as $d)  {
		if (!isPersonGuest($d)) {
			if ($d["geolat"]!="" && $d["geolng"]!="" && $d["geolat"]!=$dummy["geolat"] && $d["geolng"]!=$dummy["geolng"]) {
				
				if ( userIsLoggedOn() ) {
					$xrandom=0;
					$yrandom=0;
				} else {
					$xrandom=rand(-5,5)/100;
					$yrandom=rand(-5,5)/100;
				}
					
				if ( floatval($d["geolat"])+$xrandom>floatval($lat1) && 
					 floatval($d["geolat"])+$xrandom<floatval($lat2) && 
					 floatval($d["geolng"])+$yrandom>floatval($lng1) && 
					 floatval($d["geolng"])+$yrandom<floatval($lng2) ) {
					$points[$i]["name"]=$d["lastname"]." ".$d["firstname"];
					if (showField($d, "birthname")) $points[$i]["name"] = $points[$i]["name"]." (".$d["birthname"].")";
					$points[$i]["lat"]=$d["geolat"]+$xrandom;
					$points[$i++]["lng"]=$d["geolng"]+$yrandom;
				}
			}
		}
	}
}
 
foreach( $points as $p) { 
	echo($p["lat"].':'.$p["lng"].':'.($p["name"])."|");
}
?>
