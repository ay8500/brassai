<?php //https://codepen.io/cristiancanea/pen/GhLpI ?>

<a href="#" id="sidebar-toggle">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
</a>

<div id="sudoku_title">Sudoku</div>

<div id="sudoku_menu">
    <div id="sudoku_title">Új játék</div>
    <ul>
        <li><a class="restart1" href="#">Ügyeseknek</a></li>
        <li><a class="restart2" href="#">Haladóknak</a></li>
        <li><a class="restart3" href="#">Lángeszüknek</a></li>
        <li><a class="restart4" href="#">Zseniknek</a></li>
    </ul>
</div>

<div class="gameover_container" style="display: none">
    <div class="gameover">
        Ügyes vagy, sikerült. Elért pontszám:<span id="gameover_score">0</span>
        Játszodj újból!<br/>
        <ul>
            <li><a class="restart1" href="#">Ügyeseknek</a></li>
            <li><a class="restart2" href="#">Haladóknak</a></li>
            <li><a class="restart3" href="#">Lángeszüknek</a></li>
            <li><a class="restart4" href="#">Zseniknek</a></li>
        </ul>
    </div>
</div>

<div id="sudoku_container" >
</div>