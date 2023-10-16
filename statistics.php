<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaStatistic.class.php';
/**
 * @var dbBL
 */
global $db;

unsetActSchool();

$dbStatistic = new dbDaStatistic($db);

$classmateDeceased=$db->getTableCount("person","schoolIdsAsTeacher is null and deceasedYear is not null");
$classmatePicture=$db->getTableCount("person","schoolIdsAsTeacher is null and (picture is not null and picture <>'')");
$classmateEmail=$db->getTableCount("person","schoolIdsAsTeacher is null and (email is not null and email<>'')");
$classmateFacebook=$db->getTableCount("person","schoolIdsAsTeacher is null and (facebook is not null and facebook<>'')");
$classmateWikipedia=$db->getTableCount("person","schoolIdsAsTeacher is null and wikipedia is not null and wikipedia != '' ");
$classmatePictures=$db->getTableCount("picture","personID is not null");

$teacherDeceased=$db->getTableCount("person","schoolIdsAsTeacher is not null and deceasedYear is not null");
$teacherPicture=$db->getTableCount("person","schoolIdsAsTeacher is not null and (picture is not null and picture <>'')");;
$teacherEmail=$db->getTableCount("person","schoolIdsAsTeacher is not null and (email is not null and email<>'')");
$teacherFacebook=$db->getTableCount("person","schoolIdsAsTeacher is not null and (facebook is not null and facebook<>'')");
$teacherWikipedia=$db->getTableCount("person","schoolIdsAsTeacher is not null and wikipedia is not null and wikipedia != '' ");

$classGraduationPicture=$db->getTableCount("picture","classID is not null AND tag like '%Tabló%'","classID");
$classGraduationCardPicture=$db->getTableCount("picture","classID is not null AND tag like '%Kicsengetési kártya%'","classID");
$classHeadTeacher=$db->getTableCount("class","headTeacherID is not null AND headTeacherID <> -1");
$classTeacher=$db->getTableCount("class","teachers is not null AND teachers <> ''");
$classPicture=$db->getTableCount("picture","classID is not null","classID");

//$calendar=$dbStatistic->getActivityCalendar((new DateTime('first day of this year'))->modify("-1 year"));

$contentStatistics = $dbStatistic->getContentStatistic(isUserAdmin()?125:25);

$schoolList = $db->getSchoolList();

