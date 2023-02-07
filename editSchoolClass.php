<?php
include_once "config.class.php";
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once "dbBL.class.php";

use maierlabs\lpfw\Appl as Appl;

Appl::addJs("js/chosen.jquery.js");
Appl::addCss("css/chosen.css");
Appl::addCssStyle('
    .shadowbox {margin-bottom: 25px; box-shadow: 3px 4px 7px 1px lightgray;}
');

/**
 * @var dbBL;
 */
global $db;
$schoolList = $db->getSchoolList();
$classid= getIntParam("classid",-1);
$class=$db->getClassById($classid);
$changedByPerson = $db->getPersonByID($class["changeUserID"]);
$subTitle= ($classid>=0) ? "végzős osztály módosítása" :  "új végzős osztály létrehozása";
Appl::setSiteTitle($class["text"]. " osztály módosítása",$class["text"]. " osztály módosítása");

if ($class==null && !isActionParam("newclass") && !isActionParam("saveclass")) {
    Appl::setMessage("Osztály nem létezik!", "danger");
    include("homemenu.inc.php");
    include ("homefooter.inc.php");
    die();
}

if (isActionParam("newclass")) {
    if (getIntParam("schoolid")>0)
        setActSchool(getIntParam("schoolid"));
}

if (isActionParam("deleteclass") && isUserAdmin()) {
    $personCount=sizeof($db->getPersonListByClassId($class["id"]));
    if(  $personCount==0 ) {
        if ($db->deleteClass($classid)) {
            Appl::setMessage("Osztály sikeresen törölve!", "success");
            $classid = -1;
        } else {
            Appl::setMessage("Osztály törlése sikertelen!", "warning");
        }
    } else {
        Appl::setMessage("Osztály tartalmaz diákokat, emiatt a törlés nem lehetséges!", "warning");
    }
}

if (isActionParam("saveclass")) {
	if ($classid<0) 
		$class= $db->getClassByText(getParam("year")." ".getParam("class"));
	if ($class!=null && $classid<0) 
		Appl::setMessage("Ez az osztály már létezik!","warning");
	else {
		$classid=$db->saveClass([
				"id"=>$classid,
				"schoolID"=>getActSchoolId(),
				"eveningClass"=>getIntParam("eveningClass",0),
				"graduationYear"=>getParam("year"),
				"name"=>getParam("class"),
				"text"=>getParam("year")." ".getParam("class"),
				"teachers"=>getParam("teachers"),
				"headTeacherID"=>getIntParam("teacher",0),
                "secondHeadTeacherID"=>getIntParam("secondTeacher",0)
		]);
		if ($classid>=0 ) {
		    $db->updateRecentChangesList();
			setActClass($classid,getActSchoolId());
			$class=$db->getClassById($classid);
            Appl::setMessage("Osztály sikeresen kimentve! Köszönjük szépen.","success");
        } else {
			Appl::setMessage("Osztály kimentése sikertelen!","warning");
		}
	}
}

include("homemenu.inc.php");
?>
<div class="container-fluid">
    <?php if ($classid>=0) {
        include_once "displayCards.inc.php";
        displayClass($db, $class, false);
    } ?>

    <div class="input-group shadowbox">
        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Iskola</span>
        <select class="form-control" onchange="changeSchool();" <?php echo (isUserSuperuser() || isUserAdmin() || isActionParam("newclass"))?"":"disabled" ?> id="selectSchool">
            <?php foreach ($schoolList as $school) {
                $selected = $school["id"]==getActSchoolId()?"selected=selected":""?>
                <option value="<?php echo $school["id"] ?>" <?php echo $selected ?>><?php echo $school["name"] ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="input-group shadowbox" >
        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Tagozat</span>
        <select class="form-control" onchange="checkStatus()" id="eveningClass">
            <option value="0" <?php echo $class["eveningClass"]==0?"selected":""?>>nappali tagozat</option>
            <option value="1" <?php echo $class["eveningClass"]==1?"selected":""?>>esti tagozat</option>
        </select>
    </div>

    <div class="input-group shadowbox">
        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Ballagási év</span>
        <?php if (isUserAdmin() || isUserSuperuser()) {?>
            <input type="text" id="year" class="form-control" onkeyup="changeYear()" value="<?php echo intval($class["graduationYear"])?>"/>
        <?php } else { ?>
            <select class="form-control" onchange="changeYear()" <?php echo (isUserEditor() || isActionParam("newclass"))?"":"disabled" ?> id="selectYear"/>
                <option value="0">...válassz...</option>
                <option selected value="<?php echo isset($class)?intval($class["graduationYear"]):""?>"><?php echo isset($class)?intval($class["graduationYear"]):""?></option>
                 <?php for($year=date("Y");$year>=date("Y")-70;$year--) {?>
                    <option value="<?php echo $year?>" <?php echo (isset($class) && intval($class["graduationYear"])===$year)?"selected":""?>><?php echo $year?></option>
                <?php } ?>
            </select>
        <?php }?>
    </div>

    <div class="input-group shadowbox">
        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Osztály</span>
        <select class="form-control" onchange="changeClass()" <?php echo (isUserSuperuser() || isUserAdmin() || isUserEditor() || isActionParam("newclass"))?"":"disabled" ?> id="selectClass">
            <option value="0">...válassz...</option>
            <option value="" <?php echo (isset($class) && $class["name"]=="")?"selected":""?>>összes nappali osztályok</option>
            <option value="esti" <?php echo (isset($class) && $class["name"]=="esti")?"selected":""?>>összes esti osztályok</option>
            <?php
                $cl=12;for($cs="A";$cs<="K";$cs++) {
                        ?><option value="<?php echo $cl.$cs ?>" <?php echo (isset($class) && $class["name"]===$cl.$cs)?"selected":""?>><?php echo $cl.$cs ?></option><?php
                } $cs="R";
                ?><option value="<?php echo $cl.$cs ?>" <?php echo (isset($class) && $class["name"]===$cl.$cs)?"selected":""?>><?php echo $cl.$cs ?></option><?php
                $cl=13;for($cs="A";$cs<="K";$cs++) {
                    ?><option value="<?php echo $cl.$cs ?>" <?php echo (isset($class) && $class["name"]===$cl.$cs)?"selected":""?>><?php echo $cl.$cs ?></option><?php
                }
                    $cl=11;for($cs="A";$cs<="F";$cs++) {
                    ?><option value="<?php echo $cl.$cs ?>" <?php echo (isset($class) && $class["name"]===$cl.$cs)?"selected":""?>><?php echo $cl.$cs ?></option><?php
                }
                $cl=10;for($cs="A";$cs<="D";$cs++) {
                    ?><option value="<?php echo $cl.$cs ?>" <?php echo (isset($class) && $class["name"]===$cl.$cs)?"selected":""?>><?php echo $cl.$cs ?></option><?php
                }
             ?>
        </select>
    </div>

	<div class="input-group shadowbox">
		<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Osztályfőnők</span>	      		
		<select class="form-control" onchange="changeTeacher()" id="selectTeacher">
			<option value="0">...válassz...</option>
			<option value="-1">...nincs a listán...</option>
			<?php
                $teachers = $db->getTeacherListBySchoolId(getActSchoolId());
				foreach ($teachers as $t) {?>
				<option value="<?php echo $t['id']?>" <?php echo (isset($class['headTeacherID']) && $t['id']==$class['headTeacherID']?"selected":"") ?> > 
					<?php echo getPersonName($t).':'.getFieldValueNull($t,'function')?>
				</option>
			<?php } ?>
		</select>
	</div>

    <div class="input-group shadowbox">
        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Második osztályfőnők</span>
        <select class="form-control" onchange="changeTeacher()" id="selectHeadTeacher">
            <option value="-1">...válassz...</option>
            <option value="-1">...nincs...</option>
            <?php
            $teachers = $db->getTeacherListBySchoolId(getActSchoolId());
            foreach ($teachers as $t) {?>
                <option value="<?php echo $t['id']?>" <?php echo (isset($class['secondHeadTeacherID']) && $t['id']==$class['secondHeadTeacherID']?"selected":"") ?> >
                    <?php echo getPersonName($t).':'.getFieldValueNull($t,'function')?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="input-group shadowbox">
        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Tanáraink</span>
        <select class="chosen form-control" multiple="true" data-placeholder="...válassz..." id="teachers">
            <?php
            if (isset($class["teachers"]) && $class["teachers"]!='') {
                $savedTeachers = explode(',', $class["teachers"]);
            } else {
                $savedTeachers=array();
            }
            foreach ($teachers as $teacher) {
                $selected = in_array($teacher["id"],$savedTeachers)?"selected":"";
                if (!(isset($class["headTeacherID"]) && $teacher["id"]==$class["headTeacherID"]) && !(isset($class["secondHeadTeacherID"]) && $teacher["id"]==$class["secondHeadTeacherID"]) )  {?>
                    <option value="<?php echo $teacher["id"]?>" <?php echo $selected?>><?php echo getPersonName($teacher).':'.getFieldValueNull($teacher,'function')?></option>
            <?php } }?>
        </select>
    </div>

	<div class="well">
		<?php  if ((isUserSuperuser()) && isset($class)) {?>
			<a href="history?table=class&id=<?php echo $class["id"]?>" style="display:inline-block;" title="módosítások">
				<span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("class",$class["id"]))?></span>
			</a>
		<?php }?>
		<button class="btn btn-success disabled"   id="btNew" onclick="saveNewClass();" <?php if (!isActionParam("newclass")) echo('style="display:none"');?>>
			<span class="glyphicon glyphicon-ok-circle"></span> Új osztályt létrehozom!
		</button>
		<button class="btn btn-success disabled"  id="btSave" onclick="saveClass();" <?php if (isActionParam("newclass")) echo('style="display:none"');?>>
			<span class="glyphicon glyphicon-ok-circle"></span> Osztály módosításokat kiment
		</button>
        <?php $stat=$db->getClassStatistics($classid);?>
		<?php if (isUserAdmin() ) :?>
			<span>Diákok száma:<?php echo $stat->personCount?></span>
			<button class="btn btn-danger " <?php if($stat->personCount>0) echo "disabled";?> onclick="deleteClass();">
				<span class="glyphicon glyphicon-remove-circle"></span> Osztályt töröl
			</button>
		<?php  endif;?>
	</div>

	<div class="well " style="margin-bottom: 25px;">
		<h4>Statisztikai adatok</h4>
		<div class="form">	      		
            <a href="hometable?classid=<?php echo $classid?>">Diákok</a> száma:<?php echo $stat->personCount?><br/>
            <a href="hometable?guests=true&classid=<?php echo $classid?>">Vendégek barátok</a> száma:<?php echo $stat->guestCount?><br/>
            Diákok képpel:<?php echo $stat->personWithPicture?><br/>
            Diakok képei:<?php echo $stat->personPictures?><br/>
            <a href="picture?type=classID&typeid=<?php echo $classid?>">Osztályképek:</a><?php echo $stat->classPictures?><br/>
            Utoljára módosítva:
            <?php echo getPersonLinkAndPicture($changedByPerson) ?>
            <?php echo maierlabs\lpfw\Appl::dateTimeAsStr($class["changeDate"]);?>
        </div>
	</div>
</div>


<?php
Appl::addJsScript('
    $(document).ready(function(){
        $(".chosen").chosen();
        checkStatus();
    });
    
    var teachersChanged = false;
    
    $(".chosen").on("change", function(evt, params) {
        checkStatus();
    });
    
    function changeYear() {
	    checkStatus();
	}
		
	function changeClass() {
	    checkStatus();
	    if ($("#selectClass").val()==="") {
		    $("#eveningClass").val("0");
	    }
	    if ($("#selectClass").val()==="esti") {
		    $("#eveningClass").val("1");
	    }
	}

	function changeSchool() {
		if ($("#selectSchool option:selected").val()!==0	) {
		    document.location.href="editSchoolClass?action=newclass&schoolid="+$("#selectSchool").val();
		} 
	}

	function changeTeacher() {
	    checkStatus();
	}

	function checkStatus() {
		if ($("#selectSchool").val()!==0 &&
			($("#selectYear").val()!=0 || ($("#year").length>0 && $("#year").val().length==4 && $("#year").val()>1800 && $("#year").val()<2500)) &&
			$("#selectClass").val()!=0	&&
			$("#selectTeacher").val()!=0 
			) {
			$("#btNew").removeClass("disabled");
			$("#btSave").removeClass("disabled");
		} else {
		    console.log("Save conditions not given!");
		    $("#btNew").addClass("disabled");
		    $("#btSave").addClass("disabled");
		}
		
	}

	function saveClass() {
	    showWaitMessage();
	    console.log("Save class id:"+'.$classid.');
	    var year = $("#selectYear").val();
	    if (year == undefined)
	      year = $("#year").val();
	    document.location="editSchoolClass?action=saveclass&year="+year+"&class="+$("#selectClass").val()+"&teacher="+$("#selectTeacher").val()+"&secondTeacher="+$("#selectHeadTeacher").val()+"&teachers="+getTeachers()+"&eveningClass="+$("#eveningClass").val()+"&classid='.$classid.'";
	}

	function saveNewClass() {
	    showWaitMessage();
	    console.log("Save new class ");
	    var year = $("#selectYear").val();
	    if (year == undefined)
	      year = $("#year").val();
	    document.location="editSchoolClass?action=saveclass&year="+year+"&class="+$("#selectClass").val()+"&teacher="+$("#selectTeacher").val()+"&secondTeacher="+$("#selectHeadTeacher").val()+"&teachers="+getTeachers()+"&eveningClass="+$("#eveningClass").val();
	}
	
	function getTeachers() {
	    return $("#teachers").val().toString();
	}
	
	function deleteClass() {
		if (confirm("Biztos ki szeretnéd törölni az osztályt?"))
	    	document.location="editSchoolClass?action=deleteclass&classid='.$classid.'";
	}
');

include_once 'homefooter.inc.php';