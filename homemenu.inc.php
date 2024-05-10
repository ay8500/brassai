<?php
ob_start("ob_gzhandler");
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'logon.inc.php';
include_once 'dbBL.class.php';
include_once 'dbDaUser.class.php';
include_once 'dbDaOpinion.class.php';
include_once Config::$lpfw.'dbDaTracker.class.php';

use maierlabs\lpfw\Appl as Appl;
global $db;
global $userDB;
$trackerDb = new \maierlabs\lpfw\dbDaTracker($db->dataBase,!isUserSuperuser());
automaticLogin($userDB);

//Image gallery Menue
if (isset($_SESSION['MENUTREE'])) $menuTree =$_SESSION['MENUTREE']; else $menuTree="";

//Login if crypted loginkey present and correct
if (isset($_GET['key'])) {
    Appl::setMessage(directLogin($userDB,$_GET['key']),"");
}

$actClass = $db->handleClassSchoolChange(getParam("classid"),getParam("schoolid"));
Appl::setMember("actClass",$actClass);
Appl::setMember("actSchool",$db->getSchoolById($actClass!=null?$actClass["schoolID"]:getActSchoolId()));
Appl::setMember( "staffClass",$db->getStafClassBySchoolId($actClass!=null?$actClass["schoolID"]:getActSchoolId()));

handleLogInOff(new dbDaUser($db));
$schoolList = $db->getSchoolList();

//Events
$today = new DateTime();
$xmas = (intval(date("m")) === 12 );
$eventStyle = $xmas?" border-bottom: 2px solid red;":"border:0px";
/*easter to be added/removed */ //$eventStyle = " border-bottom: 2px solid green;";
$haloween = $today >= new DateTime("October 23") && $today < new DateTime("November 6");

if (getActSchoolId()==null || Appl::getMember("actSchool")==null || !isset(Appl::getMember("actSchool")["logo"]) ) {
    $menuSchoolLogo = "images/kolozsvar.png";
} else {
    $menuSchoolLogo = "images" . DIRECTORY_SEPARATOR . $db->getActSchoolFolder() . DIRECTORY_SEPARATOR . Appl::getMember("actSchool")["logo"];
}

?>

<!DOCTYPE html>
<html lang="hu">
  <head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo(Appl::$title==""?Config::$SiteTitle:Appl::$title) ?></title>
	<?PHP if (strstr(getenv("QUERY_STRING"),"=thumbnails")!="") { ?> 
		<meta name="robots" content="noindex,follow" />
	<?PHP } else { ?>
		<meta name="robots" content="index,follow" />
	<?PHP } ?>
	<meta name="geo.placename" content="Kolozsvár" />
	<meta name="geo.position" content="46.771919;23.592248" />
	<meta name="author" content="MaierLabs Germany" />
	<meta name="description" content="<?php echo(Config::$SiteTitle.' '.Appl::$description) ?>" />
	<meta name="keywords" content="<?php echo Appl::getMemberField("actSchool","name","")?> iskola líceum Kolozsvár Cluj Klausenburg diák diákok osztálytárs osztálytalálkozó osztályfelelös ballagás véndiák véndiákok" />
	<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php Appl::addCss("css/bootstrap.min.css");?>
    <?php Appl::addCss("css/menu.css");?>
    <?php Appl::addCss("//fonts.googleapis.com/icon?family=Material+Icons",false,false);?>
    <link rel="canonical" href="<?php echo(Config::$siteUrl.$_SERVER['REQUEST_URI']) ?>">
	<?php Appl::includeCss();?>
    <?php Appl::renderingStarted(); ?>
 </head>
<body>
<?php if (\maierlabs\lpfw\Appl::getMember("firstPicture")!==null) {
    echo('<img id="firstPicture" style="display:block;height:0px;width:0px;" src="'.\maierlabs\lpfw\Appl::getMember("firstPicture")["file"].'" />');
}?>
<div class="homeLogo" style="z-index: -1"><img id="homelogo" class="img-responsive" src="images/BrassaiLiceumNagy.JPG" /></div>

