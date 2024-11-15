<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'ltools.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
if (!isset($_SESSION['uRole']) || strstr($_SESSION['uRole'],"admin")=="")
    die("Only for admins");

if (getParam("action")=="todosomething") {
	include_once("config.class.php");
	include_once("dbBL.class.php");

    global $db;
	$sl = $db->dataBase->queryArray("SELECT id,children FROM person where schoolIdsAsTeacher is not null and children <>''");

	echo ("This is a function to do something,  database:".sizeof($sl));
    $classCount=0;$classFound=0;$personFound=0;
    unsetActSchool();
    foreach ( $sl as $teacher) {
        $classList = explode(",",$teacher["children"]);
        foreach ( $classList as $class) {
            $class = str_replace(".","",$class);
            $class = str_replace("XIII","13",$class);
            $class = str_replace("XII","12",$class);
            $classCount++;
            $classObj = $db->getClassByText($class);
            if ($classObj!=null) {
                $classFound++;
                if ($classObj["headTeacherID"] == $teacher["id"]) {
                    $personFound++;
                } else {
                    echo("<br/>Set:" . $class.'-'.$classObj["id"].' -> '.$teacher["id"]."-".getPersonName($db->getPersonByID($teacher["id"])));
                }
            } else {
                echo("<br/>Not Found:" . $class. '-'.$classObj["id"].' -> '.$teacher["id"]."-".getPersonName($db->getPersonByID($teacher["id"])));
            }
        }
    }
    echo ("<br/>PersonFound:".$personFound);
    echo ("<br/>ClassFound:".$classFound);
    echo ("<br/>Classcount:".$classCount);

	die();
}

if (getParam("action")=="decrypt") {
    echo (encrypt_decrypt("decrypt",getParam("param")));
    die();
}
if (getParam("action")=="encrypt") {
    echo (encrypt_decrypt("encrypt",getParam("param")));
    die();
}


\maierlabs\lpfw\Appl::setSiteSubTitle("Adatbank eszközök");
include('homemenu.inc.php');


if (isUserAdmin()) {?>

<div class="panel panel-default">
	<div class="panel-heading">
		<label id="dbDetails">Picture tools</label> 
	</div>
	<ul class="list-group">
  		<li class="list-group-item">
  			<div class="input-group input-group-sm">
  	  			<button class="btn btn-warning" onclick="picturesDB();" >Database</button>

  	  			<button class="btn btn-warning" onclick="picturesFS();" >File system</button>

  	  			<button class="btn btn-danger" onclick="picturesDelete();" >Delete</button>
  	  		</div>
  		</li>
  		<li class="list-group-item">
			<div class="input-group input-group-sm">
	  			<span id="inputwidth" class="input-group-addon">Result</span>	
	       		<span style="display: inherit;" type="text" class="form-control" id="pictureResult"></span>
	  		</div>
	  	</li>
	</ul>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<label id="dbDetails">Database tools</label> 
	</div>
	<ul class="list-group">
  		<li class="list-group-item">
  			<div class="input-group input-group-sm">
  	  			<button class="btn btn-danger" onclick="doSomething('todosomething');" >Do Something</button>
                <button class="btn btn-default" onclick="doSomething('decrypt');" >DecryptText</button>
                <button class="btn btn-default" onclick="doSomething('encrypt');" >EncryptText</button>
  	  		</div>
  		</li>
        <li class="list-group-item">
            <div class="input-group input-group-sm">
                <span id="inputwidth" class="input-group-addon">Text</span>
                <input type="text" id="dbText" class="form-control" />
            </div>
        </li>
  		<li class="list-group-item">
			<div class="input-group input-group-sm">
	  			<span id="inputwidth" class="input-group-addon">Result</span>	
	       		<span style="display: inherit;" type="text" class="form-control" id="databaseResult"></span>
	  		</div>
	  	</li>
	</ul>
</div>


<?php }?>

<script type="text/javascript">


function picturesDB() {
    $('#pictureResult').html('searching....');
    $.ajax({
		url:"phpscript/picturesDB",
		type:"GET",
		success:function(data){
		    $('#pictureResult').html(data);
		},
		error:function(error) {
		    $('#pictureResult').html("Error:"+error);
		}
    });
}

function picturesFS() {
    $('#pictureResult').html('searching....');
    $.ajax({
		url:"phpscript/picturesFS",
		type:"GET",
		success:function(data){
		    $('#pictureResult').html(data);
		},
		error:function(error) {
		    $('#pictureResult').html("Error:"+error);
		}
    });
}

function picturesDelete() {
    if (confirm("Are you sure, that you want to delete all unreferenced pictures?")) {
	    $('#pictureResult').html('searching....');
	    $.ajax({
			url:"phpscript/picturesFS?action=delete",
			type:"GET",
			success:function(data){
			    $('#pictureResult').html(data);
			},
			error:function(error) {
			    $('#pictureResult').html("Error:"+error);
			}
	    });
    }
}

function doSomething(action) {
    $('#databaseResult').html('working....');
    $.ajax({
		url:"database?action="+action+"&param="+$('#dbText').val(),
		type:"GET",
		success:function(data){
		    $('#databaseResult').html(data);
		},
		error:function(error) {
		    $('#databaseResult').html("Error:"+error);
		}
    });
}



</script>
<?php include 'homefooter.inc.php';?>
