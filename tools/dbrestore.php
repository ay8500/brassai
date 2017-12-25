<?php

error_reporting(0);
set_time_limit(0);

// Hier ergänzen

if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0 || strpos($_SERVER["SERVER_NAME"],".online.de")>0) {
	db_restore("db652851844.db.1and1.com", 'dbo652851844','levi1967', 'db652851844', __DIR__."/backup.sql") ;
} else {
	db_restore("localhost", "root", "root", "db652851844", __DIR__."/backup.sql");
}


// ab hier nichts mehr ändern
function db_restore($dbhost, $dbuser, $dbpwd, $dbname, $dbrestore)
{
	$allRows=0;
	$conn = mysql_connect($dbhost, $dbuser, $dbpwd) or die(mysql_error());
	mysql_select_db($dbname);
	$f = fopen($dbrestore, "r");
	echo("Restorefile:".$dbrestore."<br/>");
	if ($f) {
		while (($line = fgets($f)) !== false) {
			//$res = mysql_query(trim($line,";"));
			if ($res) {
				echo("OK:".substr($line,0,14)."<br/>");
			} else {
				echo("ERROR:".substr($line,0,14)."<br/>");
			}
			$allRows++;				
		}
		fclose($f);
	} else {
		echo("Error restore file not found!<br/>");
	}
	echo("All rows:<b>".$allRows."</b><br/>");
	echo("Have a nice day!");
}
?>