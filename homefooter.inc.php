<div class="container-fluid" style="width: 100%;background: #f8f8f8">
	<nav id="footerdiv" class="navbar navbar-default xnavbar-fixed-bottom" role="navigation">
	      <ul class="nav navbar-nav">
			<?PHP if (userIsAdmin() || (userIsEditor() )) { ?>
				<li><a href="admin.php"  >Adminsztráció</a></li>
			<?PHP }	?>
			<?PHP if (userIsAdmin() ) { ?>
				<li><a href="ig/ig.php?multipleGalleries=1" target="_new" >Képek</a></li>
				<li><a href="logingData.php"  >Loging</a></li>
				<li><a href="dataCheck.php"  >Vizsga</a></li>
				<li><a href="database.php"  >Adatbank</a></li>
			<?PHP }	?>
			<li><a href="impressum.php" style="display: inline-block;" >Impresszum</a> <span style="display: inline-block;">&copy; 2018 Levi</span></li>
	      </ul>
	</nav>
	<?php
		if (getParam('showDatabaseQuery')!=null) {
			echo "Querys:".$db->getRequestCounter()->querys." Changes:".$db->getRequestCounter()->changes."<br/>";
			$sql=$db->getRequestCounter()->sql;
			foreach ($sql as $s) {
				echo($s."<br/>");
			}
		}
	?>
</div>
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id="modal-close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"></h4>
        </div>
        <div class="modal-body">
          <p></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
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