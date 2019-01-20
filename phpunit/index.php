<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 07.12.2018
 * Time: 00:05
 */
include_once 'config.class.php';
include_once 'phpunit.class.php';

$pu = new \maierlabs\phpunit\phpunit();

?>
<html>
    <header>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>
    </header>
    <body>
        <div class="container-fluid well">
            <h3>PhpUnit webinterface for: <?php echo(\maierlabs\phpunit\config::$SiteTitle)?></h3>
            <div style="position: relative;top:-13px;">&copy; MaierLabs version:<?php echo (\maierlabs\phpunit\config::$version)?></div>
            <div class="panel-body">
                <div><button class="btn btn-success" onclick="getTestFiles()">Check site for tests</button>
                <button class="btn btn-success" onclick="runAlltests()">Run all unit tests</button></div>
                <div id="filesGauge" style="display: inline-block;width: 700px; height: 400px;"></div>
                <div id="fileGauge" style="display: inline-block; width: 700px; height: 400px;"></div>
            </div>
            <div class="panel-body">
                <div>
                    Files:<span class="badge" style="background-color: green" id="fok">0</span><span class="badge" style="background-color: red" id="ferror">0</span>
                    Tests:<span class="badge" style="background-color: green" id="tok">0</span><span class="badge" style="background-color: red" id="terror">0</span>
                    Asserts:<span class="badge" style="background-color: green" id="aok">0</span><span class="badge" style="background-color: red" id="aerror">0</span>
                </div>
            </div>
            <div class="panel-body" id="console">
                <b>Console</b>
            <div>
        </div>
    </body>
</html>

<script type="text/javascript">
    <?php include "js/phpunit.js"?>
</script>