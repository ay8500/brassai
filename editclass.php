<?php
include_once 'tools/sessionManager.php';
include_once("data.php");
include_once 'tools/ltools.php';


$resultDBoperation="";
$action = getParam("action","");
$classid= getIntParam("classid",-1);
if ($classid>=0) {
	$class=$db->getClassById($classid);
	$personCount=sizeof($db->getPersonListByClassId($class["id"]));
	if ($action=="deleteclass" && $personCount==0 && userIsAdmin()) {
		if ($db->deleteClass($classid)) {
			$resultDBoperation='<div class="alert alert-success">Új osztály sikeresen törölve!</div>';
		} else {
			$resultDBoperation='<div class="alert alert-warning">Új osztály törlése sikertelen!</div>';
		}
	}
} 
if ($action=="saveclass") {
	if ($classid<0) 
		$class= $db->getClassByText(getParam("year")." ".getParam("class"));
	if ($class!=null && $classid<0) 
		$resultDBoperation='<div class="alert alert-warning">Ez az osztály már létezik!</div>';
	else {
		$classid=$db->saveClass([
				"id"=>$classid,
				"schoolID"=>1,
				"graduationYear"=>getParam("year"),
				"name"=>getParam("class"),
				"text"=>getParam("year")." ".getParam("class"),
				"headTeacherID"=>getIntParam("teacher",0)
		]);
		if ($classid>=0 ) {
			$resultDBoperation='<div class="alert alert-success">Osztály sikeresen kimentve!</div>';
			setAktClass($classid);
		} else {
			$resultDBoperation='<div class="alert alert-warning">Osztály kimentése sikertelen!</div>';
		}
	}
}

include("homemenu.php");

?>
<div class="container-fluid">
	<?php if ($classid>=0) {?>
		<h2 class="sub_title">Végzős osztály módosítása</h2>
	<?php } else {?>
		<h2 class="sub_title">Új végzős osztály létrehozása</h2>
	<?php } ?>

	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	
	
	<?php if ($classid>=0) {  //Edit an existing class?>
		<div class="input-group " style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Iskola</span>	      		
			<select class="form-control" disabled id="selectSchool">
				<option value="1">Brassai Sámuel Liceum: Kolozsvár</option>
			</select>
		</div>
		<div class="input-group" style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Ballagási év</span>	      		
			<select class="form-control" disabled id="selectYear">
				<option value="<?php echo $class["graduationYear"] ?>"><?php echo $class["graduationYear"] ?></option>
			</select>
		</div>
		
		<div class="input-group" style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Osztály</span>	      		
			<select class="form-control" disabled id="selectClass">
				<option value="<?php echo $class["name"] ?>"><?php echo $class["name"] ?></option>
			</select>
		</div>
	<?php } else {  //Create a new class?>
		<div class="input-group " style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Iskola</span>	      		
			<select class="form-control" onchange="changeSchool()" id="selectSchool">
				<option value="1">Brassai Sámuel Liceum: Kolozsvár</option>
				<option value="2">Hiányzik a te iskolád, akkor küdj egy e-mailt a rendszergazdának. brassai@blue-l.de</option>
			</select>
		</div>
		<div class="input-group" style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Ballagási év</span>	      		
			<select class="form-control" onchange="changeYear()" id="selectYear">
				<option value="0">...válassz...</option>
				<?php for($year=1950;$year<2018;$year++) {?>
				<option value="<?php echo $year?>"><?php echo $year?></option>
				<?php } ?>
			</select>
		</div>
		
		<div class="input-group" style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Osztály</span>	      		
			<select class="form-control" onchange="changeClass()" id="selectClass">
				<option value="0">...válassz...</option>
				<?php 
					for($cl=10;$cl<14;$cl++) {
						for($cs="A";$cs<"G";$cs++) {
				?>
					<option value="<?php echo $cl.$cs ?>"><?php echo $cl.$cs ?></option>
				<?php } } ?>
			</select>
		</div>
	<?php } ?>	
	<div class="input-group" style="margin-bottom: 25px;">
		<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Osztályfőnők</span>	      		
		<select class="form-control" onchange="changeTeacher()" id="selectTeacher">
			<option value="0">...válassz...</option>
			<option value="-1">...nincs a listán...</option>
			<?php
				$teachers=$db->getPersonListByClassId(0);
				foreach ($teachers as $t) {
					if ($t["isTeacher"]==1) {
			?>
				<option value="<?php echo $t['id']?>" <?php echo (isset($class['headTeacherID']) && $t['id']==$class['headTeacherID']?"selected":"") ?> > 
					<?php echo getPersonName($t).':'.getFieldValueNull($t,'function')?>
				</option>
			<?php } }?>
		</select>
	</div>
	
	<div class="well">
		<button class="btn btn-default disabled"   id="btNew" onclick="saveNewClass();" <?php if($action!="newclass") echo('style="display:none"');?>>
			<span class="glyphicon glyphicon-ok-circle"></span> Új osztályt létrehozom!
		</button>
		<button class="btn btn-default"   id="btMail" onclick="mailto:brassai@blue-l.de" style="display:none">
			<span class="glyphicon glyphicon-email"></span> Új iskolát szeretnék
		</button>
		<button class="btn btn-default disabled"  id="btSave" onclick="saveClass();" <?php if($action=="newclass") echo('style="display:none"');?>>
			<span class="glyphicon glyphicon-ok-circle"></span> Osztály módosításokat kiment!
		</button>
		<?php if (userIsAdmin() && isset($personCount) ) :?>
			<span>Diákok száma:<?php echo $personCount?></span>
			<button class="btn btn-default " <?php if($personCount>0) echo "disabled";?> onclick="deleteClass();">
				<span class="glyphicon glyphicon-remove-circle"></span> Osztályt töröl
			</button>
		<?php  endif;?>
	</div>

	<?php $stat=$db->getClassStatistics($classid);?>
	<div class="well " style="margin-bottom: 25px;">
		<h4>Statisztikai adatok</h4>
		<div class="form">	      		
		<a href="hometable.php?classid=<?php echo $classid?>">Diákok</a> száma:<?php echo $stat->personCount?><br/>
		Diákok képpel:<?php echo $stat->personWithPicture?><br/>
		Diakok képei:<?php echo $stat->personPictures?><br/>
		<a href="picture.php?classid=<?php echo $classid?>">Osztályképek:</a><?php echo $stat->classPictures?><br/>
		</div>
	</div>
