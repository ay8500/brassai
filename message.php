<?PHP 
$diakEditStorys=true;
include("homemenu.php"); 
include_once("data.php");
include_once("userManager.php");
include 'postmessage.php';

$error=null;
if (isset($_GET["action"]) && ($_GET["action"]=="postMessage")) {
	if (userIsLoggedOn()) {
		writeMessage(getParam("T"), getParam("privacy"), null);
	}
	else {
		if (null==getParam("name") || strlen(getParam("name"))<3) {
			$error="Írd be család és keresztneved!";
		} else if (getParam("code", "")!=$_SESSION['SECURITY_CODE']) {
			$error="Biztonsági kód nem helyes. Probálkozz újból!";
		}
		else {
			writeMessage(getParam("T"), getParam("privacy"), getParam("name"));
		}
	}
}

if (isset($_GET["action"]) && ($_GET["action"]=="deleteMessage")) {
	$id=getIntParam("id",-1);
	if ($id!=-1) {
		deleteMessage($id);
	}
}
?>

<div class="container-fluid">   
<h2 class="sub_title" >Üzenöfal</h2>
<?php if (null!=$error){?>
	<div class="alert alert-warning" role="alert">
		<?php echo($error);?>
	</div>
<?php } ?>
<div class="dropdown" style="margin-bottom: 10px;">
 	<button onclick="showMessage();" class="btn btn-default" type="button" >
	<span class="glyphicon glyphicon-pencil"></span>
 	Új üzenet 
 	<span class="caret"></span>
  </button>
</div>
	<div id="message" style="display: none; margin-bottom: 10px;">
		<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" >
			<?php if (!userIsLoggedOn()) {?>
			<div class="input-group">
				<span style="min-width:120px; text-align:right" class="input-group-addon" id="basic-addon1">Név</span>
				<input type="text" class="form-control input-lg" value="<?php echo(getParam("name", ""))?>" name="name" placeholder="családnév keresztnév"/>
			</div>
			<div class="input-group">
				<span style="min-width:120px; text-align:right" class="input-group-addon" id="basic-addon1"><img style="vertical-align: middle;" alt="" src="SecurityImage/SecurityImage.php" /></span>
				<input type="text" class="form-control input-lg" value="" name="code" placeholder="biztonságí kód" />
			</div>
			<?php } ?>
			<?php $text="~" ?>
			<textarea id="story" name="T" onchange="textChanged();" ><?php echo(getParam("T", ""));?></textarea>
			<?php if (userIsLoggedOn()) {?>
			<div class="radiogroup">
				<div style="display: inline-block; padding:5px" >Ki láthatja<br /> ezt az üzenetet?</div>
				<div title="Az egész világ" class="cradio radio_world"><input type="radio" name="privacy" value="world" <?php echo getFieldCheckedWord($text)?> onclick="saveMessage();" /></div>
				<div title="Az iskolatársak" class="cradio radio_scool"><input type="radio" name="privacy" value="scool" <?php echo getFieldCheckedScool($text)?> onclick="saveMessage();" /></div>
				<div title="Az osztálytársak" class="cradio radio_class"><input type="radio" name="privacy" value="class" <?php echo getFieldCheckedClass($text)?> onclick="saveMessage();" /></div>
			</div> 
			<?php } ?>
			<button class="btn btn-default" type="submit" ><span class="glyphicon glyphicon-send"></span> küldés!</button>
			<input type="hidden" value="postMessage" name="action" />
		</form>
		<form action="<?PHP echo($SCRIPT_NAME);?>" method="get" id="deleteForm">
			<input type="hidden" name="id" id="deleteId"/>
			<input type="hidden" name="action" value="deleteMessage"/>
		</form>
	</div>		

<?PHP
if (userIsLoggedOn()) {
	$tabsCaption=Array("osztálytársaknak","iskolatársaknak","mindenkinek");
	include("tabs.php");
	echo(readMessageList(20,$tabOpen));
}
else 
	echo(readMessageList(20,2));
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
