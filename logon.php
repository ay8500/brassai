<?PHP
	include_once 'sessionManager.php';
	include_once("userManager.php");
	include_once 'sendMail.php';
	include_once 'config.php';
	include_once 'ltools.php';

	$logOnMessage="";

	
	//Logon action
	//TODO
	if (getParam("action","")=="logon") {
		$paramName=getParam("paramName","");
		$paramPassw=getParam("paramPassw","");
		if (($paramName=="") || ($paramPassw=="")) { 
			$paramName=""; $paramPassw="";
			logoutUser();
			http_response_code(400);
			$logOnMessage =getTextRes("LogInError")."<br />".getTextRes("LogInUserPassw");
			echo($logOnMessage);
		}
		else if (!checkUserLogin($paramName,$paramPassw)) {
			logoutUser();
			http_response_code(401);
			$logOnMessage = getTextRes("LogInError")."<br />".getTextRes("LogInUserPasErr");
			echo($logOnMessage);
		} else {
			$logOnMessage = "Ok";
			echo($logOnMessage);
		}
		if (! userIsAdmin()) {
			sendTheMail('code@blue-l.de',
				"<h2>Login</h2>".
				"Datenbank:".getUserDatabaseName()."<br/>".
				"Name:".$paramName."<br/>",
				"Login result:".$logOnMessage," Login");
		}
	}
	//Logoff action
	if (getParam("action","")=="logoff") {
		logoutUser();
	}
	
	//Facebook login
	if (getParam("action","")=="facebooklogin" && isset($_SESSION['FacebookId'])) {
		if (!checkFacebookUserLogin($_SESSION['FacebookId'])) {
			//logoutUser();
			$logOnMessage=getTextRes("LogInError");
		}
		if (! userIsAdmin()) {
			sendTheMail('code@blue-l.de',
				"<h2>Facebooklogin</h2>".
				"Datenbank:".getUserDatabaseName()."<br/>".
				"FacebookId:".$_SESSION['FacebookId']."<br/>",
				"FacebookName:".$_SESSION['FacebookName']."<br/>".
				"Login result:".$logOnMessage,"Login");
		}
	} 
	

function writeLogonBox() {
		global $logOnMessage;
		global $SCRIPT_NAME;
		global $SupportedLang;
		global $TXT;
		if ( !userIsLoggedOn()) {
?>
	<form action="start.php" method="get">
		<input type="hidden" value="logon" name="action"/>
		<div class="loginText"><?php echo getTextRes("LogInUser") ?></div>
		<div><input class="loginInput" type="text" size="12" name="paramName" /></div>
		<div class="loginText"><?php echo getTextRes("LogInPassw") ?></div>
		<div><input class="loginInput" type="password" size="12" name="paramPassw" /></div><br/>
		<div><input class="loginSubmit" type="submit" value="<?php echo getTextRes("LogIn") ?>" /></div><br/>
	</form> 
	<form action="start.php" method="get">
		<input type="hidden" value="lostpassw" name="action"/>
		<div><input class="loginSubmit" type="submit"  value="<?php echo getTextRes("LogInLostData")?>" /></div>
	</form>
	<form action="http://brassai.blue-l.de/fb/fblogin.php" method="get">
		<input class="loginFacebookSubmitH" type="submit"  value="" />
	</form>
	<?php  } else { ?>
	<form action="index.php" method="get">
		<input type="hidden" value="logoff" name="action" />
		<div class="loginText"><?php echo getTextRes("LogInUser").":".$_SESSION['uName'] ?></div>
		<div><input class="loginSubmit" type="submit"  value="<?php echo getTextRes("LogOut") ?>" /></div><br/>
	</form>
<?php } ?>
	<div class="loginError"><?php echo $logOnMessage ?></div><br/>
<?php 
}
	
