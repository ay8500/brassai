<?php

use \maierlabs\lpfw\Appl as Appl;

Appl::addCss('editor/ui/trumbowyg.min.css');
Appl::addJs('editor/trumbowyg.min.js');
Appl::addJs('editor/langs/hu.min.js');
Appl::addJsScript("
$( document ).ready(function() {
	$('#story').trumbowyg({
		fullscreenable: false,
		closable: false,
		lang: 'hu',
		btns: ['formatting','btnGrp-design','|', 'link', 'insertImage','btnGrp-lists'],
		removeformatPasted: true,
		autogrow: true
	});
});
");

Appl::addJs('js/chat.js',true);
Appl::addCssStyle('
	.btn-c {width:240px}
	.btn-t {margin-bottom: 5px; padding: 6px 5px 6px 5px;}
');
?>
<?php 
function showChatEnterfields($personList) {
    /**
     * @var dbDaUser
     */
    global $userDB;
	$personWithEmail=0;
	foreach ($personList as $d) {
		if(isset($d["email"]) && strlen($d["email"])>6)
			$personWithEmail++;
	}
	if ( userIsAdmin() || (false && $personWithEmail>0 && getAktClassId()==$userDB->dbDAO->getLoggedInUserClassId() )) {
		?>
		<form action="chat.php" method="post" id="chatform">
			<button id="message-btn" class="btn-c btn btn-default" type="button" onclick="showMessage();"><span class="glyphicon glyphicon-envelope"></span> Körlevelet küldök az osztálynak</button>
			<div id="message-fields"  style="display: none;">
				<div style="display: inline-block;font-size: 125%;">Körlevél e-mail <?php echo $personWithEmail?> osztálytársnak.</div>
				<button style="float: right;" class="btn btn-default" type="button" onclick="hideMessage();"><span class="glyphicon glyphicon-remove-sign"></span> bezár</button>
				<textarea id="story" name="Text" onchange="textChanged();" >Kedves osztálytársak, barátok,<br/><br/><br/><br/>Üdvözlettel <?php getLoggedInUserName($userDB)?></textarea>
				<button type="button" class="btn btn-default btn-t" onclick="setText(1);"><span class="glyphicon glyphicon-star-empty"></span> rövid</button>
				<button type="button" class="btn btn-default btn-t" onclick="setText(2);"><span class="glyphicon glyphicon-star"></span> bővebb</button>
				<button type="button" class="btn btn-default btn-t" onclick="setText(3);"><span class="glyphicon glyphicon-heart"></span> szíből</button>
				<button type="button" class="btn btn-default btn-t" onclick="setText(0);"><span class="glyphicon glyphicon-remove-circle"></span> töröl</button>
				<div id="login-fields" style="display: none;">
					<?php if (!userIsLoggedOn()) {?>
					Jelentkezz be! Körlevelet csak akkor tudsz küldeni az osztálytársaidnak ha be vagy jelentkezve.<br/>
					<input type="hidden" value="logon" name="action"/>
					<div style="display: inline-block;">
						<div class="input-group input-group" style="margin: 3px;">
			    			<span class="input-group-addon" style="width:30px;" title="Felhasználó név vagy e-mail cím"><span class="glyphicon glyphicon-user"></span></span>
			    			<input name="paramName" type="text" class="form-control" id="loginUser" placeholder="<?php Appl::_("User name"); ?>" style="display: inline-block;" />
					</div></div>
					<div style="display: inline-block;">
						<div class="input-group input-group" style="margin: 3px;">
			    			<span class="input-group-addon" style="width:30px" title="Jelszó" ><span class="glyphicon glyphicon-lock"></span></span>
			    			<input name="paramPassw" type="password" class="form-control" id="loginPassw" placeholder=<?php Appl::_("Password"); ?>  >
					</div></div>
					<?php }?>
				</div>
				<div style="display: inline-block;">
				<button class="btn btn-warning btn-t" name="action" value="sendMessage" onclick="sendMessage();"><span class="glyphicon glyphicon-send"></span> küldés</button>
				</div>
				<br/>
			</div>
		</form>
	<?php } 
}

?>
