<?php

include_once("tools/sessionManager.php");
include_once("data.php");
include_once("tools/ltools.php");


$id=getIntParam("id");

	$candles=$db->getCandleDetailByPersonId($id);
	$html ="";	
	foreach ($candles as $candle) {
		$html .='<div class="person-candle">';
		if (isset($candle["userID"]))
			$html.= getPersonLinkAndPicture($db->getPersonById($candle["userID"]));
		else 
			$html.='anonim látogató';
		$html.='<span style="float:right">'.date("Y.m.d",strtotime($candle["lightedDate"])).'</span>';
		if (userIsAdmin()) {
			$html.='<span title="'.$candle["ip"].'"> IP</span>';
		}
		$html.='</div>';
	}
	$html .='<div class="person-candle">';
	$html.='anonim látogató';
		$html.='<span style="float:right">'.date("Y.m.d").'</span>';
	$html.='</div>';


echo($html);
?>