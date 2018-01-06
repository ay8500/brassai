<?PHP 
$diakEditStorys=true;
include("homemenu.php"); 
include_once("data.php");
include_once("tools/userManager.php");
include 'postmessage.php';

$resultDBoperation="";
$paramName=getParam("name", "");
$paramText=getParam("T", "");

if (isset($_GET["action"]) && ($_GET["action"]=="postMessage")) {
	if (userIsLoggedOn()) {
		if (checkMessageContent($paramText)) {
			if (writeMessage($paramText, getParam("privacy"), getLoggedInUserName())>=0) {
				$resultDBoperation='<div class="alert alert-success" > A beadott üzenet elküldése sikerült!</div>';
				$paramName="";
				$paramText="";
			} else {
				$resultDBoperation='<div class="alert alert-warning" > A beadott üzenet kimentése nem sikerült!</div>';
			}
		} else 
			$resultDBoperation='<div class="alert alert-warning" > A beadott üzenet úgytűnik nem tartalmaz érthező magyar szöveget! <br/> Probálkozz rövidítések nélkül vagy írj egy kicsitt bővebben.</div>';
	}
	else {
		if (strlen($paramName)<4) {
			$resultDBoperation='<div class="alert alert-warning" >Írd be család és keresztneved!</div>';
		}
		else { 
			if (checkMessageContent($paramText)) {
				if (checkRequesterIP(changeType::message)) {
					if (writeMessage($paramText, getParam("privacy"), getParam("name"))>=0) {
						$resultDBoperation='<div class="alert alert-success" > A beadott üzenet elküldése sikerült!</div>';
						$paramName="";
						$paramText="";
					} else {
						$resultDBoperation='<div class="alert alert-warning" > A beadott üzenet kimentése nem sikerült!</div>';
					}
				} else {
					$resultDBoperation='<div class="alert alert-warning" > Az anonym üzenetek küldése csak egy bizonyos mértékben lehetséges!<br />Kérünk jelentkezz be ha szeretnél újjabb üzenetet küldeni.</div>';
				}
			}
			else 
				$resultDBoperation='<div class="alert alert-warning" > A beadott üzenet úgytűnik nem tartalmaz érthező magyar szöveget! <br/> Probálkozz rövidítések nélkül vagy írj egy kicsitt bővebben.</div>';
		} 
	}
}

if (isset($_GET["action"]) && ($_GET["action"]=="checkMessage")) {
	$resultDBoperation='<div class="alert alert-warning" > A beadott üzenet tartalmaz '.
		checkMessageContent($paramText,true).
		' magyar kivejezést.Eredmény:'.
		(checkMessageContent($paramText)?"Ok":"nem jo").' </div>';
}


if (isset($_GET["action"]) && ($_GET["action"]=="deleteMessage")) {
	$id=getIntParam("id",-1);
	if ($id!=-1) {
		if (deleteMessage($id)>=0)
			$resultDBoperation='<div class="alert alert-success" > Az üzenet ki lett törölve!</div>';
		else
			$resultDBoperation='<div class="alert alert-warning" > Az üzenet törlése nem sikerült!</div>';
	}
}

if (isset($_GET["action"]) && ($_GET["action"]=="commentMessage")) {
	if ($db->saveMessageComment(getIntParam("id"),getGetParam("comment", ""))===true) {
		$resultDBoperation='<div class="alert alert-success" > A beadott kommentár elküldése sikerült!</div>';
	} else {
		$resultDBoperation='<div class="alert alert-warning" > A beadott kommentár kimentése nem sikerült!</div>';
	}
}
?>

<div class="container-fluid">   
<h2 class="sub_title" >Üzenőfal</h2>
<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
<div class="dropdown" style="margin-bottom: 10px;">
 	<button onclick="showMessage();" class="btn btn-default" type="button" >
	<span class="glyphicon glyphicon-pencil"></span>
 	Írj egy új üzenet! 
 	<span class="caret"></span>
  </button>
</div>
	<div id="message" style="margin-bottom: 10px;">
		<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" >
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
			<button value="postMessage" name="action" class="btn btn-default" type="submit" ><span class="glyphicon glyphicon-send"></span> küldés!</button>
			<?php if (userIsAdmin()) {?>
				<button value="checkMessage" name="action" class="btn btn-info" type="submit" ><span class="glyphicon glyphicon-check"></span> magyar?</button>
			<?php } ?>
		</form>
		<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" id="deleteForm">
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
