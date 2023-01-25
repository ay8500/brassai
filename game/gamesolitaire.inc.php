<?php //https://codepen.io/ejsado/pen/XNpVmr?>
<div id="solitaire">
    <div id="menu-bar">
        <button id="new-game" onclick="SG.newGame(undefined)">új játék</button>
        <button id="undo" onclick="SG.undoMove()">visszalép</button>
        <span id="move-count">0 lépés</span>
        <span id="score">0 pontszám</span>
        <span id="time">0</span>
    </div>
    <div class="card-pile-row" id="top-row">
        <div class="card-pile" id="stock" onclick="SG.moveStockCard()" data-card-count="52"></div>
        <div class="card-pile" id="waste"></div>
        <div class="card-pile" id="blank"></div>
        <div class="card-pile" id="foundation-1"></div>
        <div class="card-pile" id="foundation-2"></div>
        <div class="card-pile" id="foundation-3"></div>
        <div class="card-pile" id="foundation-4"></div>
    </div>
    <div class="card-pile-row" id="bottom-row">
        <h1 id="victory">ügyes nyertél, pontszám 0 </h1>
        <div class="card-pile" id="tableau-1"></div>
        <div class="card-pile" id="tableau-2"></div>
        <div class="card-pile" id="tableau-3"></div>
        <div class="card-pile" id="tableau-4"></div>
        <div class="card-pile" id="tableau-5"></div>
        <div class="card-pile" id="tableau-6"></div>
        <div class="card-pile" id="tableau-7"></div>
    </div>
</div>

