<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once "displayCards.inc.php";
include_once "editPersonDataHelper.php";
include_once "dbBL.class.php";
include_once "dbDaOpinion.class.php";

use \maierlabs\lpfw\Appl as Appl;
global $db;

Appl::setSiteTitle("Mit tud rólam az oldal?");
Appl::setSiteSubTitle("Az összes infomációk az adatbankból.");
unsetActSchool();unsetActClass();
include("homemenu.inc.php");

$key = getParam("allDataKey");
$keyStr = encrypt_decrypt("decrypt", $key);
$keyElements = explode("-",$keyStr);
if (sizeof($keyElements)==3) {
    $action=$keyElements[0];
    $dayKey=$keyElements[1];
    $dayNow = round( (time()-strtotime("2021-12-12"))/ (60*60*24));
    $keyStr=$keyElements[2];
}
$person=$db->getPersonByID($keyStr);
if (null==$person || $action!="D" || $dayKey-$dayNow+7 < 0) {
    Appl::setMessage("Kód nem érvényes vagy lejárt","danger");
    include("homefooter.inc.php");
    die();
}

?>

<div style="text-align: left;margin: 20px;">
    A kód még érvényes <?php echo $dayKey-$dayNow+7 ?> napig.
    <?php
        $diak = $person;
        $role = $_SESSION['uRole'];
        $userName = $_SESSION['uName'];
        $userId = $_SESSION['uId'];
        unset($_SESSION['uRole']);
        unset($_SESSION['uName']);
        unset($_SESSION['uId']);
        Appl::_('<h3>Igy látják a személyes kártyámat nem bejelentkezett felhasználók</h3>');
        setPersonFields($person,false,false);
        $showAllPersonalData = true;
        $multipleInclude = true;
        include "editPersonData.php";

        $_SESSION['uRole'] = "";
        $_SESSION['uName'] = "User";
        $_SESSION['uId'] = "12";
        Appl::_('<h3>Igy látják a személyes kártyámat bejelentkezett felhasználók</h3>');
        setPersonFields($person,false,false);
        include "editPersonData.php";

        $_SESSION['uRole'] = "admin";
        $_SESSION['uName'] = "User";
        $_SESSION['uId'] = "12";
        Appl::_('<h3>Igy látják a személyes kártyámat adminisztrátorok</h3>');
        setPersonFields($person,true,true);
        include "editPersonData.php";


    ?>
</div>
<div style="text-align: left;margin: 20px;" class="table table-hover">
    <h3> Vélemények </h3>
    <?php
        $opinions = $db->dataBase->queryArray("SELECT  * FROM opinion where changeUserID = ".$person["id"]);
    if (sizeof($opinions)==0) {
        Appl::_("Jelenleg még nem adtál véleményeket ezen az oldalon!");
    } else {
        showLine("Tárgy", "Név", "Szöveg", "Vélemény", "Dátum", "IP");
        foreach ($opinions as $opinion) {
            if ($opinion["table"] == "person") {
                $pers = $db->getPersonByID($opinion["entryID"]);
                showLine("Személy", getPersonName($pers), $opinion["text"], $opinion["opinion"], $opinion["changeDate"], $opinion["changeIP"]);
            }
            if ($opinion["table"] == "picture") {
                $pict = $db->getPictureById($opinion["entryID"]);
                showLine("Kép", isset($pict["name"]) ? $pict["name"] : "", $opinion["text"], $opinion["opinion"], $opinion["changeDate"], $opinion["changeIP"]);
            }
            if ($opinion["table"] == "music") {
                $song = $db->dataBase->querySignleRow("select * from song where id=".$opinion["entryID"]);
                showLine("Zene", $song["name"], $opinion["text"], $opinion["opinion"], $opinion["changeDate"], $opinion["changeIP"]);
            }
            if ($opinion["table"] == "message") {
                showLine("Üzenet:", "", $opinion["text"], $opinion["opinion"], $opinion["changeDate"], $opinion["changeIP"]);
            }
        }
    }
    ?>
