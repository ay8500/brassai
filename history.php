<?php


function readHistoryList($elements) {
	global $db;
	$arType =array ("Személyes adatait módosította.",
					"Új képet töltött fel.",
					"Életrajzát kiegészítette.",
					"Diákkori történettel bővítette adatait.",
					"Megosztotta mit csinál szabadidejében.",
					"Facebook felhasználó kapcsolatot létesített.");
	$pictures=$db->getRecentPictureList(10);
	$ret="";
	foreach ($pictures as $picture) {
		$ret .= "<li>";
		$ret .= '<image src="convertImg.php?hight=200&thumb=true&id='.$picture["id"].'"/><br>';
		$ret .= $picture["title"]. " - ".$picture["comment"];
		$ret .= date("d.m.Y H:i:s",strtotime($picture["uploadDate"])). " - ".$picture["changeUserID"];
		$ret .= isset($picture["schoolID"])?"School:".$picture["schoolID"]:"";
		$ret .= isset($picture["classID"])?"Class:".$picture["classID"]:"";
		$ret .= isset($picture["personID"])?"Person:".$picture["personID"]:"";
		$ret .= "</li>"."\r\n";
	}
	return $ret;
}


?>