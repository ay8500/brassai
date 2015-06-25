<?php


function readHistoryList($elements) {
	$arType =array ("Személyes adatait módosította.",
					"Új képet töltött fel.",
					"Életrajzát kiegészítette.",
					"Diákkori történettel bővítette adatait.",
					"Megosztotta mit csinál szabadidejében.",
					"Facebook felhasználó kapcsolatot létesített.");
	$h=readHistory($elements);
	$ret="";
	foreach ($h as $history) {
		$ret .= "<li>";
		$ret .= $history["scoolyear"]." ";
		$ret .= $history["scoolclass"]." ";
		if ($history["type"]<=4)
		   $tabopen="&tabOpen=".$history["type"];
		else 
			$tabopen ="";
		$ret .= '<a href="editdiak.php?uid='.$history["uid"].'&scoolYear='.$history["scoolyear"].'&scoolClass='.$history["scoolclass"].$tabopen.'" >';
		$diak = getPerson($history["uid"],$history["scoolclass"].$history["scoolyear"]);
		$ret .= $diak["lastname"]." ".$diak["firstname"]." ";
		if (isset($diak["birthname"]))
			$ret .=$diak["birthname"]." ";
		$ret .='</a>';
		$ret .= $arType[$history["type"]];
		$ret .= "</li>"."\r\n";
	}
	return $ret;
}

function readHistory($elements) {
	$ret = array();
	$file=fopen("data/history.json","r");
	while (!feof($file) && $elements-->0) {
		$b = fgets($file);
		$json = json_decode($b,true);
		array_push($ret, $json);	
	}
	return $ret;
}

function checkNewHistory($type) {
	$h =readHistory(10);
	foreach ($h as $history) {
		if ($history["ip"]==$_SERVER["REMOTE_ADDR"] &&
			substr($history["date"],0,10)==date('d.m.Y') &&
			$history["type"]==$type )
			return false;
	}
	return true;
}

/**
 * type: 0 Personal data, 1-pictures, 2,3,4-Stroy, 4 Facebook
 * Enter description here ...
 * @param unknown_type $type
 */
function writeHistory($type) {
	if (checkNewHistory($type)) {
		$history = array();
		$history["ip"]=$_SERVER["REMOTE_ADDR"];
		$history["date"]=date('d.m.Y H:i');
		$history["type"]=$type;
		$history["uid"]=getAktUserId();
		$history["scoolyear"]=getAktScoolYear();
		$history["scoolclass"]=getAKtScoolClass();
		prepend($histroy);
	}
}

function prepend($histroy) {
  $string=json_encode($histroy)."\r\n";
  $context = stream_context_create();
  $filename="data/history.json";
  $fp = fopen($filename, 'r', 1, $context);
  $tmpname = "data/".md5($string);
  file_put_contents($tmpname, $string);
  file_put_contents($tmpname, $fp, FILE_APPEND);
  fclose($fp);
  unlink($filename);
  rename($tmpname, $filename);
}

?>