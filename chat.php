<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/appl.class.php';
include_once 'tools/appl.class.php';
include_once 'dbBL.class.php';

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
$class = $db->getClassById($db->getLoggedInUserClassId());
if ($class!=null)
    setAktClass($class["id"]);


Appl::setSiteSubTitle('Osztálytárs körlevek '.getAktClassName());
include("homemenu.inc.php");
include_once 'chat.inc.php';



if (isActionParam("sendMessage") && userIsLoggedOn()) {
	$mailsSent = 0;
	include_once ("sendMail.php");
	$persons = $db->getPersonListByClassId(getAktClassId(),null,null,true);
    $senderPerson=$db->getPersonLogedOn();
	foreach ($persons as $person) {
		if (    isset($person["email"]) && strlen($person["email"])>8 &&
		        sendChatMail($senderPerson, $person, getParam("Text") )) {
			$mailsSent++;
		}
	}
	Appl::setMessage("Elküldött e-mailek száma:".$mailsSent,"success");
	$entry["classID"]=getAktClassId();
	$entry["text"]=htmlspecialchars_decode(urldecode(getParam("Text")));
	$db->saveNewMessage($entry);
}

$personList=$db->getPersonListByClassId(getRealId(getAktClass()),null,null,true);
$messageList=$db->getClassMessages(getAktClassId());
$administrator=$db->getAktSchoolAdminPerson();
if(!userIsLoggedOn()) {
    $message=array();
    $message["changeDate"]=date("Y-m-d H:i:s");
    $message["changeUserID"]=$administrator["id"];
    $message["text"] ="Kedves véndiák,<br/><br/>sajnos csak bejelentkezett diákok láthatják a körleveleket. ";
    $message["text"].="Használd a bejelenkezési lehetőségeket felhasználó névvel és jelszóval, vagy facebookon keresztül.";
    $message["text"].="<br/><br/>Üdvözlettel ".getPersonName($administrator);
    array_push($messageList, $message);
}

if(sizeof($messageList)==0) {
	$message=array();
	$message["changeDate"]=date("Y-m-d H:i:s");
	$message["changeUserID"]=$db->getAktSchoolAdminPerson()["id"];
	$message["text"] ="Kedves véndiákok,<br/><br/>ennek az osztálynak még nincsenek körlevelei. ";
	$message["text"].="Az itt fogalmazott üzenetek az összes osztálytársnak akiknek ismert az email címe el lessz küldve.";
	$message["text"].="<br/><br/>Üdvözlettel ".getPersonName($administrator);;
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
   				<span style="width: 200px;display: inline-block;">Dátum:<?php echo \maierlabs\lpfw\Appl::dateTimeAsStr($message["changeDate"]);?></span>
   			</div>
   			<div style="padding: 10px;border-radius: 0px 5px 5px 5px;border: solid 1px black;">
				<?php echo $message["text"] ?>
			</div>
		<?php }?>
	</div>
</div>
<?php include 'homefooter.inc.php'; ?>

