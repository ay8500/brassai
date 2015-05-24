<?PHP include("homemenu.php"); 
include_once("data.php");
include_once("UserManager.php");

if (isset($_GET["action"]) && ($_GET["action"]=="sendMail")) {
	if ( userIsAdmin() ) {
		include_once ("sendMail.php");
		for($uid=1;$uid<=getDataSize()+1;$uid++) {
			if (isset($_GET["D".$uid])) {
				SendMail($uid, $_GET["T"],isset($_GET["U"]) );
			}
		}
	}
}
?>

<script language="JavaScript" type="text/javascript">
	function checkUncheckAll(state) {
		for(var z=0; z < document.mail.elements.length; z++) {
			if (document.mail.elements[z].type == 'checkbox') {
				if (document.mail.elements[z].name != 'U') 
					document.mail.elements[z].checked = state;
	  		}
     	}
    }
</script>
   
<p class="sub_title">Osztálytárs e-mail küldő parancsnokság</p>

<?PHP if (userIsAdmin() ) { ?>
	<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" name="mail">
	<table align="center" class="pannel" style="width:800px"><tr><td>
	<input type="checkbox" name="U"/> Bejelentkezési adatokat is küld? <br/>
	<textarea name="T" rows="8" cols="80" wrap="off" >
	  <b>Kedves %%name%%</b><br/>
	  <p>
	  Szöveg....
	  </p>
	  <p>
	  Üdvözlettel <?PHP $dd=getPersonLogedOn(); echo($dd["lastname"]." ".$dd["firstname"]); ?>
	  </p>
	  <p>
	  Ezt az e-mailt <a href=http://brassai.blue-l.de/index.php?<?PHP echo('scoolYear='.$_SESSION['scoolYear'].'&scoolClass='.$_SESSION['scoolClass']);?>>A kolozsvári Brassai Sámuel líceum <?PHP echo($_SESSION['scoolYear']);?>-ben végzett diákjainak <?PHP echo($_SESSION['scoolClass']);?></a> honlapjáról kaptad.
	  </p>
	</textarea>
	<br/>
	<input type="submit" class="submit2" value="E-Mail küldés!" />
	&nbsp;<a href="javascript:checkUncheckAll(true);">mindenkinek</a>
	&nbsp;<a href="javascript:checkUncheckAll(false);">senkinek</a>
	</td></tr></table>
	<table align="center" class="pannel" style="width:800px;" cellspacing="0" cellpadding="0">
	<tr>
	<?PHP
		for ($l=1;$l<=getDataSize()+1;$l++) {
			$d=getPerson($l);
			
			echo('<td>');
			if (strlen($d["email"])>2) echo('<input type="checkbox" name="D'.$l.'" checked />');
			else echo('&nbsp;');
			echo('</td><td>'.$d["lastname"].'&nbsp;'.$d["firstname"].'</td>');
			if ($l % 3==0) echo('<tr></tr>'."\r\n");
		}
	?>
	</tr>
	</table>
	<input type="hidden" value="sendMail" name="action" />
	</form>
	
	<?PHP if (isset($sendMailMsg)) echo('<div style="text-align:center">'.$sendMailMsg.'</div>');?>
	<p style="text-align:center">
	<a href="generateSitemap.php">Sitemap</a>&nbsp;&nbsp;|&nbsp;&nbsp;
	<a href="logingData.php">Loging</a>&nbsp;&nbsp;|&nbsp;&nbsp;
	<a href="kontakt.php">Kontakt</a>
	</p>
<?PHP } 
else
	echo "<div>Adat hozzáférési jog hiányzik!</div>";
?>
</td></tr></table>
</body>
</html>
