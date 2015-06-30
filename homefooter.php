<nav id="footerdiv" class="navbar navbar-default xnavbar-fixed-bottom" role="navigation">
  <div class="container">
      <ul class="nav navbar-nav">
        <?PHP if (userIsLoggedOn()) {	 $person=getPersonLogedOn(); ?>
				<li><a href="editDiak.php" title="<?PHP echo ($person["lastname"].' '.$person["firstname"] ) ?>">Az én adataim</a></li>
		<?PHP }  ?>	
		<?PHP if (userIsAdmin() || (userIsEditor())) { ?>
			<li><a href="admin.php"  >Adminsztráció</a></li>
		<?PHP }	?>
			<?PHP if (userIsAdmin() ) { ?>
				<li><a href="ig/ig.php?multipleGalleries=1" target="_new" >Képek</a></li>
				<li><a href="logingData.php"  >Loging</a></li>
			<?PHP }	?>
		<li><a href="impressum.php"  >Impresszum</a></li>
        
        <li><a href="#">(c) 2015 Levi</a>
      </ul>
  </div>
</nav>

</body>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    
	<?php if (isset($googleMap)) :?>
		<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAAt_D9PjCp6KIewCC6DftsBTV4tYwmYR0tDWyEKlffNzbwkWE4hTrbEDIZOQBwqdYefOLpNQ7swehXg" ></script>
		<script type="text/javascript" src="js/diakMap.js"></script>
	<?php endif ?>
	<?php if (isset($diakEditGeo)) :?>
		<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAAt_D9PjCp6KIewCC6DftsBTV4tYwmYR0tDWyEKlffNzbwkWE4hTrbEDIZOQBwqdYefOLpNQ7swehXg" ></script>
		<script type="text/javascript" src="js/diakEditGeo.js"></script>
	<?php endif?>
	<?php if (isset($diakEditStorys)) :?>
		<script src="editor/trumbowyg.min.js"></script>
		<script type="text/javascript" src="editor/langs/hu.min.js"></script>
		<script >
		$( document ).ready(function() {
			$('#story').trumbowyg({
				fullscreenable: false,
				closable: false,
				lang: "hu",
				btns: ['formatting','btnGrp-design','|', 'link', 'insertImage','btnGrp-lists','|', 'horizontalRule'],
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
		});
		onResize();
		var logoTimer;
		var logoTop=-20;
		var logoDirection =-1;
		
		function onResize() {

			var h= 	removePX($(".sub_title").css("height"))+
					removePX($(".appltitle").css("height"))+
					removePX($("#main-menu").css("height"))+32;
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
	</script>

	<script type="text/javascript" src="http://s522513082.online.de/stat/track.php?mode=js"></script>
	<noscript><img src="http://s522513082.online.de/stat/track_noscript.php" border="0" alt="" width="1" height="1"></noscript>
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-20252557-2']);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
</html>
