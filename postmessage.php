<?php
include_once 'sendMail.php';

/**
 * Read the messages and return it as a html list
 * @param integer $elements
 * @return string
 */
function readMessageList($elements) {
	global $db;
	$h=$db->getMessages($elements);
	$ret="";
	foreach ($h as $message) {
		$diak=null;
		if (isset($message["changeUserID"]) && $message["changeUserID"]>=0) {
			$diak=$db->getPersonByID($message["changeUserID"]);
		}
		if	(	userIsAdmin() ||  	//Admin
				($diak!=null && $diak["classID"]==getLoggedInUserClassId()) || //User
				($message["isDeleted"]==0 &&  
				$message["privacy"]=="world" && 
				(isset($message["changeUserID"]) || (!isset($message["changeUserID"]) && $message["changeIP"]==$_SERVER["REMOTE_ADDR"]))
				)
			) {
			if($message["isDeleted"]==0)
				$ret .= '<div style="border-style:solid; border-radius:5px; border-width:1px; background-color:#f2f2f2">';
			else 
				$ret .= '<div style="border-style:solid; border-radius:5px; border-width:1px; background-color:#fff0f0">';
				$ret .= '<img src="'.getPersonPicture($diak).'" style="height:40px; border-radius:5px;margin:2px" />';
			$ret .= '<div style="display: inline-block;vertical-align: bottom;margin-left:5px">';
			if (isset($message["name"]) && strlen($message["name"])>3) {
				$ret .=$message["name"];
			} else if (isset($message["changeUserID"]) && $message["changeUserID"]!=1 && $message["changeUserID"]!=-1) {
				$ret .= '<a href="editDiak.php?uid='.$message["changeUserID"].'" >';
				$ret .= getPersonName($diak);
				$ret .='</a>';		
			} else {
				$ret .= "Anonim felhasználó" ;
			}
			$ret .= '</div>'; 
			$ret .= '<div style="margin:10px 0px 10px 0px">';
				$ret .= '<div class="message_text">'.html_entity_decode($message["text"]).'</div>';
			if (isset($message["comment"]))
				$ret .= '<br /><b>Kommentár: </b>'.html_entity_decode($message["comment"]);
				$ret .= '<div style="margin-bottom:-5px; ">';
				$ret .= 'Datum:'.$message["changeDate"]." ";
			//Privacy
			if ($message["privacy"]=="world")
				$ret .= '<span class="cmessage message_world">Ezt az üzenetet mindenki látja.</span>';
			if ($message["privacy"]=="class")
				$ret .= '<span class="cmessage message_class">Ezt az üzenetet csak az én osztálytársaim tekinthetik meg.</span>';
			if ($message["privacy"]=="scool")
				$ret .= '<span class="cmessage message_scool">Ezt az üzenetet csak a iskolatársaim tekinthetik meg.</span>';
			//Delete button
			if ($message["changeIP"]==$_SERVER["REMOTE_ADDR"] || userIsAdmin() ||
				(isset($message["changeUserID"]) && $message["changeUserID"]!=1) &&
				$message["changeUserID"]==getLoggedInUserId() )
				if ($message["isDeleted"]==0) {
					$ret .= '<button class="btn btn-danger" onclick="deleteMessage('.$message["id"].')" >Kitöröl</button>';
			}
			$ret .= "\n";
			if (userIsAdmin()) {
				$ret .= '<span><form><input type="hidden" name="id" value="'.$message['id'].'"/>';
				$ret .= ' Komentár:<input name="comment" class="form-control" style="width:300px;display:inline-block;"/>';
				$ret .= ' <button class="btn btn-warning" name="action" value="commentMessage">Kiment</button>';
				$ret .= "</form></span>\n";
				$ret .= '<span><form><input type="hidden" name="id" value="'.$message['id'].'"/>';
				$ret .= ' Személy ID:<input name="personid" class="form-control" style="width:80px;display:inline-block;" value="'.(isset($message["changeUserID"])?$message["changeUserID"]:'').'"/>';
				$ret .= ' <button class="btn btn-warning" name="action" value="setPersonID">Kiment</button>';
				$ret .= "</form></span>\n\n";
			}
			$ret .= '</div>';
			$ret .= '</div>';
			$ret .= '</div><div style="width:100%; height:10px;"></div>'."\r\n";
		}	
	}
	return $ret;
}


/**
 * Delete one message from the message json file
 * @param unknown $id
 */
function deleteMessage($id) {
	global $db;
	$message = $db->getMessage($id);
	if ($message==null)
		return -1;
	if (userIsAdmin() || 
		(userIsLoggedOn() && $message["changeUserID"]==getLoggedInUserId()) ||
		$message["changeIP"]==$_SERVER["REMOTE_ADDR"] ) {
			
		return $db->setMessageAsDeleted($id);
	}
	return -1;
	
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
	if (getLoggedInUserId()==-1) {
		$message["name"]=$name;
		$message["privacy"]="world";
	}
	if (!userIsAdmin())
		sendHtmlMail(null, $text, " Message");
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