<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
use maierlabs\lpfw\Appl as Appl;

Appl::setSiteTitle("Kolozsvári középiskolák","Kolozsvári középiskolák");
setActClass(null,null);

include("homemenu.inc.php");
?>
<div class="container-fluid">
    <h2 class="sub_title"></h2>
</div>

<?php  include("homefooter.inc.php");?>