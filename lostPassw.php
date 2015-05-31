<?PHP 
include_once("data.php");		//the database
include_once("sendMail.php");	//send mail
include_once("userManager.php");//login logoff

$mail='';$myname="";$resultText='';$rights="";

//change the password
if (isset($_GET['action']) && ($_GET['action']=='newPassword')) {
	if (isset($_GET['mail'])) $mail=$_GET['mail'];
	if (checkEmail($mail)) {
		$ret=resetUserPasswort($mail, createPassword(8) );
		if ($ret>0) { 
			SendNewPassword($ret);
			$resultText='<div class="okay">Új jelszó a következő címre elküldve : '.$mail.'</div>';
		}
    	else if ($ret==-1)
    		$resultText='<div class="error">Mailcímet az adatbank nem ismeri!</div>';
    	else
    		$resultText='<div class="error">Jelszó módósítás nem lehetséges!</div>';
	}
	else
	   $resultText='<div class="error">Mail cím nem helyes!</div>';
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
					$resultText='<div class="okay">Sikeres bejelentkezés, hamarosan e-mailt fogsz kapni: ' .$mail.'</div>';
				}
		    	else if ($ret==-1)
		    		$resultText='<div class="error">Mailcím az adatbankban már létezik!</div>';
		    	else
		    		$resultText='<div class="error">Bejelentkezés sikertelen!</div>';
			}
			else
				$resultText='<div class="error">Válaszd ki milyen szereped van az osztályban!</div>';
		}
		else
			$resultText='<div class="error">Család és vezektékneved hibás!</div>';
	}
	else
	   $resultText='<div class="error">Mail cím nem helyes!</div>';
}
?>
<div style="text-align:left">
<table style="width:600px" class="pannel" align="center">
	<form action="<?PHP echo("$SCRIPT_NAME");?>" method="get">
	<input type="hidden"  name="action" value="newPassword" />
	<tr style="font-size:12px; font-weight:bold">
		<td rowspan="3"><img style="widht:60px;height:60px" src="images/szomoru.png"/></td><td> Elfelejtettem a jelszavam, szeretnék az email címemre egy újjat.</td>
	</tr>
	<tr>
		<td>Akkor használd ezt a funktiót ha már ismert az e-mail címed. A generált új jelszót e-mailben kapod meg, ezt bármikor megtudod módosítani.</td>
	</tr>
	<tr>
		<td>E-Mail címem: <input type="text" name="mail" value="<?PHP echo($mail); ?>" />&nbsp;&nbsp;&nbsp; 
		<br/><input class="submit2" type="submit" value="Szeretnék új jelszót!" /></td>  
	</tr>
	<tr>
		<td colspan="2" ><hr /></td>  
	</tr>
	</form>
	<form action="<?PHP echo("$SCRIPT_NAME");?>" method="get">
	<input type="hidden"  name="action" value="newUser" />
	
	<tr style="font-size:12px; font-weight:bold">
		<td rowspan="5"><img style="widht:60px;height:60px" src="images/beszedes.png"/><td> Új vagyok ezen az oldalon szeretnék én is bejelentkezni.</td>
	</tr>
	<tr>
		<td>Ird be az e-mail címedet és rövidesen megkapod a megadott címre a bejelentkezési adatokat.</td>
	</tr>
	<tr>
		<td>E-Mail címem: <input type="text" name="mail" value="<?PHP echo($mail); ?>" />&nbsp;&nbsp;&nbsp;
		<br />Nevem: <input type="text" name="myname" value="<?PHP echo($myname); ?>" size="40"/>
		</td>  
	</tr>
	<tr>
		<td>Az <?PHP echo(getAktScoolYear()); ?>-ben végzett <?PHP echo(getAKtScoolClass());?> osztálynak az oldalait szeretném használni mint:
		<select name="rights" size="1">
			<option value="">...válassz!...</option>
			<option value="user">Véndiák</option>
			<option value="editor">Osztály felelős</option>
			<option value="editor">Osztályfőnök</option>
			<option value="viewer">Tanár</option>
		</select>
	</tr>
	<tr>
		<td><input class="submit2" type="submit" value="Szeretnék bejelentkezni!" /></td>
	</tr>
	</form> 
	<tr>
		<td colspan="2" ><hr /></td>  
	</tr>
	<tr style="font-size:12px; font-weight:bold">
		<td rowspan="2"><img style="widht:60px;height:60px" src="images/vicces.png"/><td> Vannak bejelenkezési adataim de nem sikerül bekerülni.</td>
	</tr>
	<tr>
		<td>Ne add fel hamar a harcot a technika ellen, próbáld meg még egyszer.
		<br/>Tippek:
		<ul>
		<li>A becenév nem tartalmaz ékezetes betüket!</li> 
		<li>Esetleg használd a kopirozás-beillesztés <a  target="_blank" href="http://en.wikipedia.org/wiki/Cut,_copy,_and_paste">(C&amp;P)</a> technikát a jelszó beadására.</li>
		<li>Az adatok csak a te osztályodra vonatkoznak!</li> 
		</ul> 
		</td>
	</tr>
	<tr>
		<td colspan="2"><hr /></td>  
	</tr>
	<tr style="font-size:12px; font-weight:bold">
	   <td rowspan="2"><img style="widht:60px;height:60px" src="images/mosolygo.png"/><td>Segitséget kérek:</td>
	</tr> 
	<tr><td>Küldj egy e-mailt a <a href="mailto:brassai@blue-l.de">brassai@blue-l.de</a> címre ha bármilyen kérdésed van.</td></tr>
	<tr>
		<td colspan="2" ><hr /></td>  
	</tr>
	<tr>
		<td colspan="2"><?PHP echo($resultText); ?></td>
	</tr>
</table>
</div>

