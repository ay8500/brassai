<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'displayCards.inc.php';
include_once 'chat.inc.php';

use \maierlabs\lpfw\Appl as Appl;
global $db;
Appl::setMember("actClass",$db->handleClassSchoolChange(getParam("classid"),getParam("schoolid")));
Appl::setMember( "staffClass",$db->getStafClassBySchoolId(getActSchoolId()));
$class = Appl::getMember("actClass");

// Title and subtitle of the page schoolmate or guests
$guests = getParam("guests", "")=="true";
if (isActClassStaf()) {
	Appl::setSiteSubTitle($guests?"Barátaink":"Tanáraink");
    Appl::setSiteTitle(Appl::$subTitle);
} else {
	if ($guests) {
		Appl::setSiteSubTitle(getActSchoolClassName()." Vendégek, jó barátok");
        Appl::setSiteTitle(Appl::$subTitle);
	} else {
		if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
			$headTeacher=$db->getPersonByID($class["headTeacherID"]);
            $applSubTitle = getActSchoolClassName()." Osztályfőnök: ".getPersonLinkAndPicture($headTeacher);
            if (isset($class["secondHeadTeacherID"]) && $class["secondHeadTeacherID"]>=0) {
                $secondHeadTeacher = $db->getPersonByID($class["secondHeadTeacherID"]);
                $applSubTitle .= " és ".getPersonLinkAndPicture($secondHeadTeacher);
            }
			Appl::setSiteSubTitle($applSubTitle);
            Appl::setSiteTitle(getActSchoolClassName()." Osztályfőnök ".getPersonName($headTeacher));
		} else {
			Appl::setSiteSubTitle(getActSchoolClassName()." Osztálytársak");
            Appl::setSiteTitle(Appl::$subTitle);
		}
	}
}

include("homemenu.inc.php");

if (isActionParam("saveok")) {
	Appl::setMessage("Köszzönjük szépen a hozzájárásod. Személyes adatok kimentése sikerült.", "success");
}


if (isActionParam("delete_diak") &&  isUserLoggedOn() && ((isUserEditor() && getRealId(getActClass())==$db->getLoggedInUserClassId()) || isUserSuperuser()) ) {
	if ($db->deletePersonEntry(getIntParam("uid" ))) {
        Appl::setMessage("Személy sikeresen törölve!", "success");
        $db->updateRecentChangesList();
    } else {
		Appl::setMessage("Személy törölése sikertelen! ","danger");
	}
}
if (isActClassStaf() && !$guests) {
    $personList = $db->getTeacherListBySchoolId(getActSchoolId());
} else {
    if ($class==null) {
        $class=$db->getStafClassBySchoolId(getActSchoolId());
    }
    $personList = $db->getPersonListByClassId($class, $guests);
}

// Toolbar for new schoolmate or guests
?>
<div class="container-fluid">
	<div style="margin-bottom: 15px;width: 100%;background-color: #E3E3E3;padding: 10px;">
		<form action="editPerson">
			<?php if ($guests) {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newguest"><span class="glyphicon glyphicon-user"></span> Névsor bővítése jó baráttal</button>
			<?php } elseif (!isActClassStaf()) {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newperson"><span class="glyphicon glyphicon-user"></span> Névsor bővítése új véndiákkal</button> 
			<?php } else  {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newteacher"><span class="glyphicon glyphicon-user"></span> Névsor bővítése új tanárral</button>
			<?php } ?>
		</form>
		<?php if (!isActClassStaf()) {showChatEnterfields($personList);} ?>
		<br/>
		<?php if ($guests) {?>
			Vendégek&nbsp;száma:<span id="personCount"><?php echo($db->getCountOfPersons(getRealId(getActClass()), $guests));?></span>
		<?php } elseif (!isActClassStaf()) {?>
			Véndiákok&nbsp;száma:<span id="personCount"><?php echo($db->getCountOfPersons(getRealId(Appl::getMember("actClass")), $guests));?></span>
		<?php } else  {
            usort($personList, "compareAlphabeticalByLastFirstName");
            ?>
			Tanárok&nbsp;száma:<span id="personCount"><?php echo(sizeof($personList));?></span>
		<?php } ?>
	</div>
<?php
foreach ($personList as $d)	
{ 
	displayPerson($db,$d);
}
?>
</div>
<?php  include("homefooter.inc.php");?>
