<?php
abstract class GameType {
    const GAME2048 = 1;
    const SUDOKU = 2;
    const SOLITAIRE = 3;
    const MAHJONG = 4;
}
const gameName = array(
    array("name"=>"","logo"=>"","icon"=>""),
    array("name"=>"2048","logo"=>"images/game2048.jpg","icon"=>"pawn"),
    array("name"=>"Sudoku","logo"=>"images/gamesudoku.jpg","icon"=>"th"),
    array("name"=>"Solitaire","logo"=>"images/gamesolitaire.jpg","icon"=>"heart"),
    array("name"=>"Mahjong","logo"=>"images/gamemahjong.jpg","icon"=>"yen")
);
