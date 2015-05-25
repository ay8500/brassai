<?php
	include_once 'ltools.php';
	include_once 'data.php';
	
	$tab=getIntParam("tabOpen", 0);
	if ($tab==2) {
		$title="Rövid életrajzom: továbbképzések munkahelyek";
		$type="cv";
	} elseif ($tab==3) {
		$title="Kedvenc diákkori történetek";
		$type="story";
	} elseif ($tab==4) {
		$title="Ezt szeretem csinálni szabadidőmben";
		$type="spare";
	} 
	$text =loadTextData(getAktDatabaseName(), $uid, $type);
?>		

<div style="padding: 10px;">
	<h3><?php  echo $title; ?></h3>
	<?php if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) { ?>
	<form id="stroryForm" onsubmit="saveStory(); return false;">
	<fieldset>
		<textarea id="story" style="visibility:hidden; height:400px;" >
<?php echo getFieldValue($text); ?>
		</textarea>
	</fieldset>
	<br/>
		<div class="radiogroup">
			<div style="display: inline-block; padding:5px" >Ki láthatja<br /> ezt a szöveget?</div>
			<div title="Az egész világ" class="cradio radio_world"><input type="radio" name="privacy" value="world" <?php echo getFieldCheckedWord($text)?> onclick="saveStory();" /></div>
			<div title="Az iskolatársak" class="cradio radio_scool"><input type="radio" name="privacy" value="scool" <?php echo getFieldCheckedScool($text)?> onclick="saveStory();" /></div>
			<div title="Az osztálytársak" class="cradio radio_class"><input type="radio" name="privacy" value="class" <?php echo getFieldCheckedClass($text)?> onclick="saveStory();" /></div>
		</div> 
		<div class="radiogroup">
			<div style="display: inline-block; padding:5px" >
				Utóljára módósítva:<br />
				<?php echo getTextDataDate(getAktDatabaseName(), $uid, $type)?>
			</div>
			<div style="display: inline-block; padding:5px" >
				<input type="submit" value="<?php echo getTextRes("Save");?>" />
			</div>
		</div>
	</form>
	<?php } else {
		$okText=getFieldAccessValue($text);
		if ($okText!=null)
			echo $okText;
		else { 
			$name="";
			if (userIsLoggedOn()) $name=getPersonLogedOn()["user"];
			?>
			Ez az oldal jelenleg üres.<br />
			Ha szeretnél többet megtudni a véndiákról, akkor üzenj neki. Ahoz csak kattinsd meg a mellékelt gombot.<br />
			<div style="margin:15px">
				Biztonsági kód: <input id="code" type="text" size="6" value="" default="Kód"><img style="vertical-align: middle;" alt="" src="SecurityImage/SecurityImage.php" /><br />
			</div>
			<div style="margin:15px">
				Nevem: <input id="name" type="text" value="<?php echo $name ?>" default="Név">
				<input id="more" type="button" value="Szeretnék többet olvasni róla!" onclick="sendMoreInfoRequest();" >
			</div>
			<?php } ?> 
	<?php }  ?>
	<div style="margin-top:10px; padding:5px; border-radius:4px; display: none;" id="ajaxStatus"></div>
</div>



<?php if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) : ?>
<script type="text/javascript">

	function saveStory() {
		var data = {
			id: "<?php echo $uid; ?>",
		    type:"<?php  echo $type; ?>",
		    privacy:$('input[name=privacy]:checked', '#stroryForm').val(),
		    story: $("#story").val()
		}
		$('#ajaxStatus').html('küldés...');
		$.ajax({
			url:"editDiakStorySave.php",
			type:"POST",
			dataType: 'json',
			success:function(data){
			    $('#ajaxStatus').css("background-color","lightgreen");
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
<script>
	function sendMoreInfoRequest() {
		$.ajax({
			url:"editDiakStoryMoreInfoRequest.php?title=<?php echo $title?>&tab=<?php echo $tab?>&code="+$("#code").val()+"&name="+$("#name").val(),
			success:function(data){
			    $('#ajaxStatus').css("background-color","lightgreen");
				$('#ajaxStatus').html(' Üzenet sikeresen elküldve. ');
				$('#ajaxStatus').show();
				setTimeout(function(){
				    $('#ajaxStatus').html('');
				    $('#ajaxStatus').hide();
				}, 2000);
			},
			error:function(data){
			    $('#ajaxStatus').css("background-color","lightcoral");
				$('#ajaxStatus').html(data.responseText);
				$('#ajaxStatus').show();
				setTimeout(function(){
			    	$('#ajaxStatus').html('');
			    	$('#ajaxStatus').hide();
				}, 5000);
			}
		});
	}
</script>