<nav id="main-menu" class="navbar navbar-default" style="background-color: #ffffff00; <?php echo $eventStyle?>" role="navigation">
  <div class="container-fluid" id="mainmenucontainer" >
    <!-- Brand and toggle get grouped for better mobile display -->
      <a class="btn btn-default" style="top:7px; padding:3px; position: absolute" href="start" title="Újdonságok"><img src="<?php echo $menuSchoolLogo ?>" style="border: 1px solid gray; border-radius:7px; height: 38px; margin: -5px;" /></a>
      <div class="navbar-header" style="margin-left:32px;">
          <button type="button" class="navbar-toggle" style="" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
          </button>
      </div>
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1" style="margin-left:22px;">
		<ul class="nav navbar-nav">
            <li>
                <a  href="index" class="dropdown-toggle" data-toggle="dropdown" title="Kolozsvári iskolák">Iskolák<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="index"><img style="display:inline-block;height:22px; margin-right: 6px" src="images/logo.JPG" />Start</a></li>
                    <li><a href="start?all=all"><img style="display:inline-block;height:22px; margin-right: 6px" src="images/kolozsvar.png" />Kolozsvári véndiák újdonságok</a></li>
                    <li><a href="rip?classid=all&schoolid=all"><img style="display:inline-block;height:22px; margin-right: 6px" src="images/candle10.gif" />Emléküket örökké őrizzük</a></li>
                    <?php foreach ($schoolList as $menuSchool) {
                        $selected = $menuSchool["id"]==getActSchoolId()?"font-weight:bold;color:black":""?>
                        <li><a style="<?php echo $selected?>" href="iskola-<?php echo $menuSchool["shortLink"] ?>"><img style="display:inline-block;height:22px; margin-right: 6px" src="images\school<?php echo $menuSchool["id"].DIRECTORY_SEPARATOR.$menuSchool["logo"]?>" /><?php echo $menuSchool["name"] ?></a></li>
                    <?php  }?>
                    <li><a href="search?type=bogancszurbolo"><img style="display:inline-block;height:22px; margin-right: 6px" src="images/bogancszurbolologoxs.jpg" />Bogáncs Zurboló tagok</a> </li>
                    <li><a href="iskola-terkep">Térkép</a></li>
                    <li><a href="zenetoplista?classid=all&schoolid=all">Zenetoplista</a></li>
                    <li><a href="statistics">Statisztika</a></li>
                </ul>
            </li>
            <?php if (($menuActSchool = getActSchool())!==null) { ?>
            <li class="dropdown">
				<a href="index" class="dropdown-toggle" data-toggle="dropdown">Iskolánk<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="iskola-<?php echo $menuActSchool["shortLink"]?>-osztalyok">Osztályok</a> </li>
                    <li><a href="iskola-<?php echo $menuActSchool["shortLink"]?>-info">Iskolánkról</a></li>
                    <li><a href="rip?classid=all">Emléküket örökké őrizzük</a></li>
					<li><a href="hometable?classid=<?php echo Appl::getMemberId("staffClass")?>">Tanáraink</a></li>
                    <li><a href="hometable?guests=true&classid=<?php echo Appl::getMemberId("staffClass")?>">Barátaink</a></li>
                    <?php if ( $menuActSchool["awardName"]!=null) { ?>
                        <li><a href="search?type=jmlaureat&schoolid=<?php echo $menuActSchool['id']?>"><?php echo $menuActSchool["awardName"] ?> díjasok</a></li>
                    <?php } ?>
                    <li><a href="search?type=unknown&schoolid=<?php echo $menuActSchool['id']?>">Nem tudunk róluk</a></li>
                    <li><a href="search?type=incharge&schoolid=<?php echo $menuActSchool['id']?>">Osztályfelelősők</a></li>
                    <li><a href="iskola-<?php echo $menuActSchool["shortLink"]?>-kepek">Iskola képek</a></li>
                    <li><a href="picture?type=schoolID&typeid=<?php echo $menuActSchool['id']?>&album=_tablo_">Iskola tablói</a></li>
        			<li><a href="iskola-<?php echo $menuActSchool["shortLink"]?>-terkep">Térkép</a></li>
        			<li><a href="zenetoplista?classid=all&schoolid=<?php echo $menuActSchool['id']?>">Zenetoplista</a></li>
       			</ul>
      		</li>
            <?php } ?>
      		<?php if ( Appl::getMemberId("actClass")!=null && (Appl::getMember("actClass")!=Appl::getMember("staffClass") || isUserAdmin())) {
                $classStat = $db->getClassStatistics(Appl::getMemberId("actClass"), false);
            ?>
            <li id="classmenu" class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo(getSchoolClassName(Appl::getMember("actClass"),true)); ?><b
                            class="caret"></b></a>
                <ul class="dropdown-menu multi-level">
                    <li><a href="hometable?classid=<?php echo Appl::getMemberId("actClass") ?>">Véndiákok
                            <?php if ($classStat->personCount > 0) { ?><span
                                    class="badge"><?php echo $classStat->personCount ?></span><?php } ?></a></li>
                    <li><a href="hometable?guests=true&classid=<?php echo Appl::getMemberId("actClass") ?>">Vendégek barátok
                            <?php if ($classStat->guestCount > 0) { ?><span
                                    class="badge"><?php echo $classStat->guestCount ?></span><?php } ?></a></li>
                    <li><a href="rip?classid=<?php echo $actClass["id"]?>">Emléküket örökké örizzük</a></li>
                    <li><a href="picture?type=classID&typeid=<?php echo Appl::getMemberId("actClass") ?>">Osztályképek
                            <?php if ($classStat->classPictures > 0) { ?><span
                                    class="badge"><?php echo $classStat->classPictures ?></span><?php } ?></a></li>
                    <?php //<li><a href="chat">Osztálytárs körlevelek</a></li>?>
                    <li><a href="worldmap?classid=<?php echo Appl::getMemberId("actClass") ?>">Térkép</a></li>
                    <?php if (Appl::getMember("actClass")["text"] == "1985 12A") { ?>
                        <li class="dropdown-submenu"><a>Régi képek</a>
                            <ul class="dropdown-menu">
                                <li><a href="pictureGallery?view=thumbnails&gallery=CSOPORT">Osztályképek</a></li>
                                <li><a href="pictureGallery?view=thumbnails&gallery=BALLAGAS">Ballagás</a></li>
                                <li><a href="pictureGallery?view=thumbnails&gallery=LASTDAYS">Utolsó órák</a></li>
                                <li><a href="pictureGallery?view=thumbnails&gallery=EMLEKEK">Emlékek</a></li>
                                <li><a href="pictureGallery?view=thumbnails&gallery=SzepIdok">Kirándulások és
                                        bulik</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu"><a href="#">Találkozók</a>
                            <ul class="dropdown-menu">
                                <li><a href="pictureGallery?view=thumbnails&gallery=TALALK10">10-éves Találkozó</a>
                                </li>
                                <li><a href="pictureGallery?view=thumbnails&gallery=TALALK15">15-éves Találkozó</a>
                                </li>
                                <li><a href="pictureGallery?view=thumbnails&gallery=TALALK20">20-éves Találkozó</a>
                                </li>
                                <li class="dropdown-submenu"><a href="#">25-éves Találkozó</a>
                                    <ul class="dropdown-menu">
                                        <li><a href="zenetoplista">Zenetoplista</a></li>
                                        <li><a href="pictureGallery?view=thumbnails&gallery=TALALK25">Az
                                                iskolánkban</a></li>
                                        <li><a href="pictureGallery?view=thumbnails&gallery=TALALK25T">Torockói
                                                panzió</a></li>
                                        <li><a href="pictureGallery?view=thumbnails&gallery=TALALK25S">Székelykő</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu"><a href="#">30-éves Találkozó</a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="pictureGallery?view=thumbnails&gallery=TALALK30">Osztályfőnöki</a>
                                        </li>
                                        <li><a href="pictureGallery?view=thumbnails&gallery=TALALK30T">Temetőben</a>
                                        </li>
                                        <li><a href="pictureGallery?view=thumbnails&gallery=TALALK30Torocko">Torockón</a>
                                        </li>
                                        <li><a href="pictureGallery?view=thumbnails&gallery=TALALK30BuvoPatak">Buvó
                                                Patak</a></li>
                                        <li><a href="talalk30">Programajánlat</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu"><a
                                            href="pictureGallery?view=thumbnails&gallery=50evesek">50-évesek
                                        Találkozója</a>
                            </ul>
                        </li>
                    <?php } ?>
                    <li><a href="vote?classid=<?php echo Appl::getMemberId("actClass") ?>">A következő Találkozó</a></li>
                    <li><a href="editSchoolClass?classid=<?php echo Appl::getMemberId("actClass") ?>">Osztály: tanárok infók</a></li>
                    <li><a href="zenetoplista?classid=<?php echo Appl::getMemberId("actClass") ?>">Zenetoplista</a></li>
                </ul>
            </li>
            <?php } ?>
            <li>
                <a href="message">Üzenőfal</a>
            </li>
            <li>
                <a href="games" class="noblob">Játékok</a>
            </li>
            <li>
            <form class="navbar-form navbar-left" role="search" action="">
                <div class="input-group input-group" style="margin: 3px;">
                    <button type="button" class="btn btn-default " onclick="showSearchBox();"><span
                                class="glyphicon glyphicon-search"></span> Keres
                    </button>
                </div>
            </form>
            <?php if (isUserLoggedOn()) {
                $person = $db->getPersonLogedOn(); ?>
                <form class="navbar-form navbar-right" role="search">
                    <div class="input-group input-group" style="margin: 3px;">
						<span class="input-group-addon" style="width:130px">
							<?php writePersonLinkAndPicture($person); ?>
						</span>
                        <button type="button" id="uLogoffMenu" class="btn btn-default " onclick="handleLogoff();"><span
                                    class="glyphicon glyphicon-log-out"></span> <?php Appl::_("Logout")?>
                        </button>
                    </div>
                </form>
            <?php } else { ?>
                <form class="navbar-form navbar-right" role="search" action="">
                    <div class="input-group input-group" style="margin: 3px;">
                        <button type="button" id="uLogonMenu" class="btn btn-default " onclick="handleLogon();"><span
                                    class="glyphicon glyphicon-log-in"></span> <?php Appl::_("Login")?>
                        </button>
                    </div>
                </form>
            <?php } ?>
            </li>
            <?php if (isUserAdmin()) {?>
                <li style="top:18px"><span class="badge"><?php echo $trackerDb->getSiteCount($_SERVER['REQUEST_URI'])?></span></li>
            <?php }?>
        </ul>
    </div>
      <?php if ($xmas) {?>
        <div><img style="height: 129px;position: absolute;top:1px; right: 1px;" src="images/xmas.gif"></div>
      <?php } ?>
  </div>
