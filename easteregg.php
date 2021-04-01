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

$id = encrypt_decrypt("decrypt",getParam("key"));
$me = $db->getPersonByID($id);
directLogin($userDB,getParam("key"))

?>

<p>&nbsp;</p>
<div style="text-align: center">
    <?php if ($person!=null && $me!=null) {?>
        <h3>Kedves <?php echo getPersonName($me)?>,</h3>
        <h3>köszönjük szépen <?php echo getPersonName($person)?> nevében a piros tojást.</h3>

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
