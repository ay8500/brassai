<?php
	include_once("tools/sessionManager.php");
	include_once("config.php");
	include_once("logon.php");
	include_once("data.php");
	$SCRIPT_NAME = getenv("SCRIPT_NAME");
	//Image gallery Menue
	if (isset($_SESSION['MENUTREE'])) $menuTree =$_SESSION['MENUTREE']; else $menuTree="";
	
	if (getParam("classid", "")!="") {
		$class=$db->getClassByText(getParam("classid",""));
		if ($class==null)
			$class=$db->getClassById(getGetParam("classid", ""));
		setAktClass($class["id"]);
	}
	
	$db = new dbDAO;
	
	
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
	<meta name="keywords" content="Brassai Sámuel iskola líceum Kolozsvár Cluj Klausenburg diák diákok osztálytárs osztályfelelös ballagás" />
	<meta name="verify-v1" content="jYT06J7jVoHpWvFoNfx7qwVaERZQFvm1REgT7N4jMFA=" />
	
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->	 
	<link rel="stylesheet" type="text/css" href="css/menu.css?v=08062017" />
	<?php if (isset($diakEditStorys)) :?>
		<link rel="stylesheet" href="editor/ui/trumbowyg.min.css">
	<?php endif?>
	<?php if (isset($siteHeader)) { 
		echo $siteHeader;
	}?>
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
					<li><a href="index.php">Start</a></li>
					<li><a href="start.php">Újdonságok</a></li>
					<li><a href="hometable.php?classid=0">Tanáraink</a></li>
        			<li><a href="brassai.php">Brassai Sámuel élete</a></li>
        			<li><a href="iskola.php">Liceum története</a></li>
        			<li><a href="picture.php?type=schoolID&typeid=1">Iskola képek</a></li>
        			<li><a href="picture.php?type=tablo&typeid=tablo">Iskola tablói</a></li>
        			<li><a href="worldmap.php?classid=0">Térkép</a></li>
        			<li><a href="zenetoplista.php?classid=0">Zenetoplista</a></li>
       			</ul>
      		</li>
      		<?php if (getAktClassId()!=0 || userIsAdmin()) {?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo(getAktClassName());?><b class="caret"></b></a>
					<ul class="dropdown-menu multi-level">
						<li><a href="hometable.php?classid=<?php echo getAktClassId(); ?>">Véndiákok</a></li>
						<li><a href="hometable.php?guests=true&classid=<?php echo getAktClassId(); ?>">Vendégek és barátok</a></li>
						<li><a href="worldmap.php">Térkép</a></li>
						<li><a href="picture.php">Tabló és csoportképek</a></li>
						<?php if ( getRealId(getAktClass())==$db->getClassIdByText("1985 12A")) : ?>
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
								<li class="dropdown-submenu"><a href="#">30-éves Találkozó</a>
									<ul class="dropdown-menu">
										<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK30">Osztályfőnöki</a></li>
										<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK30T">Temetőben</a></li>
										<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK30Torocko">Torockón</a></li>
										<li><a href="pictureGallery.php?view=thumbnails&gallery=TALALK30BuvoPatak">Buvó Patak</a></li>
										<li><a href="talalk30.php">Programajánlat</a></li>
									</ul>
								</li>
							</ul>
						</li>
						<?PHP endif  ?>
						<li><a href="vote.php">A következő Találkozó</a></li>
						<li><a href="zenetoplista.php">Zenetoplista</a></li>
						<li><a href="editclass.php?classid=<?php echo getAktClassId();?>">Osztályinformációk</a></li>
					</ul>
	      		</li>
	      	<?php } ?>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">Osztályok<b class="caret"></b></a>
			  	<ul class="dropdown-menu" style="min-width: <?php echo userIsAdmin()?530:430?>px;columns:3; list-style-position: inside;">
		  		<li><a href="editclass.php?action=newclass">Új osztály</a></li>
			  	<?php
			  		$classes = $db->getClassList();
			  		foreach($classes as $cclass) {
			  			if ($cclass["id"]>0) {
				  			if (getAktClassId()==$cclass["id"]) 
				  				$aktualClass="actual_class_in_menu";
				  			else 
				  				$aktualClass="";
				  			?>
				  			<li>
				  				<a style="display: inline-block;" class="<?php echo($aktualClass);?>" href="hometable.php?classid=<?php echo($cclass["id"]);?>">
				  					<?php echo($cclass["text"]); ?>
				  				</a>
			  					<?php $stat=$db->getClassStatistics($cclass["id"]);?>
				  				<span class="badge" title="diákok száma"><?php echo $stat->personCount?></span>
				  				<?php if (userIsAdmin()) {?>
					  				<span class="badge" title="képek száma"><?php echo $stat->personWithPicture+$stat->personPictures+$stat->classPictures?></span>
				  				<?php }?>
				  			</li>
			  		<?php }
			  		}
			  	?>
		  		<li><a href="editclass.php?action=newclass">Új osztály</a></li>
			  	</ul>
			</li>
			<li>
				<a href="message.php">Ünzenőfal</a>
			</li>
			<form class="navbar-form navbar-left" role="search" action="">
				<div class="input-group input-group" style="margin: 3px;">
					<button type="button" id="uLogonMenu" class="btn btn-default " onclick="showSearchBox();" ><span class="glyphicon glyphicon-search" ></span> Keres</button>
				</div>
			</form>
			<?php if (userIsLoggedOn()) {
				$person=getPersonLogedOn();?>
				<form class="navbar-form navbar-right" role="search">
					<div class="input-group input-group" style="margin: 3px;">
						<span class="input-group-addon" style="width:130px">
							<?php writePersonLinkAndPicture($person);?>
						</span>
						<button type="button" id="uLogoffMenu" class="btn btn-default " onclick="handleLogoff();" ><span class="glyphicon glyphicon-log-out"></span> Kijelentkezés</button>
					</div>
				</form>
			<?php } else {?>
			<form class="navbar-form navbar-right" role="search" action="">
				<div class="input-group input-group" style="margin: 3px;">
					<button type="button" id="uLogonMenu" class="btn btn-default " onclick="handleLogon();" ><span class="glyphicon glyphicon-log-in" ></span> Bejelentkezés</button>
				</div>
			</form>
			<?php } ?>
		</ul>
	</div>
