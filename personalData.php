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
if (substr($keyStr, 0,2)=="D-") {
    $action="D";
    $keyStr=substr($keyStr,2);
}
$person=$db->getPersonByID($keyStr);
if (null==$person || $action!="D") {
    Appl::setMessage("Kód nem érvényes","danger");
    include("homefooter.inc.php");
}

?>

<div style="text-align: left;margin: 20px;">
    <?php
        $diak = $person;
        $role = $_SESSION['uRole'];
        $userName = $_SESSION['uName'];
        $userId = $_SESSION['uId'];
        unset($_SESSION['uRole']);
        unset($_SESSION['uName']);
        unset($_SESSION['uId']);
        Appl::_('<h3>Igy látják a személyes Kártyámat nem bejelentkezett felhasználók</h3>');
        setPersonFields($person,false,false);
        $showAllPersonalData = true;
        $multipleInclude = true;
        include "editPersonData.php";

        $_SESSION['uRole'] = "";
        $_SESSION['uName'] = "User";
        $_SESSION['uId'] = "12";
        Appl::_('<h3>Igy látják a személyes Kártyámat bejelentkezett felhasználók</h3>');
        setPersonFields($person,false,false);
        include "editPersonData.php";

        $_SESSION['uRole'] = "admin";
        $_SESSION['uName'] = "User";
        $_SESSION['uId'] = "12";
        Appl::_('<h3>Igy látják a személyes Kártyámat adminisztrátorok</h3>');
        setPersonFields($person,true,true);
        include "editPersonData.php";


    ?>
</div>
<div style="text-align: left;margin: 20px;">
    <h3> Vélemények </h3>
    <?php
        $opinions = $db->dataBase->queryArray("SELECT  * FROM opinion where changeUserID = ".$person["id"]);
    foreach ($opinions as $opinion) {
        if ($opinion["table"]="person") {
            $pers = $db->getPersonByID($opinion["entryID"]);
            echo(getPersonName($pers)." ".$opinion["text"]."=>".$opinion["opinion"] . " Datum:".$opinion["changeDate"] ." IP:".$opinion["changeIP"]. "<br/>");
        }
        if ($opinion["table"]="picture") {
            $pict = $db->getPictureById($opinion["entryID"]);
            echo("Kép:".(isset($pict["name"])?$pict["name"]:"")."".$opinion["text"]."=>".$opinion["opinion"] . " Datum:".$opinion["changeDate"] ." IP:".$opinion["changeIP"]. "<br/>");
        }
        if ($opinion["table"]="music") {
            echo("Zene:".$opinion["text"]."=>".$opinion["opinion"] . " Datum:".$opinion["changeDate"] ." IP:".$opinion["changeIP"]. "<br/>");
        }
        if ($opinion["table"]="message") {
            echo("Üzenet:".$opinion["text"]."=>".$opinion["opinion"] . " Datum:".$opinion["changeDate"] ." IP:".$opinion["changeIP"]. "<br/>");
        }
    }
    ?>
</div>
<div style="text-align: left;margin: 20px;">
    <h3> Képek </h3>
<?php
    $pictures = $db->getPictureList("personID=".$person["id"]);
    foreach ($pictures as $picture) {
        displayPicture($db,$picture,false);
    }
?>
</div>
    <div style="text-align: left;margin: 20px;">
        <h3> Megjelölések </h3>
        <?php
        $marks = $db->getPersonMarks($person["id"]);
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

?>