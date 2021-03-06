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

$dbStatistic = new dbDaStatistic($db);

$classmate=$db->getTableCount("person","isTeacher='0'");
$classmateDeceased=$db->getTableCount("person","isTeacher='0' and deceasedYear is not null");
$classmatePicture=$db->getTableCount("person","isTeacher='0' and (picture is not null and picture <>'')");
$classmateEmail=$db->getTableCount("person","isTeacher='0' and (email is not null and email<>'')");
$classmateFacebook=$db->getTableCount("person","isTeacher='0' and (facebook is not null and facebook<>'')");
$classmateWikipedia=$db->getTableCount("person","isTeacher='0' and homepage like '%wikipedia%'");
$classmatePictures=$db->getTableCount("picture","personID is not null");

$teacher=$db->getTableCount("person","isTeacher='1'");
$teacherDeceased=$db->getTableCount("person","isTeacher='1' and deceasedYear is not null");
$teacherPicture=$db->getTableCount("person","isTeacher='1' and (picture is not null and picture <>'')");;
$teacherEmail=$db->getTableCount("person","isTeacher='1' and (email is not null and email<>'')");
$teacherFacebook=$db->getTableCount("person","isTeacher='1' and (facebook is not null and facebook<>'')");
$teacherWikipedia=$db->getTableCount("person","isTeacher='1' and homepage like '%wikipedia%'");

$classCount=$db->getTableCount("class");
$classGraduationPicture=$db->getTableCount("picture","classID is not null AND tag like '%Tabló%'","classID");
$classGraduationCardPicture=$db->getTableCount("picture","classID is not null AND tag like '%Kicsengetési kártya%'","classID");
$classHeadTeacher=$db->getTableCount("class","headTeacherID is not null AND headTeacherID <> -1");
$classPicture=$db->getTableCount("picture","classID is not null","classID");

//$calendar=$dbStatistic->getActivityCalendar((new DateTime('first day of this year'))->modify("-1 year"));

$contentStatistics = $dbStatistic->getContentStatistic(isUserAdmin()?125:25);