</div>
</nav>

<?PHP writeLogonDiv();	?>

<div class="panel panel-default" style="display:none;margin:auto;width:220px;" id="uSearch" >
	<div class="panel-heading" >
		<b>Keresgélés</b><span class="glyphicon glyphicon-remove-circle" style="float: right;cursor: pointer;" onclick="closeSearch();"></span>
	</div>
	<form action="search.php" method="get">
		<input type="hidden" value="search" name="action"/>
		<div class="input-group input-group" style="margin: 3px;">
    		<span class="input-group-addon" style="width:30px" title="Véndiak neve"><span class="glyphicon glyphicon-search"></span></span>
    		<input type="text" class="form-control"  placeholder="családnév keresztnév" id="srcText" name="srcText" value="<?php echo getGetParam("srcText", "")?>">
		</div>
		<div style="text-align:center; margin: 3px">
			<button type="button" class="btn btn-default" style="margin: 3px;width: 167px;text-align: left;" onclick="search();"><span class="glyphicon glyphicon-log-in"></span> Keres</button>
		</div>
	</form>
</div>

<div id="topLine">
	<h1 class="appltitle">
		<span id="o1024" >A kolozsvári </span>
		Brassai Sámuel líceum <span id="o400" >egykori </span>diákjai 
		<span id="o480" ><?PHP echo(getAktClassName()) ?></span>
	</h1>
</div>

<script type="text/javascript">
	function showSearchBox(noAnimation) {
	    closeLogin();
		if (noAnimation==null || noAnimation==false)
			$("#uSearch").slideDown("slow");
		else
		    $("#uSearch").show();
		onResize(135);
	}
	
	function closeSearch() {
		$("#uSearch").slideUp("slow");
		onResize(0);
	}

	function search() {
		document.location.href="search.php?srcText="+$("#srcText").val();
	}
	
</script>
