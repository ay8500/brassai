<?php
$su = explode("/",$_SERVER["REDIRECT_REDIRECT_SCRIPT_URL"]);

if  (($su[1]=='Levi') || ($su[1]=='levi'))  {
  header('Location:Levente_Maier');
}

else if  ($su[1]=='impressum') {
  header("status: 200"); 
  include_once('impressum.php');
}

else {
header("status: 404"); 
$SiteTitle="A kolozsv�ri Brassai S�muel l�ceum: Hiba oldal";

include_once("homemenu.php"); 
?>

<h2 class="sub_title">Sajnos ez az oldal nem l�tezik ezen a szerveren.</h2>
Keresett oldal:
<?PHP
echo($su[1]." ");echo($su[2]." ");echo($su[3]." ");

include_once("homefooter.php");
 }
?>

