<div class="container-fluid" style="width: 100%;background: #f8f8f8">
	<nav id="footerdiv" class="navbar navbar-default xnavbar-fixed-bottom" role="navigation">
	      <ul class="nav navbar-nav">
			<?PHP if (userIsAdmin() || (userIsEditor())) { ?>
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
</div>
</body>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    
	<?php if (isset($googleMap)) :?>
		<script type="text/javascript" src="//maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAAt_D9PjCp6KIewCC6DftsBTV4tYwmYR0tDWyEKlffNzbwkWE4hTrbEDIZOQBwqdYefOLpNQ7swehXg" ></script>
		<script type="text/javascript" src="js/diakMap.js?v=<?php echo $webAppVersion?>"></script>
	<?php endif ?>
	<?php if (isset($diakEditGeo)) :?>
		<script type="text/javascript" src="//maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAAt_D9PjCp6KIewCC6DftsBTV4tYwmYR0tDWyEKlffNzbwkWE4hTrbEDIZOQBwqdYefOLpNQ7swehXg" ></script>
		<script type="text/javascript" src="js/diakEditGeo.js?v=<?php echo $webAppVersion?>"></script>
	<?php endif?>
	<?php if (isset($loadTextareaEditor)) :?>
		<script type="text/javascript" src="editor/trumbowyg.min.js"></script>
		<script type="text/javascript" src="editor/langs/hu.min.js"></script>
		<script >
		$( document ).ready(function() {
			$('#story').trumbowyg({
				fullscreenable: false,
				closable: false,
				lang: "hu",
				btns: ['formatting','btnGrp-design','|', 'link', 'insertImage','btnGrp-lists'],
				removeformatPasted: true,
				autogrow: true
			});
		});
		</script>
	<?php endif?>
	
	<script>
		
		$( document ).ready(function() {
	    	$(window).resize(function() {
				onResize();
			});

			onResize();

			setTimeout(clearDbMessages, 10000);
		
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
	</script>
	<?php if (isset($showWrapper)) {?>
		<script type="text/javascript" src="js/wrapper.js?v=<?php echo $webAppVersion?>"></script>
	<?php }?>
	<script type="text/javascript" src="//blue-l.de/stat/track.php?mode=js"></script>
	<noscript> <img src="//blue-l.de/stat/track_noscript.php" border="0" alt="" width="1" height="1"></noscript>

</html>
<?php ob_end_flush();?>