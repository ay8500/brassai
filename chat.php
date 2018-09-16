<?php 
include_once("tools/userManager.php");
include_once("tools/appl.class.php");
include_once("data.php");
include_once 'tools/appl.class.php';

use maierlabs\lpfw\Appl as Appl;

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

Appl::$subTitle='Osztálytárs körlevek';
include("homemenu.php"); 
include_once 'chatinc.php';



if (getGetParam("sendAction")=="sendMail" && userIsLoggedOn()) {
	$mailsSent = 0;
	include_once ("sendMail.php");
	$persons = $db->getPersonListByClassId(getAktClassId());
	foreach ($persons as $person) {
		if (sendChatMail(getAktPerson(), $person, getGetParam("Text") )) {
			$mailsSent++;
		}
	}
	Appl::$resultDbOperation= "</div>Elküldött e-mailek száma:".$mailsSent."</div>";
}

$personList=$db->getPersonListByClassId(getRealId(getAktClass()),null,null,true);
$messageList=$db->getClassMessages(getAktClassId());
if(sizeof($messageList)==0) {
	$message=array();
	$message["changeDate"]=date("Y.m.d H:i:s");
	$message["changeUserID"]=834;
	$message["text"] ="Kedves véndiákok,<br/><br/>ennek az ostálynak még nincsenek körlevelei.";
	$message["text"].="Az itt fogalmazott üzenetek az összes osztálytársnak akiknek ismert az email címe el leszz küldve.";
	$message["text"].="<br/><br/>Üdvözlettel System Administrator";
	array_push($messageList, $message);
}

?>
<div class="container-fluid">   
	<?php showChatEnterfields($personList); ?>
	<div>
		<?php foreach ($messageList as $message) {
			$person=$db->getPersonByID($message["changeUserID"]);
			?>
			<div style="display: inline-block; margin-top: 10px; margin-bottom: -1px; background-color: #e8e8e8; padding: 5px; border-left: solid 1px black;border-radius: 5px 5px 0px 0px;border-top: solid 1px black;border-right: solid 1px black;">
				<span style="width: 36px;display: inline-block;"><img src="images/<?php echo $person["picture"] ?>" class="diak_image_sicon" style="margin:2px;"/></span>
				<span style="width: 146px;display: inline-block;"><?php echo $person["lastname"]." ".$person["firstname"]?></span>
   				<span style="width: 200px;display: inline-block;">Dátum:<?php echo date("Y.m.d H:i:s",strtotime($message["changeDate"]));?></span>
   			</div>
   			<div style="padding: 10px;border-radius: 0px 5px 5px 5px;border: solid 1px black;">
				<?php echo $message["text"] ?>
			</div>
		<?php }?>
	</div>
</div>
<?php include 'homefooter.php'; ?>
<script type="text/javascript">
	$( document ).ready(function() {
		//showMessage();			
	});
</script>
