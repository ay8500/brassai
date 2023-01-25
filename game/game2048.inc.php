<?php
//2048 https://codepen.io/camsong/pen/wcKrg
//3d akció https://codepen.io/MillerTime/pen/BexBbE
//két pont https://codepen.io/MyXoToD/pen/vznLu
//vizuális memoria https://codepen.io/FCCJMC/pen/WxEYAA
//torony https://codepen.io/ste-vg/pen/ppLQNW
//flight https://codepen.io/raurir/pen/oXmEPM
//Solitär https://codepen.io/bfa/pen/ggGYeE  oder https://codepen.io/Mobius1/pen/PpJPKE
//Fill all cells https://codepen.io/matteobruni/pen/zYqavva
?>
<div class="container">
    <div class="heading">
        <h1 class="title">2048</h1>
        <div class="score-container">0</div>
    </div>
    <p class="game-intro">Csatolj össze azonos számokat, és érj el <strong>2048-at</strong></p>

    <div class="slideup" slide="0" ><span class="glyphicon glyphicon-arrow-up" slide="0"></span></div>
    <div class="slideleft" slide="3"><span class="glyphicon glyphicon-arrow-left" slide="3"></span></div>
    <div class="game-container" style="display: inline-block">
        <div class="game-message">
            <p></p>
            <div class="lower">
                <a class="retry-button">Probáld újból</a>
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
        <div class="tile-container"></div>
    </div>
    <div class="slideright" slide="1"><span class="glyphicon glyphicon-arrow-right" slide="1"></span></div>
    <div class="slidedown" slide="2"><span class="glyphicon glyphicon-arrow-down" slide="2"></span></div>

    <p class="game-explanation">
        <strong class="important">Hogyan kell játszani:</strong> A nyílbillentyűkkel vagy kattintással a barna nyílakra told az összes lapkákat egy irányba. Amikor két azonos számú lapka érintkezik, egybeolvadnak és értékük összeadodik! A játék célja egy lapkát minimum 2048-al elérni. A játéknak vége ha nincs több lehetőség új lapkákat beilleszteni a játékba.
    </p>
</div>