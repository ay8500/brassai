<?php
include_once("tools/sessionManager.php");
include_once('tools/ltools.php');
include_once 'tools/appl.class.php';

if (getParam("action")=="passw") {
	include_once("config.php");
	include_once("logon.php");
	include_once("data.php");

	$db = new dbDAO;

	echo ($db->dbUtilityEncryptPasword());
	
	die();
}

if (getParam("action")=="decrypt") {
    include_once("tools/ltools.php");
    include_once("tools/userManager.php");
    echo (encrypt_decrypt("decrypt",getParam("param")));
    die();
}
if (getParam("action")=="encrypt") {
    include_once("tools/ltools.php");
    include_once("tools/userManager.php");
    echo (encrypt_decrypt("encrypt",getParam("param")));
    die();
}


\maierlabs\lpfw\Appl::$subTitle="Adatbank eszközök";
include('homemenu.php');


if (userIsAdmin()) {?>

<div class="panel panel-default">
	<div class="panel-heading">
		<label id="dbDetails">Backup/Restore</label> 
	</div>
	<ul class="list-group">
  		<li class="list-group-item">
  			<div class="input-group input-group-sm">
  	  			<span id="inputwidth" class="input-group-addon">Password</span>	
          		<input type="text" id="dbPassword" class="form-control" />
  	  		</div>
  	  		<p></p>
  			<div class="input-group input-group-sm">
  	  			<a class="btn btn-success" href="tools/backup.sql" >Download</a>
  	  			&nbsp;
  	  			<button class="btn btn-warning" onclick="backup();" >Backup</button>
  	  			&nbsp;
  	  			<button class="btn btn-danger" onclick="restore();" >Restore</button>
  	  		</div>
  		</li>
  		<li class="list-group-item">
			<div class="input-group input-group-sm">
	  			<span id="inputwidth" class="input-group-addon">Result</span>	
	       		<span style="display: inherit;" type="text" class="form-control" id="dbResult"></span>
	  		</div>
	  	</li>
	</ul>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<label id="dbDetails">Picture tools</label> 
	</div>
	<ul class="list-group">
  		<li class="list-group-item">
  			<div class="input-group input-group-sm">
  	  			<a class="btn btn-success" href="images/images.zip" >Download</a>
  	  			&nbsp;
  	  			<button class="btn btn-danger" onclick="createZipFile();" >Create ZIP File</button>
  	  			&nbsp;&nbsp;
  	  			<button class="btn btn-warning" onclick="picturesDB();" >Database</button>
  	  			&nbsp;&nbsp;
  	  			<button class="btn btn-warning" onclick="picturesFS();" >File system</button>
  	  			&nbsp;
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
  	  			<button class="btn btn-danger" onclick="rip('passw');" >EncryptPassw</button>
                <button class="btn btn-default" onclick="rip('decrypt');" >DecryptText</button>
                <button class="btn btn-default" onclick="rip('encrypt');" >EncryptText</button>
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

function backup() {
    $('#dbResult').html('backup....');
    $.ajax({
		url:"tools/dbbackup.php?password="+$('#dbPassword').val(),
		type:"GET",
		success:function(data){
		    $('#dbResult').html(data);
		},
		error:function(error) {
		    $('#dbResult').html("Error:"+data);
		}
    });
}

function restore() {
    $('#dbResult').html('restore....');
    $.ajax({
		url:"tools/dbrestore.php?password="+$('#dbPassword').val(),
		type:"GET",
		success:function(data){
		    $('#dbResult').html(data);
		},
		error:function(error) {
		    $('#bdResult').html("Error:"+data);
		}
    });
}

function picturesDB() {
    $('#pictureResult').html('searching....');
    $.ajax({
		url:"tools/picturesDB.php",
		type:"GET",
		success:function(data){
		    $('#pictureResult').html(data);
		},
		error:function(error) {
		    $('#pictureResult').html("Error:"+data);
		}
    });
}

function picturesFS() {
    $('#pictureResult').html('searching....');
    $.ajax({
		url:"tools/picturesFS.php",
		type:"GET",
		success:function(data){
		    $('#pictureResult').html(data);
		},
		error:function(error) {
		    $('#pictureResult').html("Error:"+data);
		}
    });
}

function picturesDelete() {
    if (confirm("Are you sure, that you want to delete all unreferenced pictures?")) {
	    $('#pictureResult').html('searching....');
	    $.ajax({
			url:"tools/picturesFS.php?action=delete",
			type:"GET",
			success:function(data){
			    $('#pictureResult').html(data);
			},
			error:function(error) {
			    $('#pictureResult').html("Error:"+data);
			}
	    });
    }
}

function createZipFile() {
    $('#pictureResult').html('creating zip file....');
    $.ajax({
		url:"tools/zipPictures.php",
		type:"GET",
		success:function(data){
		    $('#pictureResult').html(data);
		},
		error:function(error) {
		    $('#pictureResult').html("Error:"+data);
		}
    });
}

function rip(action) {
    $('#databaseResult').html('working....');
    $.ajax({
		url:"database.php?action="+action+"&param="+$('#dbText').val(),
		type:"GET",
		success:function(data){
		    $('#databaseResult').html(data);
		},
		error:function(error) {
		    $('#databaseResult').html("Error:"+data);
		}
    });
}



</script>
<?php include 'homefooter.php';?>
