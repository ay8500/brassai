<?PHP
include_once("tools/sessionManager.php");
include_once("tools/userManager.php");
include_once 'tools/appl.class.php';
include_once("data.php");
include 'postmessage.php';

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

$paramName=getParam("name", "");
$paramText=getParam("T", "");

if (isActionParam("postMessage")) {
	if (userIsLoggedOn()) {
		if (checkMessageContent($paramText)) {
			if (writeMessage($paramText, getParam("privacy"), getLoggedInUserName())>=0) {
				Appl::$resultDbOperation='<div class="alert alert-success" > A beadott üzenet elküldése sikerült!</div>';
				$paramName="";
				$paramText="";
			} else {
				Appl::$resultDbOperation='<div class="alert alert-warning" > A beadott üzenet kimentése nem sikerült!</div>';
			}
		} else 
			Appl::$resultDbOperation='<div class="alert alert-warning" > A beadott üzenet úgytűnik nem tartalmaz érthető magyar szöveget! <br/> Probálkozz rövidítések nélkül vagy írj egy kicsitt bővebben.</div>';
	}
	else {
		if (strlen($paramName)<4) {
			Appl::$resultDbOperation='<div class="alert alert-warning" >Írd be család és keresztneved!</div>';
		}
		else { 
			if (checkMessageContent($paramText)) {
				if (checkRequesterIP(changeType::message)) {
					if (writeMessage($paramText, getParam("privacy"), getParam("name"))>=0) {
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
                Appl::setMessage("A beadott üzenet úgytűnik nem tartalmaz érthező magyar szöveget! <br/> Probálkozz rövidítések nélkül vagy írj egy kicsitt bővebben.","warning");
		} 
	}
}

if (isActionParam("checkMessage")) {
    Appl::setMessage('A beadott üzenet tartalmaz-e magyar kifejezést? Eredmény:'.
        (checkMessageContent($paramText)?"igen, rendben":"nem, probálkozz újból"),checkMessageContent($paramText)?"success":"warning");
}


if (isActionParam("deleteMessage")) {
	$id=getIntParam("id",-1);
	if ($id!=-1) {
		if (deleteMessage($id)>=0)
            Appl::setMessage("Az üzenet ki lett törölve!","success");
		else
            Appl::setMessage("Az üzenet törlése nem sikerült!","warning");
	}
}

if (isActionParam("commentMessage") && userIsAdmin()) {
	if ($db->saveMessageComment(getIntParam("id"),getParam("comment"))===true) {
        Appl::setMessage("A beadott kommentár elküldése sikerült.","success");
	} else {
        Appl::setMessage("A beadott kommentár kimentése nem sikerült!","warning");
	}
}

if (isActionParam("setPersonID") && userIsAdmin()) {
	if ($db->saveMessagePersonID(getIntParam("id"),getParam("personid"))===true) {
        Appl::setMessage("Személy cserérés sikerült.","success");
	} else {
        Appl::setMessage("Személy csrélés nem sikerült!","warning");
	}
}

Appl::$subTitle='Üzenőfal';
include("homemenu.php"); 
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
		<form method="get" >
			<?php if (!userIsLoggedOn()) {?>
			<div class="input-group">
				<span style="min-width:120px; text-align:right" class="input-group-addon" id="basic-addon1">Név</span>
				<input type="text" class="form-control input-lg" value="<?php echo($paramName)?>" name="name" placeholder="családnév keresztnév"/>
			</div>
			<?php } ?>
			<?php $text="~" ?>
			<textarea id="story" name="T" onchange="textChanged();" ><?php echo($paramText);?></textarea>
			<?php if (userIsLoggedOn()) {?>
			<div class="radiogroup">
				<div style="display: inline-block; padding:5px" >Ki láthatja<br /> ezt az üzenetet?</div>
				<div title="Az egész világ" class="cradio radio_world"><input type="radio" name="privacy" value="world" <?php echo getFieldCheckedWord($text)?> onclick="saveMessage();" /></div>
				<div title="Az iskolatársak" class="cradio radio_scool"><input type="radio" name="privacy" value="scool" <?php echo getFieldCheckedScool($text)?> onclick="saveMessage();" /></div>
				<div title="Az osztálytársak" class="cradio radio_class"><input type="radio" name="privacy" value="class" <?php echo getFieldCheckedClass($text)?> onclick="saveMessage();" /></div>
			</div> 
			<?php } ?>
			<button value="postMessage" name="action" class="btn btn-success" type="submit" ><span class="glyphicon glyphicon-send"></span> küldés!</button>
			<?php if (userIsAdmin()) {?>
				<button value="checkMessage" name="action" class="btn btn-info" type="submit" ><span class="glyphicon glyphicon-check"></span> magyar?</button>
			<?php } ?>
		</form>
		<form method="get" id="deleteForm">
			<input type="hidden" name="id" id="deleteId"/>
			<input type="hidden" name="action" value="deleteMessage"/>
		</form>
	</div>		

<?PHP
	echo(readMessageList(100));
?>
	
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
            $("#deleteId").val(id);
            $("#deleteForm").submit();
        }
    }
</script>
<?php include 'homefooter.php'; ?>
