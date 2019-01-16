<?php 
include_once("tools/userManager.php");
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once("sendMail.php");

use \maierlabs\lpfw\Appl as Appl;

$mail='';$myname="";$resultText='';$rights="";

//change the password
if (isActionParam('newPassword')) {
	if (isset($_GET['mail'])) $mail=$_GET['mail'];
	if (checkEmail($mail)) {
		$ret=resetUserPasswort($mail, createPassword(8) );
		if ($ret>0) { 
			SendNewPassword($db->getPersonByID($ret));
			Appl::setMessage('Új jelszó a következő címre elküldve : '.$mail, 'success');
		}
    	else if ($ret==-1)
    		Appl::setMessage('Mailcímet az adatbank nem ismeri!', 'danger');
    	else if ($ret==-3)
    		Appl::setMessage('Jelszó módósítás nem lehetséges!<br/>Naponta csak egyszer lehet új jelszót kérni.', 'danger');
	}
	else
	   Appl::setMessage('Mail cím nem helyes, vagy a mező üres.!', 'danger');
}

Appl::setSiteSubTitle('Bejelentkezni szeretnék!');
include 'homemenu.inc.php';
?>
<div class="container-fluid">

	<div class="panel panel-default">
		<div class="panel-heading"><h4>Elfelejtettem a jelszavam, szeretnék az email címemre egy újjat.</h4></div>
  		<div class="panel-body">
			<form method="get">
				<input type="hidden"  name="action" value="newPassword" />
  				<div class="alert alert-info">
					Akkor használd ezt a funkciót, ha már felhasználó vagy és ismert az e-mail címed. A generált új jelszót e-mailben kapod meg, ezt bármikor megtudod módosítani.
				</div>
				<div class="input-group" > 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">E-Mail címem:</span> 
					<input type="text" name="mail" value="<?PHP echo($mail); ?>" class="form-control" onkeyup="validateEmailInput(this,'#send_passw');" /> 
				</div>  
				<button style="margin-top:10px;" class="btn btn-default" type="submit" id="send_passw"><span class="glyphicon glyphicon-envelope"></span> Szeretnék új jelszót</button>
			</form>
		</div>
	</div>
		
	<div class="panel panel-default">
  		<div class="panel-heading"><h4>Új vagyok ezen az oldalon szeretnék én is bejelentkezni.</h4></div>
  		<div class="panel-body">
  			<div class="alert alert-info">
				Te is a <?php echo getAktSchoolName()?> véndiákja tanárnője vagy tanárja vagy és szeretnél volt osztálytársaiddal és iskolatáraiddal kapcsolatba kerülni, rajta, jelentkezz be!
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
					<li>Használd a Facebook felhasználó bejelentkezési lehetőséget.
                    <li>Javaskript jogok be kell legyenek kapcsolva ahoz, hogy bejelenkezzhess.
				</ul>
				Küldj egy e-mailt a <a href="mailto:<?php echo Config::$siteMail?>"><?php echo Config::$siteMail?></a> címre ha bármilyen kérdésed vagy megjegyzésed van.
			</div>
		</div> 
	</div>

</div>


<script type="text/javascript">

	function newUser () {
		document.location.href="signin.php?action=newUser";
	}

	function validateEmailInput(sender,button) { 
	    	if (validateEmail(sender.value)) {
	    		sender.style.borderColor="green";
	    		$(button).removeClass("disabled");
	    	} else {
	    		sender.style.borderColor="red";
	    		$(button).addClass("disabled");
	    	}
  	} 

	function validateEmail(mail) {
	   	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	   	return re.test(mail);
	}
	
</script>
<?php include("homefooter.inc.php");?>