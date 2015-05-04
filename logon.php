<?PHP
	include_once("userManager.php");

	$logOnMessage="";
	
	//Logon action
	if (isset($_GET["action"]) && ($_GET["action"]=="logon")) {
		if (isset($_GET["paramName"])) $paramName=trim($_GET["paramName"]); else $paramName="";
		if (isset($_GET["paramPassw"])) $paramPassw=trim($_GET["paramPassw"]); else $paramPassw="";
		if (($paramName=="") || ($paramPassw=="")) { 
			$paramName=""; $paramPassw="";
			logoutUser();
			$logOnMessage=$TXT["LogInUserPassw"];
		}
		if (!checkUserLogin($paramName,$paramPassw)) {
			logoutUser();
			$logOnMessage=$TXT["LogInError"];
		}
	}
	//Logoff action
	if (isset($_GET["action"]) && ($_GET["action"]=="logoff")) {
		logoutUser();
	}
	
	//Facebook login
	if ( isset($_SESSION['FacebookId']) ) {
		if (!checkFacebookUserLogin($_SESSION['FacebookId'])) {
			//logoutUser();
			$logOnMessage=$TXT["LogInError"];
		}
	} 
	
	//Change password
	if (isset($_GET["action"]) && ($_GET["action"]=="changepassword")) {
		if (isset($_GET["paramName"])) $paramName=$_GET["paramName"]; else $paramName="";
		if (isset($_GET["paramPassw"])) $paramPassw=$_GET["paramPassw"]; else $paramPassw="";
		if (isset($_GET["paramPassw1"])) $paramPassw=$_GET["paramPassw1"]; else $paramPassw1="";
		if (isset($_GET["paramPassw2"])) $paramPassw=$_GET["paramPassw2"]; else $paramPassw2="";
		if (($paramName=="") || ($paramPassw=="")) { 
			$paramName=""; $paramPassw="";
			ogoutUser();
			$logOnMessage=$TXT["LogInUserPassw"];
		}
		if (!checkUserLogin($paramName,$paramPassw)) {
			if (($paramPassw1==$paramPassw2)) { 
				if (setUserPasswort()) {
					$logOnMessage=$TXT["LogInPasswChanged"];
				}
				else
					$logOnMessage=$TXT["LogInNewPasswNotOK"];
			}
			else
				$logOnMessage=$TXT["LogInNewPasswNotEQ"];
		}
	}

function writeLogonBox() {
		global $logOnMessage;
		global $SCRIPT_NAME;
		global $SupportedLang;
		global $TXT;
		if ( !isset($_SESSION['USER']) || $_SESSION['USER']=="") {
?>
	<form action="start.php" method="get">
		<input type="hidden" value="logon" name="action"/>
		<div class="loginText"><?php echo $TXT["LogInUser"] ?></div>
		<div><input class="loginInput" type="text" size="12" name="paramName" /></div>
		<div class="loginText"><?php echo $TXT["LogInPassw"] ?></div>
		<div><input class="loginInput" type="password" size="12" name="paramPassw" /></div><br/>
		<div><input class="loginSubmit" type="submit" value="<?php echo $TXT["LogIn"]?>" /></div><br/>
	</form> 
	<form action="start.php" method="get">
		<input type="hidden" value="lostpassw" name="action"/>
		<div><input class="loginSubmit" type="submit"  value="<?php echo $TXT["LogInLostData"]?>" /></div>
	</form>
<?php  } else { ?>
	<form action="index.php" method="get">
		<input type="hidden" value="logoff" name="action" />
		<input type="hidden" value="<?php echo $_SESSION['scoolClass'] ?>" name="scoolClass"/>
		<input type="hidden" value="<?php echo $_SESSION['scoolYear'] ?>" name="scoolYear"/>
		<div class="loginText"><?php echo $TXT["LogInUser"].":".$_SESSION['USER'] ?></div>
		<div><input class="loginSubmit" type="submit"  value="<?php echo $TXT["LogOut"] ?>" /></div><br/>
	</form>
<?php } ?>
	<div class="loginError"><?php echo $logOnMessage ?></div><br/>
<?php 
}
	
function writeLogonLine() {
	global $TXT;
	if ( !isset($_SESSION['USER']) || $_SESSION['USER']=="") {
	?>
	<tr><td class="LogonLine">
		<form action="start.php" method="get">
			<input type="hidden" value="logon" name="action"/>
			<?php echo $TXT["LogInUser"] ?><input class="loginInput" type="text" size="12" name="paramName" />
			<?php echo $TXT["LogInPassw"] ?><input class="loginInput" type="password" size="12" name="paramPassw" />
			<input class="loginSubmit" type="submit" value="<?php echo $TXT["LogIn"]?>" />
		</form><form action="start.php" method="get">
			<input type="hidden" value="lostpassw" name="action"/>
			&nbsp;&nbsp;<input class="loginSubmit" type="submit"  value="<?php echo $TXT["LogInLostData"]?>" />
		</form>
		</form><form action="http://brassai.blue-l.de/fb/fblogin.php" method="get">
			&nbsp;&nbsp;<input class="loginFacebookSubmit" type="submit"  value="" />
		</form>
	</td></tr>
		<?php  } else { ?>
	<tr><td class="LogonLine">
		<form action="start.php" method="get">
			<input type="hidden" value="logoff" name="action" />
			<input type="hidden" value="<?php echo $_SESSION['scoolClass'] ?>" name="scoolClass"/>
			<input type="hidden" value="<?php echo $_SESSION['scoolYear'] ?>" name="scoolYear"/>
			<?php echo $TXT["LogInUser"].":".$_SESSION['USER'] ?>
			&nbsp;&nbsp;<input class="loginSubmit" type="submit"  value="<?php echo $TXT["LogOut"] ?>" />
		</form> 
	</td></tr>
	<?php
	}
}
?>