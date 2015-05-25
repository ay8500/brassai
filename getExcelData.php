<?php
header("Content-type: application/vnd-ms-excel"); 
header("Content-Disposition: attachment; filename=diakok.xls");
header("Pragma: no-cache");
header("Expires: 0");
include_once("data.php");  
?>
<table>
<tr style="background-color:#ffffcb;font-weight:bold;"><td>Sz.</td><td>Név</td><td>Feleség/férj neve</td><td>Cím</td><td>Email</td><td>Telefon</td><td>Mobil</td><td>Skype</td><td>Munkahely</td><td>Beosztás</td><td>Gyerekek</td></tr>
		<?PHP
		for ($l=0;$l<sizeof($data);$l++) {
			$d=$data[$l];
			if (!(strpos($d["admin"],"guest")===0)) {
				if (($l % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo("<td>".$l."</td>");
				echo("<td>".$d["lastname"].' '.$d["firstname"]);if ($d["birthname"]!="") echo("(".$d["birthname"].")"); echo( "</td>");
				echo "<td>".$d["partner"]."</td>";
				echo "<td>".getFieldValue($d["address"])." ".getFieldValue($d["zipcode"])."-".getFieldValue($d["place"])." ".getFieldValue($d["country"])."</td>";
				echo "<td><a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a>";
				echo "<td>".getFieldValue($d["phone"])."</td><td>".getFieldValue($d["mobil"])."</td><td>".getFieldValue($d["skype"])."</td>";
				echo "<td>".getFieldValue($d["employer"])."</td>";
				echo "<td>".getFieldValue($d["function"])."</td>";
				echo "<td>".getFieldValue($d["children"])."</td>";
				echo "</tr>";
			}
		}
		echo("<tr><td>Vendégek, Tanárok</td></tr>");
		for ($l=0;$l<sizeof($data);$l++) {
			$d=$data[$l];
			if ((strpos($d["admin"],"guest")===0)) {
				if (($l % 2) ==0)
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo("<td>".$l."</td>");
				echo("<td>".$d["lastname"].' '.$d["firstname"]);if ($d["birthname"]!="") echo("(".$d["birthname"].")"); echo( "</td>");
				echo "<td>".$d["partner"]."</td>";
				echo "<td>".getFieldValue($d["address"])." ".getFieldValue($d["zipcode"])."-".getFieldValue($d["place"])." ".getFieldValue($d["country"])."</td>";
				echo "<td><a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a>";
				echo "<td>".getFieldValue($d["phone"])."</td><td>".getFieldValue($d["mobil"])."</td><td>".getFieldValue($d["skype"])."</td>";
				echo "<td>".getFieldValue($d["employer"])."</td>";
				echo "<td>".getFieldValue($d["function"])."</td>";
				echo "<td>".getFieldValue($d["children"])."</td>";
				echo "</tr>";
			}
		}
		?>
</table>
