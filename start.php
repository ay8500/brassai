<?php
include_once 'tools/sessionManager.php';
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once 'data.php';

use \maierlabs\lpfw\Appl as Appl;

$userId=getIntParam("userId",-1);
if ($userId>=0) {
	$db->savePersonFacebookId($userId,$_SESSION["FacebookId"]);
}
unsetAktClass();

Appl::$subTitle = 'Újdonságok';
include("homemenu.php");
include_once 'editDiakCard.php';

function showRecentChanges($db) {
    $ids=$db->getRecentChangeList(new \DateTime(), getIntParam("limit",30));
    foreach ($ids as $id) {
        if ($id["type"]=="person") {
            $person = $db->getPersonByID($id["id"]);
            displayPerson($db,$person,true,true);
        } elseif ($id["type"]=="picture") {
            $picture=$db->getPictureById($id["id"]);
            displayPicture($db,$picture);
        }

    }
}

?>
<div class="container-fluid">
	<div class="panel panel-default " >

		<div class="panel-heading">
			<h4><span class="glyphicon glyphicon-user"></span> Új személyek, fényképek, frissitések </h4>
		</div>
		<div class="panel-body">
		<?php showRecentChanges($db);?>
		</div>
		<a href="start.php?limit=100" class="btn btn-default" style="margin:10px;text-decoration: none;" >Többet szeretnék látni</a>
        <a href="start.php?limit=300" class="btn btn-default" style="margin:10px;text-decoration: none;" >Sokkal többet szeretnék látni</a>
	</div>


	<div class="panel panel-default col-sm-12">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-home"></span> Honoldal Újdonságok:</h4>
		</div>
		<div class="panel-body">
			<ul>
                <li>Szeptember 2018: Fényképeket <a href="picture.php?type=schoolID&typeid=1&album=Iskolánk%20sportolói">albumokba</a> lehet csoportosítani</li>
                <li>Junius 2018: GDPR:<a href="gdpr.php?id=658">Személyes adatok megvédésére alkalmas kérvényenési lehetőség.</a>
				<li>Május 2018: <a href="http://ec.europa.eu/justice/smedataprotect/index_hu.htm" title="GDPR az Európai Unió általános adatvédelmi rendelete">GDPR:</a>A weboldal https biztonságos kommunikációt használ a személyes adatok megvédésére.
				<li>Január 2018: <a href="hometable.php?classid=340">Estis tanfolyamok névsora.</a></li>
				<li>December 2017: <a href="start.php">Újdonságok,</a> ezen az oldalon az utólsó frissitéseket illetve bejegyzéseket lehet megtekinteni.</li>
				<li>December 2016: <a href="picture.php?type=schoolID&typeid=1&album=_tablo_">Tablók</a> albumával bővült az oldal.</li>
				<li>Március 2016: <a href="hometable.php?classid=<?php echo Appl::getMemberId("staffClass")?>">Tanárok</a> listályával bővült az oldal.</li>
                <a>Junius 2015: <a href="message.php">Üzenőfal</a> híreknek, véleményeknek, szervezésnek, újdonságoknak.</>
				<li>Május 2015: Honoldal mobil készülékekkel is kompatibilis.</li>
				<li>Május 2015: A véndiákok életrajzzal, diákkori történetekkel és hobbikkal egészíthetik ki a profiljukat.</li>
				<li>Aprilis 2015: Bejelentkezés Facebook felhasználóval.</li>
				<li>Julius 2010:<a href="hometable.php?classid=74&guests=true">Vendégekkel és jó barátokal</a> bővült az oldal.</li>
				<li>Junius 2010: Képek <a href="pictureGallery.php?gallery=SzepIdok">Régi szép idők</a></li>
				<li>Május 2010: Zene toplista <a href="zenetoplista.php?classid=0">Zenetoplista</a></li>
                <li>Julius 2006: <a href="worldmap.php?classid=all">Térképen megjelenített szétszóródása a véndiákoknak.</a></li>
			</ul>
		</div>
	</div>
</div>

<?php include ("homefooter.php"); ?>
