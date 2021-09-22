<?php
header('Content-Encoding: UTF-8');
header("Content-type: application/vnd-ms-excel;charset=UTF-8");
header("Content-Disposition: attachment; filename=diakok.xls");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; // UTF-8 BOM
include_once 'config.class.php';
include_once  Config::$lpfw.'sessionManager.php';
include_once  'dbBL.class.php';
?>
<table>
<tr style="background-color:#ffffcb;font-weight:bold;"><td>Sz.</td><td></td><td>Név</td><td>Feleség/férj neve</td><td>Cím</td><td>Email</td><td>Telefon</td><td>Mobil</td><td>Skype</td><td>Munkahely</td><td>Beosztás</td><td>Gyerekek</td></tr>
		<?php
        $classId=getRealId(getActClass());
        if ($classId!=null) {
            echo("<tr><td>Diákok</td></tr>");
            $data = $db->getPersonListByClassId($classId,false);
            showPersonList($db,$data);

            echo("<tr><td>Vendégek</td></tr>");
            $data = $db->getPersonListByClassId($classId,true);
            showPersonList($db,$data);

            echo("<tr><td>Tanárok</td></tr>");
            $data=$db->getTeachersIdByClassId($classId);
            showPersonList($db,$data);
        }
		?>
</table>
<?php
function showPersonList($db,$data) {
    foreach ($data as $l => $p ) {
        if(!is_array($p))
            $d = $db->getPersonByID($p);
        else
            $d=$p;
        if (($l % 2) == 0)
            echo '<tr style="background-color:#f8f8f8">';
        else
            echo '<tr style="background-color:#e8f0f0">';
        echo("<td>" . ($l+1) . "</td>");
        echo("<td>" . ($d["deceasedYear"]==null?"":"+") . "</td>");
        echo("<td>" . $d["lastname"] . ' ' . $d["firstname"]);
        if ($d["birthname"] != "") echo("(" . $d["birthname"] . ")");
        echo("</td>");
        echo "<td>" . $d["partner"] . "</td>";
        echo "<td>" . getFieldValue($d["address"]) . " " . getFieldValue($d["zipcode"]) . "-" . getFieldValue($d["place"]) . " " . getFieldValue($d["country"]) . "</td>";
        echo "<td><a href=mailto:" . getFieldValue($d["email"]) . ">" . getFieldValue($d["email"]) . "</a>";
        echo "<td>" . getFieldValue($d["phone"]) . "</td><td>" . getFieldValue($d["mobil"]) . "</td><td>" . getFieldValue($d["skype"]) . "</td>";
        echo "<td>" . getFieldValue($d["employer"]) . "</td>";
        echo "<td>" . getFieldValue($d["function"]) . "</td>";
        echo "<td>" . getFieldValue($d["children"]) . "</td>";
        echo "</tr>";
    }
}
?>