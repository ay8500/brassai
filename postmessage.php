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
			if (null!=$diak && isset($diak["picture"])) {
				$ret .= '<img src="images/'.$diak["picture"].'" style="height:40px; border-radius:5px;margin:2px" />';
			} else { 
				$ret .= '<img src="images/avatar.jpg" style="height:40px; border-radius:5px;;margin:2px"  />';
			}
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
			$ret .= '<div style="margin:10px">';
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
			//Show IP
			if (userIsAdmin()) {
				$ret .= '<span class="cmessage" >'.$message["changeIP"].'</span>';
			}
			//Delete button
			if ($message["changeIP"]==$_SERVER["REMOTE_ADDR"] || userIsAdmin() ||
				(isset($message["changeUserID"]) && $message["changeUserID"]!=1) &&
				$message["changeUserID"]==getLoggedInUserId() )
				if ($message["isDeleted"]==0) {
					$ret .= '<button class="btn btn-danger" onclick="deleteMessage('.$message["id"].')" >Kitöröl</button>';
			}
			if (userIsAdmin()) {
				$ret .= '<span><form><input type="hidden" name="id" value="'.$message['id'].'">';
				$ret .= ' Komentár:<input name="comment" class="form-control" style="width:300px;display:inline-block;">';
				$ret .= ' <button class="btn btn-warning" name="action" value="commentMessage">Kiment</button>';
				$ret .= '</form></span>';
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
 * @param unknown $message
 */
function checkMessageContent($message) {
	$msg = " ".strtolower(strip_tags($message))." ";
	$rr = array("-",":",",",".","(",")","?","!");
	$msg = str_replace($rr, " ", $msg);
	$whiteList = array(	"lessz ", " volt "," rossz "," hogy "," az "," ez "," azt "," ezt "," ezzel "," azzal "," ahoz ","itt ", "ott ",
						" igen "," nem ", "akkor ", " csak ", "szia ","sziasztok", " puszi ", "kellemes ",
						"nnepek",  "boldog ", "csony", "hus", "egy ","minden","senki" );
	$count = 0;
	foreach ($whiteList as $s) {
		$count += substr_count($msg, $s);
	}
	if ($count==0)
		return false;
	return (($count+0.01)/strlen($msg) > 1/1000);
}

?>