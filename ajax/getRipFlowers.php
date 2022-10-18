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
    Hamarosan örökké friss virágokkal és személyes megemlékezéssel lehet az elhunyt kedveseink emlékét feleleveníteni.
    A virágokat, megemlékezési szöveget vagy a kiemelt megjelenítést egy adománnyal lehet véglegesíteni. Az adómány kizárolag az oldal üzemeltetését biztosítsa.   
';

$html .='
    <table>
        <tr><td></td><td>adomány</td><td>rövid ideig<br/>kedvezményes ár</td><td><button onclick="alert(\'A kedvezményes ár csak rövid ideig érvényes.\n Siess ne halazd el az alkalmat.\');">i</button></td></tr>
        <tr><td>Kiemelt megjelenités</td><td>199€</td><td>99€</td><td><button onclick="alert(\'Kiemelt megjelenítés azaz nagy felbontású fénykép, nagy gyertyák és jó felbontású virágok\n Csak egyszer lehet megrendelni, és 20 év után szűnik meg. \');">i</button></td></tr>
        <tr><td>Virágcsokor</td><td> 19€</td><td>10€ </td><td><button onclick="alert(\'Virágcsokor a bejegyzés jobb alsó sarkán\n Csak egyszer lehet megrendelni 10 évenként.\');">i</button></td></tr>
        <tr><td>Kicsi virágcsokor</td><td> 15€</td><td>5€ </td><td><button onclick="alert(\'Virágcsokor a bejegyzés jobb felső sarkán\n Csak egyszer lehet megrendelni 10 évenként.\');">i</button></td></tr>
        <tr><td>Mezei virágok</td><td> 5€/db</td><td>1€/db</td><td><button onclick="alert(\'Virágcsokor a bejegyzés bal felső sarkán\n Háromszor lehet megrendelni 5 évenként.\');">i</button></td></tr>
        <tr><td>Piros rózsa</td><td>1€/db</td><td>díjmentes</td><td><button onclick="alert(\'Virág a bejegyzés alsó részén\n Négyszer lehet megrendelni 3 évenként.\');">i</button></td></tr>
        <tr><td>Rózsaszinü rózsa</td><td> 1€/db</td><td>díjmentes</td><td><button onclick="alert(\'Virág a bejegyzés felső részén\n Hatszor lehet megrendelni 3 évenként.\');">i</button></td></tr>
        <tr><td>Megemlékezés</td><td>1€</td><td>díjmentes</td><td><button onclick="alert(\'Megemlékezés, meghatározott szöveg kiválasztása vagy egyéni szöveg\nA szöveg a látogatók listáján jelenik meg.\');">i</button></td></tr>
    </table>
';

$html .='
    <button class="btn btn-warning popupbtn" onclick="alert(\'A megemlékezés és a virágok kiválasztása elökészületben. Kicsi türelmet kérünk.\n Addig is a kolozsvarivendiakok@blue-l.de címen bármilyen kivánságot teljesítünk. \');">
        <span class="glyphicon glyphicon-euro"> Kiválasztás</span>
    </button>
';

echo($html);
?>