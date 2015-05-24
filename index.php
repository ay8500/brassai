<?PHP 
include_once("sessionManager.php");
include_once("data.php");

//Change scool year and class if parameters are there 
if (isset($_GET['scoolYear'])) {
	setAktScoolYear($_GET['scoolYear']);
} 
if (isset($_GET['scoolClass']))  {
	setAktScoolClass($_GET['scoolClass']);	
} 

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
			<div style="font-size:12px;font-weight:bold">Brassai Sámuel (1800-1897)</div>
			  <div style="font-size:11px;">
    				&quot;A tanító, mint a gazda, csak magvakat vet el,<br/>
    				melyböl a tanítvány elméjében ismeretek teremnek,<br/>
    				mint a gabona s más termék a földben.&quot;
  			</div>
   </tr>
  </table>
  </td></tr>
</table>
</td></tr><tr><td align="center">
	Ez az oldal <B>1997. junius 11.</B>-e óta elérhető.
	Utoljára módósítva <b>2015. április 28.</b>-án.<br>
</td>
</tr>
</table>
<?PHP  include ("homefooter.php");?>