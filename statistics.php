<?php 
$SiteTitle="A kolozsvári Brassai Sámuel líceum statisztikai adatai";
include('homemenu.php');
include_once('tools/userManager.php');
$resultDBoperation="";

$persons=$db->getPersonList();
$classmate=0;$classmatePicture=0;$classmateEmail=0;
$teacher=0;$teacherPicture=0;
foreach ($persons as $person) {
	if (intval($person["isTeacher"])==1) {
		$teacher++;
		if($person["picture"]!="avatar.jpg")
			$teacherPicture++;
	} else {
		$classmate++;
		if($person["picture"]!="avatar.jpg")
			$classmatePicture++;
		if(isset($person["email"]) && $person["email"]!="")
			$classmateEmail++;
	}
}
unset($persons);

$classes=$db->getClassList(getAktSchoolId());
$classCount=0;$classGraduationPicture=0;$classPicture=0;
foreach ($classes as $class) {
	if ($class["text"]!="staf")
		$classCount++;
	$pictures=$db->getPictureList("classID=".$class["id"]);
	foreach ($pictures as $picture) {
		if(isset($picture["title"]) && $picture["title"]!="" && (strstr($picture["title"],"Tabl")!="" || strstr($picture["title"],"tabl")!="") ) 
			$classGraduationPicture++;
		else
			$classPicture++;
	}
}
unset($classes);unset($pictures);

?>
<style>
.statw {width:150px; text-align:right; display: inline-block;};
</style>
<div class="container-fluid">   
	<div class="sub_title">Statistikai adatok</div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
</div>


<div class="panel panel-default">
	<div class="panel-heading text-center"><label >Tanárok</label></div>
	<ul class="list-group">
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Összesen</span></span>
	       		<span type="text" class="form-control"><?php echo $teacher?></span>
	  		</div>
  		</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képpel</span></span>	
	       		<span type="text" class="form-control"><?php echo $teacherPicture." (".round($teacherPicture/$teacher*100,2)."%)"?></span>
	  		</div>
	  	</li>
	  	<br/>
	</ul>
</div>


<div class="panel panel-default">
	<div class="panel-heading text-center"><label >Véndiákok</label></div>
	<ul class="list-group">
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Összesen</span></span>
	       		<span type="text" class="form-control"><?php echo $classmate?></span>
	  		</div>
  		</li>
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Képpel</span></span>	
	       		<span type="text" class="form-control"><?php echo $classmatePicture." (".round($classmatePicture/$classmate*100,2)."%)"?></span>
	  		</div>
	  	</li>
  		<li>
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">E-Mail címmel</span></span>	
	       		<span type="text" class="form-control"><?php echo $classmateEmail." (".round($classmateEmail/$classmate*100,2)."%)"?></span>
	  		</div>
	  	</li>
	  	<br/>
	</ul>
</div>

<div class="panel panel-default">
	<div class="panel-heading text-center"><label >Osztályok</label></div>
	<ul class="list-group">
  		<li class="">
			<div class="input-group input-group-sm">
	  			<span class="input-group-addon"><span class="statw">Összesen</span></span>
	       		<span type="text" class="form-control"><?php echo $classCount?></span>
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
	  			<span class="input-group-addon"><span class="statw">Képek</span></span>	
	       		<span type="text" class="form-control"><?php echo $classPicture?></span>
	  		</div>
	  	</li>
	  	<br/>
	</ul>
</div>

<?php include 'homefooter.php';?>

