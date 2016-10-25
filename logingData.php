<?PHP 
	include('homemenu.php');
  	include_once('userManager.php');
?>
<div class="sub_title">Loging</div>
<?PHP
if (userIsAdmin()) {  
	$year="2016";

	//initialise tabs
	$tabsCaption=Array("Bejelentkezés","Sikertelen&nbsp;bejelentkezés","Adatmódosítás","Bejelentkezési adatok","Facebook");
	include("tabs.php");
	?>
	
	<?PHP if ($tabOpen==0) { ?>
	<p align="center">
	   Sikeres bejelentkezések:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>ID</td><td>User</td><td>Passw</td></tr>
	  	<?PHP
	  		$logData= readLogingData("Login",$year);
			foreach($logData as $slog) {
				if ($slog["Result"]!="false")
		    		echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['ID']."</td><td>".$slog['CUser']."</td><td>".$slog['Passw']."</td></tr>");
	  	  }
	  	?>
	 </table>  
	</p>
	<?PHP }?>
	<?PHP if ($tabOpen==1) { ?>
	<p align="center">
	   Sikertelen Bejelentkezés:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>ID</td><td>User</td><td>Passw</td></tr>
	  	<?PHP
	  		$logData= readLogingData("Login",$year);
			foreach($logData as $slog) {
				if ($slog["Result"]=="false")
		    		echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['ID']."</td><td>".$slog['CUser']."</td><td>".$slog['Passw']."</td></tr>");
	  	  }
	  	?>
	  	 </table>  
	</p>
	<?PHP }?>
	<?PHP if ($tabOpen==2) { ?>
	<p align="center">
	   Adatok módosítva:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>ID</td><td>User</td><td>Action</td><td>Type</td></tr>
	  	<?PHP
	  		$logData= readLogingData("SaveData,SaveGeo,SaveStory",$year);
			foreach($logData as $slog) {
		    	echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['ID']."</td><td>".$slog['CUser']."</td><td>".$slog['Action']."</td><td>".$slog['Passw']."</td></tr>");
	  	  }
	  	?>
	  	 </table>  
	</p>
	<?PHP }?>
	<?PHP if ($tabOpen==3) { ?>
	<p align="center">
	   Bejelenkezési adatok módosítása:<br/>	
	  <table align="center" border="1">
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>ID</td><td>User</td><td>Action</td><td>Passw</td></tr>
	  	<?PHP
	  		$logData= readLogingData("SavePassw,SaveUsername,NewPassword",$year);
			foreach($logData as $slog) {
		    	echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['ID']."</td><td>".$slog['CUser']."</td><td>".$slog['Action']."</td><td>".$slog['Passw']."</td></tr>");
	  	  }
	  	?>
	   	 </table>  
	</p>
	<?PHP }?>
	<?PHP if ($tabOpen==4) { ?>
	<p align="center">
	   Facebook:<br/>	
	  <table align="center" border="1">
	    <tr><td>IP</td><td>Date</td><td>Scool</td><td>Result</td><td>ID</td><td>User</td></tr>
	  	<?PHP
	  		$logData= readLogingData("Facebook",$year);
			foreach($logData as $slog) {
		    	echo("<tr><td>".$slog['IP']."</td><td>".$slog['Date']."</td><td>".$slog['Scool']."</td><td>".$slog['Result']."</td><td>".$slog['ID']."</td><td>".$slog['CUser']."</td></tr>");
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
	echo '<div class="alert alert-danger" >Adat hozzáférési jog hiányzik!</div>';
	
include 'homefooter.php';
?>


