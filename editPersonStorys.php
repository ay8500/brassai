<?php
include_once Config::$lpfw.'ltools.php';
include_once 'dbBL.class.php';

\maierlabs\lpfw\Appl::addCss('editor/ui/trumbowyg.min.css');
\maierlabs\lpfw\Appl::addJs('editor/trumbowyg.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/specialchars/ui/trumbowyg.specialchars.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/specialchars/trumbowyg.specialchars.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/table/ui/trumbowyg.table.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/table/trumbowyg.table.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/colors/ui/trumbowyg.colors.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/colors/trumbowyg.colors.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/emoji/ui/trumbowyg.emoji.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/emoji/trumbowyg.emoji.js');
\maierlabs\lpfw\Appl::addJs('editor/plugins/pasteimage/trumbowyg.pasteimage.min.js');
\maierlabs\lpfw\Appl::addJs('editor/langs/hu.min.js');
\maierlabs\lpfw\Appl::addJsScript("
	$( document ).ready(function() {
		$('#story').trumbowyg({
			fullscreenable: false,
			closable: false,
			lang: 'hu',
			btns: [
                ['undo', 'redo'],
                ['formatting'],
                ['strong', 'em'],
                ['superscript', 'subscript'],
                ['link','insertImage','table','emoji'],
                ['justifyLeft', 'justifyCenter', 'justifyRight'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['specialChars'],
			    ['foreColor', 'backColor'],['viewHTML']
			],
			removeformatPasted: true,
			imageWidthModalEdit: true,
			autogrow: true
		});
	});
");
    global  $db;
    global $personid;
	$person=$db->getPersonByID($personid);
	
	$tab=getParam("tabOpen", "cv");
	if ($tab=="cv") {
		$title="Rövid életrajzom: továbbképzések munkahelyek";
		$type="cv";
		$text = $person["cv"];
	} elseif ($tab=="school") {
		$title="Kedvenc diákkori történetek";
		$type="story";
		$text = $person["story"];
	} elseif ($tab=="hobbys") {
		$title="Ezt szeretem csinálni szabadidőmben";
		$type="spare";
		$text = $person["aboutMe"];
	}
?>		

	<h3><?php  echo $title; ?></h3>
	<?php if (isUserEditor() || isUserSuperuser() || isAktUserTheLoggedInUser()) { ?>
		<form id="stroryForm" onsubmit="saveStory(); return false;">
		<fieldset onkeyup="fieldChanged();" >
			<textarea id="story" style="visibility:hidden; height:400px;" >
<?php echo htmlspecialchars_decode(getFieldValue($text)); ?>
			</textarea>
		</fieldset>
		<br/>
			<div class="radiogroup">
				<div style="display: inline-block; padding:5px" >Ki láthatja<br /> ezt a szöveget?</div>
				<div title="Az egész világ" class="cradio radio_world"><input type="radio" name="privacy" value="world" <?php echo getFieldCheckedWord($text)?> onclick="saveStory();" /></div>
				<div title="Az iskolatársak" class="cradio radio_school"><input type="radio" name="privacy" value="school" <?php echo getFieldCheckedScool($text)?> onclick="saveStory();" /></div>
				<div title="Az osztálytársak" class="cradio radio_class"><input type="radio" name="privacy" value="class" <?php echo getFieldCheckedClass($text)?> onclick="saveStory();" /></div>
			</div> 
			<div class="radiogroup">
				<div style="display: inline-block; padding:5px" >
					<input type="submit" class="btn btn-default" value="<?php \maierlabs\lpfw\Appl::_("Save");?>" />
				</div>
			</div>
		</form>
	<?php } else {
		$okText=getFieldAccessValue(htmlspecialchars_decode(htmlspecialchars_decode($text)));
		if ($okText!=null) {
			$okText = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "<a target=\"_blank\" href=\"\\0\">\\0</a>",	$okText);
			$okText = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/', '<a href="mailto:$1">$1</a>', $okText);
			echo $okText;
		} else { 
			$name="";
			if (isUserLoggedOn()) {
				$p =$db->getPersonLogedOn();
				$name=$p["user"];
			}
			?>
			<h4>Ez az oldal jelenleg üres.</h4>
			<?php if (isset($person["email"]) && $person["email"]!="") {?>
				Ha szeretnél többet megtudni a véndiákról, akkor üzenj neki. Ahoz csak kattinsd meg a mellékelt gombot.<br />
				
				<div class="input-group input-group-sl" style="margin:15px;<?php if (isUserLoggedOn()) { ?>display:none;<?php } ?>">
					<span style="min-width:130px; text-align:right" class="input-group-addon" >Biztonsági kód:</span>
					<div class="input-group-btn">
						<img style="height: 34px;border: 1px solid lightgrey;" alt="" src="SecurityImage/SecurityImage" />
					</div>
                    <input id="code" type="text" size="6" value="" placeholder="írd be az 5 karakteres biztonsági kódot" class="form-control"/>
				</div>
				
				<div class="input-group input-group-sl" style="margin:15px;<?php if (isUserLoggedOn()) { ?>display:none;<?php } ?>">
					<span style="min-width:130px; text-align:right" class="input-group-addon" >Nevem:</span>
					<input id="name" type="text" value="<?php echo $name ?>" placeholder="Név" class="form-control" />
				</div>
				
				<div style="margin:15px">
					<input class="btn btn-default" id="more" type="button" value="Szeretnék többet olvasni róla!" onclick="sendMoreInfoRequest();" >
				</div>	
			<?php } ?> 
		<?php } ?>
	<?php }  ?>

<?php
if (isUserEditor() || isUserSuperuser() || isAktUserTheLoggedInUser()) {
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
			url : 'ajax/setPersonStory',
			type : 'POST',
			dataType : 'json',
			success:function(data){
				showDbMessage(' Szöveg kimentése sikerült. <br/>Köszönjük szépen.','success');

			},
			error:function(data){
				showDbMessage(' Szöveg kimentése sikertelen. ','warning');
			},
			data:data
		});
	}
");
}
\maierlabs\lpfw\Appl::addJsScript("
	function sendMoreInfoRequest() {
		$.ajax({
			url : 'ajax/requestMoreInfo?title=".$title."&tab=".$tab."&code='+$('#code').val()+'&name='+$('#name').val(),
			success : function(data){
				showDbMessage(' Üzenet sikeresen elküldve. ','success');
			},
			error:function(data){
				showDbMessage(' Üzenet elküldés sikertelen. '+data.responseText,'warning');
			}
		});
	}
");
