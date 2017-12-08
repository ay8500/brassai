<?php 
include('homemenu.php');
include_once('tools/userManager.php');
$resultDBoperation="";

if (userIsAdmin()) {
	$resultDBoperation='<div class="alert alert-warning" >Ok</div>';
}  	
?>
<div class="container-fluid">   
	<div class="sub_title">Adatbank eszközök</div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
</div>


<?php
if (userIsAdmin()) {  
}
?>
<?php include 'homefooter.php';?>
