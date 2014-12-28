<?PHP
	include_once("sessionManager.php");
	$SCRIPT_NAME = getenv("SCRIPT_NAME");
	include_once("config.php");
	include_once("logon.php");
	include_once("data.php");
	
	if (isset($_SESSION['MENUTREE'])) $menuTree =$_SESSION['MENUTREE']; else $menuTree="";
	
	if (!(isset($googleMap) && ($googleMap))) 
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
 <head>
	<title><?PHP echo($SiteTitle) ?></title>
	<link rel="stylesheet" type="text/css" href="menu.css" />
	<?PHP if (strpos(getenv("QUERY_STRING"),"=thumbnails")) { ?> 
		<meta name="robots" content="noindex,follow" />
	<?PHP } else { ?>
		<meta name="robots" content="index,follow" />
	<?PHP } ?>
	<meta name="google-site-verification" content="ognb1O-3TIzQ_1jjeBXEMlhCg1elZ72eda1Lzis7B8g" />
	<meta name="geo.placename" content="Kolozsv�r" />
	<meta name="geo.position" content="46.771919;23.592248" />
	<meta name="author" content="Levente Maier" />
	<?PHP if ($SiteDescription!="") { ?>
		<meta name="description" content="<?PHP echo($SiteDescription) ?>" />
	<?PHP } else { ?>
		<meta name="description" content="<?PHP echo($SiteTitle) ?>" />
	<?PHP } ?>
	<meta name="keywords" content="Brassai S�muel iskola l�ceum Kolozsv�r Cluj Klausenburg di�k di�kok" />
	<meta name="verify-v1" content="jYT06J7jVoHpWvFoNfx7qwVaERZQFvm1REgT7N4jMFA=" />
	
	<script type="text/javascript" src="http://www.blue-l.de/stat/track.php?mode=js"></script>
	<noscript><img src="http://www.blue-l.de/stat/track_noscript.php" border="0" alt="" width="1" height="1"></noscript>
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
	
	<link rel="stylesheet" type="text/css" href="ddsmoothmenu.css" />
	<link rel="stylesheet" type="text/css" href="ddsmoothmenu-v.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript" src="js/ddsmoothmenu.js"></script>
	<script type="text/javascript">
	  ddsmoothmenu.init({	mainmenuid: 'smoothmenu', orientation: 'v', classname: 'ddsmoothmenu-v', contentsource: "markup" })
	</script>

 </head>
<body>

<?PHP } ?>
<table border="0" style="width:100%; height:100%;margin-top:0px" >
<tr>
	<td style="vertical-align: top;" rowspan="2">
		<table width="100%" border="0" cellspacing="1" cellpadding="0">
			<tr>
				<td style="width:100px;height:142px">
				    <img src="images/logo.JPG" style="width:100px;height:142px" />
				</td></tr>
			<tr><td>
				<div id="smoothmenu" class="ddsmoothmenu-v">
					<ul>
					<li><a href="index.php">Honoldal</a></li>
					<li><a href="brassai.php">Brassai S�muel</a></li>
					<li><a href="iskola.php">L�ceum t�rt�nete</a></li>
					<li><a href="hometable.php">Di�kok</a></li>
					<li><a href="hometable.php?guests=true">Vend�gek �s tan�rok</a></li>
					<li><a href="tablo.php">Tabl�</a></li>
					<li><a href="#">A t�bbi oszt�lyok</a>
					  <ul>
						<?PHP if (($_SESSION['scoolYear']==1985) && ($_SESSION['scoolClass']=='12A')) { ?>
					  		<li><a href="index.php?scoolYear=1985&scoolClass=12B">P�rhuzamos oszt�ly</a></li>
					  	<?PHP } else  { ?>
					  		<li><a href="index.php?scoolYear=1985&scoolClass=12A">P�rhuzamos oszt�ly</a></li>
					  	<?PHP }  ?>
					  </ul>
					<?PHP if (($_SESSION['scoolYear']==1985) && ($_SESSION['scoolClass']=='12A')) { ?>
						<li><a href="#">R�gi k�pek</a>
						<ul>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=CSOPORT">Oszt�lyk�pek</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=BALLAGAS">Ballag�s</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=LASTDAYS">Utols� �r�k</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=EMLEKEK">Eml�kek</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=SzepIdok">Kir�ndul�sok �s bulik</a></li>
						</ul></li>
						<li><a href="#">Tal�lkoz�k</a>
						<ul>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK10">10-�ves Tal�lkoz�</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK15">15-�ves Tal�lkoz�</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK20">20-�ves Tal�lkoz�</a></li>
							<li><a href="#">25-�ves Tal�lkoz�</a>
							<ul>
								<li><a href="zenetoplista.php">Zenetoplista</a></li>
								<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25">Az iskol�nkban</a></li>
								<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25T">Torock�i panzi�</a></li>
								<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25S">Sz�kelyk�</a></li>
							</ul></li>
							<li><a href="vote.php">30-�ves Tal�lkoz�</a></li>
						</ul></li>
					<?PHP }  ?>
					<?PHP if (true && ($_SESSION['scoolYear']==1985) && ($_SESSION['scoolClass']=='12B')) { ?>
						<li><a href="vote.php">30-�ves Tal�lkoz�</a></li>
					<?PHP }  ?>	
						<li><a href="worldmap.php">T�rk�p</a></li>
					<?PHP if (isset($_SESSION['UID'])&&($_SESSION['UID']>0)) {	 $person=getPersonLogedOn(); ?>
						<li><a href="editDiak.php" title="<?PHP echo ($person["lastname"].' '.$person["firstname"] ) ?>">Az �n adataim</a></li>
					<?PHP }  ?>	
						<li><a href="gb.php" >Vend�gk�nyv</a></li>
					<?PHP if (userIsAdmin() || (userIsEditor())) { ?>
						<li><a href="admin.php"  >Adminsztr�ci�</a></li>
						<li><a href="ig/ig.php?multipleGalleries=1" target="_new" >K�pek</a></li>
					<?PHP }	?>
					<?PHP if (userIsAdmin() ) { ?>
						<li><a href="logingData.php"  >Loging</a></li>
					<?PHP }	?>
						<li><a href="impressum.php"  >Impresszum</a></li>
					</ul>
				</div>
				<td class="nav"> 
			</td></tr>
		</table>
	<hr />
	Ez az oldal tetszik!<br />
	<b>Google</b>
	<g:plusone size="medium"></g:plusone>
	<br /><br />
	Facebook
	<div><a name="fb_share" type="button_count" share_url="<?php echo('http://'.$_SERVER['SERVER_NAME'].getenv("SCRIPT_NAME").'?'.$_SERVER['QUERY_STRING']);?>" href="http://www.facebook.com/sharer.php"></a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script></div>
	<br />
	iwiw
	<div><script type='text/javascript'>document.write("<iframe src='http://iwiw.hu/like.jsp?u="+encodeURIComponent(document.location)+"&title="+encodeURIComponent(document.title)+"&t=tetszik&s=white' width='90px' height='21px' style='border: none' scrolling='no' frameBorder='0'></iframe>");</script></div>
	<hr />
	<?PHP 
		writeLogonBox(); 
		$googleMap = false;
	?>
	<td><h1 class="appltitle">A kolozsv�ri Brassai S�muel l�ceum <?PHP echo(getScoolYear()) ?>-ben v�gzett di�kjai <?PHP echo(getScoolClass()) ?></h1></td>
</tr>
	<td class="content">