\maierlabs\lpfw\Appl::setSiteTitle("Statisztikai adatok", "Statistikai adatok");
\maierlabs\lpfw\Appl::addCssStyle('
	.statw {
	    width:150px; text-align:right; display: inline-block;
	    }
	    
	.content-stat {
	    margin-bottom:10px;
	    background-color:#eeeeee;
	    border-radius:5px;
	    padding:5px;
	    font-weight:bold;
	}
	.content-stat > span {
	    border: 1px solid lightgray;
	    padding: 3px;
        line-height: 13px;
        margin: 3px;
        border-radius: 5px;
        display: inline-block;
        background-color:white;
	}
    .content-stat > span > span:nth-child(2n ){
        font-weight: bold;
    }
    .content-stat > span > span:nth-child(2n-1 ){
        font-weight: normal;
        font-size:11px;
    }
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
                <span style="width: 146px;display: inline-block;"><a href="editDiak?uid=<?php echo $uid?>" ><?php echo $personName?></a></span>
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
<?php /* ?>
<div  style="margin:30px">
<div class="panel panel-default"  style="padding:5px; max-width:650px" id="calendargg"></div>
<br/>
 <?php */ ?>
<div id="teacherff" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;">
	<div class="panel-heading text-center"><span class="icon"></span><label> Tanárok</label></div>
	<ul class="list-group"  style="list-style: none;">
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Összesen</span></span>
	       		<span type="text" class="form-control"><?php echo $teacher?></span>
	       		<div class="input-group-btn"><a href="search?type=teacher" class="btn btn-default">Mutasd</a></div>
	  		</div>
  		</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Elhunyt</span></span>
	       		<span type="text" class="form-control"><?php echo $teacherDeceased?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherdeceased" class="btn btn-default">Mutasd</a></div>
	  		</div>
  		</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képpel</span></span>	
	       		<span type="text" class="form-control"><?php echo $teacherPicture." (".round($teacherPicture/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithpicture" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">E-Mail címmel</span></span>	
	       		<span type="text" class="form-control"><?php echo $teacherEmail." (".round($teacherEmail/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithemail" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Facebook</span></span>	
	       		<span type="text" class="form-control"><?php echo $teacherFacebook." (".round($teacherFacebook/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithfacebook" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Wikipedia</span></span>	
	       		<span type="text" class="form-control"><?php echo $teacherWikipedia." (".round($teacherWikipedia/$teacher*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=teacherwithwikipedia" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
	</ul>
</div>

<div class="panel panel-default"  style="padding:5px;width:400px; display:inline-block;vertical-align: top;" id="teachergg"></div>
<br/>
<div id="personff" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;">
	<div class="panel-heading text-center"><label >Véndiákok</label></div>
	<ul class="list-group"  style="list-style: none;">
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Összesen</span></span>
       			<span type="text" class="form-control"><?php echo $classmate?></span>
	       		<div class="input-group-btn"><a href="search?type=classmate" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Elhunyt</span></span>
       			<span type="text" class="form-control"><?php echo $classmateDeceased?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatedeceased" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képpel</span></span>	
	       		<span type="text" class="form-control"><?php echo $classmatePicture." (".round($classmatePicture/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithpicture" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">E-Mail címmel</span></span>	
	       		<span type="text" class="form-control"><?php echo $classmateEmail." (".round($classmateEmail/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithemail" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Facebook</span></span>	
	       		<span type="text" class="form-control"><?php echo $classmateFacebook." (".round($classmateFacebook/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithfacebook" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Wikipedia</span></span>	
	       		<span type="text" class="form-control"><?php echo $classmateWikipedia." (".round($classmateWikipedia/$classmate*100,2)."%)"?></span>
	       		<div class="input-group-btn"><a href="search?type=classmatewithwikipedia" class="btn btn-default">Mutasd</a></div>
	  		</div>
	  	</li>
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képek</span></span>	
	       		<span type="text" class="form-control"><?php echo $classmatePictures?></span>
	  		</div>
	  	</li>
	</ul>
</div>

<div class="panel panel-default"  style="padding:5px;width:400px; display:inline-block;vertical-align: top;" id="persongg"></div>
<br/>
<div id="classff" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;">
	<div class="panel-heading text-center"><label >Osztályok</label></div>
	<ul class="list-group" style="list-style: none;">
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Összesen</span></span>
	       		<span type="text" class="form-control"><?php echo $classCount?></span>
	  		</div>
  		</li>
        <li class="">
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Osztályfőnök megjelölve</span></span>
                <span type="text" class="form-control"><?php echo $classHeadTeacher." (".round($classHeadTeacher/$classCount*100,2)."%)"?></span>
            </div>
        </li>
        <li class="">
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Képekkel</span></span>
                <span type="text" class="form-control"><?php echo $classPicture." (".round($classPicture/$classCount*100,2)."%)"?></span>
            </div>
        </li>
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Tablóképpel</span></span>	
	       		<span type="text" class="form-control"><?php echo $classGraduationPicture." (".round($classGraduationPicture/$classCount*100,2)."%)"?></span>
	  		</div>
	  	</li>
        <li class="">
            <div class="input-group input-group-sm">
                <span class="input-group-addon"><span class="statw">Kicsengetési kártyával</span></span>
                <span type="text" class="form-control"><?php echo $classGraduationCardPicture." (".round($classGraduationCardPicture/$classCount*100,2)."%)"?></span>
            </div>
        </li>
	</ul>
</div>
<div class="panel panel-default"  style="padding:5px;width:400px; display:inline-block;vertical-align: top;" id="classgg"></div>
<br/>
<?php if (isUserAdmin()) {?>
<div id="adminok" class="panel panel-default" style="width: 400px;display:inline-block;vertical-align: top;">
	<div class="panel-heading text-center"><label >Administrátoroknak</label></div>
	<ul class="list-group"  style="list-style: none;">
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Geokoordináta</span></span>
       			<span type="text" class="form-control"><?php echo $db->getTableCount("person","(geolat='' or geolat is null) and place <>'' and place is not null and place not like 'Kolozsv%'");?></span>
	       		<div class="input-group-btn"><a href="search?type=nogeo" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
  		<li>
  			<div class="input-group input-group-sm">
  				<span class="input-group-addon"><span class="statw">Facebook kapcsolat</span></span>
       			<span type="text" class="form-control"><?php echo $db->getTableCount("person","facebookid <> '0' and facebookid is not null");?></span>
	       		<div class="input-group-btn"><a href="search?type=fbconnection" class="btn btn-default">Mutasd</a></div>
	       	</div>
  		</li>
	</ul>
</div>
<?php }?>

<script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
      //google.charts.load('current', {'packages':['bar']});
      google.charts.load("current", {packages:["corechart"]});
      google.charts.load("current", {packages:["calendar"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data1 = google.visualization.arrayToDataTable([
			['','',{ role: 'style' }],                                                          
            ['Összesen',<?php echo $teacher?>,'red'],
            ['Elhunyt',<?php echo $teacherDeceased?>,'darkgray'],
            ['Képpel',<?php echo $teacherPicture?>,'green'],
            ['E-Mail',<?php echo $teacherEmail?>,'blue'],
            ['Facebook',<?php echo $teacherFacebook?>,'lightblue'],
            ['Wikipedia',<?php echo $teacherWikipedia?>,'cyan']
        ]);
        var data2 = google.visualization.arrayToDataTable([
 		  ['','',{ role: 'style' }],                                                          
          ['Összesen',<?php echo $classmate?>,'red'],
          ['Elhunyt',<?php echo $classmateDeceased?>,'darkgray'],
          ['Képpel',<?php echo $classmatePicture?>,'green'],
          ['E-Mail',<?php echo $classmateEmail?>,'blue'],
          ['Facebook',<?php echo $classmateFacebook?>,'lightblue'],
          ['Wikipedia',<?php echo $classmateWikipedia?>,'cyan'],
          ['Képek',<?php echo $classmatePictures?>,'lightgreen']
		]);
        var data3 = google.visualization.arrayToDataTable([
 			['','',{ role: 'style' }],                                                          
			['Összesen',<?php echo $classCount?>,'red'],
            ['Osztályfőnök',<?php echo $classHeadTeacher?>,'green'],
            ['Képekkel',<?php echo $classPicture?>,'lightgreen'],
            ['Tablóképpel',<?php echo $classGraduationPicture?>,'cyan'],
            ['Kártyával',<?php echo $classGraduationCardPicture?>,'blue']
        ]);

        var options = {chart: { }, bars: 'horizontal', legend: { position: "none" }};
        

        var view1 = new google.visualization.DataView(data1);
        view1.setColumns([0,1,{ calc: "stringify",sourceColumn: 1,type: "string",role: "annotation" },2]);
        var chart1 = new google.visualization.BarChart(document.getElementById("teachergg"));
        options.title="Tanárok";options.height=$("#teacherff").height();
        chart1.draw(view1, options);

        var view2 = new google.visualization.DataView(data2);
        view2.setColumns([0,1,{ calc: "stringify",sourceColumn: 1,type: "string",role: "annotation" },2]);
        var chart2 = new google.visualization.BarChart(document.getElementById("persongg"));
        options.title="Véndiákok";options.height=$("#personff").height();
        chart2.draw(view2, options);

        var view = new google.visualization.DataView(data3);
        view.setColumns([0,1,{ calc: "stringify",sourceColumn: 1,type: "string",role: "annotation" },2]);
        var chart3 = new google.visualization.BarChart(document.getElementById("classgg"));
        options.title="Osztályok";options.height=$("#classff").height();
        chart3.draw(view, options);
        <?php /* ?>
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn({ type: 'date', id: 'Date' });
        dataTable.addColumn({ type: 'number', id: 'Won/Loss' });
        dataTable.addRows([
			<?php foreach ($calendar as $date=>$count) {?>
           [ new Date(<?php echo (new DateTime($date))->format("Y, n-1, j")?>), <?php echo $count?> ],
           <?php } ?>
         ]);

        var chart = new google.visualization.Calendar(document.getElementById('calendargg'));

        var options = {
          title: "Tanárok és véndiákok aktivitása",
          height: 220,
          calendar: {
          	focusedCellColor: {
              stroke: 'red',
              strokeOpacity: 0.8,
              strokeWidth: 1
            },
            monthOutlineColor: {
                stroke: '#981b48',
                strokeOpacity: 0.8,
                strokeWidth: 1
              },
            cellSize:10,
            daysOfWeek:'VHKSCPS'
          },
          colorAxis:{minValue: 0,  colors: ['#80FF00', '#FF0000']}
        };

        chart.draw(dataTable, options);
        <?php */ ?>
      }
</script>
<?php include 'homefooter.inc.php';?>
<?php
function showStatisticElement($text)
{
    echo("<span><span>" . $text["c"] .":</span><span>" . $text["content"] . " </span></span>");
}