<!DOCTYPE html>
<html lang="hu">
  <head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=8,chrome=1" />

<?php
	include_once("sessionManager.php");

	//Change scool year and class if parameters are there
	if (isset($_GET['scoolYear']))   { setAktScoolYear($_GET['scoolYear']); }
	if (isset($_GET['scoolClass']))  { setAktScoolClass($_GET['scoolClass']); }

	$SCRIPT_NAME = getenv("SCRIPT_NAME");
	include_once("config.php");
	include_once("logon.php");
	include_once("data.php");
	
	//Image gallery Menue
	if (isset($_SESSION['MENUTREE'])) $menuTree =$_SESSION['MENUTREE']; else $menuTree="";
?>
	
    <meta name="viewport" content="width=device-width, initial-scale=1">	<title><?PHP echo($SiteTitle) ?></title>
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
	
	<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu.css" />
	<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu-v.css" />
	<link rel="stylesheet" type="text/css" href="css/menu.css" />
	
	<?php if (isset($diakEditStorys)) :?>
		<link rel="stylesheet" href="editor/ui/trumbowyg.min.css">
	<?php endif?>
	
	
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
						<?php if (getAktScoolYear()==1985 && getAKtScoolClass()=='12A') { ?>
					  		<li><a href="index.php?scoolYear=1985&scoolClass=12B">Párhuzamos osztály 12B</a></li>
					  	<?php  } else {  ?>
					  		<li><a href="index.php?scoolYear=1985&scoolClass=12A">Párhuzamos osztály 12A</a></li>
					  	<?php } ?>
					  </ul>
					<?php if (getAktScoolYear()==1985 && getAKtScoolClass()=='12A') : ?>
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
					<?PHP endif  ?>
					<?php if (getAktScoolYear()==1985 && getAKtScoolClass()=='12B') : ?>
						<li><a href="vote.php">30-éves Találkozó</a></li>
					<?PHP endif  ?>	
						<li><a href="worldmap.php">Térkép</a></li>
					<?PHP if (userIsLoggedOn()) {	 $person=getPersonLogedOn(); ?>
						<li><a href="editDiak.php" title="<?PHP echo ($person["lastname"].' '.$person["firstname"] ) ?>">Az én adataim</a></li>
					<?PHP }  ?>	
						<li><a href="gb.php" >Vendégkönyv</a></li>
					<?PHP if (userIsAdmin() || (userIsEditor())) { ?>
						<li><a href="admin.php"  >Adminsztráció</a></li>
					<?PHP }	?>
					<?PHP if (userIsAdmin() ) { ?>
						<li><a href="ig/ig.php?multipleGalleries=1" target="_new" >Képek</a></li>
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
	<div class="fb-like" data-href="<?php echo('http://'.$_SERVER['SERVER_NAME'].getenv("SCRIPT_NAME").'?'.$_SERVER['QUERY_STRING']);?>" data-layout="box_count" data-action="like" data-show-faces="false" data-share="true"></div>
	<hr />
	<?PHP 
		writeLogonBox(); 
		foreach ($SupportedLang as $Language) {
			if (isset($_SESSION['LANG']) && ($Language!=$_SESSION['LANG']))
				echo('<a href='.$SCRIPT_NAME.'?language='.$Language.'><img src="images/flag_'.$Language.'.jpg" alt=""/></a>'."\r\n");
		}
	?>
	<td  id="topLine"><h1 class="appltitle">A kolozsvári Brassai Sámuel líceum <?PHP echo(getAktScoolYear()) ?>-ben végzett diákjai <?PHP echo(getAktScoolClass()) ?></h1></td>
</tr>
	<?php //writeLogonLine(); ?>
<tr>
	<td class="content">

