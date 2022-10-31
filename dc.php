<?php
if (strpos(strtoupper($_SERVER["REQUEST_URI"]),"JPG")!==false)
    die();

include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'config.class.php';
include_once 'dbBL.class.php';

use \maierlabs\lpfw\Appl as Appl;

$su= explode("?",$_SERVER["REQUEST_URI"]);

$su = explode("/",$su[0]);

$su = explode("-",$su[sizeof($su)-1]);

//print_r ($su[sizeof($su)-1]);
//die();

$id=$su[sizeof($su)-1];

if (null!=$id && $id!=='' && $id!==0 && ctype_digit($id)) {
    global $db;
	$diak=$db->getPersonByID($id);
	if ($diak==null) {
		pageNotFound();
		exit;
    } else if (sizeof($su)>2 && "megemlekezes"==$su[sizeof($su)-2]) {
        if (!isset($diak["deceasedYear"]) || $diak["deceasedYear"]=="") {
            pageNotFound();
            exit();
        }

        header("HTTP/1.1 200 OK");
        setActUserId($diak["id"]);
        include 'dbDaCandle.class.php';
        include 'rip.inc.php';
        unsetActClass();unsetActSchool();
        $personList=array($diak);
        showHeader($diak);
        echo("<h2 style='color:orange'>Megemlékezés</h2>");
        displayRipPerson($db,$diak,$db->getClassById($diak["classID"]),true,true);
        showFooter();
        exit();
	} else {
        header("HTTP/1.1 200 OK");
		setActUserId($diak["id"]);
		include ("editDiak.php");
        echo($diak["lastname"]);
		exit();
	}
}
pageNotFound();


function pageNotFound() {
	header("status: 404"); 
	Appl::addCss('http://fonts.googleapis.com/css?family=Satisfy');
	unsetActSchool();unsetActClass();
	global $db;
	include("homemenu.inc.php");
	?>
	<h2 class="sub_title">Sajnos ez az oldal nem létezik ezen a szerveren.</h2>
	<div style="background-image: url('images/kretatabla.jpg');background-size: cover;height: 600px;margin: 20px;border-radius: 30px;">	
		<div style="font-family: Satisfy;text-align: center;vertical-align:middle;color:white;padding-top:100px">
			<h1>Sajnos ez az oldal nem létezik ezen a szerveren.</h1>
			<h2>Keresett oldal: <?php echo $_SERVER["REQUEST_URI"]?></h2>
		</div>
	</div>
	<?php
	include("homefooter.inc.php");
}

function showHeader($person) {?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Véndiák megemlékezés <?php echo getPersonName($person)?></title>
    <?PHP if (strstr(getenv("QUERY_STRING"),"=thumbnails")!="") { ?>
        <meta name="robots" content="noindex,follow" />
    <?PHP } else { ?>
        <meta name="robots" content="index,follow" />
    <?PHP } ?>
    <meta name="geo.placename" content="Kolozsvár" />
    <meta name="geo.position" content="46.771919;23.592248" />
    <meta name="author" content="Levente Maier" />
    <meta name="description" content="Véndiák megemlékezés <?php echo getPersonName($person)?>" />
    <meta name="keywords" content="Véndiák megemlékezés <?php echo getPersonName($person)?>" />
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php Appl::addCss("css/bootstrap.min.css");?>
    <?php Appl::addCss("css/menu.css");?>
    <?php Appl::addJs("js/candles.js",true);?>
    <link rel="canonical" href="<?php echo(Config::$siteUrl.$_SERVER['REQUEST_URI']) ?>">
    <?php Appl::includeCss();?>
    <?php Appl::renderingStarted(); ?>
</head>
<body style="background-color: black; text-align: center">
    <img id="firstPicture" style="display:none" src="images/<?php echo $person["picture"]?>" />
    <div style="margin-top: 30px;">
<?php
}

function showFooter() {
    ?>
        <div style="width:100%;text-align:center;color:orange;position: absolute;bottom: 0px;">&copy; <?php echo date("Y");?><a style="color: orange; text-decoration: none" href="https://kolozsvarivendiakok.blue-l.de" target="_blank"> kolozsvarivendiakok.blue-l.de</a></div>
    </html>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <?php
    Appl::setApplJScript();
    Appl::includeJs();
}

