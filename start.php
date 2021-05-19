<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaSongVote.class.php';

use \maierlabs\lpfw\Appl as Appl;
global $db;

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

/**
 * Show recent changes uses the url parameters tabOpen, limit, ip an userid
 * @param dbDAO $db
 * @param string $date
 * @return string date of the last entry
 */
function showRecentChanges(dbDAO $db,$date=null) {
    if ($date!=null) {
        $date=new \DateTime($date);
    } else {
        $date=new \DateTime();
    }
    $dbSong = new dbDaSongVote($db);
    $ip = getParam("ip",null);
    $userid= getParam("userid",null);
    if (($filter=getParam("tabOpen","all"))=='user') {
        $filter='all';
        if (isUserLoggedOn() || getParam("userid")!=null) {
            if (getParam("userid") == null)
                $userid = getLoggedInUserId();
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
    }
    $ids=$db->getRecentChangesListByDate($date, getIntParam("limit",48),$filter,$ip,$userid);
    foreach ($ids as $id) {
        if ($id["type"] == "person") {
            $person = $db->getPersonByID($id["id"]);
            displayPerson($db, $person, true, true,$id["action"],$id["changeUserID"],$id["changeDate"]);
        } elseif ($id["type"] == "picture") {
            $picture = $db->getPictureById($id["id"]);
            displayPicture($db, $picture,false,$id["action"],$id["changeUserID"],$id["changeDate"]);
        } elseif ($id["type"] == "class") {
            $class= $db->getClassById($id["id"]);
            displayClass($db, $class,true);
        } elseif ($id["type"] == "article") {
            $article= $db->getArticleById($id["id"]);
            displayArticle($db, $article,true);
        } elseif ($id["type"] == "message") {
            $message= $db->getMessage($id["id"]);
            displayMessage($db, $message,true);
        } elseif ($id["type"] == "music") {
            $musicVote = $dbSong->getSongById($id["id"]);
            displayMusic($db, $musicVote,$id["action"],$id["changeUserID"],$id["changeDate"],true);
        }
    }
    if (sizeof($ids)>0)
        $date=strtotime($ids[sizeof($ids)-1]["changeDate"])-1;
    else
        $date=null;

    return date("Y-m-d H:i:s",$date);
}

//initialise tabs
$tabsCaption = array();
$tabsTranslate["search"] = array(".php");$tabsTranslate["replace"] = array("");
array_push($tabsCaption ,array("id" => "all", "caption" => 'Minden újdonság', "glyphicon" => "*public"));
array_push($tabsCaption ,array("id" => "message", "caption" => 'Üzenetek', "glyphicon" => "*chat"));
array_push($tabsCaption ,array("id" => "class", "caption" => 'Osztályok', "glyphicon" => "*people"));
array_push($tabsCaption ,array("id" => "teacher", "caption" => 'Tanárok', "glyphicon" => "*school"));
array_push($tabsCaption ,array("id" => "person", "caption" => 'Diákok', "glyphicon" => "*person"));
array_push($tabsCaption ,array("id" => "family", "caption" => 'Rokonok', "glyphicon" => "*device_hub"));
array_push($tabsCaption ,array("id" => "picture", "caption" => 'Képek', "glyphicon" => "*photo_camera"));
array_push($tabsCaption ,array("id" => "tag", "caption" => 'Jelölések', "glyphicon" => "*center_focus_strong"));
array_push($tabsCaption ,array("id" => "opinion", "caption" => 'Vélemények', "glyphicon" => "*thumb_up"));
array_push($tabsCaption ,array("id" => "candle", "caption" => 'Gyertyák', "glyphicon" => "*whatshot"));
if (isUserLoggedOn() || getParam("userid")!=null) {
    if (getParam("userid")!=null) {
        $pers = getPersonShortName($db->getPersonByID(getParam("userid")));
    } else {
        $pers = getPersonShortName($db->getPersonByID(getLoggedInUserId()));
    }
    array_push($tabsCaption, array("id" => "user", "caption" => $pers, "glyphicon" => "*person"));
} else {
    array_push($tabsCaption, array("id" => "user", "caption" => 'Én magam', "glyphicon" => "*person"));
}
$title = 'Újdonságok: '. $tabsCaption[(array_search(getParam("tabOpen","all"),array_column($tabsCaption,"id")))]["caption"];
if (getParam("tabOpen")=="easter") {
    $title = "Kellemes húsvéti ünnepeket! ";
}
Appl::setSiteTitle($title,$title);
include("homemenu.inc.php");

?>
<div class="container-fluid">
	<div class="panel panel-default " ><?php
    	include Config::$lpfw.'view/tabs.inc.php';?>
		<div class="panel-body">
		    <?php $lastDate=showRecentChanges($db);?>
            <span id="more"></span>
		</div>
        <input type="hidden" id="date" value="<?php echo $lastDate?>"/>
        <button id="buttonmore" class="btn btn-success" style="margin:10px;" onclick="showmore()">Többet szeretnék látni</button>
	</div>


	<div class="panel panel-default col-sm-12">
		<div class="panel-heading" style="margin: 1px -13px -7px -13px;">
			<h4><span class="glyphicon glyphicon-home"></span> Honoldal Újdonságok:</h4>
		</div>
		<div class="panel-body">
			<ul>
                <li>Március 2019: Személyeket lehet a fényképeken megjelölni</li>
                <li>December 2018: Családtagokat lehet megjelölni</li>
                <li>November 2018: Véleményeket lehet személyekhez és képekhez hozzáfűzni</li>
                <li>Szeptember 2018: Fényképeket <a href="picture?type=schoolID&typeid=1&album=Iskolánk%20sportolói">albumokba</a> lehet csoportosítani</li>
                <li>Junius 2018: GDPR:<a href="gdpr?id=658">Személyes adatok megvédésére alkalmas kérvényenési lehetőség.</a>
				<li>Május 2018: <a href="http://ec.europa.eu/justice/smedataprotect/index_hu.htm" title="GDPR az Európai Unió általános adatvédelmi rendelete">GDPR:</a>A weboldal https biztonságos kommunikációt használ a személyes adatok megvédésére.
				<li>Január 2018: <a href="hometable?classid=340">Estis tanfolyamok névsora.</a></li>
				<li>December 2017: <a href="start">Újdonságok,</a> ezen az oldalon az utólsó frissitéseket illetve bejegyzéseket lehet megtekinteni.</li>
				<li>December 2016: <a href="picture?type=schoolID&typeid=1&album=_tablo_">Tablók</a> albumával bővült az oldal.</li>
				<li>Március 2016: <a href="hometable?classid=<?php echo Appl::getMemberId("staffClass")?>">Tanárok</a> listályával bővült az oldal.</li>
                <li>Junius 2015: <a href="message">Üzenőfal</a> híreknek, véleményeknek, szervezésnek, újdonságoknak.</li>
				<li>Május 2015: Honoldal mobil készülékekkel is kompatibilis.</li>
				<li>Május 2015: A véndiákok életrajzzal, diákkori történetekkel és hobbikkal egészíthetik ki a profiljukat.</li>
				<li>Aprilis 2015: Bejelentkezés Facebook felhasználóval.</li>
				<li>Julius 2010:<a href="hometable?classid=74&guests=true">Vendégekkel és jó barátokal</a> bővült az oldal.</li>
				<li>Május 2010: Zene toplista <a href="zenetoplista?classid=0">Zenetoplista</a></li>
                <li>Julius 2006: <a href="worldmap?classid=all">Térképen megjelenített szétszóródása a véndiákoknak.</a></li>
			</ul>
		</div>
	</div>
</div>

<?php
$urlParam="";
if (getParam("ip")!=null)
    $urlParam="&ip=".getParam("ip");
if (getParam("userid")!=null)
    $urlParam="&userid=".getParam("userid");
Appl::addJsScript("
    function showmore(date) {
        $('#buttonmore').html('Pillanat...<img src=\"images/loading.gif\" />');
        $.ajax({
    		url:'start?action=showmore&date='+$('#date').val()+'&tabOpen=".getParam('tabOpen','all').$urlParam."',
	    	type:'GET',
    		success:function(data){
    		    var s = document.documentElement.scrollTop || document.body.scrollTop;
    		    var idx=data.lastIndexOf('#');
    		    $('#more').replaceWith(data.substring(0,idx-1)+'<span id=\"more\"></span>');	    	    
    		    document.documentElement.scrollTop = document.body.scrollTop = s;
	    	    $('#date').val(data.substring(idx+1));
	    	    $('#buttonmore').html('Többet szeretnék látni');
		    },
		    error:function(error) {
		        showMessage('Több bejegyzés nincs!');
		    }
        });
    }
");
showTabFooterLinks();
include("homefooter.inc.php");
?>
