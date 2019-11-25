<?php
include_once '../config.class.php';
include_once  Config::$lpfw.'ltools.php';
//TODO Save user information in the DB

session_start();
$_SESSION["timeZone"]=getIntParam("timezone",0);

if ( !isset($_SESSION['lastReq']) ) {
    http_response_code(401);
    $_SESSION["timeout"]=$date->format("Y-m-d H:i:s");
    echo("No session!");
    exit;
} else {
    $date=DateTime::createFromFormat('d.m.Y H:i',$_SESSION['lastReq']);
    $dateNew = new DateTime();
    $interval=date_diff($date,$dateNew);
    $minutes=$interval->format("%i");
    if ($minutes>=60) {
        $_SESSION["timeout"]=$date->format("Y-m-d H:i:s");
        http_response_code(401);
        echo("Session to old!");
        exit;
    }
    echo('Now:'.$dateNew->format("Y-m-d H:i:s"));
    echo('<br/>');
    echo('LastRequest:'.$date->format("Y-m-d H:i:s"));
    echo('<br/>');
    echo('Minutes:'.$minutes);
}
