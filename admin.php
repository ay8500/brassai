<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';

use \maierlabs\lpfw\Appl as Appl;

Appl::addCss('editor/ui/trumbowyg.min.css');
Appl::addJs('editor/trumbowyg.min.js');
Appl::addJs('editor/langs/hu.min.js');
Appl::addJsScript("
	$( document ).ready(function() {
		$('#story').trumbowyg({
			fullscreenable: false,
			closable: false,
			lang: 'hu',
			btns: ['formatting','btnGrp-design','|', 'link', 'insertImage','btnGrp-lists'],
			removeformatPasted: true,
			autogrow: true
		});
	});
");

if (isActionParam("sendMail")) {
	if (userIsEditor() || userIsSuperuser()) {
		include_once ("sendMail.php");
		$persons = $db->getPersonListByClassId(getAktClassId());
		$mailsSent =0;$mailsError =0;
		foreach ($persons as $person) {
			$uid=$person["id"];
			if (isset($_GET["D".$uid])) {
			    $sender=getFieldValue($db->getPersonByID(getLoggedInUserId()),"email");
                if (sendMailToPerson($uid, $_GET["T"], isset($_GET["U"]),$sender)) {
                    $mailsSent ++;
                } else {
                    $mailsError++;
                }
			}
		}
		if ($mailsSent>0)
		    Appl::setMessage("Elküldött e-mailek száma:".$mailsSent,"success");
        if ($mailsError>0)
            Appl::setMessage("Hibás e-mailek küldések száma:".$mailsError,"warning");
	}
}

Appl::setSiteSubTitle('Adminisztráció');
include("homemenu.inc.php");

?>

<div class="container-fluid">   

<?php if (userIsSuperuser() || userIsEditor() ) {
    //initialise tabs
    $tabsCaption = array();
    array_push($tabsCaption ,array("id" => "mail", "caption" => 'Mail&nbsp;küldés', "glyphicon" => "envelope"));
    array_push($tabsCaption ,array("id" => "user", "caption" => 'Diákok&nbsp;táblázatai', "glyphicon" => "user"));
    array_push($tabsCaption ,array("id" => "admin", "caption" => 'Administrátorok', "glyphicon" => "tower"));

    include Config::$lpfw.'view/tabs.inc.php';

	if ($tabOpen=="mail") {?>
	<form method="get" name="mail">
		<textarea id="story" name="T" style="width:95%;height:300px" wrap="off" onchange="fieldChanged();">
<b>Kedves %%name%%</b><br/>
<p>
Ide kell írni a szöveget....
</p>
<p>
Üdvözlettel <?php $dd=$db->getPersonLogedOn(); echo($dd["lastname"]." ".$dd["firstname"]); ?>
</p>
		</textarea>
		<input type="checkbox" name="U"/> Bejelentkezési adatokat is elküld.<br/>
		<button class="btn btn-default" type="submit" ><span class="glyphicon glyphicon-envelope"></span> E-Mail küldés!</button>
		<button type="button" class="btn btn-default" onclick="checkUncheckAll(true);"><span class="glyphicon glyphicon-check"></span> Mindenkit megjelöl</button>
		<button type="button" class="btn btn-default" onclick="checkUncheckAll(false);"><span class="glyphicon glyphicon-unchecked"></span> Megjelöléseket töröl</button>
		<p>
		<?php
		$persons = $db->getPersonListByClassId(getAktClassId());
		foreach ($persons as $d) {
				echo('<div style="display:inline-block; margin-right:10px">');
				if (isset($d["email"]) && strlen($d["email"])>2) 
					echo('<input type="checkbox" name="D'.$d["id"].'" checked />');
				echo($d["lastname"].'&nbsp;'.$d["firstname"].' ');
				echo("</div>");
			}
		?>
		</p>
		<input type="hidden" value="sendMail" name="action" />
		</form>
		
		<?php if (isset($sendMailMsg)) echo('<div style="text-align:center">'.$sendMailMsg.'</div>');?>
	<?php } ?>
	
	
	<?php if ($tabOpen=="user") { ?>
		<p style="text-align:center">
			<!---a href="getExcelData?data=Kontakt" target="excel">Kontaklista letöltése Excel formátumban</a>
			&nbsp;|&nbsp;-->
			<a href="getExcelData?data=All" target="excel">Összes adatok letöltése Excel formátumban</a>
		</p>
		<div>
		<table  class="table-sp" >
		<tr style="text-align:center;font-weight:bold;">
			<td style="min-width:160px">Név</td><td id="o1024" >Becenév</td><td>E-Mail</td><td id="o400">Telefon</td><td  id="o480">Mobiltelefon</td><td  id="o1024">Skype</td>
			<?php if(userIsAdmin()) {?>
				<td  id="o1024">IP</td>
			<?php }?>
			<td  id="o1024">Datum</td>
		</tr>
		
		<?php
		$persons = $db->getPersonListByClassId(getAktClassId());
		foreach ($persons as $l=>$d) {
			if (!isPersonGuest($d)) {
				if (($l % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top>".$d["lastname"].' '.$d["firstname"]."</font>".'</td><td  id="o1024">'.getFieldValue($d,"user")."</td><td>";
				echo "<a href=mailto:".getFieldValue($d,"email").">".getFieldValue($d,"email")."</a>";
				echo '<td  id="o400">'.getFieldValue($d,"phone").'</td><td  id="o480">'.getFieldValue($d,"mobil").'</td><td  id="o1024">'.getFieldValue($d,"skype").'</td>';
				if (userIsAdmin()) {
					echo '<td  id="o1024">'.getFieldValue($d,"ip").'</td>';
				}
				echo '<td  id="o1024">'.getFieldValue($d,"date")."</td>";
				echo "</tr>";
			}
		
		}
		echo("<tr><td>Vendégek, Tanárok</td></tr>");
		foreach ($persons as $d) {
			if (isPersonGuest($d)) {
				if (($l % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top>".$d["lastname"].' '.$d["firstname"]."</font>".'</td><td  id="o1024">'.getFieldValue($d,"user")."</td><td>";
				echo "<a href=mailto:".getFieldValue($d,"email").">".getFieldValue($d,"email")."</a>";
				echo '<td  id="o400">'.getFieldValue($d,"phone").'</td><td  id="o480">'.getFieldValue($d,"mobil").'</td><td  id="o1024">'.getFieldValue($d,"skype").'</td><td  id="o1024">'.getFieldValue($d,"ip").'</td><td  id="o1024">'.getFieldValue($d,"date")."</td>";
				echo "</tr>";
			}		
		}
		?>
		</table></div>
	<?php } ?>
	
	
	<?php if ($tabOpen=="admin") {?>
		<table class="table-sp"  >
		<tr style="text-align:center;font-weight:bold;"><td>Név</td><td>E-Mail</td><td id="o400">Telefon</td><td  id="o480">Mobiltelefon</td><td  id="o1024">Skype</td><td  id="o1024">IP</td><td  id="o1024">Datum</td></tr>
		<?php
		$persons = $db->getPersonListByClassId(getAktClassId());
		foreach ($persons as $idx=>$d) {
			if (isPersonAdmin($d) || isPersonEditor($d))  {
				if (($idx % 2) ==0) 
					echo '<tr style="background-color:#f8f8f8">';
				else
					echo '<tr style="background-color:#e8f0f0">';
				echo "<td valign=top align=right>".$d["lastname"].' '.$d["firstname"]."</font></td><td>";
				echo "<a href=mailto:".getFieldValue($d,"email").">".getFieldValue($d,"email")."</a>";
				echo '<td  id="o400">'.getFieldValue($d,"phone").'</td><td  id="o480">'.getFieldValue($d,"mobil").'</td><td  id="o1024">'.getFieldValue($d,"skype").'</td><td  id="o1024">'.getFieldValue($d,"ip").'</td><td  id="o1024">'.getFieldValue($d,"date")."</td>";
				echo "</td></tr>";
			}
		}
		?>
		</table>
	<?php } ?>
<?php } else {
    Appl::setMessage("Adat hozzáférési jog hiányzik!", "warning");
}
?>
</div>

<?php
Appl::addJsScript("
	function checkUncheckAll(state) {
		for(var z=0; z < document.mail.elements.length; z++) {
			if (document.mail.elements[z].type == 'checkbox') {
				if (document.mail.elements[z].name != 'U') 
					document.mail.elements[z].checked = state;
	  		}
     	}
    }
");
include 'homefooter.inc.php'; ?>
