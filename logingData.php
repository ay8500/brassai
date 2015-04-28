<?PHP 
	include('homemenu.php');
  	include_once('userManager.php');
?>
<div class="sub_title">Loging</div>
<?PHP
if (userIsAdmin()) {  
	$logData= readLogingData();
	//initialise tabs
	$tabsCaption=Array("Bejelentkezés","Adatmódosítás","Jelszó&nbsp;kérés","Sikertelen&nbsp;bejelentkezés");
	include("tabs.php");
	?>
	
	<?PHP if ($tabOpen==0) { ?>
	<p align="center">
	   Sikeres bejelentkezések:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>ID</td><td>User</td></tr>
	  	<?PHP
	  	  foreach($logData as $slog) {
	  	  	if ((strlen( $slog['User'])>0) && (substr( $slog['User'],0,4)!="Save") && ($slog['User']!="NewPassword") ) 
			    echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['ID']."</td><td>".$slog['CUser']."</td></tr>");
	  	  }
	  	?>
	 </table>  
	</p>
	<?PHP }?>
	<?PHP if ($tabOpen==1) { ?>
	<p align="center">
	   Adatok módosítva:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>Data</td><td>ID</td><td>User</td></tr>
	  	<?PHP
	  	  foreach($logData as $slog) {
	  	  	if ((strlen( $slog['User'])>0) && (substr( $slog['User'],0,4)=="Save")  ) 
			    echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['User']."</td><td>".$slog['ID']."</td><td>".$slog['CUser']."</td></tr>");
	  	  }
	  	?>
	 </table>  
	</p>
	<?PHP }?>
	<?PHP if ($tabOpen==2) { ?>
	<p align="center">
	   Jelszó kérése:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>User</td></tr>
	  	<?PHP
	  	  foreach($logData as $slog) {
	  	  	if ((strlen( $slog['User'])>0) && (strpos($slog['User'],"assword")>0)  ) 
			    echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['CUser']."</td></tr>");
	  	  }
	  	?>
	 </table>  
	</p>
	<?PHP }?>
	
	<?PHP if ($tabOpen==3) { ?>
	<p align="center">
	   Sikertelen Bejelentkezés:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>User</td><td>Passw</td></tr>
	  	<?PHP
	  	  foreach($logData as $slog) {
	  	  	if ((strlen( $slog['User'])==0)  ) 
			    echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['CUser']."</td><td>".$slog['Passw']."</td></tr>");
	  	  }
	  	?>
	 </table>  
	</p>
	<?PHP }?>
	
	</td></tr></table>
	<p>	<a href="generateSitemap.php">Sitemap</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="generateSitemap.php?htacces=true">htacces</a>
	</p>

<?PHP }
else
	echo "<div>Adat hozzáférési jog hiányzik!</div>";	
?>
</td></tr></table>
</body>
</html>
