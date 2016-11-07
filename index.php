<?PHP 
include_once("tools/sessionManager.php");
include_once("data.php");

if (!isset($siteHeader)) $siteHeader='';
$siteHeader .='<link rel="stylesheet" type="text/css" href="css/wrapper.css" /> ';

include("homemenu.php");
?>
<div class="sub_title">Szeretettel köszöntünk a Brassaista Véndiákok honlapján</div>
<div class="container-fluid">
	<div  style="padding:15px;" class="col-sm-8" >
		<h4>Használd ezt az oldalt, hogy kapcsolatba lépj és maradj egykori Brassaista osztálytársaiddal és iskolatársaiddal!</h4>
		<h5>Lehetőségeid a véndiákok oldalán:</h5>
		<div class="col-sm-4">
			<a href="hometable.php?classid=64"><img img class="indeximg" src="images/classmatex.png" /></a><br />
			Véndiákok névsorának bővítése és ápolása
		</div>
		<div class="col-sm-4">
			<a href="hometable.php?classid=0"><img img class="indeximg" src="images/teacher.png" /></a><br />
			Tanáraink névsorának bővítése és ápolása
		</div>
		<div class="col-sm-4">
			<a href="message.php"><img class="indeximg" src="images/speech-bubble.png" /></a><br />
			Üzenetek küldése osztálytársnak iskolatársaknak vagy az egész világnak.
		</div>
		<div class="col-sm-4">
			<a href="editDiak.php?uid=levi&tabOpen=0"><img img class="indeximg" src="images/identification-card.png" /></a><br />
			Személyes adatok beállítása. 
		</div>
		<div class="col-sm-4">
			<a href="editDiak.php?uid=levi&tabOpen=4"><img img class="indeximg" src="images/hand-holding-cv.png" /></a><br />
			Történetek vagy életrajz megosztása. 
		</div>
		<div class="col-sm-4">
			<a href="worldmap.php"><img img class="indeximg" src="images/geography.png" /></a><br />
			Térképen megjelenített szétszóródása az osztálytársaknak. 
		</div>
		<div class="col-sm-4">
			<a href="vote.php"><img img class="indeximg" src="images/vote.png" /></a><br />
			Találkozók szervezésére alkalmas szavazati lista 
		</div>
		<div class="col-sm-4">
			<a href="#"><img img class="indeximg" src="images/group.png" /></a><br />
			Osztályfelelősők körlevelet (E-mail) küldhetnek volt osztálytársuknak. 
		</div>
		<?php if(userIsAdmin()) :?>
		<div class="col-sm-4">
			<a href="zenetoplista.php"><img img class="indeximg" src="images/record-player.png" /></a><br />
			Zenetoplista, milyen zenére mullatnak a véndiákok. 
		</div>
		<?php endif;?>
	</div>
	<div  style="padding:15px;margin-top:20px" class="col-sm-4" >
		<img src="images/BRASSAIS.JPG"  alt="Brassai Sámuel" /><br/>
		<div style="font-size:12px;font-weight:bold;height:20px">Brassai Sámuel (1800-1897)</div>
	 	<div>
		   	&quot;A tanító, mint a gazda, csak magvakat vet el,<br/>
		   	melyböl a tanítvány elméjében ismeretek teremnek,<br/>
		   	mint a gabona s más termék a földben.&quot;
		</div>
	</div>
	<div class="row">&nbsp;</div>
	<div id="wrapper"></div>
	<div >  			
		Ez az oldal <B>1997. junius 11.</B>-e óta elérhető.	Utoljára módósítva <b>2016. oktober 3.</b>-án.
	</div>
</div>
<?php  include ("homefooter.php");?>
<script type="text/javascript" src="js/wrapper.js"></script>
