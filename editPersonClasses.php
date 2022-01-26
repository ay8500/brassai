<?php
global $db,$diak;
use maierlabs\lpfw\Appl as Appl;

$classes=$db->getClassListByTeacherID($diak["id"]);
$mainClasses=$db->getClassListByHeadTeacherID($diak["id"]);

if ($diak["gender"]=="m") {
    $teacher ="Tanár";
    $headTeacher ="Osztályfőnök";
} elseif ($diak["gender"]=="f") {
    $teacher ="Tanárnő";
    $headTeacher ="Osztályfőnöknő";
} else {
    $teacher ="Tanár";
    $headTeacher ="Osztályfőnök";
}

Appl::addCssStyle('
    #tclass {
        border-width: 1px;
        margin: 10px;
        display: inline-block;
        background-color: white;
        padding: 8px;
        border-radius: 10px;
        box-shadow: 4px 4px 15px black;
    }
');

?>


<div class="well">
    <div class="panel panel-heading"><h5>Ezek az osztályok voltak a tanítványai</h5></div>
    <?php
    if ($classes==null && $mainClasses==null) {
        Appl::setMessage("Nincsenek osztályok bejelölve.","warning");
    } else {
        displayClassList($db,$mainClasses,$headTeacher);
        displayClassList($db,$classes,$teacher);
    }
    ?>
</div>

<?php
function displayClassList($db,$classList,$teacher){
    if (sizeof($classList)>0) {
        echo("<div>".$teacher."</div>");
        foreach ($classList as $c) {
            echo('<div id="tclass"><a href="hometable?classid='.$c["id"].'"> ');
            $pictureId = $db->getGroupPictureIdByClassId($c["id"]);
            if ($pictureId >0) {
                echo('<img src="imageConvert?width=300&thumb=false&id='. $pictureId . '" style="height: 70px;"/>');
            }
            echo('<br/>'.$c["graduationYear"].' '.$c["name"] .' '.($c["eveningClass"]==1?"esti tagozat":""));
            echo('</a></div>');
        }
    }
}