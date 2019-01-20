<?php
include_once __DIR__ . '/../lpfw/ltools.php';

header('Content-Type: application/json');

$ip=getParam("ip");

echo file_get_contents("http://ip-api.com/json/".$ip);




?>
