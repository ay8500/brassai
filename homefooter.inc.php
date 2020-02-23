<div class="container-fluid" style="width: 100%;background: #f8f8f8">
	<nav id="footerdiv" class="navbar navbar-default xnavbar-fixed-bottom" role="navigation">
	      <ul class="nav navbar-nav">
			<?PHP if (userIsAdmin() || userIsSuperuser() ) { ?>
				<li><a href="admin.php"  >Adminsztráció</a></li>
                <li><a href="logingData.php"  >Loging</a></li>
			<?PHP }	?>
			<?PHP if (userIsAdmin() ) { ?>
				<li><a href="ig/ig.php?multipleGalleries=1" target="_new" >Képek</a></li>
				<li><a href="dataCheck.php"  >Vizsga</a></li>
				<li><a href="database.php"  >Adatbank</a></li>
			<?PHP }	?>
			<li><a href="impressum.php" style="display: inline-block;" >Impresszum</a> <span style="display: inline-block;">&copy; 2019 Levi</span></li>
	      </ul>
	</nav>
	<?php
		if (getParam('showDatabaseQuery')!=null) {
			echo "Querys:".$db->dataBase->getCounter()->querys." Changes:".$db->dataBase->getCounter()->changes."<br/>";
			$sql=$db->dataBase->getCounter()->sql;
			foreach ($sql as $s) {
				echo($s."<br/>");
			}
		}
	?>
</div>
</body>
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<?php
\maierlabs\lpfw\Appl::addJs('js/main.js');
if (userIsAdmin()) {
    \maierlabs\lpfw\Appl::addJsScript('    
        function showip(ip) {
            $.ajax({
                url: "ajax/getIpLocation.php?ip="+ip,
                success:function(data) {
                    showModalMessage("IP cím:"+ip+" földrajzi adatai","Ország:"+data.country+"<br/>Irányítószám:"+data.zip+"<br/>Város:"+data.city);
                }
            });
        }
    ');
} ?>

<?php \maierlabs\lpfw\Appl::setApplJScript();?>
<?php \maierlabs\lpfw\Appl::includeJs();?>
<?php if (!userIsAdmin()) { ?>
<script type="text/javascript" src="//blue-l.de/stat/track.php?mode=js"></script>
<noscript> <img src="//blue-l.de/stat/track_noscript.php" border="0" alt="" width="1" height="1"></noscript>
<?php } ?>


</html>

<?php
$db->disconnect();
ob_end_flush();
?>