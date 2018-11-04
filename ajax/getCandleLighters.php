<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/userManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../dbBL.class.php';


$id=getIntParam("id");

	$candles=$db->getCandleDetailByPersonId($id);
	$sum =$db->getCandlesByPersonId($id);
	$html =$sum." gyertya ég, meggyújtották: <br/>";	
	foreach ($candles as $candle) {
		$html .='<div class="person-candle">';
		if (isset($candle["userID"]))
			$html.= getPersonLinkAndPicture($db->getPersonById($candle["userID"]));
		else 
			$html.='anonim látogató';
		$html.='<span style="float:right">'.date("Y.m.d",strtotime($candle["lightedDate"])).'</span>';
		if (userIsAdmin()) {
			$html.='<span title="'.$candle["ip"].'" onclick="showip('."'".$candle["ip"]."'".')"> IP</span>';
		}
		$html.='</div>';
	}
	$html .='<div class="person-candle">';
	$html.='anonim látogató';
		$html.='<span style="float:right">'.date("Y.m.d").'</span>';
	$html.='</div>';


echo($html);
?>