<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
use maierlabs\lpfw\Appl as Appl;

Appl::setSiteTitle("Kincses Kolzsvár");
Appl::setSiteSubTitle("Kolozsvár kincsei");
include("homemenu.inc.php");
?>
<div class="well">
    <?php
        $marks = $db->getPersonMarks();
        foreach ($marks as $mark) {
            if(rand(0,100)==5)
                echo('<img src="imageTaggedPerson.php?pictureid='.$mark["pictureID"].'&personid='.$mark["personID"].'&size=120&rounded=true" />');
        }
    ?>
</div>
<?php  include("homefooter.inc.php");?>