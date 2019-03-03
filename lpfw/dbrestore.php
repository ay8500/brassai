<?php
session_start();

//If the db is empty just uncomment the next php line and call the link below
//The databese defined in the config.class.php with the spezified user and password has to be exisits.
//$_SESSION['uRole']="admin";
//https://localhost/brassai/lpfw/dbrestore.php?password=levi

//User is logged in and have the role of admin
if (!isset($_SESSION['uRole']) || strstr($_SESSION['uRole'],"admin")=="")
    die("Only for admins");

include_once __DIR__ . "/../config.class.php";

error_reporting(0);
set_time_limit(0);

$areYouSure=isset($_GET["password"])?$_GET["password"]:"";
$areYouSure=($areYouSure==="levi");

$db = \Config::getDatabasePropertys();
db_restore($db->host,$db->user,$db->password,$db->database, __DIR__."/backup.sql",$areYouSure) ;


// ab hier nichts mehr ändern
function db_restore($dbhost, $dbuser, $dbpwd, $dbname, $dbrestore, $areYouSure)
{
	$allRows=0;
	$okRows=0;
	$errorRows=0;
	$conn = mysqli_connect($dbhost, $dbuser, $dbpwd, $dbname) or die("Database connection Error");
	$f = fopen($dbrestore, "r");
	echo("Restorefile:".$dbrestore."<br/>");
	if ($f) {
		while (($line = fgets($f)) !== false) {
			if ($areYouSure) {
				$res = mysqli_query($conn,trim($line,";"));
				if ($res) {
					//echo("OK:".substr($line,0,150)."<br/>");
					$okRows++;
				} else {
					echo("ERROR:".substr($line,0,250)."<br/>");
					echo(mysqli_errno($conn).":".mysqli_error($conn));
					$errorRows++;
				}
			}
			$allRows++;				
		}
		fclose($f);
	} else {
		echo("Error restore file not found!<br/>");
	}
	echo("All rows:<b>".$allRows."</b><br/>");
	echo("Ok rows:<b>".$okRows."</b><br/>");
	echo("Error rows:<b>".$errorRows."</b><br/>");
}
?>