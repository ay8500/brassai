<?php

$classes=$db->getClassListByTeacherID($diak["id"]);

\maierlabs\lpfw\Appl::addCssStyle('
#tclass {border-width: 1px;
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
    <div class="panel panel-heading"><h5>Ezek az osztályok voltak a tanítványai</h5></div><?php
    if ($classes==null) {
        \maierlabs\lpfw\Appl::setMessage("Nincsenek osztályok bejelölve.","warning");
    } else {
        foreach ($classes as $c) {
            echo('<div id="tclass"><a href="hometable.php?classid='.$c["id"].'"> ' .$c["graduationYear"].' '.$c["name"] .' '.($c["eveningClass"]==1?"esti tagozat":"").'</a></div>');
        }
    }

?>
</div>