\maierlabs\lpfw\Appl::setSiteTitle("Statisztikai adatok", "Statistikai adatok");
\maierlabs\lpfw\Appl::addCssStyle('
	.statw { width:200px; text-align:right; display: inline-block;  }
	.content-stat { margin-bottom:10px; background-color:#eeeeee; border-radius:5px; padding:5px; font-weight:bold;}
	.content-stat > span { border: 1px solid lightgray;padding: 3px;line-height: 13px;margin: 3px; border-radius: 5px;display: inline-block;background-color:white;	}
    .content-stat > span > span:nth-child(2n )  { font-weight: bold;  }
    .content-stat > span > span:nth-child(2n-1 ){ font-weight: normal; font-size:11px; }
');
include('homemenu.inc.php');?>

<div class="panel panel-default " >
    <div class="panel-heading">
        <h4><span class="glyphicon glyphicon-user"></span> Legszorgalmasabb és legaktivabb tanáraink és véndiákok</h4>
    </div>
    <div class="panel-body"><?php
        $bests=$dbStatistic->getPersonChangeBest(isUserAdmin()?48:24);
        foreach ($bests as $uid=>$person) {
            $personName=$person["lastname"]." ".$person["firstname"];
            ?>
            <div style="display: inline-block; margin: 2px; background-color: #e8e8e8; padding: 2px;">
                <span style="width: 36px;display: inline-block;"><img src="<?php echo getPersonPicture($person)?>" class="diak_image_sicon" style="margin:2px;"/></span>
                <span style="width: 146px;display: inline-block;"><a href="editPerson?uid=<?php echo $uid?>" ><?php echo $personName?></a></span>
                <span style="width: 100px;display: inline-block;">Pontok:<?php echo $person["count"]?></span>
            </div>
            <?php
        }
        ?>
        <div>Pontok:  bejelentkezés=1000, zenelista=7, képek=5, új személy=3, gyertya gyújtás=2, személy módosítás=1, vélemény=1 </div>
        <div style="font-size:x-small">Pontokat csak bejelentkezett véndiákok kaphatnak, a bejelentkezésí pontszám minden nap egy ponttal csökken.</div>
    </div>
</div>

<div  style="margin:30px">
    <div class="content-stat">Gyakori lány keresztnevek<br/>
    <?php
        foreach ($contentStatistics->girlnames as $text) {  showStatisticElement($text); }
    ?>
    </div>
    <div class="content-stat">Gyakori fiú keresztnevek<br/>
        <?php
        foreach ($contentStatistics->boynames as $text) {  showStatisticElement($text); }
        ?>
    </div>
    <div class="content-stat">Gyakori családnevek<br/>
        <?php
        foreach ($contentStatistics->lastnames as $text) {  showStatisticElement($text); }
        ?>
    </div>
    <div class="content-stat">Ország statisztika<br/>
        <?php
        foreach ($contentStatistics->countrys as $text) {  showStatisticElement($text); }
        ?>
    </div>
    <div class="content-stat">Helység statisztika<br/>
        <?php
        foreach ($contentStatistics->places as $text) {  showStatisticElement($text); }
        ?>
    </div>
    <div class="content-stat">Hölgy/úr lány/fiú statisztika<br/>
        <?php
        foreach ($contentStatistics->gender as $text) {  showStatisticElement($text); }
        ?>
    </div>

</div>

<div id="teacherff" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;margin-left:30px">
	<div class="panel-heading text-center"><i style="vertical-align: bottom" class="material-icons">school</i> <label> Tanárok</label></div>
	<ul class="list-group"  style="list-style: none;">
        <?php $summ=0; foreach ($schoolList as $school) { ?>
            <li>
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><span class="statw"><?php echo $school["name"]?></span></span>
                    <span class="form-control"><?php echo $count = $db->getSchoolPersonCount($school["id"],true) ?></span>
                    <div class="input-group-btn"><a href="search?type=teacher&schoolid=<?php echo $school["id"]?>" class="btn btn-default">Mutasd</a></div>
                </div>
            </li>
        <?php $summ +=$count; } ?>
        <li>
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Összesen</span></span>
                <span class="form-control stats"><b><?php echo $teacher=$summ?></b></span>
                <div class="input-group-btn"><a href="search?type=teacher" class="btn btn-default">Mutasd</a></div>
            </div>
        </li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Elhunyt</span></span>
	       		<span class="form-control"><?php echo $teacherDeceased." (".round($teacherDeceased/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherdeceased" class="btn btn-default">Mutasd</a></div>
	  		</div>
  		</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képpel</span></span>	
	       		<span class="form-control"><?php echo $teacherPicture." (".round($teacherPicture/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithpicture" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">E-Mail címmel</span></span>	
	       		<span class="form-control"><?php echo $teacherEmail." (".round($teacherEmail/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithemail" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Facebook</span></span>	
	       		<span class="form-control"><?php echo $teacherFacebook." (".round($teacherFacebook/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithfacebook" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Wikipedia</span></span>	
	       		<span class="form-control"><?php echo $teacherWikipedia." (".round($teacherWikipedia/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithwikipedia" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
	</ul>
</div>


<div id="personff" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;margin-left:30px;">
	<div class="panel-heading text-center"><i style="vertical-align: bottom" class="material-icons">people</i> <label >Véndiákok</label></div>
	<ul class="list-group"  style="list-style: none;">
        <?php $summ=0; foreach ($schoolList as $school) { ?>
            <li>
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><span class="statw"><?php echo $school["name"]?></span></span>
                    <span class="form-control"><?php echo $count = $db->getSchoolPersonCount($school["id"],false) ?></span>
                    <div class="input-group-btn"><a href="search?type=classmate&schoolid=<?php echo $school["id"]?>" class="btn btn-default">Mutasd</a></div>
                </div>
            </li>
        <?php $summ +=$count; } ?>
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Összesen</span></span>
       			<span class="form-control"><b><?php echo $classmate=$summ?></b></span>
	       		<div class="input-group-btn"><a href="search?type=classmate" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Elhunyt</span></span>
       			<span class="form-control"><?php echo $classmateDeceased." (".round($classmateDeceased/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatedeceased" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képpel</span></span>	
	       		<span class="form-control"><?php echo $classmatePicture." (".round($classmatePicture/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithpicture" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">E-Mail címmel</span></span>	
	       		<span class="form-control"><?php echo $classmateEmail." (".round($classmateEmail/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithemail" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Facebook</span></span>	
	       		<span class="form-control"><?php echo $classmateFacebook." (".round($classmateFacebook/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithfacebook" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Wikipedia</span></span>	
	       		<span class="form-control"><?php echo $classmateWikipedia." (".round($classmateWikipedia/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithwikipedia" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képek</span></span>
	       		<span class="form-control"><?php echo $classmatePictures?></span>
	  		</div>
	  	</li>
	</ul>
</div>


<div id="classff" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;margin-left:30px">
	<div class="panel-heading text-center"><i style="vertical-align: bottom" class="material-icons">groups</i> <label >Osztályok</label></div>
	<ul class="list-group" style="list-style: none;">
        <?php $summ=0; foreach ($schoolList as $school) { ?>
            <li>
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><span class="statw"><?php echo $school["name"]?></span></span>
                    <span class="form-control"><?php echo $count = $db->getTableCount("class","schoolID=".$school["id"]); ?></span>
                </div>
            </li>
            <?php $summ +=$count; } ?>
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Összesen</span></span>
	       		<span class="form-control"><?php echo $classCount = $summ?></span>
	  		</div>
  		</li>
        <li class="">
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Osztályfőnök megjelölve</span></span>
                <span class="form-control"><?php echo $classHeadTeacher." (".round($classHeadTeacher/$classCount*100,2)."%)"?></span>
            </div>
        </li>
        <li class="">
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Tanárok megjelölve</span></span>
                <span class="form-control"><?php echo $classTeacher." (".round($classTeacher/$classCount*100,2)."%)"?></span>
            </div>
        </li>
        <li class="">
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Osztály képek</span></span>
                <span class="form-control"><?php echo $classPicture." (".round($classPicture/$classCount*100,2)."%)"?></span>
            </div>
        </li>
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Tablóképpel</span></span>	
	       		<span class="form-control"><?php echo $classGraduationPicture." (".round($classGraduationPicture/$classCount*100,2)."%)"?></span>
	  		</div>
	  	</li>
        <li class="">
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Kicsengetési kártyával</span></span>
                <span class="form-control"><?php echo $classGraduationCardPicture." (".round($classGraduationCardPicture/$classCount*100,2)."%)"?></span>
            </div>
        </li>
	</ul>
</div>


<?php if (isUserAdmin()) {?>
<div id="adminok" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;margin-left:30px">
	<div class="panel-heading text-center"><i style="vertical-align: bottom" class="material-icons">construction</i> <label >Administrátoroknak</label></div>
	<ul class="list-group"  style="list-style: none;">
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Geokoordináta</span></span>
       			<span class="form-control"><?php echo $db->getTableCount("person","(geolat='' or geolat is null) and place <>'' and place is not null and place not like 'Kolozsv%'");?></span>
	       		<div class="input-group-btn"><a href="search?type=nogeo" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
        <li>
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">fiú / lány</span></span>
                <span class="form-control"><?php echo $db->getTableCount("person","gender is null or gender = ''");?></span>
                <div class="input-group-btn"><a href="search?type=gender" class="btn btn-default">Mutasd</a></div>
            </div>
        </li>
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Facebook kapcsolat</span></span>
       			<span class="form-control"><?php echo $db->getTableCount("person","facebookid <> '0' and facebookid is not null");?></span>
	       		<div class="input-group-btn"><a href="search?type=fbconnection" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
	</ul>
</div>
<?php }?>
<?php include 'homefooter.inc.php';?>
<?php
function showStatisticElement($text)
{
    echo("<span><span>" . $text["c"] .":</span><span>" . $text["content"] . " </span></span>");
}