</nav>

<?php writeLogonDiv(); ?>

<?php
\maierlabs\lpfw\Appl::addCssStyle('
  	#searchpersontable, #searchpicturetable{
		width:100%; 
	}
	#searchpersontable td{ 
		padding:1px; 
		vertical-align:middle;
	}
	#searchpersontable tr{
		background: white;
		vertical-align: top;
	}
    #searchpersontable tr:hover {
          background-color: #efefef;
    }
');
?>
<div class="panel panel-default" style="display:none;margin:auto;width:100%;text-align: center;opacity: 0.8;" id="uSearch">
    <div class="panel-heading">
        <b>Keresgélés: név, évfolyam, kép</b>
        <span class="glyphicon glyphicon-remove-circle"  style="float: right;cursor: pointer;" onclick="closeSearch();"></span>
    </div>
    <form action="search" method="get">
        <input type="hidden" value="search" name="action"/>
        <div class="input-group" style="width:300px;margin: 3px;display: inline-table;">
            <span class="input-group-addon" style="width:30px" title="Véndiak neve"><span
                        class="glyphicon glyphicon-search"></span></span>
            <input type="text" class="form-control" placeholder="család- keresztnév, éretségi év, szöveg" id="srcText"
                   name="srcText" value="<?php echo getGetParam("srcText", "") ?>" onkeyup="searchPersonAndPicture();" />
        </div>
        <div style="text-align:center; margin: 3px">
            <button type="button" class="btn btn-default" style="margin: 3px;width: 167px;text-align: left;"
                    onclick="search();"><span class="glyphicon glyphicon-log-in"></span> Keres
            </button>
        </div>
    </form>

    <div style="width: 400px;display: inline-block;margin:5px;padding: 5px; border: solid 1px gray;border-radius:5px; vertical-align: top;">
        <div style="height:30px;padding:5px;width:100%;font-weight:bold;background-color:lightgray;">
            <span class="glyphicon glyphicon-user"></span> Tanárok, diákok <span id="searchpersonbadge"  class="badge">0</span></div>
        <div style="max-height: 200px;overflow-y: scroll;">
        <table id="searchpersontable" >
        </table>
        </div>
    </div>
    <div style="width: 400px;display: inline-block;margin:5px;padding: 5px; border: solid 1px gray;border-radius:5px; vertical-align: top;">
        <div style="height:30px;padding:5px;width:100%;font-weight:bold;background-color:lightgray;"><span class="glyphicon glyphicon-picture"></span> Képek <span id="searchpicturebadge" class="badge">0</span></div>
        <div style="max-height: 200px;overflow-y: scroll;">
            <table id="searchpicturetable">
            </table>
        </div>
    </div>
</div>
<?php

?>
<div id="topLine">
    <h1 class="appltitle">
        <?php /*easter to be changed*/
        /*
            $rpo=(new dbDaOpinion($db))->getOpinionPersonCount('person','easter',2024);
        ?>
        <a href="start?tabOpen=easter" title="<?php echo 'Meglocsolt virágszállak:'.sizeof($rpo->opinion).' locsolók:'.sizeof($rpo->user)?> ">
            <img class="blob" src="images/easter.png" style="width: 50px" />
            <span class="badge" style="left: -20px;position: relative;top: 4px;">
                <?php echo (sizeof($rpo->opinion).'/'.sizeof($rpo->user))?>
            </span>
        </a>
        <?php /*easter end */ ?>
        <?php echo(ucfirst(getActSchoolName())) ?> <span id="o400">egykori </span>diákjai
        <span id="o480"> <?php echo(getActSchoolClassName()) ?></span>
    </h1>
</div>
<div class="sub_title"><?php echo Appl::$subTitle ?></div>
<div class="resultDBoperation"><?php echo Appl::$resultDbOperation ?></div>

<?php
Appl::addJs('js/search.js',true);
?>