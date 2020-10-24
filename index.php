<?PHP
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'config.class.php';
include_once 'dbBL.class.php';

use maierlabs\lpfw\Appl as Appl;

Appl::addCss("css/wrapper.css");
Appl::addJs("js/wrapper.js");

unsetAktClass();
Appl::setSiteSubTitle('Szeretettel köszöntünk a Brassaista véndiákok honlapján<br/>Használd ezt az oldalt, hogy kapcsolatba lépj és maradj egykori Brassaista tanáraiddal, osztálytársaiddal és iskolatársaiddal!');

if (getParam('loginok')=="true")
	Appl::setMessage("Szeretettel üdvözlünk kedves ".getPersonName($db->getPersonByID(getLoggedInUserId())), "success");

if (isset($_SESSION["timeout"])) {
    unset($_SESSION["timeout"]);
    logoutUser();
    Appl::addJsScript('
        $( document ).ready(function() {
            showModalMessage("Idéglenes adatok lejártak","Sajnos rég nem használtad illetve frissitetted ezt az óldalt, idéglenes adataid emiatt törlödtek. <br/><br/>Szeretnénk ha újból bejelentkeznél, vagy egyszerüen csak élvezettel maradnál tovább ezen az oldalon!","warning");
        });
    ');
}

$personIdList=$db->getPersonIdListWithPicture();
$randPersonID=$personIdList[rand(0,sizeof($personIdList)-1)];
$today = new DateTime();
$xmas = $today >= new DateTime("December 6") || $today < new DateTime("February 21");
if ($xmas)
   Appl::addJs("js/snowFalling.js",false,true);

$haloween = $today >= new DateTime("October 23") || $today < new DateTime("November 6");
if ($haloween)
    Appl::addJs("js/haloween.js",false,true);



include("homemenu.inc.php");
?>

<div class="container-fluid">
	<div  style="padding:15px;margin-top:20px" class="col-sm-4" >
		<img src="images/BRASSAIS.JPG"  alt="Brassai Sámuel" /><br/>
		<div style="font-size:12px;font-weight:bold;height:20px">Brassai Sámuel (1800-1897)</div>
	 	<div>
		   	&quot;A tanító, mint a gazda, csak magvakat vet el,<br/>
		   	melyböl a tanítvány elméjében ismeretek teremnek,<br/>
		   	mint a gabona s más termék a földben.&quot;
		</div>
	</div>
	<div  style="padding:15px;" class="col-sm-8" >
		<h4>Lehetőségeid a véndiákok oldalán:</h4>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="start">
                <div class="inlineBox"><img img class="indeximg" src="images/classmatex.png" />
                <?php if ($xmas) { ?>
                    <div style="overflow: visible;width: 0px;height: 0px;">
                        <img style="width: 38px;position: relative;left: 30px;top: -86px;" src="images/xmas.png"></div>
                <?php } ?>
                <?php if ($haloween) { ?>
                    <div style="overflow: visible;width: 0px;height: 0px;">
                        <img style="width: 41px;position: relative;left: 34px;top: -98px;" src="images/haloween.png"></div>
                <?php } ?>
                </div>
			    <div class="inlineBox" style="vertical-align: middle;">Újdonságok a véndiákok oldalán.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="hometable?classid=<?php echo $db->getStafClassIdBySchoolId(getAktSchoolId())?>">
                <?php if ($haloween) { ?>
                    <div class="inlineBox"><img img class="indeximg" src="images/haloweenfun.png" /></div>
                <?php } else { ?>
                    <div class="inlineBox"><img img class="indeximg" src="images/teacher.png" /></div>
                <?php } ?>
                <div class="inlineBox" style="vertical-align: middle;">Tanáraink névsorának bővítése és kiegészítése.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="message">
                <div class="inlineBox"><img class="indeximg" src="images/speech-bubble.png" />
                    <?php if ($xmas) { ?>
                        <div style="overflow: visible;width: 0px;height: 0px;">
                            <img style="width: 38px;position: relative;left: 5px;top: -74px;" src="images/xmas.png"></div>
                    <?php } ?>
                    <?php if ($haloween) { ?>
                        <div style="overflow: visible;width: 0px;height: 0px;">
                            <img style="width: 42px;position: relative;left: 9px;top: -91px;" src="images/haloween.png"></div>
                    <?php } ?>
                </div>
                <div class="inlineBox" style="vertical-align: middle;">Üzenetek küldése osztálytársnak iskolatársaknak vagy az egész világnak.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="rip">
                <div class="inlineBox"><img img class="indeximg" src="images/candlerip.png" /></div>
			    <div class="inlineBox" style="vertical-align: middle;">Gyertyák tanáraink és iskolatársaink emlékére.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="editDiak?uid=658&tabOpen=school">
                <div class="inlineBox"><img img class="indeximg" src="images/hand-holding-cv.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Történetek, képek és életrajz megosztása.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="worldmap">
                <div class="inlineBox"><img img class="indeximg" src="images/geography.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Térképen megjelenített szétszóródása az osztálytársaknak.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="vote?classid=74">
                <?php if ($haloween) { ?>
                    <div class="inlineBox"><img img class="indeximg" src="images/haloweenpirat.png" /></div>
                <?php } else { ?>
                    <div class="inlineBox"><img img class="indeximg" src="images/vote.png" /></div>
                <?php } ?>
                <div class="inlineBox" style="vertical-align: middle;">Találkozók szervezésére alkalmas szavazás.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="#">
                <div class="inlineBox"><img img class="indeximg" src="images/group.png" />
                    <?php if ($xmas) { ?>
                        <div style="overflow: visible;width: 0px;height: 0px;">
                            <img style="width: 25px;position: relative;left: 23px;top: -68px;" src="images/xmas.png"></div>
                    <?php } ?>
                    <?php if ($haloween) { ?>
                        <div style="overflow: visible;width: 0px;height: 0px;">
                                <img style="width: 25px;position: relative;left: 27px;top: -78px;" src="images/haloween.png"></div>
                    <?php } ?>

                </div>
                <div class="inlineBox" style="vertical-align: middle;">Osztályfelelősők körlevelet küldhetnek volt osztálytársuknak.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="zenetoplista?classid=all">
                <?php if ($haloween) { ?>
                    <div class="inlineBox"><img img class="indeximg" src="images/haloweenmusic.png" /></div>
                <?php } else { ?>
                    <div class="inlineBox"><img img class="indeximg" src="images/record-player.png" /></div>
                <?php } ?>
                <div class="inlineBox" style="vertical-align: middle;">Véndiákok zene toplistája.</div>
            </a>
		</div>
	</div>
	<div class="row">&nbsp;</div>
	<div id="wrapper"></div>
	<div >  			
		Ez az oldal <B>1997. junius 11.</B>-e óta elérhető.	Utoljára módósítva <b>2019. december 12.</b>-én.
	</div>
</div>

<?php
Appl::addJsScript("onResize(430);",true);
include("homefooter.inc.php");
?>

