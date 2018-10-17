<?PHP
	include_once 'tools/sessionManager.php';
	include_once("tools/userManager.php");
	include_once 'tools/appl.class.php';
	include_once 'tools/ltools.php';
	include_once 'sendMail.php';
	include_once 'config.class.php';

	$logOnMessage="";
	
	//Logon action
	if (isActionParam("logon")) {
		$paramName=getParam("paramName");
		$paramPassw=getParam("paramPassw");
		if ((null==$paramName) || (null==$paramPassw)) { 
			logoutUser();
			http_response_code(400);
			$logOnMessage =Config::_text("LogInError")."<br />".Config::_text("LogInUserPassw");
		} else {
			if (!checkRequesterIP(changeType::login)) {
				logoutUser();
				http_response_code(400);
				$logOnMessage = Config::_text("LogInError")."<br />".Config::_text("LogInToManyErrors");
			} else {
				if (!checkUserLogin($paramName,$paramPassw)) {
					logoutUser();
					http_response_code(400);
					$logOnMessage = Config::_text("LogInError")."<br />".Config::_text("LogInUserPasErr");
					$db->saveRequest(changeType::login);
					saveLogInInfo("Login","",$paramName,strlen($paramPassw),"false");
				} else {
					saveLogInInfo("Login",getLoggedInUserId(),$paramName,strlen($paramPassw),"true");
					$logOnMessage = "Ok";
				}
			}
		}
		if (! userIsAdmin()) {
			sendHtmlMail(null,
				"<h2>Login</h2>".
				"Parameter:".$paramName." : ".strlen($paramPassw)."<br/>".
				"Login result:".$logOnMessage," Login");
		}
		echo($logOnMessage);
	}
	//Logoff action
	if (isActionParam("logoff")) {
		logoutUser();
	}
	
	if (isActionParam("logoffok")) {
        \maierlabs\lpfw\Appl::setMessage("Kijelentkezés megtörtént, köszünjük a látogatást, tövábbi szép időtöltést kivánunk.", "success");
	}
	
	if (isActionParam("loginok")) {
        \maierlabs\lpfw\Appl::setMessage("Szeretettel üdvözlünk kedves ".getPersonName($db->getPersonByID(getLoggedInUserId())), "success");
	}
	
	//Facebook login
	if (isActionParam("facebooklogin") && isset($_SESSION['FacebookId'])) {
		if (!checkFacebookUserLogin($_SESSION['FacebookId'])) {
			$logOnMessage=Config::_text("LogInError");
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
    		<input name="paramName" type="text" class="form-control" id="loUser" placeholder="<?php echo Config::_text("LogInUser"); ?>">
		</div>
		<div class="input-group input-group" style="margin: 3px;">
    		<span class="input-group-addon" style="width:30px" title="Jelszó" ><span class="glyphicon glyphicon-lock"></span></span>
    		<input name="paramPassw" type="password" class="form-control" id="loPassw" placeholder=<?php echo Config::_text("LogInPassw"); ?>  >
		</div>
		<div style="text-align:center; margin: 3px">
			<button type="button" class="btn btn-default" style="margin: 3px;width: 167px;text-align: left;" onclick="logon();"><span class="glyphicon glyphicon-log-in"></span> <?php echo Config::_text("LogIn"); ?></button>
		 	<button type="button" class="btn btn-default" style="margin: 3px;width: 167px;text-align: left;" onclick="lostlogon();" title="Szeretnék bejelentkezési adatokat, elfelejtettem adataimat" ><span class="glyphicon glyphicon-unchecked"></span> <?php echo Config::_text("LogInLostData"); ?></button>
		</div>
	</form>
	<div style="text-align:center; margin: 3px">
		<button class="loginFacebookSubmit" onclick="fblogin();"></button>
	</div>
	<div style="margin-top:10px; padding:5px; border-radius:4px; display: none;" id="ajaxLStatus"></div>	
<?php } ?> 
</div>

<?php
\maierlabs\lpfw\Appl::addJsScript('
    window.fbAsyncInit = function() {
        FB.init({
          appId      : "1606012466308740",
          cookie     : true,
          xfbml      : true,
          version    : "v3.0"
        });
        FB.AppEvents.logPageView();   
    };
    
    (function(d, s, id){
         var js, fjs = d.getElementsByTagName(s)[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement(s); js.id = id;
         js.src = "https://connect.facebook.net/en_US/sdk.js";
         fjs.parentNode.insertBefore(js, fjs);
    }(document, "script", "facebook-jssdk"));
    
    function checkLoginState() {
          FB.getLoginStatus(function(response) {
              if(response.status=="connected") {
                FB.api("/me?fields=id,first_name,last_name,email,link,about,picture", function(response) {
                    console.log(JSON.stringify(response));
                    var url="signin.php?action=facebooklogin&FacebookId="+response.id+"&first_name="+response.first_name+"&last_name="+response.last_name+"&email="+response.email;
                    console.log(url);
                    location.href=url;
                });
              }
          });
        }
    
    function fblogin() {
        FB.login(checkLoginState, {scope: "email"});
    }
    
	function logon() {
		$.ajax({
			url:"logon.php?action=logon&paramName="+$("#loUser").val()+"&paramPassw="+$("#loPassw").val(),
			success:function(data){
				var url=location.href;
				url=location.href.replace("action","loginok");
				if (url.indexOf("?") !== -1) 
					url=location.href+"&action=loginok";
				else
					url=location.href+"?action=loginok";
				location.href=url;
			},
			error:function(data){
			    $("#ajaxLStatus").css("background-color","lightcoral");
				$("#ajaxLStatus").html(data.responseText);
				$("#ajaxLStatus").show();
				setTimeout(function(){
			    	$("#ajaxLStatus").html(data);
			    	$("#ajaxLStatus").slideUp("slow");
				}, 3000);
			}
		});
	}

	function lostlogon() {
		location.href="lostPassw.php";
	}
	
	function handleLogoff() {
		$.ajax({
			url:"logon.php?action=logoff",
			success:function(data){
				var url=location.href;
				url=location.href.replace("action","logoffok");
				if (url.indexOf("?") !== -1) 
					url=location.href+"&action=logoffok";
				else
					url=location.href+"?action=logoffok";
				location.href=url;
			}
		});
	}

	function handleLogon() {
	    closeSearch();
	    $("#uLogon").slideDown("slow");
	    $("#loUser").focus();
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
');
}