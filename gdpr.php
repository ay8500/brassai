<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';

use \maierlabs\lpfw\Appl as Appl;

Appl::addCssStyle('
	.fb-radio{width: 25px;height: 25px;position: relative;top: -6px;}
	.margin-hor1{margin-left:40px;}
');

Appl::setSiteTitle("GDPR Adatok törlésének kérvényezése");
Appl::setSiteSubTitle('Személyes adatok törlésének kérvényezése');

global $db;
$gdprPerson=$db->getPersonByID(getIntParam("id"));
if (null!=$gdprPerson) {
    if (isActionParam("setGdpr") && isUserAdmin()) {
        if ($db->savePersonField($gdprPerson["id"],"gdpr",getIntParam("gdpr",0),true)>=0) {
            Appl::setMessage("Gdpr kimentve", "info");
            $gdprPerson["gdpr"]=getIntParam("gdpr",0);
        } else
            Appl::setMessage("Hibás gdpr kimentés!","danger");
    } elseif (isUserLoggedOn() && getLoggedInUserId()==$gdprPerson["id"] && getParam("action")=="gdprok") {
        if ($db->savePersonField($gdprPerson["id"],"gdpr",getIntParam("gdpr",100),true)>=0) {
            Appl::setMessage("Köszönjük a gdpr visszajelzést", "info");
            $gdprPerson["gdpr"]=getIntParam("gdpr",0);
        } else
            Appl::setMessage("Hibás gdpr kimentés!","danger");
    } elseif (getParam("action")!=null) {
	
		if (!isUserLoggedOn() && isset($_SESSION['SECURITY_CODE']) && getParam('code')!=$_SESSION['SECURITY_CODE']) {
			Appl::setMessage("Biztonságí kód nem helyes. Probáld még egyszer!","warning");
		} 

		if (isUserLoggedOn() || (isset($_SESSION['SECURITY_CODE']) && getParam('code')===$_SESSION['SECURITY_CODE'])) {
			include_once 'sendMail.php';
			$html="";
			$html .="<h2>Véndiákok honoldala</h2>";
			$html .='<div>Server-Addr:'.print_r($_SERVER["SERVER_ADDR"],true).'</div>';
			$html .='<div>Remote-Addr:'.print_r($_SERVER["REMOTE_ADDR"],true).'</div>';
			$html .='<div>Request-Time:'.date("Y.m.d H:i:s",print_r($_SERVER["REQUEST_TIME"],true)).'</div>';
			$html .='<div>'.print_r($_REQUEST,true).'</div>';
            if (\maierlabs\lpfw\Appl::sendHtmlMail(getParam("email"), $html, "GDPR"))
                Appl::setMessage("Személyes adatok védelme kérvényezve. Hamarosan visszajelzük e-mailben vagy telefonon.","info");
            else
                Appl::setMessage("Személyes adatok védelmének kérvényezése nem sikerült. Kérünk probálkozz újból vagy írj a brassai@blue-l.de címre egy kérvényt.","warning");
		}
	}
} else {
    $gdprPerson = $db->getPersonByID(getAktUserId());
}
include 'homemenu.inc.php';
?>
<form>
<div class="container-fluid">
    <h3 class="margin-hor">Megbizható és személyes adatokat biztonságbantartó oldal</h3>
    <div id="page1">
        <div class="margin-def">
            <input class="left fb-radio" type="radio" name="action" value="gdprok" onclick="checkgdpr();"
                <?php echo getParam("action","")=="gdprok"?"checked=checked":"" ?>
                <?php echo $gdprPerson["gdpr"]==100?"checked=checked":"" ?>
            />
            <div class="margin-hor1"> Személyes adtaim itt biztonságban vannak és kétségtelenül csak azt a célt szolgálják, hogy ennek az oldalnak a tartalmát gazdagítsák.</div></div>
        <div style="clear:both;"></div>
    </div>
	<h3>Ezennel kérvényezem <?php echo getPersonLinkAndPicture($gdprPerson)?> személyes adatainak védelmét a következő formában:</h3>
	<div id="page1">
		<h4 class="margin-hor">Válassz ki egy maradandó végleges törlési opciót</h4>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="deletesocialnetwork" onclick="checkgdpr();"
                  <?php echo getParam("action","")=="deletesocialnetwork"?"checked=checked":"" ?>
                  <?php echo $gdprPerson["gdpr"]==1?"checked=checked":"" ?>
            />
			<div class="margin-hor1"> E-Mail, Facebook címem törlését szeretném.</div></div>
		<div style="clear:both;"></div>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="deleteaddresses" onclick="checkgdpr();"
                <?php echo getParam("action","")=="deleteaddresses"?"checked=checked":"" ?>
                <?php echo $gdprPerson["gdpr"]==2?"checked=checked":"" ?>
            />
			<div class="margin-hor1">Lakcímem, E-Mail, Facebook címem törlését szeretném.</div></div>
		<div style="clear:both;"></div>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="deletebutname" onclick="checkgdpr();"
                <?php echo getParam("action","")=="deletebutname"?"checked=checked":"" ?>
                <?php echo $gdprPerson["gdpr"]==3?"checked=checked":"" ?>
            />
			<div class="margin-hor1"> Nevemen kívül minden személyes adatnak a törlését szeretném.</div></div>
		<div style="clear:both;"></div>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="deletepictures" onclick="checkgdpr();"
                <?php echo getParam("action","")=="deletepictures"?"checked=checked":"" ?>
                <?php echo $gdprPerson["gdpr"]==4?"checked=checked":"" ?>
            />
			<div class="margin-hor1"> Profilképen és személyes képeim törlését szeretném.</div></div>
		<div style="clear:both;"></div>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="deleteall" onclick="checkgdpr();"
                <?php echo getParam("action","")=="deleteall"?"checked=checked":"" ?>
                <?php echo $gdprPerson["gdpr"]==5?"checked=checked":"" ?>
            />
			<div class="margin-hor1"> Teljes és végleges törlést szeretnék, ebben az osztályban az én nevem alatt soha ne legyen bejegyzés!</div></div>
		<div style="clear:both;"></div>
		<h4 class="margin-hor">Válassz ki egy adat láthatósági opciót</h4>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="onlyschoolmates" onclick="checkgdpr();"
                <?php echo getParam("action","")=="onlyschoolmates"?"checked=checked":"" ?>
                <?php echo $gdprPerson["gdpr"]==6?"checked=checked":"" ?>
            />
			<div class="margin-hor1"> Csak nevem és lakhelyem városa legyen látható mindenki számára, a többi adatokat csak iskolatársak látják.</div></div>
		<div style="clear:both;"></div>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="onlyclassmates" onclick="checkgdpr();"
                <?php echo getParam("action","")=="onlyclassmates"?"checked=checked":"" ?>
                <?php echo $gdprPerson["gdpr"]==7?"checked=checked":"" ?>
            />
			<div class="margin-hor1"> Csak nevem és lakhelyem városa legyen látható mindenki számára, a többi adatokat csak osztálytársak látják.</div></div>
		<div style="clear:both;"></div>
		<h4 class="margin-hor">Mit tud rólam ez az oldal?</h4>
		<div class="margin-def">
			<input class="left fb-radio" type="radio" name="action" value="alldatamail" onclick="checkgdpr();"
                <?php echo getParam("action","")=="alldatamail"?"checked=checked":"" ?>
            />
			<div class="margin-hor1"> Szeretnék egy e-mail-t az összes személyes adataimról.</div></div>
		<div style="clear:both;"></div>
	</div>
	
	<div class="input-group" style="margin: 20px 0px 20px 0px;">
		<span style="min-width:120px; text-align:right" class="input-group-addon">E-Mail</span>
		<input type="text" class="form-control" value="<?php echo getParam("email")==null?getFieldValue($gdprPerson,"email"):getParam("email")?>" name="email" id="email-gdpr" placeholder="info@email.ro" onkeyup="checkgdpr();"/>
	</div>

	<div class="input-group" style="margin: 20px 0px 20px 0px;">
		<span style="min-width:120px; text-align:right" class="input-group-addon">Telefon</span>
		<input type="text" class="form-control" value="<?php echo getParam("phone")==null?getFieldValue($gdprPerson,"phone"):getParam("phone")?>" name="phone" placeholder="+40 264 123456"/>
	</div>
	
	<div class="input-group" style="margin: 20px 0px 20px 0px;">
		<span style="min-width:120px; text-align:right" class="input-group-addon">Megjegyzés</span>
		<input type="text" class="form-control" value="<?php echo getParam("text","")?>" name="text" />
	</div>
	
	<?php if (!isUserLoggedOn()) {?>
	<div class="input-group" style="margin: 20px 0px 20px 0px;">
		<span style="min-width:120px; text-align:right" class="input-group-addon">Biztonsági cód</span>
		<span class="input-group-addon" style="padding:2px">
			<img style="vertical-align: middle;" alt="" src="SecurityImage/SecurityImage" />
		</span>
		<input type="text" class="form-control" value="" id="code-gdpr" name="code" placeholder="írd be az 5 karakteres biztonsági kódot" onkeyup="checkgdpr();" />
	</div>
	<?php }?>

	<div>
		<button type="submit" disabled="disabled" class="btn btn-warning" id="submit-gdpr">Adatvédelmi kérést végrehajt</button>
        <?php if (isUserAdmin()) {?>
            <button type="button" class="btn btn-danger" onclick="setGdpr();" >Adatvédelmi kérést leszögez</button>
        <?php } ?>
	</div>

	<div style="margin-top: 20px;">
		<b>Fontos:</b> Kérjük a megadott e-mail címre küldött levélben a linket megkattintani, a kérvényezett folyamat csak ezután lessz végrehajtva. A link csak 2 napig érvényes. Örvendünk mert tudtunk segíteni a személyes adatok védelméért.      
	</div>
</div>
<input type="hidden" name="id" value="<?php echo getAktUserId()?>" />
</form>
<?php 
Appl::addJsScript("
	$( document ).ready(function() {
		checkgdpr();
	});
	function checkgdpr() {
		validateEmailInput('email-gdpr','');
		if ($('#code-gdpr').length>0) {
			if ($('#code-gdpr').val().length<5) {
				$('#submit-gdpr').attr('disabled',true);
				document.getElementById('code-gdpr').style.borderColor='red';
				return false;
			} else {
				document.getElementById('code-gdpr').style.borderColor='green';
			}
		}
		if ($('input[name=action]:checked').val() == null) {
			$('#submit-gdpr').attr('disabled',true);
			return false;
		}
		$('#submit-gdpr').attr('disabled',!validateEmailInput('email-gdpr',''));
	}
		
	function validateEmailInput(sender) { 
    	if (validateEmail(document.getElementById(sender).value)) {
    		document.getElementById(sender).style.borderColor='green';
			return true;
    	} else {
    		document.getElementById(sender).style.borderColor='red';
			return false;
    	}
  	} 

	function validateEmail(mail) {
	   	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	   	return re.test(mail);
	}
	
	function setGdpr() {
	    var gdpr=$('input[name=\"action\"]:checked').val();
	    switch(gdpr) {
          case 'deletesocialnetwork': gdpr=1; break;
          case 'deleteaddresses': gdpr=2; break;
          case 'deletebutname': gdpr=3; break;
          case 'deletepictures': gdpr=4; break;
          case 'deleteall': gdpr=5; break;
          case 'onlyschoolmates': gdpr=6; break;
          case 'onlyclassmates': gdpr=7; break;
          case 'gdprok': gdpr=100; break;
          default: gdpr=0;
        }
	    location.href='gdpr?id=".getParam("id")."&action=setGdpr&gdpr='+gdpr;
	}
		
");
?>
<?php include_once("homefooter.inc.php");?>
