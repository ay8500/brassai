<?PHP
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include 'message.inc.php';

use \maierlabs\lpfw\Appl as Appl;
global $db;
global $userDB;

\maierlabs\lpfw\Appl::addCss('editor/ui/trumbowyg.min.css');
\maierlabs\lpfw\Appl::addJs('editor/trumbowyg.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/specialchars/ui/trumbowyg.specialchars.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/specialchars/trumbowyg.specialchars.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/table/ui/trumbowyg.table.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/table/trumbowyg.table.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/colors/ui/trumbowyg.colors.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/colors/trumbowyg.colors.min.js');
\maierlabs\lpfw\Appl::addCss('editor/plugins/emoji/ui/trumbowyg.emoji.min.css');
\maierlabs\lpfw\Appl::addJs('editor/plugins/emoji/trumbowyg.emoji.js');
\maierlabs\lpfw\Appl::addJs('editor/plugins/pasteimage/trumbowyg.pasteimage.min.js');
\maierlabs\lpfw\Appl::addJs('editor/langs/hu.min.js');
\maierlabs\lpfw\Appl::addJsScript("
	$( document ).ready(function() {
		$('#story').trumbowyg({
			fullscreenable: false,
			closable: false,
			lang: 'hu',
			btns: [
                ['undo', 'redo'],
                ['formatting'],
                ['strong', 'em'],
                ['superscript', 'subscript'],
                ['link','insertImage','table','emoji'],
                ['justifyLeft', 'justifyCenter', 'justifyRight'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['specialChars'],
			    ['foreColor', 'backColor'],['viewHTML']
			],
			removeformatPasted: true,
			imageWidthModalEdit: true,
			autogrow: true
		});
	});
");

$paramName=getParam("name", "");
$paramText=getParam("T", "");

if (isActionParam("postMessage")) {
	if (isUserLoggedOn()) {
		if (checkMessageContent($paramText)->ok) {
			if (writeMessage($db,$paramText, getParam("privacy"), getLoggedInUserName($userDB))>=0) {
                Appl::setMessage("A beadott üzenet elküldése sikerült","success");
				$paramName="";
				$paramText="";
			} else {
                Appl::setMessage("A beadott üzenet kimentése nem sikerült!","warning");
			}
		} else {
            Appl::setMessage("A beadott üzenet úgytűnik nem tartalmaz érthető magyar szöveget! <br/> Probálkozz rövidítések nélkül vagy írj egy kicsitt bővebben. <br/> Azért kell a szöveget ellenőrizni, hogy ne lehessen az oldalt reklám céljával visszaélésre használni. ", "info");
        }
	}
	else {
		if (strlen($paramName)<4) {
            Appl::setMessage("Írd be család és keresztneved!","warning");
		}
		else { 
			if (checkMessageContent($paramText)->ok) {
				if ($db->checkRequesterIP(changeType::message)) {
					if (writeMessage($db,$paramText, getParam("privacy"), getParam("name"))>=0) {
                        $db->saveRequest(changeType::message);
						Appl::setMessage('A beadott üzenet elküldése sikerült!',"success");
						$paramName="";
						$paramText="";
					} else {
                        Appl::setMessage("A beadott üzenet kimentése nem sikerült!","warning");
					}
				} else {
                    Appl::setMessage("Az anonym üzenetek küldése csak egy bizonyos mértékben lehetséges!<br />Kérünk jelentkezz be ha szeretnél újjabb üzenetet küldeni.","warning");
				}
			}
			else
                Appl::setMessage("A beadott üzenet úgytűnik nem tartalmaz értelmes magyar szöveget! <br/> Probálkozz rövidítések nélkül és írj egy kicsitt bővebben.","warning");
		} 
	}
}

if (isActionParam("checkMessage")) {
    error_reporting(~E_ALL );
    $checkMsg=checkMessageContent($paramText);
    $text = 'A beadott üzenet tartalmaz-e magyar értelmes szöveget? ';
    $text .="Szavak:".$checkMsg->words." Magyar szavak:".$checkMsg->count." Eredmény: ";
    $text .=$checkMsg->ok?"rendben":"nem, probálkozz újból";
    Appl::setMessage($text,$checkMsg->ok?"success":"warning");
}


if (isActionParam("deactivateMessage")) {
	$id=getIntParam("id",-1);
	if ($id!=-1) {
		if (deactivateMessage($db,$id))
            Appl::setMessage("Az üzenet ki lett törölve!","success");
		else
            Appl::setMessage("Az üzenet törlése nem sikerült!","warning");
	}
}

if (isActionParam("deleteMessage") && isUserAdmin()) {
    $id=getIntParam("id",-1);
    if ($id!=-1) {
        if (deleteMessage($db,$id))
            Appl::setMessage("Az üzenet véglegesen ki lett törölve!","success");
        else
            Appl::setMessage("Az üzenet végleges törlése nem sikerült!","warning");
    }
}

if (isActionParam("commentMessage") && isUserAdmin()) {
	if ($db->saveMessageComment(getIntParam("id"),getParam("comment"))===true) {
        Appl::setMessage("A beadott kommentár elküldése sikerült.","success");
	} else {
        Appl::setMessage("A beadott kommentár kimentése nem sikerült!","warning");
	}
}

if (isActionParam("setPersonID") && isUserAdmin()) {
	if ($db->saveMessagePersonID(getIntParam("id"),getParam("personid"))===true) {
        Appl::setMessage("Személy csere sikerült.","success");
	} else {
        Appl::setMessage("Személy csere nem sikerült!","warning");
	}
}

Appl::setSiteTitle(getActSchoolName()." véndiákok üzenőfala",'Üzenőfal');
include("homemenu.inc.php");
?>

<div class="container-fluid">   
<div class="dropdown" style="margin-bottom: 10px;">
 	<button onclick="showMessage();" class="btn btn-default" type="button" >
	<span class="glyphicon glyphicon-pencil"></span>
 	Írj egy új üzenet! 
 	<span class="caret"></span>
  </button>
</div>
	<div id="message" style="margin-bottom: 10px;">
		<form method="post" >
			<?php if (!isUserLoggedOn()) {?>
                <div class="input-group">
                    <span style="min-width:120px; text-align:right" class="input-group-addon" id="basic-addon1">Név</span>
                    <input type="text" class="form-control input-lg" value="<?php echo($paramName)?>" name="name" placeholder="családnév keresztnév"/>
                </div>
			<?php } ?>
			<?php $text="~~" ?>
<div style="margin:1px"><textarea id="story" name="T" onchange="textChanged();"><?php echo(htmlspecialchars_decode($paramText));?></textarea></div>
			<?php if (isUserLoggedOn()) {?>
                <div class="radiogroup">
                    <div style="display: inline-block; padding:5px" >Ki láthatja<br /> ezt az üzenetet?</div>
                    <div title="Az egész világ" class="cradio radio_world"><input type="radio" name="privacy" value="world" <?php echo getFieldCheckedWord($text)?> onclick="saveMessage();" /></div>
                    <div title="Az iskolatársak" class="cradio radio_scool"><input type="radio" name="privacy" value="scool" <?php echo getFieldCheckedScool($text)?> onclick="saveMessage();" /></div>
                    <div title="Az osztálytársak" class="cradio radio_class"><input type="radio" name="privacy" value="class" <?php echo getFieldCheckedClass($text)?> onclick="saveMessage();" /></div>
                </div>
			<?php } ?>
			<button value="postMessage" name="action" class="btn btn-success" type="submit" ><span class="glyphicon glyphicon-send"></span> küldés</button>
			<?php if (isUserAdmin()) {?>
				<button value="checkMessage" name="action" class="btn btn-info" type="submit" ><span class="glyphicon glyphicon-check"></span> magyar?</button>
			<?php } ?>
		</form>
		<form method="get" id="deleteForm">
			<input type="hidden" name="id" id="deleteId"/>
			<input type="hidden" name="action" value="deleteMessage"/>
		</form>
	</div>		

<?php displayMessageList(20); ?>
	
</div>
<script type="text/javascript">
	var messageVisible=false;
	function showMessage() {
		if (messageVisible) {
			$("#message").hide("slow");messageVisible=false;
		} else {
			$("#message").show("slow");messageVisible=true;
		}
    }

    function deleteMessage(id) {
        if (confirm("Szeretnéd az üzenetet véglegesen törölni?")) {
            document.location.href="message?id="+id+"&action=deleteMessage";
        }
    }
    function deactivateMessage(id) {
        if (confirm("Szeretnéd az üzenetet törölni?")) {
            document.location.href="message?id="+id+"&action=deactivateMessage";
        }
    }
</script>
<?php include 'homefooter.inc.php'; ?>
