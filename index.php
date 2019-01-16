<?PHP 
include_once 'tools/sessionManager.php';
include_once 'tools/appl.class.php';
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
			<a class="inlineBox" href="start.php">
                <div class="inlineBox"><img img class="indeximg" src="images/classmatex.png" /></div>
			    <div class="inlineBox" style="vertical-align: middle;">Újdonságok a véndiákok oldalán.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="hometable.php?classid=10">
                <div class="inlineBox"><img img class="indeximg" src="images/teacher.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Tanáraink névsorának bővítése és kiegészítése.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="message.php">
                <div class="inlineBox"><img class="indeximg" src="images/speech-bubble.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Üzenetek küldése osztálytársnak iskolatársaknak vagy az egész világnak.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="rip.php">
                <div class="inlineBox"><img img class="indeximg" src="images/candleicon.jpg" /></div>
			    <div class="inlineBox" style="vertical-align: middle;">Gyújts te is gyertyát tanáraid és iskolatársaid emlékére.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="editDiak.php?uid=levi&tabOpen=school">
                <div class="inlineBox"><img img class="indeximg" src="images/hand-holding-cv.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Történetek, képek és életrajz megosztása.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="worldmap.php">
                <div class="inlineBox"><img img class="indeximg" src="images/geography.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Térképen megjelenített szétszóródása az osztálytársaknak.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="vote.php?classid=74">
                <div class="inlineBox"><img img class="indeximg" src="images/vote.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Találkozók szervezésére alkalmas szavazati lista.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="#">
                <div class="inlineBox"><img img class="indeximg" src="images/group.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Osztályfelelősők körlevelet (E-mailt) küldhetnek volt osztálytársuknak.</div>
            </a>
		</div>
		<div class="col-sm-4" style="margin-top: 14px;">
			<a class="inlineBox" href="zenetoplista.php?classid=all">
                <div class="inlineBox"><img img class="indeximg" src="images/record-player.png" /></div>
                <div class="inlineBox" style="vertical-align: middle;">Véndiákok toplistája, milyen zenére mullatnak az egykori diákok.</div>
            </a>
		</div>
	</div>
	<div class="row">&nbsp;</div>
	<div id="wrapper"></div>
	<div >  			
		Ez az oldal <B>1997. junius 11.</B>-e óta elérhető.	Utoljára módósítva <b>2018. december 12.</b>-én.
	</div>
</div>

<?php include("homefooter.inc.php");?>

