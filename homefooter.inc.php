<div class="container-fluid" style="width: 100%;background: #f8f8f8">
	<nav id="footerdiv" class="navbar navbar-default xnavbar-fixed-bottom" role="navigation">
	      <ul class="nav navbar-nav">
			<?PHP if (isUserSuperuser() ) { ?>
				<li><a href="admin"  >Adminsztráció</a></li>
                <li><a href="logingData"  >Loging</a></li>
			<?PHP }	?>
			<?PHP if (isUserAdmin() ) { ?>
				<li><a href="ig/ig?multipleGalleries=1" target="_new" >Képek</a></li>
				<li><a href="dataCheck"  >Vizsga</a></li>
				<li><a href="database"  >Adatbank</a></li>
                <li><a href="<?php echo $_SERVER['REQUEST_URI'].substr_count($_SERVER['REQUEST_URI'],"&")==0?'?':'&' ?>showDatabaseQuery=true"  >Sebesség</a></li>
			<?PHP }	?>
			<li><a href="impressum" style="display: inline-block;" >Impresszum</a> <span style="display: inline-block;">Vers:<?php echo Config::$webAppVersion?></span></li>
	      </ul>
        <ul class="nav navbar-nav" style="float: right;margin: 10px;">
            <li style="margin-top: -12px"><a href="https://www.jetbrains.com/phpstorm/">Built using PhpStorm</a></li>
            <li><a href="https://jb.gg/OpenSource" style="height: 60px"><img style="margin-top: -34px; margin-left: -22px;" alt="JetBrains" src="images/jb_square.svg"></a></li>
        </ul>
	</nav>

	<?php
        global $db;
		if (getParam('showDatabaseQuery')!=null) {
			echo "Querys:".$db->dataBase->getCounter()->querys." Changes:".$db->dataBase->getCounter()->changes." Time:".$db->dataBase->getCounter()->time."<br/>";
			$sql=$db->dataBase->getCounter()->sql;
			foreach ($sql as $s) {
				echo($s."<br/>");
			}
		}
	?>
</div>
</body>
<?php if (!isActionParam("showmore")) {
    include_once "displayOpinionDiv.inc.php";
} ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<?php
\maierlabs\lpfw\Appl::addJs('js/main.js');
if (isUserAdmin()) {
    \maierlabs\lpfw\Appl::addJsScript('    
        function showip(ip) {
            $.ajax({
                url: "ajax/getIpLocation?ip="+ip,
                success:function(data) {
                    var m  = "Ország:"+data.country+"<br/>";
                        m += "Irányítószám:"+data.zip+"<br/>";
                        m += "Város:"+data.city+"<hr/>";
                        m += "Ország:"+data.x.country_name+"<br/>";
                        m += "Irányítószám:"+data.x.zip+"<br/>";
                        m += "Város:"+data.x.city+"<br/>";
                        m += "ISP:"+data.isp+"<br/>";
                        m += "ORG:"+data.org+"<br/>";
                        m += "AS:"+data.as+"<br/>";
                    showModalMessage("IP cím:"+ip+" földrajzi adatai",m);
                }
            });
        }
    ');
}
global $haloween;
if ($haloween)
    \maierlabs\lpfw\Appl::addJs("js/haloween.js",false,true);
global $xmas;
if ($xmas)
    \maierlabs\lpfw\Appl::addJs("js/snowFalling.js",false,true);


?>

<?php \maierlabs\lpfw\Appl::setApplJScript();?>
<?php \maierlabs\lpfw\Appl::addCookieCompilance();?>
<?php \maierlabs\lpfw\Appl::includeJs();?>

<?php if (!isUserAdmin() && !isUserSuperuser()) { ?>
<script type="text/javascript" src="//blue-l.de/stat/track.php?mode=js"></script>
<noscript> <img src="//blue-l.de/stat/track_noscript.php"  alt="" width="1" height="1"></noscript>
<?php } ?>
</html>

<?php
$db->disconnect();
ob_end_flush();
?>