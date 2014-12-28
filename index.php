<?PHP 
include_once("sessionManager.php");

//Change scool year and class if parameters are there 
$classchanged = false;
if (isset($_GET['scoolYear'])) {
	if ($_SESSION['scoolYear']!=$_GET['scoolYear']) $classchanged=true;
	$_SESSION['scoolYear']=$_GET['scoolYear'];
} 
if (isset($_GET['scoolClass']))  {
	if ($_SESSION['scoolClass']!=$_GET['scoolClass'])  $classchanged=true;
	$_SESSION['scoolClass']=$_GET['scoolClass'];	
} 

//include login logoff
if ($classchanged) {
	include_once("userManager.php");
	logoutUser();
} 

//include the menu.
$SiteDescription="A kolozsvári Brassai Sámuel líceum véndiákok honlapja";
include("homemenu.php");
?>
<h2 class="sub_title">Szeretettel köszöntünk honlapunkon</h2>
<table align="center"class="pannel">
  <tr><td>

  <table style="width: 600px;">
  <tr><td align="center">
  <table style="width: 500px;" align="center" border="1">
	<tr>
		<td style="text-align:center;width:300px">
			<img src="images/BRASSAIS.JPG"  alt="Brassai Sámuel" /><br/>
			<div style="font-size:12px;font-weight:bold">Brassai S&aacute;muel (1800-1897)</div>
			  <div style="font-size:11px;">
    				&quot;A tan&iacute;t&oacute;, mint a gazda, csak magvakat vet el,<br/>
    				melyb&otilde;l a tan&iacute;tv&aacute;ny elm&eacute;j&eacute;ben ismeretek
    				teremnek,<br/>mint a gabona s m&aacute;s term&eacute;k a f&ouml;ldben.&quot;
  			</div>
   </tr>
  </table>
  </td></tr>
</table>
</td></tr><tr><td align="center">
	Ez az oldal <B>1997. junius 11.</B>-e &oacute;ta el&eacute;rhet&otilde;.
	Utolj&aacute;ra m&oacute;d&oacute;s&iacute;tva <b>2014. december 29.</b>-én.<br>
</td>
</tr>
</table>
<?PHP  include ("homefooter.php");?>