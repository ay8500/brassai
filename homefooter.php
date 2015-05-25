</td>
</tr>
</table>


</body>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="js/ddsmoothmenu.js"></script>
	<script type="text/javascript">
	  ddsmoothmenu.init({	mainmenuid: 'smoothmenu', orientation: 'v', classname: 'ddsmoothmenu-v', contentsource: "markup" });
	</script>
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
				btns: ['viewHTML','formatting','btnGrp-design','|', 'link', 'insertImage','btnGrp-lists','|', 'horizontalRule'],
				removeformatPasted: true
			});

			//$('#story').trumbowyg('html','');
		});
		</script>
	<?php endif?>
	
	<script type="text/javascript" src="http://s522513082.online.de/stat/track.php?mode=js"></script>
	<noscript><img src="http://s522513082.online.de/stat/track_noscript.php" border="0" alt="" width="1" height="1"></noscript>
	
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

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
	
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
  		var js, fjs = d.getElementsByTagName(s)[0];
  		if (d.getElementById(id)) return;
  		js = d.createElement(s); js.id = id;
  		js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&version=v2.3&appId=1606012466308740";
  		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	</script>
	
</html>