</div>

    <div style="text-align: left;margin: 20px;">
        <h3> Meggyújtott gyertyák </h3>
        <?php
        $candles = $db->dataBase->queryArray("select * from candle where userID=".$person["id"]);
        if (sizeof($candles)==0) {
            Appl::_("Jelenleg még nem gyújtottál gyertyákat ezen az oldalon!");

        } else {
            showLine("", "Név", "Szöveg", "", "Dátum", "IP");
            $nr = 1;
            foreach ($candles as $candle) {
                $pers = $db->getPersonByID($candle["personID"]);
                showLine($nr++,getPersonName($pers),"","meggyújtva",$candle["lightedDate"],$candle["ip"]);
            }
        }
        ?>
    </div>

    <div style="text-align: left;margin: 20px;">
        <h3> Játékok </h3>
        <?php
        $games = $db->dataBase->queryArray("select * from game where userId=".$person["id"]);
        $gameArray = array("2048","Sudoku","Solitaire","Mahjong");
        if (sizeof($games)==0) {
            Appl::_("Jelenleg még nem játszodtál játékokat ezen az oldalon!");
        } else {
            showLine("Tárgy", "Név", "Szöveg", "Megkezdve", "Abbahagyva", "IP");
            foreach ($games as $game) {
                showLine("Játék",$gameArray[$game["gameId"]]," Pontok:" . $game["highScore"],$game["dateBegin"] ,$game["dateEnd"] ,$game["ip"]) ;
            }
        }
        ?>
    </div>

    <div style="text-align: left;margin: 20px;">
        <h3> Látogatások </h3>
        <?php
        $sessions = $db->dataBase->queryArray("SELECT * FROM tracker where userId = ".$person["id"]." group by sessId");
        if (sizeof($sessions)==0) {
            Appl::_("Jelenleg még nem voltál ezen az oldalon!");
        } else {
            showLine("Látogatás","Dátum", "Browser", "Honnan", "", "IP");
            $nr=0;
            foreach ($sessions as $session) {
                $nr++;
                showLine($nr,$session["date"] ,$session["agent"], $session["referrer"],"",$session["ip"] );
            }
        }
        ?>
    </div>

    <div style="text-align: left;margin: 20px;">
        <h3> Módosításaid </h3>
        <?php
        $changes = $db->dataBase->queryArray("SELECT * FROM history where changeUserID=".$person["id"]." group by entryId,`table` order by changeDate desc limit 500");
        if (sizeof($changes)==0) {
            Appl::_("Jelenleg még módosítottál semmit ezen az oldalon!");
        } else {
            showLine("Modosítás","Tárgy", "Név", "Törlés", "Dátum", "IP");
            $nr=0;
            foreach ($changes as $change) {
                $nr++;
                if ($change["table"]=="person") {
                    $pers = $db->getPersonByID($change["entryID"]);
                    showLine($nr, "Személy", getPersonName($pers), $change["deleted"], $change["changeDate"], $change["changeIP"]);
                } elseif ($change["table"]=="picture") {
                    $pict = $db->getPictureById($change["entryID"]);
                    showLine($nr, "Kép", $pict["title"]." ".$pict["comment"], $change["deleted"], $change["changeDate"], $change["changeIP"]);
                } elseif ($change["table"]=="class") {
                    $cl = $db->getClassById($change["entryID"]);
                    showLine($nr, "Osztály", $cl["text"], $change["deleted"], $change["changeDate"], $change["changeIP"]);
                } else {
                    showLine($nr, $change["table"], "", $change["deleted"], $change["changeDate"], $change["changeIP"]);
                }
            }
        }
        ?>
    </div>

    <div style="text-align: left;margin: 20px;">
        <h3> Képek </h3>
        <?php
            $pictures = $db->getPictureList("personID=".$person["id"]);
            if (sizeof($pictures)==0)
                Appl::_("Jelenleg még nincsenek képeid ezen az oldalon!");
            foreach ($pictures as $picture) {
                displayPicture($db,$picture,false);
            }
        ?>
    </div>

    <div style="text-align: left;margin: 20px;">
        <h3> Megjelölések </h3>
        <?php
        $marks = $db->getPersonMarks($person["id"]);
        if (sizeof($marks)==0)
            Appl::_("Jelenleg még nincsenek képek ezen az oldalon ahol te meglennél jelölve!");
        foreach ($marks as $mark) {
            $pict = $db->getPictureById($mark["pictureID"]);
            displayPicture($db,$pict,false);
            ?>
                <span style="position: relative; bottom:-10px;right:140px;z-index: 10;height: 0px;">
                    <img style="box-shadow: 2px 2px 17px 6px black;border-radius:35px; " src="imageTaggedPerson?pictureid=<?php echo $pict["id"] ?>&personid=<?php echo $person["id"] ?>&size=120"/>
                </span>
            <?php
        }
        ?>
    </div>
<?php
//restore original user data
$_SESSION['uRole'] = $role;
$_SESSION['uName'] = $userName;
$_SESSION['uId'] = $userId;

include("homefooter.inc.php");

function showLine($text1,$text2,$text3,$text4,$text5,$text6) {
    echo('<div class="row">');
    echo('<div class="col-sm-1">'.$text1.'</div>');
    echo('<div class="col-sm-2">'.$text2.'</div>');
    echo('<div class="col-sm-3">'.$text3.'</div>');
    echo('<div class="col-sm-2">'.$text4.'</div>');
    echo('<div class="col-sm-2">'.$text5.'</div>');
    echo('<div class="col-sm-2">'.$text6.'</div>');
    echo('</div>');
}

?>