<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'displayCards.inc.php';
include_once 'dbBL.class.php';
include_once 'dbDaUser.class.php';


use \maierlabs\lpfw\Appl as Appl;

Appl::setSiteTitle("Kellemes húsvéti ünnepeket");
Appl::setSiteSubTitle("Piros tojás küldés");
include("homemenu.inc.php");

$id = encrypt_decrypt("decrypt",getParam("id"));
$person = $db->getPersonByID($id);

$key = encrypt_decrypt("decrypt",getParam("key"));
$me = $db->getPersonByID($key);
directLogin($userDB,getParam("key"));

$dbOpinion = new dbDaOpinion($db);
$oldOpinion = $dbOpinion->getOpinion($id,"person","easteregg","changeDate > '".date("Y")."-01-01 00:00:00'");
if (sizeof($oldOpinion)==0) {
    $dbOpinion->setOpinion($id, getLoggedInUserId(), "person", "easteregg", "");
}

?>

<p>&nbsp;</p>
<div style="text-align: center">
    <?php if ($person!=null && $me!=null && sizeof($oldOpinion)==0) {?>
        <h3>Kedves <?php echo getPersonName($me)?>,</h3>
        <h3>köszönjük szépen <?php echo getPersonName($person)?> nevében a piros tojást.</h3>

    <?php } elseif ($person!=null && $me!=null && sizeof($oldOpinion)>0) {?>
        <h3>Kedves <?php echo getPersonName($me)?>,</h3>
        <h3>köszönjük szépen <?php echo getPersonName($person)?> nevében a piros tojást,</h3>
        <h3>ezt a linket csak egyszer lehet használni.</h3>
    <?php } else { ?>
        <h3>Sajnos ez az oldal nincs helyesen felhívva.</h3>
    <?php } ?>
    <h3>Kellemes húsvéti ünnepeket!</h3>
</div>

<?php
displayPerson($db,$person,true);

displayPerson($db,$me,true);

include("homefooter.inc.php");
?>
