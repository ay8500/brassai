<?php

error_reporting(0);
set_time_limit(0);

// Hier ergänzen

if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0 || strpos($_SERVER["SERVER_NAME"],".online.de")>0) {
	db_backup("db652851844.db.1and1.com", 'dbo652851844','levi1967', 'db652851844', __DIR__."/backup.sql") ;
} else {
	db_backup("localhost", "root", "root", "db652851844", __DIR__."/backup.sql");
}


// ab hier nichts mehr ändern
function db_backup($dbhost, $dbuser, $dbpwd, $dbname, $dbbackup)
{
	$allRows=0;
	$conn = mysql_connect($dbhost, $dbuser, $dbpwd) or die(mysql_error());
	mysql_select_db($dbname);
	$f = fopen($dbbackup, "w");
	echo("Backupfile:".$dbbackup."<br/>");
	$tables = mysql_list_tables($dbname);
	while ($cells = mysql_fetch_array($tables))
	{
		$table = $cells[0];
		fwrite($f,"DROP TABLE IF EXISTS`".$table."`;\n");
		/*
		$res = mysql_query("SHOW CREATE TABLE `".$table."`");
		if ($res)
		{
			echo("Table:".$table);
			$rows=0;
			$create = mysql_fetch_array($res);
			$create[1] .= ";";
			$line = str_replace("\n", "", $create[1]);
			fwrite($f, $line."\n");
			$data = mysql_query("SELECT * FROM `".$table."`");
			$num = mysql_num_fields($data);
			while ($row = mysql_fetch_array($data))
			{
				$line = "INSERT INTO `".$table."` VALUES(";
				for ($i=1;$i<=$num;$i++)
				{
					$line .= "'".mysql_real_escape_string($row[$i-1])."', ";
				}
				$line = substr($line,0,-2);
				fwrite($f, $line.");\n");
				$rows++;$allRows++;
			}
			echo(" Rows:".$rows."<br/>");
		}
		*/
	}
	fclose($f);
	echo("All rows:<b>".$allRows."</b><br/>");
	echo(" Habe a nice day!");
}
?>