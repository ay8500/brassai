<?php
session_start();
//User is logged in and have the role of admin
if (!isset($_SESSION['uRole']) || strstr($_SESSION['uRole'],"admin")=="")
    die("Only for admins");

include_once __DIR__ . "/../config.class.php";

error_reporting(0);
set_time_limit(0);

$backupTables=isset($_GET["tables"])?$_GET["tables"]:"";

$areYouSure=isset($_GET["password"])?$_GET["password"]:"";
$areYouSure=($areYouSure==="levi");

$db = \Config::getDatabasePropertys();
db_backup($db->host,$db->user,$db->password,$db->database, __DIR__."/backup.sql",$areYouSure,$backupTables) ;


// ab hier nichts mehr ändern
function db_backup($dbhost, $dbuser, $dbpwd, $dbname, $dbbackup,$areYouSure,$backupTables)
{
    $allRows=0;
    $conn = mysqli_connect($dbhost, $dbuser, $dbpwd,$dbname) or die(mysqli_error());
    if ($areYouSure) {
        $f = fopen($dbbackup, "w");
        echo("Backupfile:".$dbbackup."<br/>");
    } else {
        echo("Password not correct! Only backup simulation started.<br/>");
    }

    $tables = mysqli_query($conn,"SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '".$dbname."'");
    while ($cells = mysqli_fetch_array($tables))
    {
        $table = $cells["TABLE_NAME"];
        $tableArray=explode(",",$backupTables);

        if ($backupTables=="" || in_array($table,$tableArray) ) {
            if ($areYouSure)
                fwrite($f,"DROP TABLE IF EXISTS`".$table."`;\n");
            $res = mysqli_query($conn,"SHOW CREATE TABLE `".$table."`");
            if ($res)
            {
                echo("Table:".$table );
                if ($table=="message" || true) {

                    $create = mysqli_fetch_array($res);
                    $create[1] .= ";";
                    $line = str_replace("\n", "", $create[1]);
                    if ($areYouSure)
                        fwrite($f, $line."\n");

                    $sql = "SELECT * FROM `".$table."`";
                    $data = mysqli_query($conn,$sql);
                    echo(" Rows:".mysqli_num_rows($data));

                    $rows=0;
                    $tableRows=mysqli_num_rows($data);
                    $maxRows=$tableRows<=50000?50000:$tableRows;

                    while ($rows<$tableRows) {
                        echo(".");
                        ob_flush();flush();
                        $data = mysqli_query($conn,$sql.' limit '.$rows.',20000');
                        $num = mysqli_num_fields($data);

                        while ($row = mysqli_fetch_row($data)) {
                            if ($areYouSure) {
                                $line = "INSERT INTO `" . $table . "` VALUES(";
                                for ($i = 1; $i <= $num; $i++) {
                                    if (isset($row[$i - 1])) {
                                        $line .= "'" . mysqli_real_escape_string($conn, $row[$i - 1]) . "', ";
                                    } else {
                                        $line .= "null, ";
                                    }
                                }
                                $line = substr($line, 0, -2);
                                fwrite($f, $line . ");\n");
                            }
                            $rows++;
                            $allRows++;
                        }
                    }
                    echo(" Backup:".$rows);
                    mysqli_free_result($data);
                }
                echo("<br/>");
            }
        }
    }
    if ($areYouSure) fclose($f);
    echo("All rows:<b>".$allRows."</b>");
    ob_end_flush();
}
?>