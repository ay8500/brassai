<?php

include_once 'dbBL.class.php';
include_once 'dbDaUser.class.php';
include_once Config::$lpfw.'logon.inc.php';

global $db;
handleLogInOff(new dbDaUser($db));
?>