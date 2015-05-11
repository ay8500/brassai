<?php
	include_once 'ltools.php';
	include_once 'data.php';
	
	$tab=getIntParam("tabOpen", 0);
	if ($tab==2) {
		$title="Rövid életrajzom: továbbképzések munkahelyek.";
		$text =loadTextData(getDatabaseName(), $uid, "cv");
	} elseif ($tab==3) {
		$title="Kedvenc diákkori történetek.";
		$text =loadTextData(getDatabaseName(), $uid, "story");
			} elseif ($tab==4) {
		$title="Ezt szeretem csinálni szabadidőmben.";
		$text =loadTextData(getDatabaseName(), $uid, "spare");
	} 
	
	if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])) {
		$disabled="";
	} else {
		$disabled = "disabled";
	}
?>		
<div style="padding: 10px;">
	<div style="text-align:center"> <?PHP echo($resultDBoperation); ?> </div>
	<h3><?php  echo $title ?></h3>
	<textarea style="text-align: left;" rows="40" cols="100" <?php echo $disabled ?> >
<?php echo $text; ?>
	</textarea>
	<br/>
	<?php if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])): ?>
		<input type="checkbox" id="visibleforall">Ezt a szöveget csak az osztálytársaim láthatják. 
		<input type="button" value="<?php echo getTextRes("Save");?>" />
	<?php endif ?>
</div>
