<?php
/**
 * Returns a html code containing buttons to light a candle.
 * test local call https://localhost/brassai/ajax/getLightCandle?id=608
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
$person = $db->getPersonByID($id);
$html ="";

$html .='
    <button class="btn btn-warning popupclose" onclick="hideLightCandle('.$id.');">
        <span class="glyphicon glyphicon-remove-circle"></span>
    </button>
';

if (($daysLeft=$dbCandle->checkLightning($person["id"],getLoggedInUserId()))!==false) {
    $html .='
        Az általad meggyújtott<br/> gyertya még <?php echo($daysLeft)?> napig ég!
        <div style="clear: both"></div><br/>
        Látogass el újból és gyújts új gyertyát elhunyt tanárod vagy osztálytársad emlékére!
    ';
} else {
    $html .='
        <h3>Meggyújtok egy gyertyát!</h3>
        <div style="clear: both"></div><br/>
    ';
    if (!isUserLoggedOn()){
        $html .='
            A gyertya 2 hónapig fog égni, látogass majd megint el, és gyújtsd meg újból.<br/><br/>
            Jelentkezz be, ha szeretnéd hogy gyertyáid <b>6</b> hónapot égjenek.
        ';
    } else {
        $html .='
            A gyertya 6 hónapig fog égni, látogass majd megint el, és gyújtsd meg újból.<br/><br/>
            <button class="btn btn-warning" style="margin:10px;color:black" onclick="lightCandle('.$id.',false);hideLightCandle('.$id.');">
                <img style="height: 25px;border-radius: 33px;" src="images/match.jpg" alt="Meggyújt"/> Meggyújtom nevem alatt
            </button>
        ';
    }
    $html .='
        <button class="btn btn-warning" style="margin:10px;color:black" onclick="lightCandle('.$id.',true);hideLightCandle('.$id.');">
            <img style="height: 25px;border-radius: 33px;" src="images/match.jpg" alt="Meggyújt"/> Meggyújtom mint anonim
        </button>
    ';

}




echo($html);
?>