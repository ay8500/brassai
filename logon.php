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
			echo("<form action=\"start.php\" method=\"get\">"."\r\n");
			echo('<div class="loginText">'.$TXT["LogIn"].':</div>');
			echo("<div class=\"loginText\">".$TXT["LogInUser"].":</div>");
			echo("<div><input class=\"loginInput\" type=\"text\" size=\"12\" name=\"paramName\"/></div>");
			echo("<div class=\"loginText\">".$TXT["LogInPassw"].":</div>");
			echo("<input type=\"hidden\" value=\"logon\" name=\"action\"/>");
			echo("<div><input class=\"loginInput\" type=\"password\" size=\"12\" name=\"paramPassw\"/></div><br/>");
			echo("<div><input class=\"loginSubmit\" type=\"submit\" value=\"".$TXT["LogIn"]."\" /></div><br/>");
			echo("</form>"); 
			echo("<form action=\"start.php\" method=\"get\">");
			echo("<input type=\"hidden\" value=\"lostpassw\" name=\"action\"/>");
			echo("<div><input class=\"loginSubmit\" type=\"submit\"  value=\"".$TXT["LogInLostData"]."\" /></div>");
			echo("</form>"."\r\n"); 
		}
		else {
			echo("<form action=\"index.php\" method=\"get\">"."\r\n");
			echo("<input type=\"hidden\" value=\"logoff\" name=\"action\"/>");
			echo("<input type=\"hidden\" value=\"".$_SESSION['scoolClass']."\" name=\"scoolClass\"/>");
			echo("<input type=\"hidden\" value=\"".$_SESSION['scoolYear']."\" name=\"scoolYear\"/>");
			echo("<div class=\"loginText\">".$TXT["LogInUser"].":".$_SESSION['USER']."</div>");
			echo("<div><input class=\"loginSubmit\" type=\"submit\"  value=\"".$TXT["LogOut"]."\" /></div><br/>"."\r\n");
			echo("</form>"."\r\n"); 
		}
		echo("<div class=\"loginError\">".$logOnMessage."</div><br/>");
		foreach ($SupportedLang as $Language) {
			if (isset($_SESSION['LANG']) && ($Language!=$_SESSION['LANG']))
				echo('<a href='.$SCRIPT_NAME.'?language='.$Language.'><img src="images/flag_'.$Language.'.jpg" alt=""/></a>'."\r\n");
		}

	}

?>