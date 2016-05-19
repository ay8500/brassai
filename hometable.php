<?PHP 
include_once 'sessionManager.php';
include("homemenu.php");
include_once("data.php");
include_once 'ltools.php';

$guests = getParam("guests", "")=="true";
if ($guests )
	echo('<h2 class="sub_title">Tanárok, régi volt osztálytársak, vendégek, jó barátok.</h2>');
else
	echo('<h2 class="sub_title">Osztálytársak</h2>');

?>
<div class="container-fluid">
<?php

openDatabase(getAktDatabaseName());

foreach ($data as $l => $d)	
{ 
	if ( $guests == isPersonGuest($d) ) {
		if ( userIsAdmin() || userIsEditor()) {
			$personLink='editDiak.php?uid='.$d["id"];
		}
		else {
			if (getLoggedInUserId()==$d["id"]) 
				$personLink="editDiak.php?uid=".$d["id"];
			else { 
				if ($_SERVER["SERVER_NAME"]=="localhost")
					$personLink='editDiak.php?uid='.$d["id"];
				else 
					$personLink=getPersonLink($d["lastname"],$d["firstname"]);
			}
		}
		
		echo "<table border=0 width=100%><tr><td width=170>\r\n" ;
		echo '<a href="'.$personLink.'">';
		echo "<img src=\"images/".$d["picture"].'" border="0" title="'.$d["lastname"].' '.$d["firstname"].'" class="diak_image_medium" />';
		echo '</a>';
		echo "</td><td valign=top>";
		echo "<h4>".$d["lastname"].' '.$d["firstname"];
		if(showField($d,"birthname"))  	echo("&nbsp;(".$d["birthname"].")");
		echo("</h4>"); 
		echo "<table>\r\n";
		if(showField($d,"partner")) 	echo "<tr><td valign=top align=right>Élettárs:</td><td>".$d["partner"]."</td></tr>";
		if(showField($d,"education")) 	echo "<tr><td valign=top align=right>Végzettség:</td><td>".$d["education"]."</td></tr>";
		if(showField($d,"employer")) 	echo "<tr><td valign=top align=right>Munkahely:</td><td>".getFieldValue($d["employer"])."</td></tr>";
		if(showField($d,"function")) 	echo "<tr><td valign=top align=right>Beosztás:</td><td>".getFieldValue($d["function"])."</td></tr>";
		if(showField($d,"children")) 	echo "<tr><td valign=top align=right>Gyerekek:</td><td>".$d["children"]."</td></tr>";
		if(showField($d,"address")||showField($d,"place")||showField($d,"zipcode")) { 
			echo ("<tr><td valign=top align=right>Cím:</td><td>");
			if(showField($d,"address")) echo(getFieldValue($d["address"]).", ");
			if(showField($d,"zipcode")) echo(getFieldValue($d["zipcode"])." ");
			if(showField($d,"place"))   echo(getFieldValue($d["place"]));
			echo("</td></tr>");
		}
		if(showField($d,"country")) 	echo "<tr><td valign=top align=right>Ország:</td><td>".getFieldValue($d["country"])."</td></tr>";
		if(showField($d,"phone")) 		echo "<tr><td valign=top align=right>Telefon:</td><td>".getFieldValue($d["phone"])."</td></tr>";
		if(showField($d,"mobil")) 		echo "<tr><td valign=top align=right>Mobil:</td><td>".getFieldValue($d["mobil"])."</td></tr>";
		if(showField($d,"email")) 		echo "<tr><td valign=top align=right>E-Mail:</td><td><a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a></td></tr>";
		if(showField($d,"skype")) 		echo "<tr><td valign=top align=right>Skype:</td><td>".getFieldValue($d["skype"])."</td></tr>";
		if(showField($d,"facebook"))	echo '<tr><td valign=top align=right>Facebook:</td><td><a href="'.urldecode(getFieldValue($d["facebook"])).'">'.urldecode(getFieldValue($d["facebook"]))."</a></td></tr>";
		if(showField($d,"homepage"))	echo '<tr><td valign=top align=right>Honoldal:</td><td><a href="'.urldecode(getFieldValue($d["homepage"])).'">'.urldecode(getFieldValue($d["homepage"]))."</a></td></tr>";
		//echo '<tr><td valign=top align=right><a href="'.$personLink.'">Több info</a></td><td>&nbsp;</td></tr>';
	  	echo "</table>";
		echo "</td></tr></table>\r\n";
	}
}
?>
</div>
<?php  include ("homefooter.php");?>
