<style>
.btn-c {width:240px}
.btn-t {margin-bottom: 5px; padding: 6px 5px 6px 5px;}
</style>

<?php
$loadTextareaEditor=true;

function showChatEnterfields($personList) {
	$personWithEmail=0;
	foreach ($personList as $d) {
		if($d["email"]!="")
			$personWithEmail++;
	}
	if ( userIsAdmin() || (false && $personWithEmail>=0 && getAktClassId()==getLoggedInUserClassId() )) {
		?>
		<form action="chat.php" method="post" id="chatform">
			<button id="message-btn" class="btn-c btn btn-default" type="button" onclick="showMessage();"><span class="glyphicon glyphicon-envelope"></span> Körlevelet küldök az osztálynak</button>
			<div id="message-fields"  style="display: none;">
				<div style="display: inline-block;font-size: 125%;">Körlevél e-mail <?php echo $personWithEmail?> osztálytársnak.</div>
				<button style="float: right;" class="btn btn-default" type="button" onclick="hideMessage();"><span class="glyphicon glyphicon-remove-sign"></span> bezár</button>
				<textarea id="story" name="Text" onchange="textChanged();" >Kedves osztálytársak, barátok,<br/><br/><br/><br/>Üdvözlettel <?php getLoggedInUserName()?></textarea>
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
			    			<input name="paramName" type="text" class="form-control" id="loginUser" placeholder="<?php echo getTextRes("LogInUser"); ?>" style="display: inline-block;" />
					</div></div>
					<div style="display: inline-block;">
						<div class="input-group input-group" style="margin: 3px;">
			    			<span class="input-group-addon" style="width:30px" title="Jelszó" ><span class="glyphicon glyphicon-lock"></span></span>
			    			<input name="paramPassw" type="password" class="form-control" id="loginPassw" placeholder=<?php echo getTextRes("LogInPassw"); ?>  >
					</div></div>
					<?php }?>
				</div>
				<div style="display: inline-block;">
				<button type="button" class="btn btn-warning btn-t" name="sendAction" value="sendMessage" onclick="sendMessage();"><span class="glyphicon glyphicon-send"></span> küldés</button>
				</div>
				<br/>
			</div>
		</form>
	<?php } 
}

?>
<script type="text/javascript">
	var loggedInUser=<?php echo getLoggedInUserId()?>;
	var loginShowed=false;

	var text0="<br/>";
	var text1="Kedves osztálytársak, barátok,<br/><br/>....<br/><br/>";
	    text1+="<br>Mindenkit üdvözlök <?php echo userIsLoggedOn()?getLoggedInUserName():''?>";
	var text2="Kedves osztálytársak, barátok,<br/><br>szeretettel köszöntelek a brassaista véndiákok honlapján keresztül.";
	    text2+="<br/><br/>...<br/><br/>Remélem jól vagytok és várjátok újra találkozzunk.<br/><br/>";
	    text2+="Barátsággal <?php echo userIsLoggedOn()?getLoggedInUserName():''?>";
	var text3="Kedves osztálytársak, barátok,<br/><br>sok szeretettel köszöntelek a brassaista véndiákok honlapján keresztül.";
	    text3+="<br/><br/>...<br/><br/>Remélem boldogak, egészségesek vagytok és nagyon várjátok újra találkozzunk.<br/><br/>";
	    text3+="Mindenkit ölelek és puszilok <?php echo userIsLoggedOn()?getLoggedInUserName():''?>";

	
	function sendMessage() {
		if (loggedInUser<0 && loginShowed==false) {
		    $("#login-fields").slideDown("slow");
		    loginShowed=true;
		} else {
			if (confirm("Szeretnéd a beadott üzenetet az osztálytársaidnak elküldeni?")) {
				$("#chatform").submit();
			}
		} 
	}

	
	function setText(type) {
		if( $("#story").trumbowyg('html')==text0 ||
			$("#story").trumbowyg('html')==text1 ||
			$("#story").trumbowyg('html')==text2 ||
			$("#story").trumbowyg('html')==text3 || 
			confirm("A körlevél szövege már módosítva van. Szeretnéd ennek ellenére tartalmát kicserélni?") ){
				if (type==0) {
				    $("#story").trumbowyg('html', text0);
				}else if (type==1) {
				    $("#story").trumbowyg('html', text1);
				}else if (type==2) {
					    $("#story").trumbowyg('html', text2);
				}else if (type==3) {
			    	$("#story").trumbowyg('html', text3);
			}
		}
	}
	
	function showMessage() {
		$("#new-btn").hide();
		$("#message-btn").hide();
		$("#story").trumbowyg('html', text2);
    	$("#message-fields").slideDown("slow");
	}

	function hideMessage() {
		$("#new-btn").show();
		$("#message-btn").show();
		$("#message-fields").slideUp("slow");
	}
</script>
