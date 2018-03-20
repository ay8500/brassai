<?PHP
	include_once 'tools/sessionManager.php';
	include_once("tools/userManager.php");
	include_once 'tools/ltools.php';
	include_once 'sendMail.php';
	include_once 'config.php';

	$logOnMessage="";
	
	//Logon action
	if (isActionParam("logon")) {
		$paramName=getParam("paramName");
		$paramPassw=getParam("paramPassw");
		if ((null==$paramName) || (null==$paramPassw)) { 
			logoutUser();
			http_response_code(400);
			$logOnMessage =getTextRes("LogInError")."<br />".getTextRes("LogInUserPassw");
		} else {
			if (!checkRequesterIP(changeType::login)) {
				logoutUser();
				http_response_code(400);
				$logOnMessage = getTextRes("LogInError")."<br />".getTextRes("LogInToManyErrors");
			} else {
				if (!checkUserLogin($paramName,$paramPassw)) {
					logoutUser();
					http_response_code(400);
					$logOnMessage = getTextRes("LogInError")."<br />".getTextRes("LogInUserPasErr");
					$db->saveRequest(changeType::login);
					saveLogInInfo("Login","",$paramName,$paramPassw,"false");
				} else {
					$db->savePersonField(getAktUserId(),'userLastLogin', date("Y-m-d H:i:s"));
					saveLogInInfo("Login",getLoggedInUserId(),"","","true");
					$logOnMessage = "Ok";
				}
			}
		}
		if (! userIsAdmin()) {
			sendHtmlMail(null,
				"<h2>Login</h2>".
				"Parameter:".$paramName." : ".$paramPassw."<br/>".
				"Login result:".$logOnMessage," Login");
		}
		echo($logOnMessage);
	}
	//Logoff action
	if (isActionParam("logoff")) {
		logoutUser();
	}
	
	//Facebook login
	if (isActionParam("facebooklogin") && isset($_SESSION['FacebookId'])) {
		if (!checkFacebookUserLogin($_SESSION['FacebookId'])) {
			//logoutUser();
			$logOnMessage=getTextRes("LogInError");
		}
		if (! userIsAdmin()) {
			saveLogInInfo("Facebook",getLoggedInUserId(),$_SESSION['FacebookId'],"","true");
			sendHtmlMail(null,
				"<h2>Facebooklogin</h2>".
				"FacebookId:".$_SESSION['FacebookId']."<br/>".
				"FacebookName:".$_SESSION['FacebookName']."<br/>".
				"Login result:".$logOnMessage,"Login");
		}
	} 
	


function writeLogonDiv() {
	if (!userIsLoggedOn()) {
		?>
<div class="panel panel-default" style="display:none;margin:auto;width:220px;" id="uLogon" >
	<div class="panel-heading" >
		<b>Bejelentkezés</b><span class="glyphicon glyphicon-remove-circle" style="float: right;cursor: pointer;" onclick="closeLogin();"></span>
	</div>
	<form action="" method="get">
		<input type="hidden" value="logon" name="action"/>
		<div class="input-group input-group" style="margin: 3px;">
    		<span class="input-group-addon" style="width:30px" title="Felhasználó név vagy e-mail cím"><span class="glyphicon glyphicon-user"></span></span>
    		<input name="paramName" type="text" class="form-control" id="loUser" placeholder="<?php echo getTextRes("LogInUser"); ?>">
		</div>
		<div class="input-group input-group" style="margin: 3px;">
    		<span class="input-group-addon" style="width:30px" title="Jelszó" ><span class="glyphicon glyphicon-lock"></span></span>
    		<input name="paramPassw" type="password" class="form-control" id="loPassw" placeholder=<?php echo getTextRes("LogInPassw"); ?>  >
		</div>
		<div style="text-align:center; margin: 3px">
			<button type="button" class="btn btn-default" style="margin: 3px;width: 167px;text-align: left;" onclick="logon();"><span class="glyphicon glyphicon-log-in"></span> <?php echo getTextRes("LogIn"); ?></button>
		 	<button type="button" class="btn btn-default" style="margin: 3px;width: 167px;text-align: left;" onclick="lostlogon();" title="Szeretnék bejelentkezési adatokat, elfelejtettem adataimat" ><span class="glyphicon glyphicon-unchecked"></span> <?php echo getTextRes("LogInLostData"); ?></button>
		</div>
	</form>
	<!-- 
	<form action="http://brassai.blue-l.de/fb/fblogin.php" method="get">
		<div style="text-align:center; margin: 3px">
		<input class="loginFacebookSubmit" style="text-align:center; margin: auto;" type="submit"  value="" />
		</div>
	</form>
	 -->
	<div style="margin-top:10px; padding:5px; border-radius:4px; display: none;" id="ajaxLStatus"></div>	
<?php } ?> 
</div>
<script type="text/javascript">
	function logon() {
		$.ajax({
			url:"logon.php?action=logon&paramName="+$("#loUser").val()+"&paramPassw="+$("#loPassw").val(),
			success:function(data){
				url=location.href.replace("action","location");
				location.href=url;
			},
			error:function(data){
			    $('#ajaxLStatus').css("background-color","lightcoral");
				$('#ajaxLStatus').html(data.responseText);
				$('#ajaxLStatus').show();
				setTimeout(function(){
			    	$('#ajaxLStatus').html(data);
			    	$('#ajaxLStatus').slideUp('slow');
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
				url=location.href.replace("action","location");
				location.href=url;
			}
		});
	}

	function handleLogon() {
	    closeSearch();
	    $("#uLogon").slideDown("slow");
	    onResize(220);
	    $(":input").keyup(function (e) {
			if (e.which == 13) {
				logon();
			}
		});
	}

	function closeLogin() {
		$("#uLogon").slideUp("slow");
		onResize(0);
	}
</script>
<?php } ?>