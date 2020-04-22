<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';

use \maierlabs\lpfw\Appl as Appl;

Appl::setSiteTitle("Szünet játékok");
Appl::setSiteSubTitle('Szünet játékok');

include 'homemenu.inc.php';
?>
<div class="container-fluid">
    <div class="above-game">
        <p class="game-intro">Join the numbers and get to the <strong>2048 tile!</strong></p>
        <a class="restart-button">New Game</a>
    </div>

    <div class="game-container">
        <div class="game-message">
            <p></p>
            <div class="lower">
                <a class="keep-playing-button">Keep going</a>
                <a class="retry-button">Try again</a>
            </div>
        </div>

        <div class="grid-container">
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
        </div>

        <div class="tile-container">

        </div>
    </div>

    <p class="game-explanation">
        <strong class="important">How to play:</strong> Use your <strong>arrow keys</strong> to move the tiles. When two tiles with the same number touch, they <strong>merge into one!</strong>
    </p>
    <hr>
    <p>
        <strong class="important">Note:</strong> This site is the official version of 2048. You can play it on your phone via <a href="http://git.io/2048">http://git.io/2048.</a> All other apps or sites are derivatives or fakes, and should be used with caution.
    </p>
    <hr>
    <p>
        Created by <a href="http://gabrielecirulli.com" target="_blank">Gabriele Cirulli.</a> Based on <a href="https://itunes.apple.com/us/app/1024!/id823499224" target="_blank">1024 by Veewo Studio</a> and conceptually similar to <a href="http://asherv.com/threes/" target="_blank">Threes by Asher Vollmer.</a>
    </p>
</div>
    </div>
    <script src="js/game2048/bind_polyfill.js"></script>
    <script src="js/game2048/classlist_polyfill.js"></script>
    <script src="js/game2048/animframe_polyfill.js"></script>
    <script src="js/game2048/keyboard_input_manager.js"></script>
    <script src="js/game2048/html_actuator.js"></script>
    <script src="js/game2048/grid.js"></script>
    <script src="js/game2048/tile.js"></script>
    <script src="js/game2048/local_storage_manager.js"></script>
    <script src="js/game2048/game_manager.js"></script>
    <script src="js/game2048/application.js"></script>

<?php
include 'homefooter.inc.php';


