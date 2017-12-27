<?php

error_reporting(0);
set_time_limit(0);

$password=isset($_GET["password"])?$_GET["password"]:"";
$password=($password==="levi");

if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0 || strpos($_SERVER["SERVER_NAME"],".online.de")>0) {
	db_backup("db652851844.db.1and1.com", 'dbo652851844','levi1967', 'db652851844', __DIR__."/backup.sql",$password) ;
} else {
	db_backup("localhost", "root", "root", "db652851844", __DIR__."/backup.sql",$password);
}



// ab hier nichts mehr ändern
function db_backup($dbhost, $dbuser, $dbpwd, $dbname, $dbbackup,$password)
{
	$allRows=0;
	$conn = mysqli_connect($dbhost, $dbuser, $dbpwd,$dbname) or die(mysqli_error());
	if ($password) {
		$f = fopen($dbbackup, "w");
		echo("Backupfile:".$dbbackup."<br/>");
	} else {
		echo("Password not correct! Only backup simulation started.<br/>");
	}
	
	$tables = mysqli_query($conn,"SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '".$dbname."'");
	while ($cells = mysqli_fetch_array($tables))
	{
		$table = $cells["TABLE_NAME"];
		fwrite($f,"DROP TABLE IF EXISTS`".$table."`;\n");

		$res = mysqli_query($conn,"SHOW CREATE TABLE `".$table."`");
		if ($res)
		{
			echo("Table:".$table );
			if ($table=="message" || true) {
				$rows=0;
				$create = mysqli_fetch_array($res);
				$create[1] .= ";";
				$line = str_replace("\n", "", $create[1]);
				fwrite($f, $line."\n");
				$data = mysqli_query($conn,"SELECT * FROM `".$table."`");
				$num = mysqli_num_fields($data);
				while ($row = mysqli_fetch_array($data))
				{
					if($password) {
						$line = "INSERT INTO `".$table."` VALUES(";
						for ($i=1;$i<=$num;$i++)
						{
							if (isset($row[$i-1])) {
								$line .= "'".mysqli_real_escape_string($conn,$row[$i-1])."', ";
							} else {
								$line .="null, ";
							}
						}
						$line = substr($line,0,-2);
						fwrite($f, $line.");\n");
					}
					$rows++;$allRows++;
				}
				echo(" Rows:".$rows);
			}
			echo("<br/>");
		}
	}
	if ($password) fclose($f);
	echo("All rows:<b>".$allRows."</b>");
}
?>