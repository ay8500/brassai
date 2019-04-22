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
                                        $field = htmlspecialchars($row[$i - 1], ENT_QUOTES | ENT_HTML401, 'UTF-8',);
                                        $line .= "'" . mysqli_real_escape_string($conn, Utf8_ansi($field)) . "', ";
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

function Utf8_ansi($valor='') {
    $utf8_ansi2 = array(
        "u00c0" =>"À",
        "u00c1" =>"Á",
        "u00c2" =>"Â",
        "u00c3" =>"Ã",
        "u00c4" =>"Ä",
        "u00c5" =>"Å",
        "u00c6" =>"Æ",
        "u00c7" =>"Ç",
        "u00c8" =>"È",
        "u00c9" =>"É",
        "u00ca" =>"Ê",
        "u00cb" =>"Ë",
        "u00cc" =>"Ì",
        "u00cd" =>"Í",
        "u00ce" =>"Î",
        "u00cf" =>"Ï",
        "u00d1" =>"Ñ",
        "u00d2" =>"Ò",
        "u00d3" =>"Ó",
        "u00d4" =>"Ô",
        "u00d5" =>"Õ",
        "u00d6" =>"Ö",
        "u00d8" =>"Ø",
        "u00d9" =>"Ù",
        "u00da" =>"Ú",
        "u00db" =>"Û",
        "u00dc" =>"Ü",
        "u00dd" =>"Ý",
        "u00df" =>"ß",
        "u00e0" =>"à",
        "u00e1" =>"á",
        "u00e2" =>"â",
        "u00e3" =>"ã",
        "u00e4" =>"ä",
        "u00e5" =>"å",
        "u00e6" =>"æ",
        "u00e7" =>"ç",
        "u00e8" =>"è",
        "u00e9" =>"é",
        "u00ea" =>"ê",
        "u00eb" =>"ë",
        "u00ec" =>"ì",
        "u00ed" =>"í",
        "u00ee" =>"î",
        "u00ef" =>"ï",
        "u00f0" =>"ð",
        "u00f1" =>"ñ",
        "u00f2" =>"ò",
        "u00f3" =>"ó",
        "u00f4" =>"ô",
        "u00f5" =>"õ",
        "u00f6" =>"ö",
        "u00f8" =>"ø",
        "u00f9" =>"ù",
        "u00fa" =>"ú",
        "u00fb" =>"û",
        "u00fc" =>"ü",
        "u00fd" =>"ý",
        "u00ff" =>"ÿ");
    return strtr($valor, $utf8_ansi2);
}

?>