<?PHP
include_once("data.php");

//Mail send operation message
$sendMailMsg = "";

//Count of sent mails
$sendMailCount = 0;

//Automatic send option not usesd yet
/*
$action=""; if (isset($_POST["action"])) $action=$_POST["action"];
if ($action == 'sendMail')
{

}
*/

/**
 * Send new password
 * todo: use mail template instead of hard coded text
 */
function SendNewPassword($uid) {
	global $sendMailMsg;
	$diak = getPerson($uid);
	$text='<p style="font-weight: bold;">Kedeves '.$diak["lastname"]." ".$diak["firstname"].'</p>';
	$text.="Ezt az e-mail azért kapod mert kérdésedre megvátoztak a bejelentkezési adataid.<br />";
	$text.="<p>";
	$text.="Végzős osztály:".getAktDatabaseName().'-'.getAKtScoolClass()."<br/>";
	$text.="Felhasználónév:".$diak["user"]."<br/>";
	$text.="Jelszó:".$diak["passw"]."<br/>";
	$text.='Direkt link az én adataimhoz: <a href="http://brassai.blue-l.de/editDiak.php?key='.generateUserLoginKey($uid).'">'.$diak["lastname"]." ".$diak["firstname"].'</a>';
	$text.="</p><p>";
	$text.='<a href=http://brassai.blue-l.de/index.php?scoolYear='.getAktScoolYear().'&scoolClass='.getAKtScoolClass().'>A véndiakok diákok honlapja</a>';
	$text.="</p>";
	$text.="<p>Üdvözlettel a vebadminsztátor.";
	sendHtmlMail(getFieldValue($diak["email"]),$text," jelszó kérés");
}

/**
 * send mail to new user
 */
function sendNewUserMail($firstname,$lastname,$mail,$passw,$rights,$year,$class) {
	$text='<p style="font-weight: bold;">Kedeves '.$lastname." ".$firstname.'</p>';
	$text.="Ezt az e-mail azért kapod mert bejelentkeztél a Brassai Sámuel véndiákok honoldalára.<br />";
	$text.="<p>Ennek nagyön örvendünk, és köszöjük érdeklődésed. </p>";
	$text.="<p>A véndiákok honoldala lehetőséget nyújt neked, volt iskola- és osztálytásaiddal kapcsolatba lépjél. Ez az oldal ingyenes, nem tartalmaz reklámot és ami a legfontosabb, látogatásod és aktivitásaid biztonságban maradnak! Adataid, képeid és bejegyzésed csak arra a célra vannak tárólva, hogy a véndiákok oldalát gazdagítsák! Ezenkivül csak te határozod meg ki láthatja őket.</p>";
	$text.="<p>";
	if (isset($year) && isset($class))
		$text.="Végzős osztály:".$year.'-'.$class."<br/>";
	$text.="Hamarosan még egy emailt fogsz kapni a felhasználó névvel és jelszóval.<br/>";
	$text.="Mail címed: ".$mail."<br/>";
	$text.="<p>Szerep: ".$rights."</p>";
	$text.="</p><p>";
	$text.='<a href=http://brassai.blue-l.de/index.php?scoolYear='.$year.'&scoolClass='.$class.'>A véndiakok diákok honlapja</a>';
	$text.="</p>";
	$text.="<p>Üdvözlettel az adminsztátor.</p>";
	sendHtmlMail($mail,$text," új bejelenkezés");
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
		$text.='<hr/><p>Direkt link az én adataimhoz: <a href="http://brassai.blue-l.de/editDiak.php?key='.generateUserLoginKey($uid).'">'.$diak["lastname"]." ".$diak["firstname"].'</a></p>';
		if ($userData) {
			$text.="<hr/><p>Bejelentkezési Adatok<br/>Becenév: ".$diak["user"]." <br/>Jelszó: ".$diak["passw"]."<br/></p>";
		}
		sendHtmlMail(getFieldValue($diak["email"]),$text);
		$sendMailCount++;
		$sendMailMsg="Elködött mailek száma:".$sendMailCount;
}


/**
 * send text to recipient
 */
function sendHtmlMail($recipient,$text,$subject="") {
	/* sender */
	$absender = 'brassai<brassai@blue-l.de>';

	/* reply */
	$reply = '';

	/* subject */
	$subject = 'Brassai Samuel Líceum Vendiakok Honlapja '.$subject;

	/* Nachricht */
	$message = '<html>
	    <head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	        <title>Brassai Samuel Liceum Vendiakok Honlapja</title>
	    </head>
	    <body>'.$text.'
	    </body>
	</html>
	';

	// build mail header 
	$headers = 'From:' . $absender . "\r\n";
	$headers .= 'Reply-To:' . $reply . "\r\n"; 
	$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n"; 
	$headers .= 'X-Sender-IP: ' . $_SERVER["REMOTE_ADDR"] . "\r\n"; 
	$headers .= "Content-Type: text/html;charset=utf-8\r\n";

	mail("code@blue-l.de", $subject, $message, $headers);
	if (isset($recipient)) {
		return mail($recipient, $subject, $message, $headers);
	}
	return false;
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