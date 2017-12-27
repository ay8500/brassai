<?php

error_reporting(0);
set_time_limit(0);

$password=isset($_GET["password"])?$_GET["password"]:"";
$password=($password==="levi");

if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0 || strpos($_SERVER["SERVER_NAME"],".online.de")>0) {
	db_restore("db652851844.db.1and1.com", 'dbo652851844','levi1967', 'db652851844', __DIR__."/backup.sql",$password) ;
} else {
	db_restore("localhost", "root", "root", "db652851844", __DIR__."/backup.sql",$password);
}


// ab hier nichts mehr ändern
function db_restore($dbhost, $dbuser, $dbpwd, $dbname, $dbrestore, $password)
{
	$allRows=0;
	$okRows=0;
	$errorRows=0;
	$conn = mysqli_connect($dbhost, $dbuser, $dbpwd, $dbname) or die(mysql_error());
	$f = fopen($dbrestore, "r");
	echo("Restorefile:".$dbrestore."<br/>");
	if ($f) {
		while (($line = fgets($f)) !== false) {
			if ($password) { 
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