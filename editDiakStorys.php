<?php
include_once 'tools/ltools.php';
include_once 'dbBL.class.php';
	
	$person=$db->getPersonByID($personid);
	
	$tab=getIntParam("tabOpen", 0);
	if ($tab==2+$tabOffset) {
		$title="Rövid életrajzom: továbbképzések munkahelyek";
		$type="cv";
		$text = $person["cv"];
	} elseif ($tab==3+$tabOffset) {
		$title="Kedvenc diákkori történetek";
		$type="story";
		$text = $person["story"];
	} elseif ($tab==4+$tabOffset) {
		$title="Ezt szeretem csinálni szabadidőmben";
		$type="spare";
		$text = $person["aboutMe"];
	}
?>		

	<h3><?php  echo $title; ?></h3>
	<?php if ( userIsAdmin() || userIsEditor() || userIsSuperuser() || isAktUserTheLoggedInUser()) { ?>
		<form id="stroryForm" onsubmit="saveStory(); return false;">
		<fieldset onkeyup="fieldChanged();" >
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
					<input type="submit" class="btn btn-default" value="<?php echo Config::_text("Save");?>" />
				</div>
			</div>
		</form>
	<?php } else {
		$okText=getFieldAccessValue($text);
		if ($okText!=null) {
			$okText = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "<a target=\"_blank\" href=\"\\0\">\\0</a>",	$okText);
			$okText = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/', '<a href="mailto:$1">$1</a>', $okText);
			echo $okText;
		} else { 
			$name="";
			if (userIsLoggedOn()) {
				$p =$db->getPersonLogedOn();
				$name=$p["user"];
			}
			?>
			<h4>Ez az oldal jelenleg üres.</h4>
			<?php if (isset($person["email"]) && $person["email"]!="") {?>
				Ha szeretnél többet megtudni a véndiákról, akkor üzenj neki. Ahoz csak kattinsd meg a mellékelt gombot.<br />
				
				<div class="input-group input-group-sl" style="margin:15px;<?php if (userIsLoggedOn()) { ?>display:none;<?php } ?>">
					<span style="min-width:110px; text-align:right" class="input-group-addon" >Biztonsági kód:</span>
					<input id="code" type="text" size="6" value="" placeholder="Kód" class="form-control"/>
					<div class="input-group-btn">
						<img style="vertical-align: middle;" alt="" src="SecurityImage/SecurityImage.php" />
					</div>
				</div>
				
				<div class="input-group input-group-sl" style="margin:15px;<?php if (userIsLoggedOn()) { ?>display:none;<?php } ?>">
					<span style="min-width:110px; text-align:right" class="input-group-addon" >Nevem:</span>
					<input id="name" type="text" value="<?php echo $name ?>" placeholder="Név" class="form-control" />
				</div>
				
				<div style="margin:15px">
					<input class="btn btn-default" id="more" type="button" value="Szeretnék többet olvasni róla!" onclick="sendMoreInfoRequest();" >
				</div>	
			<?php } ?> 
		<?php } ?>
	<?php }  ?>
	<div id="ajaxStatus" style="margin-top:10px; padding:5px; border-radius:4px;"></div>

<?php
if ( userIsAdmin() || userIsEditor() || userIsSuperuser() || isAktUserTheLoggedInUser()) {
    \maierlabs\lpfw\Appl::addJsScript("
	function saveStory() {
	    fieldSaved();
		var data = {
			id : ".$personid.",
		    type : '".$type."',
		    privacy : $('input[name=privacy]:checked', '#stroryForm').val(),
		    story : $('#story').val()
		};
		$('#ajaxStatus').html('kiment...');
		$.ajax({
			url : 'ajax/editDiakStorySave.php',
			type : 'POST',
			dataType : 'json',
			success:function(data){
				showAjaxStatus(' Kimetés sikerült. ','lightgreen');
			},
			data:data
		});
	}
");
}
\maierlabs\lpfw\Appl::addJsScript("
	function sendMoreInfoRequest() {
		$.ajax({
			url : 'ajax/editDiakStoryMoreInfoRequest.php?title=".$title."&tab=".$tab."&code='+$('#code').val()+'&name='+$('#name').val(),
			success : function(data){
				showAjaxStatus(' Üzenet sikeresen elküldve. ','lightgreen');
			},
			error:function(data){
				showAjaxStatus(data.responseText,'lightcoral');
			}
		});
	}

	function showAjaxStatus(m,color) {
	    $('#ajaxStatus').css('background-color',color);
		$('#ajaxStatus').html(m);
		$('#ajaxStatus').show();
		setTimeout(function(){
	    	$('#ajaxStatus').html('');
	    	$('#ajaxStatus').hide();
		}, 4000);
	}
");
