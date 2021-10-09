<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';
include_once Config::$lpfw.'logger.class.php';

if (isActionParam("phpunit_logoff")) {
    logoutUser();
    echo(json_encode($_SESSION));
    return;
};

if (isActionParam("phpunit_logon")) {
    $_SESSION['uRole']='admin';
    $_SESSION['uName']='phptest';
    $_SESSION['uId']=98989898;
    $_SESSION['actSchool'] =1;

    if (getParam("session")!=null) {
        $jsonSession = json_decode(html_entity_decode(getParam("session")),true);
        foreach ($jsonSession as $index => $value) {
            $_SESSION[$index] = $value;
        }
    }

    echo(json_encode($_SESSION));
    return;
};

if ( !isUserSuperuser() && !isUserAdmin() ) {
    header("HTTP/1.0 401 Not authorized");
    echo("Not authorized");
    die();
}