</div>

<?php 
include_once 'homefooter.php';
?>

<script>

	function changeYear() {
	    checkStatus();
	}
		
	function changeClass() {
	    checkStatus();
	}

	function changeSchool() {
		if ($("#selectSchool").val()==1	) {
		    $("#btNew").show();$("#btMail").hide();
		} else {
		    $("#btNew").hide();$("#btMail").show();
		    
		}
	    checkStatus();
	}

	
	function changeTeacher() {
	    checkStatus();
	}

	function checkStatus() {
		if ($("#selectSchool").val()==1	&&
			$("#selectYear").val()!=0	&&
			$("#selectClass").val()!=0	&&
			$("#selectTeacher").val()!=0 ) {
			$("#btNew").removeClass("disabled");
			$("#btSave").removeClass("disabled");
		} else {
		    $("#btNew").addClass("disabled");
		    $("#btSave").addClass("disabled");
		}
		
		if ($("#selectYear").val()!=0	||
			$("#selectClass").val()!=0	||
			$("#selectTeacher").val()!=0 ) {
			$("#btCancel").removeClass("disabled");
		} else {
		    $("#btCancel").addClass("disabled");
		}
	}

	function saveClass() {
	    document.location='editclass.php?action=saveclass&year='+$("#selectYear").val()+'&class='+$("#selectClass").val()+'&teacher='+$("#selectTeacher").val()+"&classid=<?php echo $classid?>";
	}

	function saveNewClass() {
	    document.location='editclass.php?action=saveclass&year='+$("#selectYear").val()+'&class='+$("#selectClass").val()+'&teacher='+$("#selectTeacher").val();
	}
	
	function deleteClass() {
		if (confirm('Biztos ki szeretnéd törölni az osztályt?'))
	    	document.location='editclass.php?action=deleteclass&classid=<?php echo $classid?>';
	}
	
</script>