<?php
include_once 'tools/sessionManager.php';
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once 'dbBL.class.php';

use \maierlabs\lpfw\Appl as Appl;

$userId=getIntParam("userId",-1);
if ($userId>=0) {
	$db->savePersonFacebookId($userId,$_SESSION["FacebookId"]);
}
unsetAktClass();

include_once 'displayCards.inc.php';

if (isActionParam('showmore')) {
    $date=showRecentChanges($db,getParam('date'));
    echo('#'.$date);
    return;
}

if (isActionParam("updatecache")) {
    $db->updateRecentChangesList();
}

/**
 * Show recent changes
 * @param dbDAO $db
 * @param string $date
 * @return string
 */
function showRecentChanges($db,$date=null) {
    if ($date!=null) {
        $date=new \DateTime($date);
    }
    $ids=$db->getRecentChangeList($date, getIntParam("limit",48));
    $keylist = array();
    $entryID=0;
    foreach ($ids as $id) {
        if ($id["type"] == "person") {
            $person = $db->getPersonByID($id["id"]);
            $entryID=$person["id"];
            if (!in_array($id["type"].$entryID,$keylist ))
                displayPerson($db, $person, true, true,$id["action"],$id["changeUserID"],$id["changeDate"]);
        } elseif ($id["type"] == "picture") {
            $picture = $db->getPictureById($id["id"]);
            $entryID=$picture["id"];
            if (!in_array($id["type"].$entryID,$keylist ))
                displayPicture($db, $picture,false,$id["action"],$id["changeUserID"],$id["changeDate"]);
        }
        if (!in_array($id["type"].$entryID,$keylist ))
            array_push($keylist , $id["type"].$entryID);
    }
    $date=strtotime($ids[sizeof($ids)-1]["changeDate"])-1;

    return date("Y-m-d H:i:s",$date);
}

Appl::setSiteTitle('Újdonságok','Újdonságok');
include("homemenu.inc.php");

?>
<div class="container-fluid">
	<div class="panel panel-default " >

		<div class="panel-heading">
			<h4><span class="glyphicon glyphicon-user"></span> Új személyek, fényképek, frissitések
                <?php if (userIsAdmin()) { ?>
                    <form><button name="action" value="updatecache" class="btn btn-default">Új lista <?php echo Appl::dateTimeAsStr($db->getAcceleratorDate(1)) ?></button></form>
                <?php } ?>
            </h4>
		</div>
		<div class="panel-body" id="changes">
		<?php $lastDate=showRecentChanges($db);?>
		</div>
        <input type="hidden" id="date" value="<?php echo $lastDate?>"/>
        <button id="buttonmore" class="btn btn-default" style="margin:10px;" onclick="showmore()">Többet szeretnék látni</button>
	</div>


	<div class="panel panel-default col-sm-12">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-home"></span> Honoldal Újdonságok:</h4>
		</div>
		<div class="panel-body">
			<ul>
                <li>November 2018: Véleményeket lehet személyekhez és képekhez hozzáfűzni</li>
                <li>Szeptember 2018: Fényképeket <a href="picture.php?type=schoolID&typeid=1&album=Iskolánk%20sportolói">albumokba</a> lehet csoportosítani</li>
                <li>Junius 2018: GDPR:<a href="gdpr.php?id=658">Személyes adatok megvédésére alkalmas kérvényenési lehetőség.</a>
				<li>Május 2018: <a href="http://ec.europa.eu/justice/smedataprotect/index_hu.htm" title="GDPR az Európai Unió általános adatvédelmi rendelete">GDPR:</a>A weboldal https biztonságos kommunikációt használ a személyes adatok megvédésére.
				<li>Január 2018: <a href="hometable.php?classid=340">Estis tanfolyamok névsora.</a></li>
				<li>December 2017: <a href="start.php">Újdonságok,</a> ezen az oldalon az utólsó frissitéseket illetve bejegyzéseket lehet megtekinteni.</li>
				<li>December 2016: <a href="picture.php?type=schoolID&typeid=1&album=_tablo_">Tablók</a> albumával bővült az oldal.</li>
				<li>Március 2016: <a href="hometable.php?classid=<?php echo Appl::getMemberId("staffClass")?>">Tanárok</a> listályával bővült az oldal.</li>
                <li>Junius 2015: <a href="message.php">Üzenőfal</a> híreknek, véleményeknek, szervezésnek, újdonságoknak.</li>
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

<?php
Appl::addJsScript("
    function showmore(date) {
        $('#buttonmore').html('Pillanat...<img src=\"images/loading.gif\" />');
        $.ajax({
    		url:'start.php?action=showmore&date='+$('#date').val(),
	    	type:'GET',
    		success:function(data){
    		    var idx=data.lastIndexOf('#');
	    	    $('#changes').html($('#changes').html()+data.substring(0,idx-1));
	    	    $('#date').val(data.substring(idx+1));
	    	     $('#buttonmore').html('Tovább !');
		    },
		    error:function(error) {
		        showMessage('Több bejegyzés nincs!');
		        $('#buttonmore').html('Tovább !');
		    }
        });
    }
");
include("homefooter.inc.php");
?>
