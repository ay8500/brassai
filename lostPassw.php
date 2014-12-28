<?PHP 
include_once("data.php");		//the database
include_once("sendMail.php");	//send mail
include_once("userManager.php");//login logoff

$mail='';$myname="";$resultText='';$rights="";

//change the password
if (isset($_GET['action']) && ($_GET['action']=='newPassword')) {
	if (isset($_GET['mail'])) $mail=$_GET['mail'];
	if (checkEmail($mail)) {
		$ret=setUserPasswort($mail, createPassword(8) );
		if ($ret>0) { 
			SendNewPassword($ret);
			$resultText='<div class="okay">�j jelsz� a k�vetkez� c�mre elk�ldve : '.$mail.'</div>';
		}
    	else if ($ret==-1)
    		$resultText='<div class="error">Mailc�met az adatbank nem ismeri!</div>';
    	else
    		$resultText='<div class="error">Jelsz� m�d�s�t�s nem lehets�ges!</div>';
	}
	else
	   $resultText='<div class="error">Mail c�m nem helyes!</div>';
}

//new user
if (isset($_GET['action']) && ($_GET['action']=='newUser')) {
	if (isset($_GET['mail'])) $mail=$_GET['mail'];
	if (checkEmail($mail)) {
		$passw= createPassword(8); 
		if (isset($_GET['myname'])) $myname=$_GET['myname'];
		$xname=split(' ',$myname);
		if (isset($xname[0]) && isset($xname[1])) {
			if (isset($_GET['rights'])) $rights=$_GET['rights'];
			if (strlen($rights)>0) {			 
				$ret=createNewUser($myname,$mail,$passw,$rights);
				if ($ret==0) {
					sendNewUserMail($xname[1],$xname[0],$mail,$passw,$rights);
					$resultText='<div class="okay">Sikeres bejelentkez�s, hamarosan e-mailt fogsz kapni: ' .$mail.'</div>';
				}
		    	else if ($ret==-1)
		    		$resultText='<div class="error">Mailc�m az adatbankban m�r l�tezik!</div>';
		    	else
		    		$resultText='<div class="error">Bejelentkez�s sikertelen!</div>';
			}
			else
				$resultText='<div class="error">V�laszd ki milyen szereped van az oszt�lyban!</div>';
		}
		else
			$resultText='<div class="error">Csal�d �s vezekt�kneved hib�s!</div>';
	}
	else
	   $resultText='<div class="error">Mail c�m nem helyes!</div>';
}
?>
<div style="text-align:left">
<table style="width:600px" class="pannel" align="center">
	<form action="<?PHP echo("$SCRIPT_NAME");?>" method="get">
	<input type="hidden"  name="action" value="newPassword" />
	<tr style="font-size:12px; font-weight:bold">
		<td rowspan="3"><img style="widht:60px;height:60px" src="images/szomoru.png"/></td><td> Elfelejtettem a jelszavam, szeretn�k az email c�memre egy �jjat.</td>
	</tr>
	<tr>
		<td>Akkor haszn�ld ezt a funkti�t ha m�r ismert az e-mail c�med. A gener�lt �j jelsz�t e-mailben kapod meg, ezt b�rmikor megtudod m�dos�tani.</td>
	</tr>
	<tr>
		<td>E-Mail c�mem: <input type="text" name="mail" value="<?PHP echo($mail); ?>" />&nbsp;&nbsp;&nbsp; 
		<br/><input class="submit2" type="submit" value="Szeretn�k �j jelsz�t!" /></td>  
	</tr>
	<tr>
		<td colspan="2" ><hr /></td>  
	</tr>
	</form>
	<form action="<?PHP echo("$SCRIPT_NAME");?>" method="get">
	<input type="hidden"  name="action" value="newUser" />
	
	<tr style="font-size:12px; font-weight:bold">
		<td rowspan="5"><img style="widht:60px;height:60px" src="images/beszedes.png"/><td> �j vagyok ezen az oldalon szeretn�k �n is bejelentkezni.</td>
	</tr>
	<tr>
		<td>Ird be az e-mail c�medet �s r�videsen megkapod a megadott c�mre a bejelentkez�si adatokat.</td>
	</tr>
	<tr>
		<td>E-Mail c�mem: <input type="text" name="mail" value="<?PHP echo($mail); ?>" />&nbsp;&nbsp;&nbsp;
		<br />Nevem: <input type="text" name="myname" value="<?PHP echo($myname); ?>" size="40"/>
		</td>  
	</tr>
	<tr>
		<td>Az <?PHP echo($_SESSION['scoolYear']); ?>-ben v�gzett <?PHP echo($_SESSION['scoolClass']);?> oszt�lynak az oldalait szeretn�m haszn�lni mint:
		<select name="rights" size="1">
			<option value="">...v�lassz!...</option>
			<option value="user">V�ndi�k</option>
			<option value="editor">Oszt�ly felel�s</option>
			<option value="editor">Oszt�lyf�n�k</option>
			<option value="viewer">Tan�r</option>
		</select>
	</tr>
	<tr>
		<td><input class="submit2" type="submit" value="Szeretn�k bejelentkezni!" /></td>
	</tr>
	</form> 
	<tr>
		<td colspan="2" ><hr /></td>  
	</tr>
	<tr style="font-size:12px; font-weight:bold">
		<td rowspan="2"><img style="widht:60px;height:60px" src="images/vicces.png"/><td> Vannak bejelenkez�si adataim de nem siker�l beker�lni.</td>
	</tr>
	<tr>
		<td>Ne add fel hamar a harcot a technika ellen, pr�b�ld meg m�g egyszer.
		<br/>Tippek:
		<ul>
		<li>A becen�v nem tartalmaz �kezetes bet�ket!</li> 
		<li>Esetleg haszn�ld a kopiroz�s-beilleszt�s <a  target="_blank" href="http://en.wikipedia.org/wiki/Cut,_copy,_and_paste">(C&amp;P)</a> technik�t a jelsz� bead�s�ra.</li>
		<li>Az adatok csak a te oszt�lyodra vonatkoznak!</li> 
		</ul> 
		</td>
	</tr>
	<tr>
		<td colspan="2"><hr /></td>  
	</tr>
	<tr style="font-size:12px; font-weight:bold">
	   <td rowspan="2"><img style="widht:60px;height:60px" src="images/mosolygo.png"/><td>Segits�get k�rek:</td>
	</tr> 
	<tr><td>K�ldj egy e-mailt a <a href="mailto:brassai@blue-l.de">brassai@blue-l.de</a> c�mre ha b�rmilyen k�rd�sed van.</td></tr>
	<tr>
		<td colspan="2" ><hr /></td>  
	</tr>
	<tr>
		<td colspan="2"><?PHP echo($resultText); ?></td>
	</tr>
</table>
</div>

