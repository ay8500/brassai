<?php 
include('homemenu.php');
include_once('tools/userManager.php');
$resultDBoperation="";

//if (userIsAdmin()) {$resultDBoperation='<div class="alert alert-warning" >Ok</div>';}  	
?>
<div class="container-fluid">   
	<div class="sub_title">Adatbank eszközök</div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
</div>


<?php if (userIsAdmin()) {?>

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
		<label id="dbDetails">Picture analysis</label> 
	</div>
	<ul class="list-group">
  		<li class="list-group-item">
  			<div class="input-group input-group-sm">
  	  			<button class="btn btn-warning" onclick="pictures();" >Go</button>
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


<?php }?>
<?php include 'homefooter.php';?>

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

function pictures() {
    $('#pictureResult').html('restore....');
    $.ajax({
		url:"tools/pictures.php",
		type:"GET",
		success:function(data){
		    $('#pictureResult').html(data);
		},
		error:function(error) {
		    $('#pictureResult').html("Error:"+data);
		}
    });
}

</script>
