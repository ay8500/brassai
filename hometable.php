<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once 'config.class.php';
include_once 'dbBL.class.php';
include_once 'displayCards.inc.php';
include_once 'chat.inc.php';

use \maierlabs\lpfw\Appl as Appl;
Appl::setMember("aktClass",$db->handleClassSchoolChange(getParam("classid"),getParam("schoolid")));
Appl::setMember( "staffClass",$db->getStafClassBySchoolId(getAktSchoolId()));
$class = Appl::getMember("aktClass");

// Title an subtitle of the page schoolmate or guests
$guests = getParam("guests", "")=="true";
if (isAktClassStaf()) {
	Appl::setSiteSubTitle($guests?"Barátaink":"Tanáraink");
    Appl::setSiteTitle(Appl::$subTitle);
} else {
	if ($guests) {
		Appl::setSiteSubTitle(getAktClassName()." Vendégek, jó barátok");
        Appl::setSiteTitle(Appl::$subTitle);
	} else {
		if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
			$headTeacher=$db->getPersonByID($class["headTeacherID"]);
			Appl::setSiteSubTitle(getAktClassName()." Osztályfőnök: ".getPersonLinkAndPicture($headTeacher));
            Appl::setSiteTitle(getAktClassName()." Osztályfőnök ".getPersonName($headTeacher));
		} else {
			Appl::setSiteSubTitle(getAktClassName()." Osztálytársak");
            Appl::setSiteTitle(Appl::$subTitle);
		}
	}
}

include("homemenu.inc.php");

if (isActionParam("saveok")) {
	Appl::setMessage("Köszzönjük szépen, személyes adatok kimentése sikerült.", "success");
}


if (isActionParam("delete_diak") &&  userIsLoggedOn() && ((userIsEditor() && getRealId(getAktClass())==$db->getLoggedInUserClassId()) || userIsAdmin() || userIsSuperuser()) ) {
	if ($db->deletePersonEntry(getIntParam("uid" ))) {
        Appl::setMessage("Véndiák sikeresen törölve!", "success");
        $db->updateRecentChangesList();
    } else {
		Appl::setMessage("Véndiák törölése sikertelen! ","warning");
	}
}
$personList=$db->getPersonListByClassId(getRealId($class),$guests);

// Toolbar for new schoolmate or guests
?>
<div class="container-fluid">
	<div style="margin-bottom: 15px;width: 100%;background-color: #E3E3E3;padding: 10px;">
		<form action="editDiak.php">
			<?php if ($guests) {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newguest"><span class="glyphicon glyphicon-user"></span> Névsor bővítése jó baráttal</button>
			<?php } elseif (!isAktClassStaf()) {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newperson"><span class="glyphicon glyphicon-user"></span> Névsor bővítése új véndiákkal</button> 
			<?php } else  {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newteacher"><span class="glyphicon glyphicon-user"></span> Névsor bővítése új tanárral</button>
			<?php } ?>
		</form>
		<?php if (!isAktClassStaf()) {showChatEnterfields($personList);} ?>
		<br/>
		<?php if ($guests) {?>
			Vendégek&nbsp;száma:<span id="personCount"><?php echo($db->getCountOfPersons(getRealId(getAktClass()), $guests));?></span>
		<?php } elseif (!isAktClassStaf()) {?>
			Véndiákok&nbsp;száma:<span id="personCount"><?php echo($db->getCountOfPersons(getRealId(Appl::getMember("aktClass")), $guests));?></span>
		<?php } else  {?>
			Tanárok&nbsp;száma:<span id="personCount"><?php echo($db->getCountOfPersons(getRealId(getAktClass()), $guests));?></span>
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
