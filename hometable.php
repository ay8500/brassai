<?php
include_once 'tools/sessionManager.php';
include_once("config.php");
include_once("data.php");
include_once 'tools/ltools.php';
include_once 'editDiakCard.php';
include_once 'chatinc.php';

// Title of the page schoolmate or guests
$guests = getParam("guests", "")=="true";
if (getAktClassId()==0) 
	$subTitle=$guests?"Barátaink":"Tanáraink";
else
	$subTitle=$guests?"Vendégek, jó barátok":"Osztálytársak";

$SiteTitle = $SiteTitle.': '.$subTitle;
include("homemenu.php");
	
$resultDBoperation="";
if (getParam("action","")=="delete_diak" &&  userIsLoggedOn() && ((userIsEditor() && getRealId(getAktClass())==getLoggedInUserClassId()) || userIsAdmin() || userIsSuperuser()) ) {
	if ($db->deletePersonEntry(getIntParam("uid" )))
		$resultDBoperation='<div class="alert alert-success">Véndiák sikeresen törölve!</div>';
	else
		$resultDBoperation='<div class="alert alert-warning">Véndiák törölése sikertelen! Hibakód:9811</div>';
}
$personList=$db->getPersonListByClassId(getRealId(getAktClass()),$guests);

// Toolbar for new schoolmate or guests
?>
<h2 class="sub_title"><?php $subTitle?></h2>
<div class="container-fluid">
	<div style="margin-bottom: 15px;width: 100%;background-color: #E3E3E3;padding: 10px;">
		<form action="editDiak.php">
			<?php if ($guests) {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newguest"><span class="glyphicon glyphicon-user"></span> Névsor bővítése jó baráttal</button>
			<?php } elseif (getAktClassId()!=0) {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newperson"><span class="glyphicon glyphicon-user"></span> Névsor bővítése új véndiákkal</button> 
			<?php } else  {?>
				<button id="new-btn" class="btn-c btn btn-default" name="action" value="newteacher"><span class="glyphicon glyphicon-user"></span> Névsor bővítése új tanárral</button>
			<?php } ?>
		</form>
		<?php if (getAktClassId()!=0) {showChatEnterfields($personList);} ?>
		<br/>
		<?php if ($guests) {?>
			Vendégek&nbsp;száma:<?php echo($db->getCountOfPersons(getRealId(getAktClass()), $guests));?>
		<?php } elseif (getAktClassId()!=0) {?>
			Véndiákok&nbsp;száma:<?php echo($db->getCountOfPersons(getRealId(getAktClass()), $guests));?>
		<?php } else  {?>
			Tanárok&nbsp;száma:<?php echo($db->getCountOfPersons(getRealId(getAktClass()), $guests));?>
		<?php } ?>
	</div>


	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>

<?php
foreach ($personList as $d)	
{ 
	displayPerson($db,$d);
}
?>
</div>
<?php  include ("homefooter.php");?>
