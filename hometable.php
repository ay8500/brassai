<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once("config.class.php");
include_once("data.php");
include_once 'editDiakCard.php';

use \maierlabs\lpfw\Appl as Appl;
// Title of the page schoolmate or guests
$guests = getParam("guests", "")=="true";
$class = handleClassSchoolChange();

if (isAktClassStaf()) {
	Appl::$subTitle=$guests?"Barátaink":"Tanáraink";
	$SiteTitle = $SiteTitle.': '.Appl::$subTitle;
} else {
	if ($guests) {
		Appl::$subTitle=getAktClassName()." Vendégek, jó barátok";
		$SiteTitle = $SiteTitle.': '.Appl::$subTitle;
	} else {
		if (isset($class["headTeacherID"]) && $class["headTeacherID"]>=0) {
			$headTeacher=$db->getPersonByID($class["headTeacherID"]);
			Appl::$subTitle=getAktClassName()." Osztályfőnök: ".getPersonLinkAndPicture($headTeacher);
			$SiteTitle = $SiteTitle." ".getAktClassName()." Osztályfőnök ".getPersonName($headTeacher);
		} else {
			Appl::$subTitle=getAktClassName()." Osztálytársak";
			$SiteTitle = $SiteTitle.': '.Appl::$subTitle;
		}
	}
}

if (isActionParam("saveok")) {
	Appl::setMessage("Köszzönjük szépen, személyes adatok kimentése sikerült.", "success");
}
include_once 'chatinc.php';

include("homemenu.php");


if (getParam("action","")=="delete_diak" &&  userIsLoggedOn() && ((userIsEditor() && getRealId(getAktClass())==getLoggedInUserClassId()) || userIsAdmin() || userIsSuperuser()) ) {
	if ($db->deletePersonEntry(getIntParam("uid" )))
		Appl::$resultDbOperation='<div class="alert alert-success">Véndiák sikeresen törölve!</div>';
	else
		Appl::$resultDbOperation='<div class="alert alert-warning">Véndiák törölése sikertelen! Hibakód:9811</div>';
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
<?php  include ("homefooter.php");?>
