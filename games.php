<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaGames.class.php';

use \maierlabs\lpfw\Appl as Appl;
global $db ;
global $tabOpen;
$dbGames = new dbDaGames($db);

abstract class GameType {
    const GAME2048 = 1;
    const SUDOKU = 2;
    const SOLITAIRE = 3;
    const MAHJONG = 4;
}

Appl::addCssStyle('
  .game-box {
        vertical-align: text-top; width:600px; min-height:410px;
        border: 1px solid gray;  border-radius: 5px;display: inline-block;margin: 0px 15px 15px 0px;
        -webkit-box-shadow: 5px 5px 13px 5px rgba(0,0,0,0.61); 
        box-shadow: 5px 5px 13px 5px rgba(0,0,0,0.61);
  }
  .game-box-left {display:inline-block;margin-left: 5px;text-align: center;}     
  .game-box-right {
    display:inline-block;vertical-align: top;margin: 10px;height: 400px;
    overflow-y: auto; margin-right: 25px;position: relative;float: right;
    }  
  .game-logo { width:170px;display: block;margin-top: 30px; }
');

//initialise tabs
$tabsCaption = array();
$tabsTranslate["search"] = array(".php");$tabsTranslate["replace"] = array("");
array_push($tabsCaption ,array("id" => "bestlist", "caption" => 'A legjobb játékosok', "glyphicon" => "globe"));
array_push($tabsCaption, array("id" => "solitaire", "caption" => 'Solitaire', "glyphicon" => "heart"));
array_push($tabsCaption ,array("id" => "sudoku", "caption" => 'Sudoku', "glyphicon" => "th"));
if (isUserAdmin())
    array_push($tabsCaption ,array("id" => "mahjong", "caption" => 'Mahjong', "glyphicon" => "yen"));
array_push($tabsCaption ,array("id" => "2048", "caption" => '2048', "glyphicon" => "pawn"));

//array_push($tabsCaption ,array("id" => "memory", "caption" => 'Memory', "glyphicon" => ""));
if (isUserLoggedOn() || getParam("userid")!=null) {
    if (getParam("userid")!=null) {
        $pers = getPersonShortName($db->getPersonByID(getParam("userid")));
    } else {
        $pers = getPersonShortName($db->getPersonByID(getLoggedInUserId()));
    }
    array_push($tabsCaption, array("id" => "user", "caption" => $pers. " játékai", "glyphicon" => "user"));
} else {
    array_push($tabsCaption, array("id" => "user", "caption" => 'Az én játékaim', "glyphicon" => "user"));
}

$lang= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$agent = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];


$title = 'Logikai játékok: '. $tabsCaption[(array_search(getParam("tabOpen","A legjobb játékosok"),array_column($tabsCaption,"id")))]["caption"];
Appl::setSiteTitle($title,$title);

if (getParam("tabOpen")=="sudoku")
    Appl::addCss("game/gamesudoku.css");
elseif (getParam("tabOpen")=="memory")
    Appl::addCss("game/gamememory.css");
elseif (getParam("tabOpen")=="2048")
    Appl::addCss("game/game2048.css");
elseif (getParam("tabOpen")=="solitaire")
    Appl::addCss("game/gamesolitaire.css");
elseif (getParam("tabOpen")=="mahjong")
    Appl::addCss("game/gamemahjong.css");

include("homemenu.inc.php");
?>

