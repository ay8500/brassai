<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'ltools.php';

include_once __DIR__ . '/../dbBL.class.php';
include_once __DIR__ . '/../dbDaGames.class.php';

header('Content-Type: application/json');

global $db;
$dbGame = new dbDaGames($db);

$game = $dbGame->getGameById(getIntParam("gameid"));
if ($game==null) {
    header("HTTP/1.0 500 Internal Server Error");
    die("Game not found");
}

if ($dbGame->saveGame(getIntParam("gameid"),htmlspecialchars_decode(getParam("gamestatus")))) {
    echo json_encode($game);
} else {
    header("HTTP/1.0 500 Internal Server Error");
    die("Save Error");
}



?>
