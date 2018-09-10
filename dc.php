<?php
include_once 'tools/appl.class.php';

$su= explode("?",$_SERVER["REQUEST_URI"]);

$su = explode("/",$su[0]);

$su = explode("-",$su[sizeof($su)-1]);

//print_r ($su[sizeof($su)-1]);
//die();

$id=$su[sizeof($su)-1];

include_once 'tools/sessionManager.php';
include_once 'tools/appl.class.php';
include_once 'config.php';
include_once 'tools/userManager.php';
include_once 'data.php';

if (null!=$id && $id!=='' && $id!==0 && ctype_digit($id)) {
	$diak=$db->getPersonByID($id);
	if ($diak==null) {
		ppperror();
		exit;
	} else {
		setAktUserId($diak["id"]);
		header("status: 200");
		include ("editDiak.php");
		exit();
	}
}
ppperror();
exit;

function ppperror() { 
	header("status: 404"); 
	Appl::addCss('http://fonts.googleapis.com/css?family=Satisfy');
	setAktSchool(1);unsetAktClass();
	include("homemenu.php"); 
	?>
	<h2 class="sub_title">Sajnos ez az oldal nem létezik ezen a szerveren.</h2>
	<div style="background-image: url('images/kretatabla.jpg');background-size: cover;height: 600px;margin: 20px;border-radius: 30px;">	
		<div style="font-family: Satisfy;text-align: center;vertical-align:middle;color:white;padding-top:100px">
			<h1>Sajnos ez az oldal nem létezik ezen a szerveren.</h1>
			<h2>Keresett oldal: <?php echo $_SERVER["REQUEST_URI"]?></h2>
		</div>
	</div>
	<?php
	include("homefooter.php");
}
?>

