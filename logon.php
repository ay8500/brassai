<?PHP
	include_once("userManager.php");
	include_once 'sendMail.php';

	$logOnMessage="";
	
	//Logon action
	if (isset($_GET["action"]) && ($_GET["action"]=="logon")) {
		if (isset($_GET["paramName"])) $paramName=trim($_GET["paramName"]); else $paramName="";
		if (isset($_GET["paramPassw"])) $paramPassw=trim($_GET["paramPassw"]); else $paramPassw="";
		if (($paramName=="") || ($paramPassw=="")) { 
			$paramName=""; $paramPassw="";
			logoutUser();
			$logOnMessage=getTextRes("LogInUserPassw");
		}
		if (!checkUserLogin($paramName,$paramPassw)) {
			logoutUser();
			$logOnMessage=getTextRes("LogInError");
		}
		sendTheMail('code@blue-l.de',
			"<h2>Login</h2>".
			"Datenbank:".getDatabaseName()."<br/>".
			"Name:".$paramName."<br/>",
			"Login result:".$logOnMessage," Login");
	}
	//Logoff action
	if (isset($_GET["action"]) && ($_GET["action"]=="logoff")) {
		logoutUser();
	}
	
	//Facebook login
	if ( isset($_SESSION['FacebookId']) ) {
		if (!checkFacebookUserLogin($_SESSION['FacebookId'])) {
			//logoutUser();
			$logOnMessage=getTextRes("LogInError");
		}
		sendTheMail('code@blue-l.de',
			"<h2>Facebooklogin</h2>".
			"Datenbank:".getDatabaseName()."<br/>".
			"FacebookId:".$_SESSION['FacebookId']."<br/>",
			"FacebookName:".$_SESSION['FacebookName']."<br/>".
			"Login result:".$logOnMessage," Login");
	} 
	
	//Change password
	if (isset($_GET["action"]) && ($_GET["action"]=="changepassword")) {
		if (isset($_GET["paramName"])) $paramName=$_GET["paramName"]; else $paramName="";
		if (isset($_GET["paramPassw"])) $paramPassw=$_GET["paramPassw"]; else $paramPassw="";
		if (isset($_GET["paramPassw1"])) $paramPassw=$_GET["paramPassw1"]; else $paramPassw1="";
		if (isset($_GET["paramPassw2"])) $paramPassw=$_GET["paramPassw2"]; else $paramPassw2="";
		if (($paramName=="") || ($paramPassw=="")) { 
			$paramName=""; $paramPassw="";
			logoutUser();
			$logOnMessage=getTextRes("LogInUserPassw");
		}
		if (!checkUserLogin($paramName,$paramPassw)) {
			if (($paramPassw1==$paramPassw2)) { 
				if (setUserPasswort()) {
					$logOnMessage=getTextRes("LogInPasswChanged");
					}
				else
					$logOnMessage=getTextRes("LogInNewPasswNotOK");
			}
			else
				$logOnMessage=getTextRes("LogInNewPasswNotEQ");
		}
		sendTheMail('code@blue-l.de',
			"<h2>Change Password</h2>".
			"Datenbank:".getDatabaseName()."<br/>".
			"Name:".$paramName."<br/>",
			"Login result:".$logOnMessage," Change Password");
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
		<input type="hidden" value="<?php echo $_SESSION['scoolClass'] ?>" name="scoolClass"/>
		<input type="hidden" value="<?php echo $_SESSION['scoolYear'] ?>" name="scoolYear"/>
		<div class="loginText"><?php echo getTextRes("LogInUser").":".$_SESSION['USER'] ?></div>
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
			<input type="hidden" value="<?php echo $_SESSION['scoolClass'] ?>" name="scoolClass"/>
			<input type="hidden" value="<?php echo $_SESSION['scoolYear'] ?>" name="scoolYear"/>
			<?php echo getTextRes("LogInUser").":".$_SESSION['USER'] ?>
			&nbsp;&nbsp;<input class="loginSubmit" type="submit"  value="<?php echo getTextRes("LogOut") ?>" />
		</form> 
	</td></tr>
	<?php
	}
}
?>