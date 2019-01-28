<?php
    include_once 'lpfw/sessionManager.php';
    include_once 'lpfw/userManager.php';
	include('homemenu.inc.php');
?>
<div class="sub_title">Loging</div>
<?PHP
if (userIsAdmin()) {  
	$year="2019";

	//initialise tabs
	$tabsCaption=Array("","Sikertelen&nbsp;bejelentkezés","Adatmódosítás","Bejelentkezési adatok","Facebook");
    //initialise tabs
    $tabsCaption = array();
    array_push($tabsCaption ,array("id" => "ok", "caption" => 'Bejelentkezés', "glyphicon" => "user"));
    array_push($tabsCaption ,array("id" => "error", "caption" => 'Sikertelen&nbsp;bejelentkezés', "glyphicon" => "user"));
    array_push($tabsCaption ,array("id" => "change", "caption" => 'Adatmódosítás', "glyphicon" => "user"));
    array_push($tabsCaption ,array("id" => "userdata", "caption" => 'Bejelentkezési adatok', "glyphicon" => "user"));
    array_push($tabsCaption ,array("id" => "facebook", "caption" => 'Facebook', "glyphicon" => "tower"));

    include 'lpfw/view/tabs.inc.php';
	?>
	
	<?PHP if ($tabOpen=='ok') { ?>
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
	<?PHP if ($tabOpen=='error') { ?>
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
	<?PHP if ($tabOpen=='change') { ?>
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
	<?PHP if ($tabOpen=='userdata') { ?>
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
	<?PHP if ($tabOpen=='facebook') { ?>
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
	
include 'homefooter.inc.php';
?>


