<?php
//2048 https://codepen.io/camsong/pen/wcKrg

//két pont https://codepen.io/MyXoToD/pen/vznLu
//vizuális memoria https://codepen.io/FCCJMC/pen/WxEYAA
//torony https://codepen.io/ste-vg/pen/ppLQNW
?>
<div class="container">
    <div class="heading">
        <h1 class="title">2048</h1>
        <div class="score-container">0</div>
    </div>
    <p class="game-intro">Csatolj össze azonos számokat, és érj el <strong>2048-at</strong></p>

    <div class="game-container">
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

        <div class="tile-container">

        </div>
    </div>

    <p class="game-explanation">
        <strong class="important">HOGYAN KELL JÁTSZANI:</strong> A nyílbillentyűkkel mozgasd a lapkákat. Amikor két azonos számú lapka érintkezik, egybeolvadnak!
    </p>
</div>