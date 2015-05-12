<?PHP
	include_once("sessionManager.php");
	$SCRIPT_NAME = getenv("SCRIPT_NAME");
	include_once("config.php");
	include_once("logon.php");
	include_once("data.php");
	
	//Image gallery Menue
	if (isset($_SESSION['MENUTREE'])) $menuTree =$_SESSION['MENUTREE']; else $menuTree="";
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
 <head>
	<title><?PHP echo($SiteTitle) ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<?PHP if (strpos(getenv("QUERY_STRING"),"=thumbnails")) { ?> 
		<meta name="robots" content="noindex,follow" />
	<?PHP } else { ?>
		<meta name="robots" content="index,follow" />
	<?PHP } ?>
	<meta name="google-site-verification" content="ognb1O-3TIzQ_1jjeBXEMlhCg1elZ72eda1Lzis7B8g" />
	<meta name="geo.placename" content="Kolozsvár" />
	<meta name="geo.position" content="46.771919;23.592248" />
	<meta name="author" content="Levente Maier" />
	<?PHP if (isset($SiteDescription) && $SiteDescription!="") { ?>
		<meta name="description" content="<?PHP echo($SiteDescription) ?>" />
	<?PHP } else { ?>
		<meta name="description" content="<?PHP echo($SiteTitle) ?>" />
	<?PHP } ?>
	<meta name="keywords" content="Brassai Sámuel iskola líceum Kolozsvár Cluj Klausenburg diák diákok" />
	<meta name="verify-v1" content="jYT06J7jVoHpWvFoNfx7qwVaERZQFvm1REgT7N4jMFA=" />
	
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
	
	<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu.css" />
	<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu-v.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
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
		<link rel="stylesheet" type="text/css" href="css/widgEditor.css" />
		<script type="text/javascript" src="js/widgEditor.js"></script>
	<?php endif?>
	<link rel="stylesheet" type="text/css" href="css/menu.css" />
	
 </head>
<body>


<table border="0" style="width:100%; height:100%;margin-top:0px" >
<tr>
	<td style="vertical-align: top;" rowspan="3">
		<table width="100%" border="0" cellspacing="1" cellpadding="0">
			<tr>
				<td style="width:100px;height:142px">
				    <img src="images/logo.JPG" style="width:100px;height:142px" />
				</td></tr>
			<tr><td>
				<div id="smoothmenu" class="ddsmoothmenu-v">
					<ul>
					<li><a href="index.php">Honoldal</a></li>
					<li><a href="brassai.php">Brassai Sámuel</a></li>
					<li><a href="iskola.php">Líceum története</a></li>
					<li><a href="hometable.php">Diákok</a></li>
					<li><a href="hometable.php?guests=true">Vendégek és tanárok</a></li>
					<li><a href="tablo.php">Tabló</a></li>
					<li><a href="#">A többi osztályok</a>
					  <ul>
						<?PHP if (($_SESSION['scoolYear']==1985) && ($_SESSION['scoolClass']=='12A')) { ?>
					  		<li><a href="index.php?scoolYear=1985&scoolClass=12B">Párhuzamos osztály</a></li>
					  	<?PHP } else  { ?>
					  		<li><a href="index.php?scoolYear=1985&scoolClass=12A">Párhuzamos osztály</a></li>
					  	<?PHP }  ?>
					  </ul>
					<?PHP if (($_SESSION['scoolYear']==1985) && ($_SESSION['scoolClass']=='12A')) { ?>
						<li><a href="#">Régi képek</a>
						<ul>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=CSOPORT">Osztályképek</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=BALLAGAS">Ballagás</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=LASTDAYS">Utolsó órák</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=EMLEKEK">Emlékek</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=SzepIdok">Kirándulások és bulik</a></li>
						</ul></li>
						<li><a href="#">Találkozók</a>
						<ul>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK10">10-éves Találkozó</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK15">15-éves Találkozó</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK20">20-éves Találkozó</a></li>
							<li><a href="#">25-éves Találkozó</a>
							<ul>
								<li><a href="zenetoplista.php">Zenetoplista</a></li>
								<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25">Az iskolánkban</a></li>
								<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25T">Torockói panzió</a></li>
								<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25S">Székelykő</a></li>
							</ul></li>
							<li><a href="talalk30.php">30-éves Találkozó</a></li>
						</ul></li>
					<?PHP }  ?>
					<?PHP if (true && ($_SESSION['scoolYear']==1985) && ($_SESSION['scoolClass']=='12B')) { ?>
						<li><a href="vote.php">30-éves Találkozó</a></li>
					<?PHP }  ?>	
						<li><a href="worldmap.php">Térkép</a></li>
					<?PHP if (userIsLoggedOn()) {	 $person=getPersonLogedOn(); ?>
						<li><a href="editDiak.php" title="<?PHP echo ($person["lastname"].' '.$person["firstname"] ) ?>">Az én adataim</a></li>
					<?PHP }  ?>	
						<li><a href="gb.php" >Vendégkönyv</a></li>
					<?PHP if (userIsAdmin() || (userIsEditor())) { ?>
						<li><a href="admin.php"  >Adminsztráció</a></li>
						<li><a href="ig/ig.php?multipleGalleries=1" target="_new" >Képek</a></li>
					<?PHP }	?>
					<?PHP if (userIsAdmin() ) { ?>
						<li><a href="logingData.php"  >Loging</a></li>
					<?PHP }	?>
						<li><a href="impressum.php"  >Impresszum</a></li>
					</ul>
				</div>
				</td>
			</tr>
		</table>
	<hr />
	<?php echo getTextRes("Like") ?><br /><br />
	<g:plusone size="medium"></g:plusone>
	<br /><br />
	<div><a name="fb_share" type="button_count" share_url="<?php echo('http://'.$_SERVER['SERVER_NAME'].getenv("SCRIPT_NAME").'?'.$_SERVER['QUERY_STRING']);?>" href="http://www.facebook.com/sharer.php"></a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script></div>
	<hr />
	<?PHP 
		writeLogonBox(); 
		foreach ($SupportedLang as $Language) {
			if (isset($_SESSION['LANG']) && ($Language!=$_SESSION['LANG']))
				echo('<a href='.$SCRIPT_NAME.'?language='.$Language.'><img src="images/flag_'.$Language.'.jpg" alt=""/></a>'."\r\n");
		}
	?>
	<td  id="topLine"><h1 class="appltitle">A kolozsvári Brassai Sámuel líceum <?PHP echo(getScoolYear()) ?>-ben végzett diákjai <?PHP echo(getScoolClass()) ?></h1></td>
</tr>
	<?php //writeLogonLine(); ?>
<tr>
	<td class="content">