<div class="container-fluid">
	<div class="panel panel-default " ><?php
    	include Config::$lpfw.'view/tabs.inc.php';?>
		<div class="panel-body">

            <?php if ($tabOpen=="bestlist") {
                showBestList($dbGames,GameType::SOLITAIRE,"Solitaire","solitaire","images/gamesolitaire.jpg");
                showBestList($dbGames,GameType::SUDOKU,"Sudoku","sudoku","images/gamesudoku.jpg");
                showBestList($dbGames,GameType::MAHJONG,"Mahjong","mahjong","images/game2048.jpg");
                showBestList($dbGames,GameType::GAME2048,"2048","2048","images/game2048.jpg");
            }?>

            <?php if ($tabOpen=="2048") {
                Appl::addJs("game/game2048.js");
                include_once "game/game2048.inc.php";
                $game = getGameFromDB($dbGames, getIntParam("id",-1), GameType::GAME2048);
                /*
                $tile = new stdClass();
                $gameId = null;
                if (getIntParam("gameid")==1 && getIntParam("id",-1)!=-1) {
                    $game = $dbGames->getGameById(getIntParam("id"));
                    $gameId = $game["id"];
                    $tile = $game["gameStatus"];
                } else {
                    $game = $dbGames->getLastActivGame(getLoggedInUserId(),$ip,$agent,$lang,1);
                    if ($game!=null) {
                        $gameId = $game["id"];
                        if (isset($game["gameStatus"]))
                            $tile = $game["gameStatus"];
                    } else {
                        $gameId = $dbGames->createGame(getLoggedInUserId(),$ip,$agent,$lang,1);
                    }
                }
                */
                Appl::addJsScript('var game2048=null');
                Appl::addJsScript('
                    game2048 = new GameManager(4, KeyboardInputManager, HTMLActuator,\''.json_encode($game->gameStatus).'\','.$game->gameId.');
                ',true);
            }?>


            <?php if ($tabOpen=="solitaire") {
                Appl::addJs("game/gamesolitaire.js");
                include_once "game/gamesolitaire.inc.php";
                /*
                $gameStatus = new stdClass();
                $gameId = null;
                if (getIntParam("gameid")==3 && getIntParam("id",-1)!=-1) {
                    $game = $dbGames->getGameById(getIntParam("id"));
                    $gameId = $game["id"];
                    $gameStatus = $game["gameStatus"];
                } else {
                    $game = $dbGames->getLastActivGame(getLoggedInUserId(),$ip,$agent,$lang,3);
                    if ($game!=null) {
                        $gameId = $game["id"];
                        if (isset($game["gameStatus"]))
                            $gameStatus = $game["gameStatus"];
                    } else {
                        $gameId = $dbGames->createGame(getLoggedInUserId(),$ip,$agent,$lang,3);
                    }
                }
                */
                $game = getGameFromDB($dbGames, getIntParam("id",-1), GameType::SOLITAIRE);
                Appl::addJsScript('SG.startGame('.$game->gameId.",". json_encode($game->gameStatus).");",true);
            }?>

            <?php if ($tabOpen=="mahjong") {
                Appl::addJs("https://cdnjs.cloudflare.com/ajax/libs/phaser/3.19.0/phaser.min.js");
                Appl::addJs("game/gamemahjong.js");
                ?>
                    <div>Lehetséges párok:<span id="game-pairs"></span></div>
                    <div id="game-mahjong" style="width: 100%; height: 600px;"></div>
                    <button onclick="suffleBoard()">Új keverés!</button>
                <?php
                $gameId="undefined";
                Appl::addJsScript('startGame( '.$gameId.','. json_encode(getNewMahongGameStatus()).',true)');
            } ?>

            <?php if ($tabOpen=="memory") {
                Appl::addJs("game/gamememory.js");
                include_once "game/gamememory.inc.php";
            }?>

            <?php if ($tabOpen=="sudoku") {
                Appl::addJs("game/gamesudoku.js");
                include_once "game/gamesudoku.inc.php";
                $status =getNewSudokuGameStatus();
                $gameId = null;
                if (getIntParam("gameid")==2 && getIntParam("id",-1)!=-1) {
                    $game = $dbGames->getGameById(getIntParam("id"));
                    $gameId = $game["id"];
                    $status = $game["gameStatus"];
                    if (!isset($status["won"]))
                        $status = getNewSudokuGameStatus();
                } else {
                    $game = $dbGames->getLastActivGame(getLoggedInUserId(),$ip,$agent,$lang,2);
                    if ($game!=null) {
                        $gameId = $game["id"];
                        if (isset($game["gameStatus"]) && $game["gameStatus"]!=null)
                            $status = $game["gameStatus"];
                    } else {
                        $gameId = $dbGames->createGame(getLoggedInUserId(),$ip,$agent,$lang,2);
                    }
                }
                Appl::addJsScript('
                    gamesudoku('.$gameId.',
                                '.(isset($status["fixedCellsNr"])?$status["fixedCellsNr"]:40).',
                                '.(isset($status["secondsElapsed"])?intval($status["secondsElapsed"]):0).',
                                '.(isset($status["score"])?intval($status["score"]):0).',
                                '.(isset($status["board"])?json_encode($status["board"]):json_encode([])).',
                                '.(isset($status["boardSolution"])?json_encode($status["boardSolution"]):json_encode([])).',
                                '.(isset($status["boardValues"])?json_encode($status["boardValues"]):json_encode([])).',
                                '.json_encode(isset($status["over"])?$status["over"]:false).');
                ',true);
            }?>

            <?php if ($tabOpen=="user") {
                showUserGames($dbGames, GameType::SOLITAIRE, "Solitaire", "solitaire", "images/gamesolitaire.jpg");
                showUserGames($dbGames, GameType::SUDOKU, "Sudoku", "sudoku", "images/gamesudoku.jpg");
                showUserGames($dbGames, GameType::MAHJONG, "Mahjong", "mahjong", "images/game2048.jpg");
                showUserGames($dbGames, GameType::GAME2048, "2048", "2048", "images/game2048.jpg");
            }?>
		</div>
	</div>
</div>


<?php
include("homefooter.inc.php");

function getNewSudokuGameStatus() {
    return array("fixedCellsNr"=>40,"secondsElapsed"=>0,"score"=>0,"board"=>null,"boardSolution"=>null,"boardValues"=>null,"boardNotes"=>null,"won"=>false,"over"=>false);
}

function getNewMahongGameStatus() {
    return  array( "layout"=> "turtle",  "tiles"=> array(
            array("active" => true, "name"=> 13),
            array("active" => true, "name" => 20),
            array("active" => true, "name" => 14),
            array("active" => true, "name" => 16),
            array("active" => true, "name" => 17),
            array("active" => true, "name" => 11),
            array("active" => true, "name" => 16),
            array("active" => true, "name" => 19),
            array("active" => true, "name" => 18),
            array("active" => true, "name" => 12),
            array("active" => true, "name" => 13),
            array("active" => true, "name" => 18),
            array("active" => true, "name" => 20),
            array("active" => true, "name" => 14),
            array("active" => true, "name" => 17),
            array("active" => true, "name" => 12),
            array("active" => true, "name" => 19),
            array("active" => true, "name" => 11)
    ));
}

/**
 * Get game from database or create a new game if game not exist or gameId is null
 * @param dbDaGames $dbGames
 * @param int $gameId
 * @param int $gameTypeId
 * @return object
 */
function getGameFromDB($dbGames, $gameId, $gameTypeId) {
    $lang= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $ret = new stdClass();
    if ($gameId != -1) {
        //return a game by id
        $game = $dbGames->getGameById(getIntParam("id"));
        $ret->gameId = $game["id"];
        if (isset($game["gameStatus"]))
            $ret->gameStatus=$game["gameStatus"];
    } else {
        //return a game that is not over
        $game = $dbGames->getLastActivGame(getLoggedInUserId(), $ip, $agent, $lang, $gameTypeId);
        if ($game != null) {
            $ret->gameId = $game["id"];
            if (isset($game["gameStatus"]))
                $ret->gameStatus = $game["gameStatus"];
        } else {
            //create a new game
            $ret->gameId = $dbGames->createGame(getLoggedInUserId(), $ip, $agent, $lang, $gameTypeId);
            $ret->gameStatus = null;
        }
    }
    return $ret;
}

/**
 * Show the list of best user
 * @param dbDaGames $dbGames
 * @param GameType $gameType
 * @param string $gameName
 * @param string $tabId
 * @param string $gameLogo
 */
function showBestList($dbGames,$gameType,$gameName,$tabId,$gameLogo) {
?>
    <div class="game-box">
    <div class="game-box-left">
        <h2><?php echo $gameName ?></h2>
        <a class="btn btn-success" href="games?tabOpen=<?php echo $tabId?>">Játszani szeretnék</a>
        <img src="<?php echo $gameLogo?>" class="game-logo"/>
    </div>
    <div class="game-box-right">
        <table>
            <?php
            $personList = $dbGames->getBestPlayers($gameType,15);
            foreach ($personList as $idx=>$person) {?>
                <tr>
                    <td style="padding-right: 10px;padding-bottom: 5px"><?php echo $idx+1 ?></td>
                    <td style="padding-right: 10px"><?php writePersonLinkAndPicture($person)?></td>
                    <td style="padding-right: 10px"><?php echo Appl::dateAsStr(new DateTime($person["aktDate"])) ?></td>
                    <td style="text-align: right"><?php echo $person["highScore"]?></td>
                </tr>
            <?php }
            ?>
        </table>
    </div>
</div>
<?php
}

/**
 * Show a list of the user games based on userId or IP or UserAgent und Language
 * @param dbDaGames $dbGames
 * @param GameType $gameType
 * @param string $gameName
 * @param string $tabId
 * @param string $gameLogo
 */
function showUserGames($dbGames,$gameType,$gameName,$tabId,$gameLogo) {
    $lang= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $ip = $_SERVER['REMOTE_ADDR'];
    ?>
    <div class="game-box">
        <div  class="game-box-left">
            <h2><?php echo $gameName?></h2>
            <a class="btn btn-success" href="games?tabOpen=<?php echo $tabId?>">Játszani szeretnék</a>
            <img src="<?php echo $gameLogo?>" class="game-logo"/>
        </div>
        <div class="game-box-right">
            <?php
            $gameList = $dbGames->getGameByUseridAgentLangGameId(getLoggedInUserId(),$ip,$agent,$lang,$gameType,25);
            if (sizeof($gameList)>0) {
                ?><table><?php
                foreach ($gameList as $game) {?>
                    <tr>
                        <td style="padding: 10px"><?php echo Appl::dateTimeAsStr(new DateTime($game["aktDate"])) ?></td>
                        <td style="padding: 10px"><?php echo Appl::dateTimeAsIntervalStr(new DateTime($game["dateBegin"]),new DateTime($game["dateEnd"])) ?></td>
                        <td style="text-align:right;width: 80px"><b><?php echo $game["highScore"]?></b></td>
                        <td style="padding: 5px">
                            <?php if (($game["gameStatus"]["over"])===true) { ?>
                                <a class="btn btn-warning" href="games?tabOpen=<?php echo $tabId?>&gameid=<?php echo $gameType?>&id=<?php echo($game["id"])?>">Eredmény</a>
                            <?php } else { ?>
                                <a class="btn btn-success" href="games?tabOpen=<?php echo $tabId?>&gameid=<?php echo $gameType?>&id=<?php echo($game["id"])?>">Folytatom</a>
                            <?php }  ?>
                        </td>

                    </tr>
                <?php  }
                ?></table><?php
            } else {
                ?>
                Sajnos ezt a játékot még nem próbáltad ki<br/>Rajta, kattints a "Játszani szeretnék" gombra!'
                <?php
            } ?>
        </div>
    </div>
    <?php
}
