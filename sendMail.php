<?PHP
include_once("dbBL.class.php");
include_once 'lpfw/appl.class.php';

/**
 * Send new password
 * todo: use mail template instead of hard coded text
 */
function SendNewPassword($diak) {
	$text='<p style="font-weight: bold;">Kedeves '.$diak["lastname"]." ".$diak["firstname"].'</p>';
	$text.="Ezt az e-mail azért kapod mert kérdésedre megvátoztak a bejelentkezési adataid.<br />";
	$text.="<p>";
	$text.="Végzős osztály:".getAktClassName()."<br/>";
	$text.="Felhasználónév:".$diak["user"]."<br/>";
	$text.="Jelszó:".encrypt_decrypt("decrypt",$diak["passw"])."<br/>";
	$text.='Direkt link az én adataimhoz: <a href="'.Config::$siteUrl.'/editDiak.php?key='.generateUserLoginKey($diak['id']).'">'.$diak["lastname"]." ".$diak["firstname"].'</a><br/>';
	$text.="</p><p>";
	$text.='<a href='.Config::$siteUrl.'/index.php?classid='.getRealId(getAktClass()).'>A véndiakok diákok honlapja</a>';
	$text.="</p>";
	$text.="<p>Üdvözlettel a vebadminsztátor.";
	\maierlabs\lpfw\Appl::sendHtmlMail(getFieldValue($diak["email"]),$text," jelszó kérés");
}

/**
 * send mail to new user
 */
function sendNewUserMail($firstname,$lastname,$mail,$passw,$user,$rights,$year,$class,$uid=null) {
	$text='<p style="font-weight: bold;">Kedves '.$lastname." ".$firstname.'</p>';
	$text.="Ezt az e-mail azért kapod mert bejelentkeztél a Brassai Sámuel véndiákok honoldalára.<br />";
	$text.="<p>Ennek nagyön örvendünk, és köszöjük érdeklődésed. </p>";
	$text.="<p>A véndiákok honoldala lehetőséget nyújt neked, a volt iskola- és osztálytásaiddal kapcsolatba lépjél. Ez az oldal ingyenes, nem tartalmaz reklámot és ami a legfontosabb, látogatásod és aktivitásaid biztonságban maradnak! Adataid, képeid és bejegyzésed csak arra a célra vannak tárólva, hogy a véndiákok oldalát gazdagítsák! Ezenkivül csak te határozod meg ki láthatja őket.</p>";
	$text.="<p>";
	if (isset($year) && isset($class))
		$text.="Végzős osztály:".$year.'-'.$class."<br/>";
	if (isset($uid) && null!=$uid) {
		$text.='Direkt link az én adataimhoz: <a href="'.Config::$siteUrl.'/editDiak.php?key='.generateUserLoginKey($uid).'">'.$lastname."&nbsp;".$firstname.'</a><br/>';
	}
	if ($passw=="") {
		$text.="Hamarosan még egy emailt fogsz kapni a felhasználó névvel és jelszóval.<br/>";
	} else {
		$text.="Felhasználóneved: ".$user."<br/>";
		$text.="Jelszavad: ".encrypt_decrypt("decrypt",$passw)."<br/>";
	}
	if ($rights!="") {
		$text.="<p>Szerep: ".$rights."</p>";
	}
	$text.="</p><p>";
	$text.='<a href='.Config::$siteUrl.'/index.php?classid='.$year.' '.$class.'>A véndiakok honlapja</a>';
	$text.="</p>";
	$text.="<p>Üdvözlettel az adminsztátor.</p>";
    \maierlabs\lpfw\Appl::sendHtmlMail($mail,$text," új bejelenkezés");
}

function sendChatMail($senderPerson,$toPerson,$text) {
    return true;
}
/**
 *send mail  
 */
function SendMail($uid,$text,$userData,$sender) {
		global $db;
		$diak = $db->getPersonByID($uid);
		
		$text=str_replace("%%name%%",$diak["lastname"]." ".$diak["firstname"],$text);
		$text=str_replace("\"","&quot;",$text);
		$text.='<hr/><p>Direkt link az én adataimhoz: <a href="'.Config::$siteUrl.'/editDiak.php?key='.generateUserLoginKey($uid).'">'.$diak["lastname"]." ".$diak["firstname"].'</a></p>';
		if ($userData) {
			$text.="<hr/><p>Bejelentkezési Adatok<br/>Becenév: ".$diak["user"]." <br/>Jelszó: ".encrypt_decrypt("decrypt",$diak["passw"])."<br/></p>";
		}
        return \maierlabs\lpfw\Appl::sendHtmlMail(getFieldValue($diak["email"]),$text,"", $sender);
}



?>