<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaGames.class.php';

use \maierlabs\lpfw\Appl as Appl;
global $db;
global $tabOpen;
$dbGames = new dbDaGames($db);

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
    array_push($tabsCaption ,array("id" => "mahjong", "caption" => 'Mahjong', "glyphicon" => "th"));
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
    \maierlabs\lpfw\Appl::addCss("game/gamesudoku.css");
elseif (getParam("tabOpen")=="memory")
    \maierlabs\lpfw\Appl::addCss("game/gamememory.css");
elseif (getParam("tabOpen")=="2048")
    \maierlabs\lpfw\Appl::addCss("game/game2048.css");
elseif (getParam("tabOpen")=="solitaire")
    \maierlabs\lpfw\Appl::addCss("game/gamesolitaire.css");
elseif (getParam("tabOpen")=="mahjong")
    \maierlabs\lpfw\Appl::addCss("game/gamemahjong.css");

include("homemenu.inc.php");
?>

<div class="container-fluid">
	<div class="panel panel-default " ><?php
    	include Config::$lpfw.'view/tabs.inc.php';?>
		<div class="panel-body">

            <?php if ($tabOpen=="bestlist") {?>
                <div class="game-box">
                    <div class="game-box-left">
                        <h2>Solitaire</h2>
                        <a class="btn btn-success" href="games?tabOpen=solitaire">Játszani szeretnék</a>
                        <img src="images/gamesolitaire.jpg" class="game-logo"/>
                    </div>
                    <div class="game-box-right">
                        <table>
                            <?php
                            $personList = $dbGames->getBestPlayers(3,15);
                            foreach ($personList as $idx=>$person) {?>
                                <tr>
                                    <td style="padding-right: 10px;padding-bottom: 5px"><?php echo $idx+1 ?></td>
                                    <td style="padding-right: 10px"><?php writePersonLinkAndPicture($person)?></td>
                                    <td style="padding-right: 10px"><?php echo \maierlabs\lpfw\Appl::dateAsStr(new DateTime($person["aktDate"])) ?></td>
                                    <td style="text-align: right"><?php echo $person["highScore"]?></td>
                                </tr>
                            <?php }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="game-box">
                    <div class="game-box-left">
                        <h2>Sudoku</h2>
                        <a class="btn btn-success" href="games?tabOpen=sudoku">Játszani szeretnék</a>
                        <img src="images/gamesudoku.jpg" class="game-logo"/>
                    </div>
                    <div class="game-box-right">
                        <table>
                            <?php
                            $personList = $dbGames->getBestPlayers(2,15);
                            foreach ($personList as $idx=>$person) {?>
                                <tr>
                                    <td style="padding-right: 10px;padding-bottom: 5px"><?php echo $idx+1 ?></td>
                                    <td style="padding-right: 10px"><?php writePersonLinkAndPicture($person)?></td>
                                    <td style="padding-right: 10px"><?php echo \maierlabs\lpfw\Appl::dateAsStr(new DateTime($person["aktDate"])) ?></td>
                                    <td style="text-align: right"><?php echo $person["highScore"]?></td>
                                </tr>
                            <?php }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="game-box">
                    <div class="game-box-left">
                        <h2>2048</h2>
                        <a class="btn btn-success" href="games?tabOpen=2048">Játszani szeretnék</a>
                        <img src="images/game2048.jpg" class="game-logo"/>
                    </div>
                    <div class="game-box-right">
                        <table>
                        <?php
                            $personList = $dbGames->getBestPlayers(1,15);
                            foreach ($personList as $idx=>$person) {?>
                                    <tr>
                                        <td style="padding-right: 10px;padding-bottom: 5px"><?php echo $idx+1 ?></td>
                                        <td style="padding-right: 10px"><?php writePersonLinkAndPicture($person)?></td>
                                        <td style="padding-right: 10px"><?php echo \maierlabs\lpfw\Appl::dateAsStr(new DateTime($person["aktDate"])) ?></td>
                                        <td style="text-align: right"><?php echo $person["highScore"]?></td>
                                        <?php /*
                                        <td style="padding-left: 10px">
                                            <?php if ($person["gameStatus"]["over"]==true || $person["gameStatus"]["won"]==true ) { ?>
                                                <a class="btn btn-success btn-xs" href="games?tabOpen=2048&gameid=1&id=<?php echo($person["theGameId"])?>">Lássam</a>
                                            <?php }?>
                                        </td>
                                        */ ?>
                                    </tr>
                            <?php }
                        ?>
                        </table>
                    </div>
                </div>
            <?php }?>

            <?php if ($tabOpen=="2048") {
                \maierlabs\lpfw\Appl::addJs("game/game2048.js");
                include_once "game/game2048.inc.php";
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
                \maierlabs\lpfw\Appl::addJsScript('var game2048=null');
                \maierlabs\lpfw\Appl::addJsScript('
                    game2048 = new GameManager(4, KeyboardInputManager, HTMLActuator,\''.json_encode($tile).'\','.$gameId.');
                ',true);
            }?>


            <?php if ($tabOpen=="solitaire") {
                \maierlabs\lpfw\Appl::addJs("game/gamesolitaire.js");
                include_once "game/gamesolitaire.inc.php";
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
                \maierlabs\lpfw\Appl::addJsScript('SG.startGame('.$gameId.",". json_encode($gameStatus).");",true);
            }?>

            <?php if ($tabOpen=="mahjong") {
                ?>
                    <div>Lehetséges párok:<span id="game-pairs"></span></div>
                    <div id="game-mahjong" style="width: 100%; height: 600px;">
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/phaser/3.19.0/phaser.min.js"></script>
                <script type="text/javascript" src="game/gamemahjong.js"></script>
                <?php
                //\maierlabs\lpfw\Appl::addJs("https://cdnjs.cloudflare.com/ajax/libs/phaser/3.19.0/phaser.min.js");
                //\maierlabs\lpfw\Appl::addJs("game/gamemahjong.js");
                Appl::addJsScript('
                    startGame({
    "layout": "test",
    "tiles": [
        {
            "active": false,
            "name": 13
        },
        {
            "active": true,
            "name": 20
        },
        {
            "active": true,
            "name": 14
        },
        {
            "active": true,
            "name": 16
        },
        {
            "active": true,
            "name": 17
        },
        {
            "active": true,
            "name": 11
        },
        {
            "active": true,
            "name": 16
        },
        {
            "active": true,
            "name": 19
        },
        {
            "active": true,
            "name": 18
        },
        {
            "active": true,
            "name": 12
        },
        {
            "active": false,
            "name": 13
        },
        {
            "active": true,
            "name": 18
        },
        {
            "active": true,
            "name": 20
        },
        {
            "active": true,
            "name": 14
        },
        {
            "active": true,
            "name": 17
        },
        {
            "active": true,
            "name": 12
        },
        {
            "active": true,
            "name": 19
        },
        {
            "active": true,
            "name": 11
        }
    ]
});
                ',true);
                ?></div><?php }?>

            <?php if ($tabOpen=="memory") {
                \maierlabs\lpfw\Appl::addJs("game/gamememory.js");
                include_once "game/gamememory.inc.php";
            }?>

            <?php if ($tabOpen=="sudoku") {
                \maierlabs\lpfw\Appl::addJs("game/gamesudoku.js");
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
                \maierlabs\lpfw\Appl::addJsScript('
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

            <?php if ($tabOpen=="user") {?>
                <div class="game-box">
                    <div  class="game-box-left">
                        <h2>Solitaire</h2>
                        <a class="btn btn-success" href="games?tabOpen=solitaire">Játszani szeretnék</a>
                        <img src="images/gamesolitaire.jpg" class="game-logo"/>
                    </div>
                    <div class="game-box-right">
                        <?php
                        $gameList = $dbGames->getGameByUseridAgentLangGameId(getLoggedInUserId(),$ip,$agent,$lang,3,25);
                        if (sizeof($gameList)>0) {
                            ?><table><?php
                            foreach ($gameList as $game) {?>
                                <tr>
                                    <td style="padding: 10px"><?php echo \maierlabs\lpfw\Appl::dateTimeAsStr(new DateTime($game["aktDate"])) ?></td>
                                    <td style="padding: 10px"><?php echo \maierlabs\lpfw\Appl::dateTimeAsIntervalStr(new DateTime($game["dateBegin"]),new DateTime($game["dateEnd"])) ?></td>
                                    <td style="text-align:right;width: 80px"><b><?php echo $game["highScore"]?></b></td>
                                    <td style="padding: 5px">
                                        <?php if (($game["gameStatus"]["over"])===true) { ?>
                                            <a class="btn btn-warning" href="games?tabOpen=solitaire&gameid=3&id=<?php echo($game["id"])?>">Eredmény</a>
                                        <?php } else { ?>
                                            <a class="btn btn-success" href="games?tabOpen=solitaire&gameid=3&&id=<?php echo($game["id"])?>">Folytatom</a>
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

                <div class="game-box">
                    <div class="game-box-left">
                        <h2>Sudoku</h2>
                        <a class="btn btn-success" href="games?tabOpen=sudoku">Játszani szeretnék</a>
                        <img src="images/gamesudoku.jpg" class="game-logo"/>
                    </div>
                    <div class="game-box-right">
                        <?php
                        $gameList = $dbGames->getGameByUseridAgentLangGameId(getLoggedInUserId(),$ip,$agent,$lang,2,25);
                        if (sizeof($gameList)>0) {
                            ?><table><?php
                            foreach ($gameList as $game) {?>
                                <tr>
                                    <td style="padding: 10px"><?php echo \maierlabs\lpfw\Appl::dateTimeAsStr(new DateTime($game["aktDate"])) ?></td>
                                    <td style="padding: 10px"><?php echo \maierlabs\lpfw\Appl::dateTimeAsIntervalStr(new DateTime($game["dateBegin"]),new DateTime($game["dateEnd"])) ?></td>
                                    <td style="text-align:right;width: 80px"><b><?php echo $game["highScore"]?></b></td>
                                    <td style="padding: 5px">
                                        <?php if (($game["gameStatus"]["over"])===true) { ?>
                                            <a class="btn btn-warning" href="games?tabOpen=sudoku&gameid=2&id=<?php echo($game["id"])?>">Eredmény</a>
                                        <?php } else { ?>
                                            <a class="btn btn-success" href="games?tabOpen=sudoku&gameid=2&&id=<?php echo($game["id"])?>">Folytatom</a>
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

                <div class="game-box">
                    <div class="game-box-left">
                        <h2>2048</h2>
                        <a class="btn btn-success" href="games?tabOpen=2048">Játszani szeretnék</a>
                        <img src="images/game2048.jpg" class="game-logo"/>
                    </div>
                    <div class="game-box-right">
                        <?php
                        $gameList = $dbGames->getGameByUseridAgentLangGameId(getLoggedInUserId(),$ip,$agent,$lang,1,25);
                        if (sizeof($gameList)>0) {
                            ?><table><?php
                            foreach ($gameList as $game) {?>
                                <tr>
                                    <td style="padding: 10px"><?php echo \maierlabs\lpfw\Appl::dateTimeAsStr(new DateTime($game["dateBegin"])) ?></td>
                                    <td style="padding: 10px"><?php echo \maierlabs\lpfw\Appl::dateTimeAsIntervalStr(new DateTime($game["dateBegin"]),new DateTime($game["dateEnd"])) ?></td>
                                    <td style="text-align:right;width: 80px"><b><?php echo $game["highScore"]?></b></td>
                                    <td style="padding: 5px">
                                        <?php if (($game["gameStatus"]["over"])===true) { ?>
                                            <a class="btn btn-warning" href="games?tabOpen=2048&gameid=1&id=<?php echo($game["id"])?>">Eredmény</a>
                                        <?php } else { ?>
                                            <a class="btn btn-success" href="games?tabOpen=2048&gameid=1&&id=<?php echo($game["id"])?>">Folytatom</a>
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
            <?php }?>

		</div>
	</div>
</div>


<?php
include("homefooter.inc.php");

function getNewSudokuGameStatus() {
    return array("fixedCellsNr"=>40,"secondsElapsed"=>0,"score"=>0,"board"=>null,"boardSolution"=>null,"boardValues"=>null,"boardNotes"=>null,"won"=>false,"over"=>false);
}

?>