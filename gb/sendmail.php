<?PHP
include_once("config.php");
$action=""; if (isset($_POST["action"])) $action=$_POST["action"];
if ($action == 'i')
{
	$paramId="";	if (isset($_POST["id"]))    $paramId=$_POST["id"];
	$paramName="";	if (isset($_POST["dname"])) $paramName=$_POST["dname"];
	$paramCity="";	if (isset($_POST["dcity"])) $paramCity=$_POST["dcity"];
	$paramMail="";	if (isset($_POST["dmail"])) $paramMail=$_POST["dmail"];
	$paramWWW=""; 	if (isset($_POST["dwww"]))  $paramWWW=$_POST["dwww"]; else $paramWWW="http:// ";
	$paramText="";	if (isset($_POST["dtext"])) $paramText=$_POST["dtext"];
	$paramCode="";	if (isset($_POST["dcode"])) $paramCode=$_POST["dcode"];

	sendTheMail($paramId,$paramName,$paramCity,$paramMail,$paramWWW,$paramText);
}

	
function sendTheMail($paramId,$paramName,$paramCity,$paramMail,$paramWWW,$paramText) {
	global $CFG;
	/* Empfänger */
	$empfaenger = $CFG["EMailSendTo"];

	/* Empfänger CC */
	$empfaengerCC = $CFG["EMailCC"];

	/* Empfänger BCC */
	$empfaengerBCC = $CFG["EMailBCC"];

	/* Absender */
	$absender = $CFG["EMailFrom"];

	/* Rueckantwort */
	$reply = '';

	/* Betreff */
	$subject = $CFG["EMailSubject"];

	/* Nachricht */
	$message = '<html>
	    <head>
	        <title>'.$CFG["EMailSubject"].'</title>
	    </head>
	    <body>
			<p><b>'.$CFG["EMailSubject"].'</b></p>
	        <table width="214" border="1" cellspacing="0" cellpadding="0">
	            <tr>
	                <td>&nbsp;ID</td>
	                <td>&nbsp;'.$paramId.'</td>
	            </tr>
	            <tr>
	                <td>&nbsp;Name</td>
	                <td>&nbsp;'.$paramName.'</td>
	            </tr>
	            <tr>
	                <td>&nbsp;Ort</td>
	                <td>&nbsp;'.$paramCity.'</td>
	            </tr>
	            <tr>
	                <td>&nbsp;Mail</td>
	                <td>&nbsp;'.$paramMail.'</td>
	            </tr>
	            <tr>
	                <td>&nbsp;WWW</td>
	                <td>&nbsp;'.$paramWWW.'</td>
	            </tr>
	            <tr>
	                <td>&nbsp;Text</td>
	                <td>&nbsp;'.$paramText.'</td>
	            </tr>
	        </table>
			<p>
				<a href="http://'.getenv("SCRIPT_NAME").'">http://'.getenv("SCRIPT_NAME").'</a>
			</p>
	    </body>
	</html>
	';



	/* Baut Header der Mail zusammen */
	$headers = 'From:' . $absender . "\n";
	$headers .= 'Reply-To:' . $reply . "\n"; 
	$headers .= 'X-Mailer: PHP/' . phpversion() . "\n"; 
	$headers .= 'X-Sender-IP: ' .$_SERVER["REMOTE_ADDR"]. "\n"; 
	$headers .= "Content-type: text/html\n";

	// Extrahiere Emailadressen
	$empfaengerString = implode(',', $empfaenger);
	$empfaengerCCString = implode(',', $empfaengerCC);
	$empfaengerBCCString = implode(',', $empfaengerBCC);

	$headers .= 'Cc: ' . $empfaengerCCString . "\n";
	$headers .= 'Bcc: ' . $empfaengerBCCString . "\n";

	/* Verschicken der Mail */
	if ($_SERVER["SERVER_NAME"]!="localhost") {
		mail($empfaengerString, $subject, $message, $headers);
	}
}
?>
 
