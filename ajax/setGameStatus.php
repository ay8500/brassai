<?php
/**
 * Save Game Status if a game is over or won an new game will be created
 * Parameter:
 * gameid Integer
 * gamestatus JsonString important fields are won and over
 */
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
    header("HTTP/1.0 404 Game not found");
    die("Game not found");
}
$gamestatus = json_decode($game["gameStatusJson"],true);

//Only running games are updated
if ($gamestatus["over"]==false &&  $gamestatus["won"]==false) {
    if ($dbGame->saveGame(getIntParam("gameid"), htmlspecialchars_decode(getParam("gamestatus")))) {
        $gamestatus = json_decode(htmlspecialchars_decode(getParam("gamestatus")), true);
        if ($gamestatus["over"] || $gamestatus["won"]) {
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $agent = $_SERVER['HTTP_USER_AGENT'];
            $ip = $_SERVER['REMOTE_ADDR'];
            $gameId = $dbGame->createGame($game["userId"], $ip, $agent, $lang, $game["gameId"]);
            $game = $dbGame->getGameById($gameId);
        }
    } else {
        header("HTTP/1.0 500 Internal Server Error");
        die("Save Error");
    }
    echo json_encode($game);
}

?>
