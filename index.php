<?PHP 
include_once("tools/sessionManager.php");
include_once("data.php");

if (!isset($siteHeader)) $siteHeader='';
$siteHeader .='<link rel="stylesheet" type="text/css" href="css/wrapper.css?v=02052018" /> ';
$showWrapper=true;

unsetAktClass();



include("homemenu.php");
?>
<div class="sub_title">Szeretettel köszöntünk a Brassaista Véndiákok honlapján<br/>Használd ezt az oldalt, hogy kapcsolatba lépj és maradj egykori Brassaista osztálytársaiddal és iskolatársaiddal!<br/></div>
<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
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
		<div class="col-sm-4">
			<a class="inlineBox" href="editDiak.php?action=newperson"><img img class="indeximg" src="images/classmatex.png" /></a>
			<div class="inlineBox">Véndiákok névsorának bővítése és ápolása.</div>
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="hometable.php?classid=0"><img img class="indeximg" src="images/teacher.png" /></a>
			<div class="inlineBox">Tanáraink névsorának bővítése és kiegészítése.</div>
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="message.php"><img class="indeximg" src="images/speech-bubble.png" /></a>
			<div class="inlineBox">Üzenetek küldése osztálytársnak iskolatársaknak vagy az egész világnak.</div>
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="rip.php"><img img class="indeximg" src="images/candleicon.jpg" /></a>
			<div class="inlineBox">Emléküket örökké őrizzük.<br/>Gyújts te is gyertyát tanáraid és iskolatársaid emlékére.</div> 
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="editDiak.php?uid=levi&tabOpen=4"><img img class="indeximg" src="images/hand-holding-cv.png" /></a>
			<div class="inlineBox">Történetek, képek és életrajz megosztása.</div> 
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="worldmap.php"><img img class="indeximg" src="images/geography.png" /></a>
			<div class="inlineBox">Térképen megjelenített szétszóródása az osztálytársaknak.</div> 
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="vote.php?classid=64"><img img class="indeximg" src="images/vote.png" /></a>
			<div class="inlineBox">Találkozók szervezésére alkalmas szavazati lista.</div> 
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="#"><img img class="indeximg" src="images/group.png" /></a>
			<div class="inlineBox">Osztályfelelősők körlevelet (E-mailt) küldhetnek volt osztálytársuknak.</div> 
		</div>
		<div class="col-sm-4">
			<a class="inlineBox" href="zenetoplista.php?classid=0"><img img class="indeximg" src="images/record-player.png" /></a>
			<div class="inlineBox">Véndiákok toplistája, milyen zenére mullatnak az egykori diákok.</div> 
		</div>
	</div>
	<div class="row">&nbsp;</div>
	<div id="wrapper"></div>
	<div >  			
		Ez az oldal <B>1997. junius 11.</B>-e óta elérhető.	Utoljára módósítva <b>2018. február 17.</b>-én.
	</div>
</div>

<?php  include ("homefooter.php");?>

