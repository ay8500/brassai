<?PHP 
include("homemenu.php");

//Change scool year and class if parameters are there 
if (isset($_GET['scoolYear']))   { $_SESSION['scoolYear']=$_GET['scoolYear']; } 
if (isset($_GET['scoolClass']))  { $_SESSION['scoolClass']=$_GET['scoolClass']; }

$SiteTitle=$_SESSION['scoolYear']."-".$_SESSION['scoolClass'];
   //Parameter guests
   if (isset($_GET["guests"])) $guests = true; else $guests=false;
	if ( $guests )
		$SiteTitle=$SiteTitle.' tanárok, vendégek, jó barátok';
	else
		$SiteTitle=$SiteTitle.' osztálytársak';

	if ( $guests )
		echo('<h2 class="sub_title">Tanárok, vendégek, jó barátok.</h2>');
	else
		echo('<h2 class="sub_title">Osztálytársak</h2>');

?>



<table style="text-align:cleft;width:100%;" ><tr><td>
<?php
	include_once("data.php");
	
foreach ($data as $l => $d)	
{ 
	if ( 
			(  $guests && (($d["admin"]=="viewer") || ($d["admin"]=="editor"))) || 
			(!($guests) && ($d["admin"]=="") )  
		) { 
			
		echo "<table border=0 width=100%><tr><td width=150>\r\n" ;
		echo "<img src=\"images/".$d["picture"].'" border="0" alt="'.$d["lastname"].' '.$d["firstname"].'" />';
		echo "</td><td valign=top>";
		echo "<h3>".$d["lastname"].' '.$d["firstname"];
			if ($d["birthname"]!="") echo("&nbsp;(".$d["birthname"].")");
		echo("</h3>"); 
		echo "<table>\r\n";
		echo "<tr><td valign=top align=right>Élettárs:</td><td>".$d["partner"]."</td></tr>";
		echo "<tr><td valign=top align=right>Végzettség:</td><td>".$d["education"]."</td></tr>";
		if(showField($d["employer"])) echo "<tr><td valign=top align=right>Munkahely:</td><td>".getFieldValue($d["employer"])."</td></tr>";
		if(showField($d["function"])) echo "<tr><td valign=top align=right>Beosztás:</td><td>".getFieldValue($d["function"])."</td></tr>";
		echo "<tr><td valign=top align=right>Gyerekek:</td><td>".$d["children"]."</td></tr>";
		if(showField($d["address"])||showField($d["place"])||showField($d["zipcode"])) { 
			echo ("<tr><td valign=top align=right>Cím:</td><td>");
			if(showField($d["address"])) echo(getFieldValue($d["address"]).", ");
			if(showField($d["zipcode"])) echo(getFieldValue($d["zipcode"])." ");
			if(showField($d["place"]))   echo(getFieldValue($d["place"]));
			echo("</td></tr>");
		}
		if(showField($d["country"])) echo "<tr><td valign=top align=right>Ország:</td><td>".getFieldValue($d["country"])."</td></tr>";
		if(showField($d["phone"])) echo "<tr><td valign=top align=right>Telefon:</td><td>".getFieldValue($d["phone"])."</td></tr>";
		if(showField($d["mobil"])) echo "<tr><td valign=top align=right>Mobil:</td><td>".getFieldValue($d["mobil"])."</td></tr>";
		if(showField($d["email"])) echo "<tr><td valign=top align=right>E-Mail:</td><td><a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a></td></tr>";
		if(showField($d["skype"])) echo "<tr><td valign=top align=right>Skype:</td><td>".getFieldValue($d["skype"])."</td></tr>";
		if(showField($d["facebook"])) echo '<tr><td valign=top align=right>Facebook:</td><td><a href="'.urldecode(getFieldValue($d["facebook"])).'">'.urldecode(getFieldValue($d["facebook"]))."</a></td></tr>";
		if(isset($d["homepage"]) && showField($d["homepage"])) echo '<tr><td valign=top align=right>Honoldal:</td><td><a href="'.urldecode(getFieldValue($d["homepage"])).'">'.urldecode(getFieldValue($d["homepage"]))."</a></td></tr>";
		if ( userIsAdmin() || userIsEditor()) {
			echo '<tr><td valign=top align=right><a href="editDiak.php?uid='.$l.'">Módósít</a></td><td>&nbsp;</td></tr>';
		}
		else {
			if (isset($_SESSION['UID']) && $_SESSION['UID']==$l) {
				echo '<tr><td valign=top align=right><a href="editDiak.php">Módósít</a></td><td>&nbsp;</td></tr>';
			}
			else {
				echo '<tr><td valign=top align=right><a href="'.getPersonLink($d["lastname"],$d["firstname"]).'">Több info</a></td><td>&nbsp;</td></tr>';
			}
		}
  	echo "</table>";
  echo "</td></tr></table>\r\n";
  }

}
 echo("\r\n<br><br><br>\r\n");
 
?>
<?PHP  include ("homefooter.php");?>
