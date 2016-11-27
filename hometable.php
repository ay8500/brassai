<?php
include_once 'tools/sessionManager.php';
include("homemenu.php");
include_once("data.php");
include_once 'tools/ltools.php';
include_once 'editDiakCard.php';


$resultDBoperation="";
if (getParam("action","")=="delete_diak" &&  userIsLoggedOn() && ((userIsEditor() && getRealId(getAktClass())==getLoggedInUserClassId()) || userIsAdmin()) ) {
	if ($db->deletePersonEntry(getIntParam("uid" )))
		$resultDBoperation='<div class="alert alert-success">Véndiák sikeresen törölve!</div>';
	else
		$resultDBoperation='<div class="alert alert-warning">Véndiák törölése sikertelen! Hibakód:9811</div>';
}

// Title of the page schoolmate or guests
$guests = getParam("guests", "")=="true";
if (getAktClassId()==0) {
	echo('<h2 class="sub_title">Tanáraink</h2>');
} else {
	if ($guests )
		echo('<h2 class="sub_title">Nem végzős osztálytársak, vendégek, jó barátok.</h2>');
	else
		echo('<h2 class="sub_title">Osztálytársak</h2>');
}
	
// Toolbar for new schoolmate or guests
?>
<div class="container-fluid">
	<div style="margin-bottom: 15px;width: 100%;background-color: #E3E3E3;padding: 10px;">
		<form action="editDiak.php">
			<?php if ($guests) {?>
				<input type="hidden" name="action" value="newguest" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új vendéggel, jó baráttal"/>
				Vendégek száma:
			<?php } else if (getAktClassId()!=0) {?>
				<input type="hidden" name="action" value="newperson" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új véndiákkal "/>
				Véndiákok száma:
			<?php } else  {?>
				<input type="hidden" name="action" value="newteacher" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új tanárral "/>
				Tanárok száma:
			<?php } ?>
		</form>
		<?php echo($db->getCountOfPersons(getRealId(getAktClass()), $guests));?>
	</div>


	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>

<?php
$personList=$db->getPersonListByClassId(getRealId(getAktClass()));
foreach ($personList as $d)	
{ 
	if ( $guests == isPersonGuest($d) ) {

		editDiakCard($d);
	}
}
?>
</div>
<?php  include ("homefooter.php");?>
