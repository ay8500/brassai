<?php
include_once __DIR__ . '/../tools/sessionManager.php';
include_once __DIR__ . '/../tools/userManager.php';
include_once __DIR__ . '/../tools/ltools.php';
include_once __DIR__ . '/../tools/appl.class.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';

$dbCandle= new dbDaCandle($db);
$id=getIntParam("id");

	$candles=$dbCandle->getCandleDetailByPersonId($id);
	$sum =$dbCandle->getCandlesByPersonId($id);
	$html =$sum." gyertya ég, meggyújtották: <br/>";	
	foreach ($candles as $candle) {
		$html .='<div class="person-candle">';
		if (isset($candle["userID"]))
			$html.= getPersonLinkAndPicture($db->getPersonById($candle["userID"]));
		else 
			$html.='Anonim felhasználó';
		$html.='<span style="float:right">'.maierlabs\lpfw\Appl::dateAsStr($candle["lightedDate"]).'</span>';
		if (userIsAdmin()) {
			$html.='<span title="'.$candle["ip"].'" onclick="showip('."'".$candle["ip"]."'".')"> IP</span>';
		}
		$html.='</div>';
	}
	$html .='<div class="person-candle">';
	$html.='Anonim felhasználó';
		$html.='<span style="float:right">'.maierlabs\lpfw\Appl::dateAsStr(new DateTime()).'</span>';
	$html.='</div>';


echo($html);
?>