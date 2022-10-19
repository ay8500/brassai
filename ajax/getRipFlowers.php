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
    <h3>Díszítsd a gyertyákat virágokkal és megemlékezéssel.</h3>
    Hamarosan örökké friss virágokkal és személyes megemlékezéssel lehet az elhunyt kedveseink emlékét feleleveníteni.
    A virágokat, megemlékezési szöveget vagy a kiemelt megjelenítést egy önkéntes adománnyal lehet véglegesíteni. Az önkéntes hozzájárulási adómány kizárolag az oldal üzemeltetését biztosítsa.
    A cikkek a megrendelő kérésére vagy neve alatt vagy mint anonim lesznek kitüntetve.   
';

$html .='
    <table class="riptable">
        <tr><td></td><td>önkéntes<br/>adomány</td><td>rövid ideig<br/>kedvezményes</td><td><button onclick="alert(\'A kedvezményes ár csak rövid ideig érvényes.\n Siess ne halazd el az alkalmat.\');">i</button></td></tr>
        <tr><td>Kiemelt megjelenités</td><td>199€</td><td>99€</td><td><button onclick="alert(\'Kiemelt megjelenítés azaz nagy felbontású fénykép, nagy gyertyák és jó felbontású virágok.\n Megrendelés 20 év után szűnik meg. \');">i</button></td></tr>
        <tr><td>Virágcsokor</td><td> 19€</td><td>10€ </td><td><button onclick="alert(\'Virágcsokor a bejegyzés jobb alsó sarkán\n Megrendelés 10 évig érvényes.\');">i</button></td></tr>
        <tr><td>Kicsi virágcsokor</td><td> 15€</td><td>5€ </td><td><button onclick="alert(\'Virágcsokor a bejegyzés jobb felső sarkán\n Megrendelés 10 évre szól.\');">i</button></td></tr>
        <tr><td>Mezei virágok</td><td> 5€/db</td><td>1€/db</td><td><button onclick="alert(\'Virágcsokor a bejegyzés bal felső sarkán\nHáromszór lehet megrendelni az 5 éves rendelést.\');">i</button></td></tr>
        <tr><td>Piros rózsa</td><td>1€/db</td><td>díjmentes</td><td><button onclick="alert(\'Virágok a bejegyzés alsó részén\n Hétszer lehet megrendelni 3 évig érvényes.\');">i</button></td></tr>
        <tr><td>Rózsaszinű rózsa</td><td> 1€/db</td><td>díjmentes</td><td><button onclick="alert(\'Virágok a bejegyzés felső részén\n Négyszer lehet megrendelni 3 évig érvényes.\');">i</button></td></tr>
        <tr><td>Megemlékezés</td><td>1€</td><td>díjmentes</td><td><button onclick="alert(\'Megemlékezés, meghatározott szöveg kiválasztása vagy egyéni szöveg\nA szöveg a látogatók listáján korlátlan ideig jelenik meg.\');">i</button></td></tr>
    </table>
';

$html .='
    <button class="btn btn-warning popupbtn" onclick="alert(\'A megemlékezés és a virágok kiválasztása elökészületben. Kicsi türelmet kérünk.\n Addig is a kolozsvarivendiakok@blue-l.de címen bármilyen kivánságot teljesítünk. \');">
        <span class="glyphicon glyphicon-euro"> Kiválasztás</span>
    </button>
';

echo($html);
?>