function writeLogonLine() {
	global $TXT;
	if (!userIsLoggedOn()) {
	?>
	<tr><td class="LogonLine">
		<form action="start.php" method="get">
			<input type="hidden" value="logon" name="action"/>
			<?php echo getTextRes("LogInUser"); ?><input class="loginInput" type="text" size="12" name="paramName" />
			<?php echo getTextRes("LogInPassw"); ?><input class="loginInput" type="password" size="12" name="paramPassw" />
			<input class="loginSubmit" type="submit" value="<?php echo getTextRes("LogIn"); ?>" />
		</form>
		<form action="start.php" method="get">
			<input type="hidden" value="lostpassw" name="action"/>
			&nbsp;&nbsp;<input class="loginSubmit" type="submit"  value="<?php echo getTextRes("LogInLostData") ?>" />
		</form>
		<form action="http://brassai.blue-l.de/fb/fblogin.php" method="get">
			&nbsp;&nbsp;<input class="loginFacebookSubmit" type="submit"  value="" />
		</form>
	</td></tr>
	<?php  } else { ?>
	<tr><td class="LogonLine">
		<form action="start.php" method="get">
			<input type="hidden" value="logoff" name="action" />
			<?php echo getTextRes("LogInUser").":".$_SESSION['uName'] ?>
			&nbsp;&nbsp;<input class="loginSubmit" type="submit"  value="<?php echo getTextRes("LogOut") ?>" />
		</form> 
	</td></tr>
	<?php
	}
}

function writeLogonDiv() {
	if (!userIsLoggedOn()) {
		?>
<div class="panel panel-default" style="display:none;margin:auto;width:220px;" id="uLogon" >
	<div class="panel-heading" >
		Bejelentkezés<span class="glyphicon glyphicon-remove-circle" style="float: right;cursor: pointer;" onclick="closeLogin();"></span>
	</div>
	<form action="" method="get">
		<input type="hidden" value="logon" name="action"/>
		<div class="input-group input-group" style="margin: 3px;">
    		<span class="input-group-addon" style="width:30px"><span class="glyphicon glyphicon-user"></span></span>
    		<input type="text" class="form-control" id="loUser" placeholder="<?php echo getTextRes("LogInUser"); ?>">
		</div>
		<div class="input-group input-group" style="margin: 3px;">
    		<span class="input-group-addon" style="width:30px"><span class="glyphicon glyphicon-lock"></span></span>
    		<input type="password" class="form-control" id="loPassw" placeholder=<?php echo getTextRes("LogInPassw"); ?> onkeypress="keypressed();" >
		</div>
		 <button type="button" class="btn btn-default" style="margin: 3px;" onclick="logon();"><?php echo getTextRes("LogIn"); ?></button>
		 <button type="button" class="btn btn-default" style="margin: 3px;" onclick="lostlogon();"><?php echo getTextRes("LogInLostData"); ?></button>
	</form>
	<form action="http://brassai.blue-l.de/fb/fblogin.php" method="get">
		<div style="text-align:center; margin: 3px">
		<input class="loginFacebookSubmit" style="text-align:center; margin: auto;" type="submit"  value="" />
		</div>
	</form>
	<div style="margin-top:10px; padding:5px; border-radius:4px; display: none;" id="ajaxStatus"></div>	
<?php } ?> 
</div>
<script type="text/javascript">
	function keypressed() {
		$('#loPassw').keyup(function(e){
	    	if(e.keyCode == 13)
	    	{
	        	logon();
	    	}
		});
	}
		
	function logon() {
		$.ajax({
			url:"logon.php?action=logon&paramName="+$("#loUser").val()+"&paramPassw="+$("#loPassw").val(),
			success:function(data){
			    //location.reload();
			    location.href="start.php";
			},
			error:function(data){
			    $('#ajaxStatus').css("background-color","lightcoral");
				$('#ajaxStatus').html(data.responseText);
				$('#ajaxStatus').show();
				setTimeout(function(){
			    	$('#ajaxStatus').html('');
			    	$('#ajaxStatus').hide('slow');
				}, 3000);
			}
		});
			
	}

	function lostlogon() {
		location.href="start.php?action=lostpassw";
	}
	
	function handleLogoff() {
		$.ajax({
			url:"logon.php?action=logoff",
			success:function(data){
				if (location.href.search("editdiak.php")>0)
					location.href="index.php";
				else
			    	location.reload();
			}
		});
	}

	function handleLogon() {
	    $("#uLogon").show("slow");
	}

	function closeLogin() {
		$("#uLogon").hide("slow");
	}
		
</script>
<?php } ?>