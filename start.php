<?php 
include_once 'tools/sessionManager.php';
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once 'data.php';


$userId=getIntParam("userId",-1);
if ($userId>=0) {
	$db->savePersonFacebookId($userId,$_SESSION["FacebookId"]);
}
unsetAktClass();

$SiteTitle="A kolozsvári Brassai Sámuel véndiákok bejelentkezési oldala";
Appl::$subTitle = 'Újdonságok';
include("homemenu.php");
include_once 'editDiakCard.php';
?>
<div class="container-fluid">
	<div class="panel panel-default " >

		<div class="panel-heading">
			<h4><span class="glyphicon glyphicon-user"></span> Legszorgalmasabb és legaktivabb tanáraink és véndiákok</h4>
		</div>
		<div class="panel-body">
		<?php
		$bests=$db->getPersonChangeBest(userIsAdmin()?100:30);
		foreach ($bests as $uid=>$count) {
			if ($count>=1) {
				$person=$db->getPersonByID($uid);
				$personName=$person["lastname"]." ".$person["firstname"];
				if ($uid>0 && strlen($personName)>2) {
				?>
				<div style="display: inline-block; margin: 2px; background-color: #e8e8e8; padding: 2px;">
					<span style="width: 36px;display: inline-block;"><img src="<?php echo getPersonPicture($person)?>" class="diak_image_sicon" style="margin:2px;"/></span>
					<span style="width: 146px;display: inline-block;"><a href="editDiak.php?uid=<?php echo $uid?>" ><?php echo $personName?></a></span>
	   				<span style="width: 100px;display: inline-block;">Pontok:<?php echo $count?></span>
	   			</div>
				<?php
				}
			}
		}
		?>
		<div>Pontok:  bejelentkezés=1000, zenelista=7, képek=5, új személy=3, gyertya gyújtás=2, személy módosítás=1 </div>
		<div style="font-size:x-small">Pontokat csak bejelentkezett véndiákok kaphatnak, a bejelentkezésí pontszám minden nap egy ponttal csökken.</div>
	</div>
				

		<div class="panel-heading">
			<h4><span class="glyphicon glyphicon-user"></span> Új személyek, frissitések </h4>
		</div>
		<div class="panel-body">
		<?php 
		$persons=$db->getRecentChangedPersonList(getIntParam("persons", 18));
		foreach ($persons as $person) {
			displayPerson($db,$person,true,true);
		}
		?>
		</div>
		<a href="start.php?persons=200" class="btn btn-default" style="margin:10px;text-decoration: none;" >Többet szeretnék látni</a>
	</div>

	<div class="panel panel-default col-sm-12" style="margin-right:10px;">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-picture"></span> Új fényképek:</h4>
		</div>
		<div class="panel-body">
		<?php 
			$pictures=$db->getRecentPictureList(getIntParam("pictures", 18));
			foreach ($pictures as $picture) {
				displayPicture($db,$picture);
			}
		?>
		</div>
		<a href="start.php?pictures=100" class="btn btn-default" style="margin:10px;text-decoration: none;" >Többet szeretnék látni</a>
	</div>

	<div class="panel panel-default col-sm-12">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-home"></span> Honoldal Újdonságok:</h4>
		</div>
		<div class="panel-body">
			<ul>
				<li>Május 2018: <a href="http://ec.europa.eu/justice/smedataprotect/index_hu.htm" title="GDPR az Európai Unió általános adatvédelmi rendelete">GDPR:</a>Az oldal https biztonságos kommunikációt használ a személyes adatok megvédésére.
				<li>Január 2018: <a href="hometable.php?classid="></a> Estisek tanfolyamok névsora új menüponton keresztül érhetbő el.</li>
				<li>December 2017: <a href="start.php"></a> Ez az oldal az utólsó frissitéseket illetve bejegyzéseket is tartalmazza.</li>
				<li>December 2016: <a href="picture.php?type=tablo&typeid=tablo">Tablók</a> albumával bővült az oldal.</li>
				<li>Március 2016: <a href="hometable.php?classid=0 staf">Tanárok</a> listályával bővült az oldal.</li>
				<li>Junius 2015: Üzenőfal híreknek, véleményeknek, szervezésnek, újdonságoknak.</li>
				<li>Május 2015: Honoldal mobil készülékekkel is kompatibilis.</li>
				<li>Május 2015: A véndiákok életrajzzal, diákkori történetekkel és hobbikkal egészíthetik ki a profiljukat.</li>
				<li>Aprilis 2015: Bejelentkezés Facebook felhasználóval.</li>
				<li>Julius 2010:<a href="hometable.php?guests=true">Vendégekkel és jó barátokal</a> bővült az oldal.</li>
				<li>Junius 2010: Képek <a href="pictureGallery.php?gallery=SzepIdok">Régi szép idők</a></li>
				<li>Május 2010: Zene toplista <a href="zenetoplista.php?classid=0">Zenetoplista</a></li>
			</ul>
		</div>
	</div>
</div>
<?php 
include ("homefooter.php"); 

function sortBests($a,$b) {
	if (intval($a)<intval($b))
		return 1;
	elseif (intval($a)>intval($b))
		return -1;
	else
		return 0;
}
?>
