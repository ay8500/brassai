<?php
/**
 * Returns a html code containing the candle lighters. One candle is always lighted by the system
 * test local call https://localhost/brassai/ajax/getCandleLighters?id=608
 *
 */

include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaCandle.class.php';

include_once Config::$lpfw.'dbDaTracker.class.php';
global $db;
$trackerDb = new \maierlabs\lpfw\dbDaTracker($db->dataBase);

use maierlabs\lpfw\Appl as appl;
$dbCandle= new dbDaCandle($db);
$id=getIntParam("id");

$candles=$dbCandle->getCandleDetailByPersonId($id);
$sum =$dbCandle->getCandlesByPersonId($id);
if ($sum>0) {
    $html = $sum . appl::_text(" gyertya ég, meggyújtották:") . "<br/>";
    //The candle lighted by the system
    $html .= '<div class="person-candle">';
    $html .= appl::_text('Internet');
    $html .= '<span style="float:right">' . appl::dateAsStr(new DateTime()) . '</span>';
    $html .= '</div>';
    //Candles lighted by users
    foreach ($candles as $candle) {
        $html .= '<div class="person-candle">';
        if (isset($candle["userID"]) && intval($candle["showAsAnonymous"])==0)
            $html .= getPersonLinkAndPicture($db->getPersonById($candle["userID"]));
        else
            $html .= appl::_text('anonim látogató');
        $html .= '<span style="float:right">' . appl::dateAsStr($candle["lightedDate"]) . '</span>';
        if (userIsAdmin()) {
            $html .= '<span title="' . $candle["ip"] . '" onclick="showip(' . "'" . $candle["ip"] . "'" . ')"> IP</span>';
        }
        $html .= '</div>';
    }
    echo($html);
}
?>