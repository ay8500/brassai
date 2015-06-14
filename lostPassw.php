<?PHP 
include_once("data.php");		//the database
include_once("sendMail.php");	//send mail
include_once("userManager.php");//login logoff
include_once 'ltools.php';

$arRole = array(	"...válassz!...",
					"Végzős véndiák vagyok, a Brassai Sámuel líceumban ballagtam.",
					"Véndiák vagyok, egykori Brassaista vagyok, nem ott ballagtam.",
					"Osztály felelős vagyok. Szeretném az osztálytársaim adatait szerkeszteni.",
					"Osztályfőnök voltam a végzős osztályban.",
					"Tanár vagyok illetve voltam a Brassai Sámuel líceumban.",
					"Sok jó barátom van a véndiákok között.");
$arRoleValue = array(	"", "viewer", "viewer", "editor", "editor", "guest", "guest");

$mail='';$myname="";$resultText='';$rights="";

//change the password
if (getParam('action','')=='newPassword') {
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
if (getParam('action','')=='newUser') {
	$mail=getParam('mail',"");
	if (checkEmail($mail)) {
		$passw= createPassword(8); 
		if (isset($_GET['myname'])) $myname=$_GET['myname'];
		$xname=split(' ',$myname);
		if (isset($xname[0]) && isset($xname[1])) {
			if (intval(getParam("role", ""))>0) {	
				if (strlen(getParam("year", ""))>0) {
					if (strlen(getParam("class", ""))>0) {
						$r=intval(getParam("role", ""));
						$ret=createNewUser($myname,$mail,$passw,$arRoleValue[$r],getParam("class", ""),getParam("year",""));
						if ($ret==0) {
							sendNewUserMail($xname[1],$xname[0],$mail,$passw,$arRoleValue[$r],getParam("class", ""),getParam("year",""));
							$resultText='<div class="okay">Sikeres bejelentkezés, hamarosan e-mailt fogsz kapni: ' .$mail.'</div>';
						}
				    	else if ($ret==-1)
				    		$resultText='<div class="error">Mailcím az adatbankban már létezik!</div>';
		    			else
		    				$resultText='<div class="error">Bejelentkezés sikertelen!</div>';
					}
		    		else
		    			$resultText='<div class="error">Válassz egy osztályt!</div>';
		    	}
		    	else 
		    		$resultText='<div class="error">Válaszd ki melyik évben volt a ballagás!</div>';
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
<div class="container-fluid">

	<div class="panel panel-default">
		<div class="panel-heading"><h4>Elfelejtettem a jelszavam, szeretnék az email címemre egy újjat.</h4></div>
  		<div class="panel-body">
			<form action="<?PHP echo("$SCRIPT_NAME");?>" method="get">
				<input type="hidden"  name="action" value="newPassword" />
  				<div class="alert alert-warning">
					Akkor használd ezt a funktiót ha már felhasználó vagy és ismert az e-mail címed. A generált új jelszót e-mailben kapod meg, ezt bármikor megtudod módosítani.
				</div>
				<div class="input-group"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">E-Mail címem:</span> 
					<input type="text" name="mail" value="<?PHP echo($mail); ?>" class="form-control" /> 
				</div>  
				<button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-envelope"></span> Szeretnék új jelszót</button>
			</form>
		</div>
	</div>
	
	<div><?PHP echo($resultText); ?></div>
	
	<div class="panel panel-default">
  		<div class="panel-heading"><h4>Új vagyok ezen az oldalon szeretnék én is bejelentkezni.</h4></div>
  		<div class="panel-body">
  			<div class="alert alert-warning">
				Te is a Brassai Sámuel liceumban végeztél és szeretnél volt osztálytársaiddal és iskolatáraiddal kapcsolatba kerülni, rajta, jelentkezz be!
			</div>
  			<form action="<?PHP echo("$SCRIPT_NAME");?>" method="get">
				<input type="hidden"  name="action" value="newUser" />
				<div class="input-group"> 
  					<span  style="min-width:150px; text-align:right" class="input-group-addon">E-Mail címem</span> 
					<input type="text" name="mail" value="<?PHP echo($mail); ?>"  class="form-control" 	/>
				</div>
				<div class="input-group"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Nevem</span>
					<input type="text" name="myname" value="<?PHP echo($myname); ?>" class="form-control" 	/>
				</div>
				<div class="input-group"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Beosztás</span>
					<select id="role" name="role" size="1" class="form-control" >
						<?php foreach ($arRole as $i => $r) {
							if (getParam("role", "")==$i) $selected="selected"; else $selected="";
							echo('<option '.$selected.' value="'.$i.'">'.$r.'</option>');
						}?>
					</select>
				</div>
				<div class="input-group"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Ballagási év</span>
					<select id="year" name="year" size="1" class="form-control" >
						<option value="0">...válassz!...</option>
						<?php for ($i=2000;$i>1955;$i--) {
							if (getParam("year", "")==$i) $selected="selected"; else $selected="";
							echo('<option '.$selected.' value="'.$i.'">'.$i.'</option>');
						} ?>
					</select>
				</div>
				<div class="input-group"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Ballagási osztály</span>
					<select id="class" name="class" size="1" class="form-control" >
						<option value="">...válassz!...</option>
						<option value="12A">12A</option>
						<option value="12B">12B</option>
						<option value="12C">12C</option>
						<option value="12D">12D</option>
						<option value="13A">13A (esti tagozat)</option>
						<option value="13B">13B (esti tagozat)</option>
					</select>
				</div>
				<button class="btn btn-default" type="submit" ><span class="glyphicon glyphicon-user"></span> Szeretnék bejelentkezni</button>
				
			</form>
		</div>
	</div>
	
	<div class="panel panel-default">
  		<div class="panel-heading"><h4>Vannak bejelenkezési adataim de nem sikerül bejelentkezni.</h4></div>
  		<div class="panel-body">
  			<div class="alert alert-warning">
				Ne add fel hamar a harcot a technika ellen, próbáld meg még egyszer.
				Tippek:
				<ul>
					<li>A becenév nem tartalmaz ékezetes betüket.</li> 
					<li>Esetleg használd a kopirozás-beillesztés <a  target="_blank" href="http://en.wikipedia.org/wiki/Cut,_copy,_and_paste">(C&amp;P)</a> technikát a jelszó beadására.</li>
				</ul>
				Küldj egy e-mailt a <a href="mailto:brassai@blue-l.de">brassai@blue-l.de</a> címre ha bármilyen kérdésed van.
			</div>
		</div> 
	</div>


</div>

