<?php
include_once __DIR__ . '/../tools/ltools.php';

session_start();
$_SESSION["timeZone"]=getIntParam("timezone",0);

if ( !isset($_SESSION['lastReq']) ) {
    http_response_code(401);
    echo("No session!");
    exit;
} else {
    $date=DateTime::createFromFormat('d.m.Y H:i',$_SESSION['lastReq']);
    $dateNew = new DateTime();
    echo('Now:'.$dateNew->format("Y-m-d H:i:s"));
    echo('<br/>');
    echo('LastRequest:'.$date->format("Y-m-d H:i:s"));
    $interval=date_diff($date,$dateNew);
    echo('<br/>');
    $minutes=$interval->format("%i");
    echo('Minutes:'.$minutes);
    if ($minutes>90) {
        session_destroy();
        http_response_code(401);
        echo("Session to old!");
        exit;
    }
}
