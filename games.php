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

//initialise tabs
$tabsCaption = array();
array_push($tabsCaption ,array("id" => "bestlist", "caption" => 'A legjobb játékosok', "glyphicon" => "globe"));
array_push($tabsCaption ,array("id" => "2048", "caption" => '2048', "glyphicon" => ""));
array_push($tabsCaption ,array("id" => "sudoku", "caption" => 'Sudoku', "glyphicon" => ""));
array_push($tabsCaption ,array("id" => "memory", "caption" => 'Memory', "glyphicon" => ""));
if (userIsLoggedOn() || getParam("userid")!=null) {
    if (getParam("userid")!=null) {
        $pers = getPersonShortName($db->getPersonByID(getParam("userid")));
    } else {
        $pers = getPersonShortName($db->getPersonByID(getLoggedInUserId()));
    }
    array_push($tabsCaption, array("id" => "user", "caption" => $pers. " játékai", "glyphicon" => "user"));
} else {
    array_push($tabsCaption, array("id" => "user", "caption" => 'Az én anonim játékaim', "glyphicon" => "user"));
}

$lang= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$agent = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];


$title = 'Logikai játékok: '. $tabsCaption[(array_search(getParam("tabOpen","A legjobb játékosok"),array_column($tabsCaption,"id")))]["caption"];
Appl::setSiteTitle($title,$title);

\maierlabs\lpfw\Appl::addCss("game/game2048.css");
\maierlabs\lpfw\Appl::addCss("game/gamememory.css");
include("homemenu.inc.php");
?>

<div class="container-fluid">
	<div class="panel panel-default " ><?php
    	include Config::$lpfw.'view/tabs.inc.php';?>
		<div class="panel-body">

            <?php if ($tabOpen=="bestlist") {?>
                <div style="border: 1px solid black; max-width: 600px;border-radius: 5px;">
                    <div style="display: inline-block;margin-left: 25px ">
                        <h2>2048</h2>
                        <a class="btn btn-success" href="games?tabOpen=2048">Játszani szeretnék</a>
                        <br/> <img src="images/game2048.jpg" style="width: 200px;margin-left: -20px"/>
                    </div>
                    <div style="display: inline-block;vertical-align: top;margin: 10px">
                        <table>
                        <?php
                            $personList = $dbGames->getBestPlayers(1,10);
                            foreach ($personList as $idx=>$person) {?>
                                    <tr>
                                        <td style="padding-right: 20px"><?php echo $idx+1 ?></td>
                                        <td style="padding-right: 20px"><?php writePersonLinkAndPicture($person)?></td>
                                        <td style="padding-right: 20px"><?php echo \maierlabs\lpfw\Appl::dateAsStr(new DateTime($person["dateBegin"])) ?></td>
                                        <td style="text-align: right"><?php echo $person["highScore"]?></td>
                                    </tr>
                            <?php }
                        ?>
                        </table>
                    </div>
                </div>
            <? }?>

            <?php if ($tabOpen=="2048") {
                \maierlabs\lpfw\Appl::addJs("game/game2048.js");
                \maierlabs\lpfw\Appl::addJs("https://cdnjs.cloudflare.com/ajax/libs/hammer.js/1.0.6/hammer.min.js");
                include_once "game/game2048.inc.php";
                $tile = new stdClass();
                $gameId = null;
                if (getIntParam("gameid")==1 && getIntParam("id",-1)!=-1) {
                    $game = $dbGames->getGameById(getIntParam("id"));
                    $gameId = $game["id"];
                    $tile = $game["gameStatus"];
                } else {
                    $game = $dbGames->getLastActivGame(getLoggedInUserId(),$ip,$agent,$lang,1);
                    $gameId = $game["id"];
                    if  (isset($game["gameStatus"]))
                        $tile = $game["gameStatus"];
                }
                if($gameId==null) {
                    $game = $dbGames->createGame(getLoggedInUserId(),$ip,$agent,$lang,1);
                    $gameId = $game["id"];
                }
                \maierlabs\lpfw\Appl::addJsScript('
                    var manager = new GameManager(4, KeyboardInputManager, HTMLActuator,\''.json_encode($tile).'\','.$gameId.');
                ',true);
            }?>

            <?php if ($tabOpen=="memory") {
                \maierlabs\lpfw\Appl::addJs("game/gamememory.js");
                include_once "game/gamememory.inc.php";
            }?>

            <?php if ($tabOpen=="user") {?>
                <div style="border: 1px solid black;border-radius: 5px; ">
                    <div style="display: inline-block;margin-left: 10px;">
                        <h2>2048</h2>
                        <a class="btn btn-default" href="games?tabOpen=2048">Játszani szeretnék</a>
                        <br/> <img src="images/game2048.jpg" style="width: 200px"/>
                    </div>
                    <div style="display: inline-block;vertical-align: top;margin: 10px">
                        <?php
                        $gameList = $dbGames->getGameByUseridAgentLangGameId(getLoggedInUserId(),$ip,$agent,$lang,1,25);
                        if (sizeof($gameList)>0) {
                            foreach ($gameList as $game) {?>
                                <table>
                                    <tr>
                                        <td><?php echo \maierlabs\lpfw\Appl::dateTimeAsStr(new DateTime($game["dateBegin"])) ?></td>
                                        <td style="text-align:right;width: 100px"><?php echo $game["highScore"]?></td>
                                        <td style="padding: 5px">
                                            <?php if (isset($game["gameStatus"]["over"])) { ?>
                                        <?php if (($game["gameStatus"]["over"])===true) { ?>
                                            <a class="btn btn-success" href="games?tabOpen=2048&gameid=1&id=<?php echo($game["id"])?>">Végeredmény</a>
                                        <?php } else { ?>
                                            <a class="btn btn-warning" href="games?tabOpen=2048&gameid=1&&id=<?php echo($game["id"])?>">folytatom</a>
                                        <?php }  ?>
                                            <?php }  ?>
                                        </td>
                                    </tr>
                                </table>
                            <?php  }
                        } else {
                            ?>
                                Sajnos ezt a játékot még nem próbáltad ki<br/>Rajta, kattints a "Játszani szeretnék" gombra!'
                            <?php
                        } ?>
                    </div>
                </div>
            <? }?>

		</div>
	</div>
</div>

<?php include("homefooter.inc.php"); ?>
