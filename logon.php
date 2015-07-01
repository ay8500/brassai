<?PHP
	include_once 'sessionManager.php';
	include_once("userManager.php");
	include_once 'sendMail.php';
	include_once 'config.php';
	include_once 'ltools.php';

	$logOnMessage="";
	
	//Logon action
	if (getParam("action","")=="logon") {
		$paramName=getParam("paramName");
		$paramPassw=getParam("paramPassw");
		if ((null==$paramName) || (null==$paramPassw)) { 
			$paramName=""; $paramPassw="";
			logoutUser();
			http_response_code(400);
			$logOnMessage =getTextRes("LogInError")."<br />".getTextRes("LogInUserPassw");
			saveLogInInfo("Login","","","","false");
			echo($logOnMessage);
		}
		else if (!checkUserLogin($paramName,$paramPassw)) {
			logoutUser();
			http_response_code(401);
			$logOnMessage = getTextRes("LogInError")."<br />".getTextRes("LogInUserPasErr");
			saveLogInInfo("Login","",$paramName,$paramPassw,"false");
			echo($logOnMessage);
		} else {
			saveLogInInfo("Login",getLoggedInUserId(),"","","true");
			$logOnMessage = "Ok";
			echo($logOnMessage);
		}
		if (! userIsAdmin()) {
			sendHtmlMail(null,
				"<h2>Login</h2>".
				"Parameter:".$paramName." : ".$paramPassw."<br/>".
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
			saveLogInInfo("Facebook",getLoggedInUserId(),$_SESSION['FacebookId'],"","true");
			sendHtmlMail(null,
				"<h2>Facebooklogin</h2>".
				"Datenbank:".getUserDatabaseName()."<br/>".
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
    		<input type="text" class="form-control" id="loUser" placeholder="<?php echo getTextRes("LogInUser"); ?>">
		</div>
		<div class="input-group input-group" style="margin: 3px;">
    		<span class="input-group-addon" style="width:30px" title="Jelszó" ><span class="glyphicon glyphicon-lock"></span></span>
    		<input type="password" class="form-control" id="loPassw" placeholder=<?php echo getTextRes("LogInPassw"); ?>  >
		</div>
		<div style="text-align:center; margin: 3px">
			<button type="button" class="btn btn-default" style="margin: 3px;" onclick="logon();"><span class="glyphicon glyphicon-log-in"></span> <?php echo getTextRes("LogIn"); ?></button>
		 	<button type="button" class="btn btn-default" style="margin: 3px;" onclick="lostlogon();" title="Szeretnék bejelentkezési adatokat, elfelejtettem adataimat" ><span class="glyphicon glyphicon-unchecked"></span> <?php echo getTextRes("LogInLostData"); ?></button>
		</div>
	</form>
	<form action="http://brassai.blue-l.de/fb/fblogin.php" method="get">
		<div style="text-align:center; margin: 3px">
		<input class="loginFacebookSubmit" style="text-align:center; margin: auto;" type="submit"  value="" />
		</div>
	</form>
	<div style="margin-top:10px; padding:5px; border-radius:4px; display: none;" id="ajaxLStatus"></div>	
<?php } ?> 
</div>
<script type="text/javascript">
	document.onkeydown=function(){
	    if(window.event.keyCode=='13'){
	        logon();
	    }
	};
		
	function logon() {
		$.ajax({
			url:"logon.php?action=logon&paramName="+$("#loUser").val()+"&paramPassw="+$("#loPassw").val(),
			success:function(data){
			    //location.reload();
			    location.href="start.php";
			},
			error:function(data){
			    $('#ajaxLStatus').css("background-color","lightcoral");
				$('#ajaxLStatus').html(data.responseText);
				$('#ajaxLStatus').show();
				setTimeout(function(){
			    	$('#ajaxLStatus').html('');
			    	$('#ajaxLStatus').hide('slow');
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