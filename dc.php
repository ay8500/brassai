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
	} else {
        header("HTTP/1.1 200 OK");
		setAktUserId($diak["id"]);
		include ("editDiak.php");
        echo($diak["lastname"]);
		exit();
	}
}
pageNotFound();


function pageNotFound() {
	header("status: 404"); 
	Appl::addCss('http://fonts.googleapis.com/css?family=Satisfy');
	unsetAktSchool();unsetAktClass();
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
?>

