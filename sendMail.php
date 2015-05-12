<?PHP
include_once("data.php");

//Mail send operation message
$sendMailMsg = "";

//Count of sent mails
$sendMailCount = 0;

//Automatic send option
$action=""; if (isset($_POST["action"])) $action=$_POST["action"];
if ($action == 'sendMail')
{

}

/**
 * Send new password
 * todo: use mail template instea of had coded text
 */
function SendNewPassword($uid) {
	global $sendMailMsg;
	$diak = getPerson($uid);
	$text='<p style="font-weight: bold;">Kedeves '.$diak["lastname"]." ".$diak["firstname"].'</p>';
	$text.="Ezt az e-mail azért kapod mert kérésedre megvátoztak a bejelentkezési adataid.<br />";
	$text.="<p>";
	$text.="Végzős osztály:".$_SESSION['scoolYear'].'-'.$_SESSION['scoolClass']."<br/>";
	$text.="Becenév:".$diak["user"]."<br/>";
	$text.="Jelszó:".$diak["passw"]."<br/>";
	$text.="</p><p>";
	$text.='<a href=http://brassai.blue-l.de/index.php?scollYear='.$_SESSION['scoolYear'].'&scoolClass='.$_SESSION['scoolClass'].'>A vézös diákok honlapja</a>';
	$text.="</p>";
	$text.="<p>Üdvözlettel az adminsztátor.";
	sendTheMail(getFieldValue($diak["email"]),$text);
}

/**
 * send mail to new user
 */
function sendNewUserMail($firstname,$lastname,$mail,$passw,$rights) {
	$text='<p style="font-weight: bold;">Kedeves '.$lastname." ".$firstname.'</p>';
	$text.="Ezt az e-mail azért kapod mert bejelentkezési adatokat kértél.<br />";
	$text.="<p>";
	$text.="Végzős osztály:".$_SESSION['scoolYear'].'-'.$_SESSION['scoolClass']."<br/>";
	$text.="Hamarosan még egy emailt fogsz kapni a bejelentkezési becenévvel és jelszóval.<br/>";
	$text.="Mail címed: ".$mail."<br/>";
	$text.="</p><p>";
	$text.='<a href=http://brassai.blue-l.de/index.php?scollYear='.$_SESSION['scoolYear'].'&scoolClass='.$_SESSION['scoolClass'].'>A vézös diákok honlapja</a>';
	$text.="</p>";
	$text.="<p>Üdvözlettel az adminsztátor.";
	sendTheMail($mail,$text);
	$text.="<p>Szerep: ".$rights."</p>";
	sendTheMail("brassai@blue-l.de",$text);
}

/**
 *send mail  
 */
function SendMail($uid,$text,$userData) {
		global $sendMailCount;
		global $sendMailMsg;
		$diak = getPerson($uid);
		
		$text=str_replace("%%name%%",$diak["lastname"]." ".$diak["firstname"],$text);
		$text=str_replace("\"","&quot;",$text);
		$text.="<hr/><p>Bejelentkezési Adatok<br/>Becenév: ".$diak["user"]." <br/>Jelszó: ".$diak["passw"]."<br/></p>";
		sendTheMail(getFieldValue($diak["email"]),$text);
		//echo($text);
		$sendMailCount++;
		$sendMailMsg="Elködött mailek száma:".$sendMailCount;
}

/**
 * send text to recipient
 */
function sendTheMail($recipient,$text,$subject="") {
	/* recipient */
	$empfaenger = array('<'.$recipient.'>');

	/* recipient CC */
	$empfaengerCC = array('');

	/* recipient BCC */
	$empfaengerBCC = array('');

	/* sender */
	$absender = 'brassai<brassai@blue-l.de>';

	/* reply */
	$reply = '';

	/* subject */
	$subject = 'Brassai Samuel Liceum Vendiakok Honlapja '.$subject;

	/* Nachricht */
	$message = '<html>
	    <head>
	        <title>Brassai Samuel Liceum Vendiakok Honlapja</title>
	    </head>
	    <body>'.$text.'
	    </body>
	</html>
	';

	// build mail header 
	$headers = 'From:' . $absender . "\n";
	$headers .= 'Reply-To:' . $reply . "\n"; 
	$headers .= 'X-Mailer: PHP/' . phpversion() . "\n"; 
	$headers .= 'X-Sender-IP: ' . $_SERVER["REMOTE_ADDR"] . "\n"; 
	$headers .= "Content-type: text/html\n";

	// extract mail recipients
	$empfaengerString = implode(',', $empfaenger);
	$empfaengerCCString = implode(',', $empfaengerCC);
	$empfaengerBCCString = implode(',', $empfaengerBCC);

	$headers .= 'Cc: ' . $empfaengerCCString . "\n";
	$headers .= 'Bcc: ' . $empfaengerBCCString . "\n";

	mail($empfaengerString, $subject, $message, $headers);
}

/**
 * Validate mail adrress
 */
function checkEmail($email) {
  if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email)){
    list($username,$domain)=split('@',$email);
    return true;
  }
  return false;
}


?>
 
