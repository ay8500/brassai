<?php 
include('homemenu.php');
include_once('tools/userManager.php');
$resultDBoperation="";

//if (userIsAdmin()) {$resultDBoperation='<div class="alert alert-warning" >Ok</div>';}  	
?>
<div class="container-fluid">   
	<div class="sub_title">Adatmódosítások</div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
</div>


<?php if (userIsAdmin()) {
	$history=$db->getHistory(getParam("table"), getParam("id"));
?>

<div class="panel panel-default">
	<div class="panel-heading">
		<label id="dbDetails">Adat</label> 
	</div>
	<ul class="list-group">
		<?php foreach ($history as $item) {?>
  		<li class="list-group-item">
  			<div class="input-group input-group-sm">
  	  			<span id="inputwidth" class="input-group-addon"><?php echo $item["id"]?></span>	
          		<div style="width:80%;"><?php echo var_dump(json_decode($item["jsonData"]))?></div>
  	  		</div>
  	  		<p></p>
  			<div class="input-group input-group-sm">
  	  			<button class="btn btn-warning" onclick="backup();" >Backup</button>
  	  			&nbsp;
  	  			<button class="btn btn-danger" onclick="restore();" >Delete</button>
  	  		</div>
  		</li>
  		<?php } ?>
	</ul>
</div>

<?php }?>
<?php include 'homefooter.php';?>

<script type="text/javascript">


</script>
