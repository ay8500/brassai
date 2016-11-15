<?php
include_once 'tools/sessionManager.php';
include("homemenu.php");
include_once("data.php");
include_once 'tools/ltools.php';


$resultDBoperation="";
$action = getParam("action","");
$classid= getIntParam("classid",-1);
if ($classid>=0)
	$class=$db->getClassById($classid);

?>
<div class="container-fluid">
	<?php if ($classid>=0) {?>
		<h2 class="sub_title">Végzős osztály módosítása</h2>
	<?php } else {?>
		<h2 class="sub_title">Új végzős osztály létrehozása</h2>
	<?php } ?>

	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	
	<div class="input-group " style="margin-bottom: 25px;">
		<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Iskola</span>	      		
		<select class="form-control" disabled onchange="changeYear()" id="selectSchool">
			<option value="1">Brassai Sámuel Liceum: Kolozsvár</option>
		</select>
	</div>
	
	<?php if ($classid>=0) {?>
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
	<?php } else {?>
		<div class="input-group" style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Ballagási év</span>	      		
			<select class="form-control" onchange="changeYear()" id="selectYear">
				<option value="0">...válassz...</option>
				<option value="1">1990</option>
				<option value="1">1991</option>
			</select>
		</div>
		
		<div class="input-group" style="margin-bottom: 25px;">
			<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Osztály</span>	      		
			<select class="form-control" onchange="changeClass()" id="selectClass">
				<option value="0">...válassz...</option>
				<option value="1">12A</option>
				<option value="1">12B</option>
			</select>
		</div>
	<?php } ?>	
	<div class="input-group" style="margin-bottom: 25px;">
		<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Osztályfőnők</span>	      		
		<select class="form-control" onchange="changeTeacher()" id="selectTeacher">
			<option value="0">...válassz...</option>
			<option value="1">...nincs a listán...</option>
			<option value="1" style="height:50px;"><img src="images/oooteac/d2-0.jpg"/> Kiss Lajos: Magyar irodalom</option>
			<option value="1" >Pérfi: Kémia</option>
		</select>
	</div>
	
	<div class="well">
		<button class="btn btn-default disabled"   id="btNew" onclick="saveNewClass();" <?php if($action!="newclass") echo('style="display:none"');?>>
			<span class="glyphicon glyphicon-ok-circle"></span> Új osztályt létrehozom!
		</button>
		<button class="btn btn-default disabled"  id="btSave" onclick="saveClass();" <?php if($action=="newclass") echo('style="display:none"');?>>
			<span class="glyphicon glyphicon-ok-circle"></span> Osztályt módosításokat kiment!
		</button>
		<button class="btn btn-default disabled"  id="btCancel" onclick="cancelValues();">
			<span class="glyphicon glyphicon-remove-circle"></span> Adatokat törlöm
		</button>
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

	function changeTeacher() {
	    checkStatus();
	}

	function checkStatus() {
		if ($("#selectYear").val()!=0	&&
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

	function cancelValues() {
		$("#selectYear").val(0);
		$("#selectClass").val(0);
		$("#selectTeacher").val(0);
		checkStatus();
	}

	function saveClass() {
	    document.location='editclass.php?action=saveclass&year='+$("#selectYear").val()+'&class='+$("#selectClass").val()+'$teacher='+$("#selectTeacher").val()+"&classid=<?php echo $classid?>";
	}

	function saveNewClass() {
	    document.location='editclass.php?action=saveclass&year='+$("#selectYear").val()+'&class='+$("#selectClass").val()+'$teacher='+$("#selectTeacher").val();
	}
	
	
</script>