<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 07.12.2018
 * Time: 10:22
 */
include 'config.class.php';
include 'phpunit.class.php';


header('Content-Type: application/json');

$pu = new \phpunit\phpunit();

$testFiles=$pu->getDirContents(\phpunit\config::$startDir,\phpunit\config::$excludeFiles );

echo json_encode($testFiles);