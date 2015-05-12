<?php
	include_once 'ltools.php';
	include_once 'data.php';
	
	$tab=getIntParam("tabOpen", 0);
	if ($tab==2) {
		$title="Rövid életrajzom: továbbképzések munkahelyek.";
		$type="cv";
	} elseif ($tab==3) {
		$title="Kedvenc diákkori történetek.";
		$type="story";
	} elseif ($tab==4) {
		$title="Ezt szeretem csinálni szabadidőmben.";
		$type="spare";
	} 
	$text =loadTextData(getDatabaseName(), $uid, $type);
	
?>		

<?php if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])): ?>
<script type="text/javascript">
	function saveStory() {
		var data = {
			id: "<?php echo $uid; ?>",
		    type:"<?php  echo $type; ?>",
		    story: $("#story").val()
		}
		$('#ajaxStatus').html('küldés...');
		$.ajax({
			url:"editDiakStorySave.php",
			type:"POST",
			dataType: 'json',
			success:function(data){
				$('#ajaxStatus').html(' Kimetés sikerült. ');
				$('#ajaxStatus').show();
				setTimeout(function(){
				    $('#ajaxStatus').html('');
				    $('#ajaxStatus').hide();
				}, 2000);
			},
			data:data
		});
	}
	
</script>
<?php endif ?>


<div style="padding: 10px;">
	<h3><?php  echo $title; ?></h3>
	<?php if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])) { ?>
	<form onsubmit="saveStory(); return false;">
	<fieldset>
		<textarea id="story" style="text-align: left;" rows="40" cols="100"  class="widgEditor nothing" >
<?php echo $text; ?>
		</textarea>
	</fieldset>
	<br/>
		<input type="checkbox" id="visibleforall">Ezt a szöveget csak az osztálytársaim láthatják. 
		<input type="submit" onclick="saveStory();" value="<?php echo getTextRes("Save");?>" />
		&nbsp; <span style="padding:3px; background-color: lightgreen; border-radius:4px; display: none;" id="ajaxStatus"></span>
	</form>
	<?php } else { ?>
		<?php echo $text; ?>
	<?php } ?>
</div>
