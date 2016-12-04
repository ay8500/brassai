<?PHP 
include_once("data.php");		//the database
include_once("sendMail.php");	//send mail
include_once("tools/userManager.php");//login logoff
include_once 'tools/ltools.php';

$mail='';$myname="";$resultText='';$rights="";

//change the password
if (getParam('action','')=='newPassword') {
	if (isset($_GET['mail'])) $mail=$_GET['mail'];
	if (checkEmail($mail)) {
		$ret=resetUserPasswort($mail, createPassword(8) );
		if ($ret>0) { 
			SendNewPassword($ret);
			$resultText='<div class="alert alert-success">Új jelszó a következő címre elküldve : '.$mail.'</div>';
		}
    	else if ($ret==-1)
    		$resultText='<div class="alert alert-danger">Mailcímet az adatbank nem ismeri!</div>';
    	else
    		$resultText='<div class="alert alert-danger">Jelszó módósítás nem lehetséges!</div>';
	}
	else
	   $resultText='<div class="alert alert-danger">Mail cím nem helyes!</div>';
}

//new user
if (getParam('action','')=='newUser') {
	$mail=getParam('mail',"");
	if (checkEmail($mail)) {
		$passw= ""; 
		if (isset($_GET['myname'])) $myname=$_GET['myname'];
		$xname=split(' ',$myname);
		if (isset($xname[0]) && isset($xname[1])) {
			if (checkFirstName($xname[1])) {
				if (intval(getParam("role", ""))>0) {	
					if (strlen(getParam("year", ""))>0 || intval(getParam("role", ""))>4) {
						if (strlen(getParam("class", ""))>0 || intval(getParam("role", ""))>4) {
							$r=intval(getParam("role", ""));
							//$ret=createNewUser($myname,$mail,$passw,$arRoleValue[$r],getParam("class", ""),getParam("year",""));
							sendNewUserMail($xname[1],$xname[0],$mail,$passw,$arRoleValue[$r],getParam("class", ""),getParam("year",""));
							$resultText='<div class="alert alert-success">Sikeres bejelentkezés, hamarosan e-mailt fogsz kapni: ' .$mail.'</div>';
						}
			    		else
			    			$resultText='<div class="alert alert-danger">Válassz egy osztályt!</div>';
			    	}
			    	else 
			    		$resultText='<div class="alert alert-danger">Válaszd ki melyik évben volt a ballagás!</div>';
				}
				else 
					$resultText='<div class="alert alert-danger">Válaszd ki milyen szereped van az osztályban!</div>';
			}
			else
				$resultText='<div class="alert alert-danger">A keresztnevedet nem ismeri fel a honoldal.</div>';
		}
		else
			$resultText='<div class="alert alert-danger">Ird be család és keresztneved!</div>';
	}
	else
	   $resultText='<div class="alert alert-danger">Mail cím nem helyes!</div>';
}

?>
<div class="sub_title">Bejelentkezni szeretnék!</div>
<div class="container-fluid">

	<div class="panel panel-default">
		<div class="panel-heading"><h4>Elfelejtettem a jelszavam, szeretnék az email címemre egy újjat.</h4></div>
  		<div class="panel-body">
			<form action="<?PHP echo("$SCRIPT_NAME");?>" method="get">
				<input type="hidden"  name="action" value="newPassword" />
  				<div class="alert alert-info">
					Akkor használd ezt a funkciót, ha már felhasználó vagy és ismert az e-mail címed. A generált új jelszót e-mailben kapod meg, ezt bármikor megtudod módosítani.
				</div>
				<div class="input-group"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">E-Mail címem:</span> 
					<input type="text" name="mail" value="<?PHP echo($mail); ?>" class="form-control" onkeyup="validateEmailInput(this,'#send_passw');" /> 
				</div>  
				<button class="btn btn-default" type="submit" id="send_passw"><span class="glyphicon glyphicon-envelope"></span> Szeretnék új jelszót</button>
			</form>
		</div>
	</div>
	
	<div class="resultDBoperation"><?PHP echo($resultText); ?></div>
	
	<div class="panel panel-default">
  		<div class="panel-heading"><h4>Új vagyok ezen az oldalon szeretnék én is bejelentkezni.</h4></div>
  		<div class="panel-body">
  			<div class="alert alert-info">
				Te is a <?php echo getAktSchoolName()?> véndiákja tanárnöje vagy tanárja vagy és szeretnél volt osztálytársaiddal és iskolatáraiddal kapcsolatba kerülni, rajta, jelentkezz be!
				<br />
				Ez az oldal ingyenes, nem tartalmaz reklámot és ami a legfontosabb, látogatásod és aktivitásaid biztonságban maradnak! Adataid, képeid és bejegyzésed csak arra a célra vannak tárólva, hogy a véndiákok oldalát gazdagítsák! Ezenkivül csak te határozod meg ki láthatja őket.
			</div>
			<button class="btn btn-default" onclick="newUser();"><span class="glyphicon glyphicon-user"></span> Szeretnék bejelentkezni</button>
		</div>
	</div>
	
	<div class="panel panel-default">
  		<div class="panel-heading"><h4>Vannak bejelenkezési adataim de nem sikerül bejelentkezni.</h4></div>
  		<div class="panel-body">
  			<div class="alert alert-info">
				Ne add fel hamar a harcot az internet ellen, próbáld meg még egyszer.
				Tippek:
				<ul>
					<li>A becenév nem tartalmaz ékezetes betüket.</li>
					<li>E-mail címedet is használhatod mint felhasználó név.</li>
					<li>Javaskript jogok be kell legyenek kapcsolva ahoz, hogy bejelenkezzhess.
				</ul>
				Küldj egy e-mailt a <a href="mailto:brassai@blue-l.de">brassai@blue-l.de</a> címre ha bármilyen kérdésed vagy megjegyzésed van.
			</div>
		</div> 
	</div>

</div>


<script type="text/javascript">
	function changeClass() {
		
	}

	function newUser () {
		document.location.href="start.php?action=newUser";
	}

	function validateEmailInput(sender,button) { 
	    	if (validateEmail(sender.value)) {
	    		sender.style.color="green";
	    		$(button).removeClass("disabled");
	    	} else {
	    		sender.style.color="red";
	    		$(button).addClass("disabled");
	    	}
  	} 

	function validateEmail(mail) {
	   	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	   	return re.test(mail);
	}
	
		
</script>
