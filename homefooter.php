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
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
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

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript" >
	$( document ).ready(function() {
    	$(window).resize(function() {
			onResize();
		});
		onResize();
		setTimeout(clearDbMessages, 10000);
	    setTimeout(checkSession,10000);
	});
	var logoTimer;
	var logoTop=-20;
	var logoDirection =-1;
	
	function onResize(hplus) {

		var h= 	removePX($(".sub_title").css("height"))+
				removePX($(".appltitle").css("height"))+
				removePX($("#main-menu").css("height"))+32;
		if (null!=hplus)
			h +=hplus;
		var hh = removePX($("#homelogo").css("height"));
	    
	    $(".homeLogo").css("height",(h)+"px");
	    clearInterval(logoTimer);
	    logoTimer = setInterval(function() { 
		    $("#homelogo").css("top",logoTop+"px");
		    logoTop=logoTop+logoDirection;
		    if (logoTop<h-hh) 	logoDirection=1;
		    if( logoTop>=0) 	logoDirection=-1;
		}, 50);
	}

	function removePX(p) {
		if (null!=p)
			return parseInt(p.substr(0,p.length-2));
		else
			return 0;
	}

	function clearDbMessages() {
		if ($(".resultDBoperation").html()!="")
			$(".resultDBoperation").slideUp("slow");
	}

	function showDbMessage(text,type) {
		$(".resultDBoperation").html('<div class="alert alert-'+type+'">'+text+'</div>');
		$(".resultDBoperation").slideDown("slow");
		setTimeout(clearDbMessages, 10000);
	}

	<?php if (userIsAdmin()) {?>
		function showip(ip) {
		    $.ajax({
		    	url: "getIpLocation.php?ip="+ip
			}).success(function(data) {
			    showModalMessage("IP cím:"+ip+" földrajzi adatai","Ország:"+data.country+"<br/>Irányítószám:"+data.zip+"<br/>Város:"+data.city);
			});
		}
	<?php } ?>

    function checkSession() {
        $.ajax({
            url: "ajax/userSessionAlive.php",
            type:"GET",
            success:function(data){
                setTimeout(checkSession,10000);
            },
            error:function(error) {
                showModalMessage("Kedves felhasználó",'<div class="alert alert-warning">Sajnos rég nem frissitetted ezt az óldalt, idéglenes adataid emiatt törlödtek. <br/><br/>Jelentkezz be újból!</div>');
            }
        });
    }

	function showModalMessage(title,text) {
	    $(".modal-title").html(title);
		$(".modal-body").html(text);
		$('#myModal').modal({show: 'false' });
	}
			
</script>
<?php \maierlabs\lpfw\Appl::includeJs();?>
<script type="text/javascript" src="//blue-l.de/stat/track.php?mode=js"></script>
<noscript> <img src="//blue-l.de/stat/track_noscript.php" border="0" alt="" width="1" height="1"></noscript>

</html>

<?php
$db->disconnect();
ob_end_flush();
?>