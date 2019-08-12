<?php
include_once 'sendMail.php';

/**
 * display the message list
 * @param integer $elements
 * @return string
 */
function displayMessageList($elements, $offset=0) {
	global $db;
	$messageList=$db->getMessages($elements,$offset);
	$ret="";
	foreach ($messageList as $message) {
		$person=null;
		if (isset($message["changeUserID"]) && $message["changeUserID"]>=0) {
			$person=$db->getPersonByID($message["changeUserID"]);
		}
		if	(	userIsAdmin() ||  	//Admin
				($person!=null && $person["classID"]==$db->getLoggedInUserClassId()) || //User
				($message["isDeleted"]==0 &&  
				$message["privacy"]=="world" && 
				(isset($message["changeUserID"]) || (!isset($message["changeUserID"]) && $message["changeIP"]==$_SERVER["REMOTE_ADDR"]))
				)
			) {
		    displayMessage($message,$person);
		}
	}
	return $ret;
}

/**
 * @param $message
 * @param $person
 * @return void
 */
function displayMessage($message, $person) {
    if($message["isDeleted"]==0) {?>
        <div style="border-style:solid; border-radius:5px; border-width:1px; background-color:#f2f2f2">
    <?php } else { ?>
        <div style="border-style:solid; border-radius:5px; border-width:1px; background-color:#fff0f0">
    <?php }?>
    <img src="<?php echo getPersonPicture($person)?>" style="height:40px; border-radius:5px;margin:2px" />
    <div style="display: inline-block;vertical-align: bottom;margin-left:5px">
    <?php if (isset($message["name"]) && strlen($message["name"])>3) {
        echo $message["name"];
    } else if (isset($message["changeUserID"]) && $message["changeUserID"]!=1 && $message["changeUserID"]!=-1) {
        echo '<a href="editDiak.php?uid='.$message["changeUserID"].'" >'.getPersonName($person).'</a>';
    } else {
        echo "Anonim felhasználó";
    }?>
    </div>
    <div style="margin:10px 0px 10px 0px">
        <div class="message_text"><?php echo htmlspecialchars_decode(htmlspecialchars_decode(htmlspecialchars_decode($message["text"])))?></div>
        <?php if (isset($message["comment"])) {?>
            <br /><b>Kommentár: </b><?php echo htmlspecialchars_decode(htmlspecialchars_decode($message["comment"])); ?>
        <?php } ?>
        <div style="margin-bottom:-5px; ">
            <?php echo  'Datum:'.\maierlabs\lpfw\Appl::dateTimeAsStr($message["changeDate"])." "; ?>
            <?php //Privacy
            if ($message["privacy"]=="world")
                echo '<span class="cmessage message_world">Ezt az üzenetet mindenki látja.</span>';
            if ($message["privacy"]=="class")
                echo '<span class="cmessage message_class">Ezt az üzenetet csak az én osztálytársaim tekinthetik meg.</span>';
            if ($message["privacy"]=="scool")
                echo '<span class="cmessage message_scool">Ezt az üzenetet csak a iskolatársaim tekinthetik meg.</span>';
            //Delete button
            if ($message["changeIP"]==$_SERVER["REMOTE_ADDR"] || userIsAdmin() ||
                (isset($message["changeUserID"]) && $message["changeUserID"]!=1) &&
                $message["changeUserID"]==getLoggedInUserId() )
                if ($message["isDeleted"]==0) {
                    echo '<button class="btn btn-danger" onclick="deleteMessage('.$message["id"].')" >Kitöröl</button>';
                }
            if (userIsAdmin()) {?>
                <span>
                    <form><input type="hidden" name="id" value="<?php echo $message['id'] ?>"/>
                        Komentár:<input name="comment" class="form-control" style="width:300px;display:inline-block;"/>
                        <button class="btn btn-warning" name="action" value="commentMessage">Kiment</button>
                    </form>
                </span>
                <span>
                    <form><input type="hidden" name="id" value="'.$message['id'].'"/>
                        Személy ID:<input name="personid" class="form-control" style="width:80px;display:inline-block;" value="<?php echo (isset($message["changeUserID"])?$message["changeUserID"]:'')?>"/>
                        <button class="btn btn-warning" name="action" value="setPersonID">Kiment</button>
                    </form>
                </span>
            <?php }?>
        </div>
    </div>
    </div><div style="width:100%; height:10px;"></div>
<?php }

/**
 * Delete one message
 * @param integer $id
 * @return boolean
 */
function deleteMessage($id) {
	global $db;
	$message = $db->getMessage($id);
	if ($message==null)
		return false;
	if (userIsAdmin() || 
		(userIsLoggedOn() && $message["changeUserID"]==getLoggedInUserId()) ||
		$message["changeIP"]==$_SERVER["REMOTE_ADDR"] ) {
			
		return $db->setMessageAsDeleted($id);
	}
	return false;
	
}


/**
 * Write message 
 * @param unknown $text
 * @param unknown $privacy
 * @param unknown $name
 */
function writeMessage($text,$privacy,$name) {
	global $db;
	$message = array();
	$message["text"]=$text;
	$message["privacy"]=$privacy;
	$message["isDeleted"]=0;
	if (!userIsLoggedOn()) {
		$message["name"]=$name;
		$message["privacy"]="world";
	}
	if (!userIsAdmin())
        \maierlabs\lpfw\Appl::sendHtmlMail(null, $text, " Message");
	$db->saveRequest(changeType::message);
	return $db->saveNewMessage($message); 
}




/**
 * Check if the message is hungarian human
 * @param string $message
 * @param boolean $returnCount if true will return the count of hungarian words
 */
function checkMessageContent($message,$returnCount=false) {
	if (strlen($message)<10)
		if ($returnCount)
			return 0;
		else
			return false;
	$msg = " ".mb_strtolower(strip_tags($message))." ";
	$rr = array("/","=","-",":",",",".","(",")","?","!");
	$msg = str_replace($rr, " ", $msg);
	$whiteList = array(	"lessz ", " volt "," van "," rossz "," hogy "," az "," ez "," azt "," ezt "," ezzel "," azzal "," ahoz "," itt ", " ott "," de "," is ",
						" igen "," nem ", "akkor ", " csak ", "szia ","sziasztok", " puszi ", "kellemes ","nagyon","puszilok",
						"legyek", " aki ", "mikor", "honlap", "honoldal","vagyok","leszek"," vagy ",
						" én ",
						"ünnepek",  "boldog ", "karácsony", "husvét", "egy ","minden","senki","neked","fénykép" );
	$count = 0;
	foreach ($whiteList as $s) {
		$count += substr_count($msg, $s);
	}
	if ($returnCount) {
		return $count;
	} else {
		if ($count==0)
			return false;
		return ($count > strlen($msg) /70);
	}
}

?>