<?php
/**
 * Returns a html code containing the flowers.
 * test local call https://localhost/brassai/ajax/getRipFlowers?id=608
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
$html ="";

$html .='
    <button class="btn btn-warning popupclose" onclick="hideFlowers('.$id.');">
        <span class="glyphicon glyphicon-remove-circle"></span>
    </button>
';

$html .='
    <h3>Díszítsd a gyertyákat virágokkal.</h3>
    Hamarosan örökké friss virákokkal és személyes megemlékezéssel lehet az elhunyt kedveseink emlékét feleleveníteni. 
';

$html .="
    <table>
        <tr><td></td><td>ár</td><td>rövid ideig<br/>leszálított ár</td></tr>
        <tr><td>Megemlékezés</td><td>1€</td><td>díjmentes</td></tr>
        <tr><td>Piros rózsa</td><td>1€/db</td><td>díjmentes</td></tr>
        <tr><td>Rózsaszinü rózsa</td><td> 1€/db</td><td>díjmentes</td></tr>
        <tr><td>Mezei virágok</td><td> 5€/db</td><td>1€/db</td></tr>
        <tr><td>Kicsi virágcsokor</td><td> 15€</td><td>5€ </td></tr>
        <tr><td>Virágcsokor</td><td> 19€</td><td>10€ </td></tr>
    </table>
";

$html .='
    <button class="btn btn-warning popupbtn" onclick="alert(\'A megemlékezés és a virágok kiválasztása elökészületben. Kicsi türelmet kérünk.\n Addig is a kolozsvarivendiakok@blue-l.de címen bármilyen kivánságot teljesítünk. \');">
        <span class="glyphicon glyphicon-euro"> Kiválasztás</span>
    </button>
';

echo($html);
?>