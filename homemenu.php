<?php
	include_once("sessionManager.php");
	include_once("config.php");
	include_once("logon.php");
	include_once("data.php");
	$SCRIPT_NAME = getenv("SCRIPT_NAME");
	//Image gallery Menue
	if (isset($_SESSION['MENUTREE'])) $menuTree =$_SESSION['MENUTREE']; else $menuTree="";
?>
<!DOCTYPE html>
<html lang="hu">
  <head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?PHP echo($SiteTitle) ?></title>
	<?PHP if (strstr(getenv("QUERY_STRING"),"=thumbnails")!="") { ?> 
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
	
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->	 
	<link rel="stylesheet" type="text/css" href="css/menu.css" />
	<?php if (isset($diakEditStorys)) :?>
		<link rel="stylesheet" href="editor/ui/trumbowyg.min.css">
	<?php endif?>
 </head>
<body>
<div class="homeLogo"><img id="homelogo" class="img-responsive" src="images/BrassaiLiceumNagy.JPG" /></div>

<nav id="main-menu" class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" style="float:none;" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<ul class="nav navbar-nav">
			<li class="dropdown">
				<a href="index.php" class="dropdown-toggle" data-toggle="dropdown">Iskolánkról<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="index.php">Üdvözlet</a></li>
        			<li><a href="brassai.php">Brassai Sámuel élete</a></li>
        			<li><a href="iskola.php">Liceum története</a></li>
       			</ul>
      		</li>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo(getAktScoolYear()."-".getAKtScoolClass()) ?><b class="caret"></b></a>
				<ul class="dropdown-menu multi-level">
					<li><a href="hometable.php">Diákok</a></li>
					<li><a href="hometable.php?guests=true">Vendégek és tanárok</a></li>
					<li><a href="worldmap.php">Térkép</a></li>
					<li><a href="tablo.php">Tabló</a></li>
					<?php if (getAktScoolYear()=="1985" && getAKtScoolClass()=='12A') : ?>
					<li class="dropdown-submenu"><a>Régi képek</a>
						<ul class="dropdown-menu">
							<li><a href="pictureGallery.php?view=thumbnails&gallery=CSOPORT">Osztályképek</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=BALLAGAS">Ballagás</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=LASTDAYS">Utolsó órák</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=EMLEKEK">Emlékek</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=SzepIdok">Kirándulások és bulik</a></li>
						</ul>
					</li>
					<li class="dropdown-submenu"><a href="#">Találkozók</a>
						<ul class="dropdown-menu">
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK10">10-éves Találkozó</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK15">15-éves Találkozó</a></li>
							<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK20">20-éves Találkozó</a></li>
							<li class="dropdown-submenu"><a href="#">25-éves Találkozó</a>
								<ul class="dropdown-menu">
									<li><a href="zenetoplista.php">Zenetoplista</a></li>
									<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25">Az iskolánkban</a></li>
									<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25T">Torockói panzió</a></li>
									<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK25S">Székelykő</a></li>
								</ul>
							</li>
							<li><a href="talalk30.php">30-éves Találkozó</a></li>
						</ul>
					</li>
					<?PHP endif  ?>
					<?php if (getAktScoolYear()==1985 && getAKtScoolClass()=='12B') : ?>
					<li><a href="vote.php">30-éves Találkozó</a></li>
					<?PHP endif  ?>	
				</ul>
      		</li>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">A többi osztályok</a>
			  	<ul class="dropdown-menu">
				<?php if (getAktScoolYear()==1985 && getAKtScoolClass()=='12A') { ?>
			  		<li><a href="hometable.php?scoolYear=1985&scoolClass=12B">Párhuzamos osztály 12B</a></li>
			  	<?php  } else {  ?>
			  		<li><a href="hometable.php?scoolYear=1985&scoolClass=12A">Párhuzamos osztály 12A</a></li>
			  	<?php } ?>
			  	</ul>
			</li>
			<?php if (userIsLoggedOn()) {?>
			<form class="navbar-form navbar-right" role="search">
				<div class="input-group input-group" style="margin: 3px;">
					<span class="input-group-addon" style="width:130px">
						<img src="images/<?php echo getPersonLogedOn()["picture"] ?>" style="height:20px; border-round:3px;" />
						<a href="editDiak.php"><?php echo getPersonLogedOn()["lastname"]." ".getPersonLogedOn()["firstname"] ?></a>
					</span>
					<button id="uLogonMenu" type="button" class="btn btn-default " onclick="handleLogoff();" ><span class="glyphicon glyphicon-log-out"></span> Kijelenkezés</button>
				</div>
			</form>
			<?php } else {?>
			<form class="navbar-form navbar-right" role="search">
				<button id="uLogonMenu" type="button" class="btn btn-default " onclick="handleLogon();" ><span class="navbar-font glyphicon glyphicon-log-in" > Bejelenkezés</span><span> </span></button>
			</form>
			<?php } ?>
		</ul>
	</div>
</div>
</nav>

<?PHP writeLogonDiv();	?>
<div id="topLine"><h1 class="appltitle">
	<span id="o1024" >A kolozsvári </span>
	Brassai Sámuel líceum <span id="o400" >egykori </span>diákjai 
	<span id="o480" ><?PHP echo(getAktScoolYear()." ".getAktScoolClass()) ?></span>
</h1></div>

