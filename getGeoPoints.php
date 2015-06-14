<?PHP
include_once("sessionManager.php");
include_once("data.php");
$lat1=$_GET["lat1"];
$lng1=$_GET["lng1"];
$lat2=$_GET["lat2"];
$lng2=$_GET["lng2"];

$points = Array();

$i=0;
foreach ($data as $d)  {
	if (!isPersonGuest($d)) {
		if (($d["geolat"]!="") && ($d["geolng"]!="")) {
			//if ( ($d["geolat"]>$lat1) && ($d["geolat"]<$lat2) && ($d["geolng"]>$lng1) && ($d["geolng"]<$lng2) ) {
				if ( userIsLoggedOn() )
					$random=0;
				else
					$random=rand(1,10)/100;
				$points[$i]["name"]=$d["lastname"]." ".$d["firstname"];
				if (showField($d, "birthname")) $points[$i]["name"] = $points[$i]["name"]." (".$d["birthname"].")";
				$points[$i]["lat"]=$d["geolat"]+$random;
				$points[$i++]["lng"]=$d["geolng"]+$random;
			//}
		}
	}
}

 
foreach( $points as $p) { 
	echo($p["lat"].':'.$p["lng"].':'.($p["name"])."|");
}
?>
