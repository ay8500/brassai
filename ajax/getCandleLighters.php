<?php
/**
 * Returns a html code containing the candle lighters. One candle is always lighted by the system
 * test local call https://localhost/brassai/ajax/getCandleLighters.php?id=608
 *
 */

include_once __DIR__ . '/../lpfw/sessionManager.php';
include_once __DIR__ . '/../lpfw/userManager.php';
include_once __DIR__ . '/../lpfw/ltools.php';
include_once __DIR__ . '/../lpfw/appl.class.php';
include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';

use maierlabs\lpfw\Appl as appl;
$dbCandle= new dbDaCandle($db);
$id=getIntParam("id");

$candles=$dbCandle->getCandleDetailByPersonId($id);
$sum =$dbCandle->getCandlesByPersonId($id);
if ($sum>0) {
    $html = $sum . appl::_text(" gyertya ég, meggyújtották:") . "<br/>";
    //Candles lighted by users
    foreach ($candles as $candle) {
        $html .= '<div class="person-candle">';
        if (isset($candle["userID"]))
            $html .= getPersonLinkAndPicture($db->getPersonById($candle["userID"]));
        else
            $html .= appl::_text('Anonim felhasználó');
        $html .= '<span style="float:right">' . appl::dateAsStr($candle["lightedDate"]) . '</span>';
        if (userIsAdmin()) {
            $html .= '<span title="' . $candle["ip"] . '" onclick="showip(' . "'" . $candle["ip"] . "'" . ')"> IP</span>';
        }
        $html .= '</div>';
    }
    //The candle lighted by the system
    $html .= '<div class="person-candle">';
    $html .= appl::_text('Anonim felhasználó');
    $html .= '<span style="float:right">' . appl::dateAsStr(new DateTime()) . '</span>';
    $html .= '</div>';


    echo($html);
}
?>