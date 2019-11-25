<?php

include_once 'dbBL.class.php';
include_once 'dbDaUser.class.php';
include_once Config::$lpfw.'logon.inc.php';


handleLogInOff(new dbDaUser($db));
?>