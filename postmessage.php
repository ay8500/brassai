<?php
include_once 'sendMail.php';

function readMessageList($elements, $pricacy) {
	$h=readMessage($elements,$pricacy);
	$ret="";
	foreach ($h as $message) {
		$diak=null;
		if (isset($message["uid"])) {
			$diak=getPerson($message["uid"],$message["scoolclass"].$message["scoolyear"]);
		}
		$ret .= '<div style="margin:4px; display:table-row">';
		$ret .= '<div style="float:left; border-style:solid; border-radius:5px; border-width:1px;   margin-right: 10px; ">';
		if (null!=$diak && isset($diak["picture"])) {
			$ret .= '<img src="images/'.$diak["picture"].'" style="height:40px; border-radius:5px;" />';
		} else { 
			$ret .= '<img src="images/avatar.jpg" style="height:40px; border-radius:5px;"  />';
		}
		$ret .= '<div style="display: inline-block;width: 118px;vertical-align: middle;">';
		if (isset($message["uid"])) {
			$ret .= '<a href="editDiak.php?uid='.$message["uid"].'&scoolYear='.$message["scoolyear"].'&scoolClass='.$message["scoolclass"].'" >';
			$ret .= $diak["lastname"]." ".$diak["firstname"]." ";
			if (isset($diak["birthname"]))
				$ret .='('.$diak["birthname"].") ";
			$ret .='</a>';		
		}
		else 
			$ret .= $message["name"];
		$ret .= '</div>'; 
		$ret .= '</div>'; 
		$ret .= '<div style="display:table;">';
		$ret .= html_entity_decode($message["text"]);
		if (isset($message["comment"]))
			$ret .= '<br /><b>Kommentár: </b>'.html_entity_decode($message["comment"]);
		$ret .= '</div>';
		$ret .= '</div><div style="width:100%; height:3px;"></div>';
		$ret .= '<div style="margin-bottom:25px; ">';
		$ret .= 'Datum:'.$message["date"]." ";
		if ($message["ip"]==$_SERVER["REMOTE_ADDR"] || 
			(isset($message["uid"]) &&
			$message["uid"]==getLoggedInUserId() &&
			$message["scoolyear"]==getUScoolYear() &&
			$message["scoolclass"]==getUScoolClass()))
			$ret .= '<button class="btn btn-default" onclick="deleteMessage('.$message["id"].')" >Kitöröl</button>';
		$ret .= '</div>'."\r\n";
	}
	return $ret;
}

function readMessage($elements,$privacy=2) {
	$ar_privacy = array ("class","scool","world");
	$ret = array();
	$file=fopen("data/message.json","r");
	while (!feof($file) && $elements>0) {
		$b = fgets($file);
		$json = json_decode($b,true);
		if(	$json["privacy"]==$ar_privacy[$privacy] &&
			( !isset($json["deleted"]) || $json["deleted"]!="true") &&
			(	($privacy==0 &&
			    $json["scoolyear"] == getUScoolYear() &&
			    $json["scoolclass"] == getUScoolClass() )  
			 || $privacy!=0)
			) 
		{
			array_push($ret, $json);
			$elements--;
		}
	}
	return $ret;
}

function getNextMessageId() {
	$file=fopen("data/message.json","r");
	while (!feof($file) ) {
		$b = fgets($file);
		$json = json_decode($b,true);
		return $json["id"]+1;
	}
	return 0;
}

function deleteMessage($id) {
	$ret = array();
	$filer=fopen("data/message.json","r");
	$filew=fopen("data/message.json".$id,"w");
	while (!feof($filer))  {
		$b = fgets($filer);
		if (strlen($b)>6) {
			$message = json_decode($b,true);
			if(	$message["id"]==$id ) {
				if ($message["ip"]==$_SERVER["REMOTE_ADDR"] || 
					(isset($message["uid"]) &&
					$message["uid"]==getLoggedInUserId() &&
					$message["scoolyear"]==getUScoolYear() &&
					$message["scoolclass"]==getUScoolClass()))
				{			
					$message["deleted"]="true";
			
				}
			}
			fwrite($filew, json_encode($message)."\r\n");
		}
	}
  	fclose($filer);fclose($filew);
  	unlink("data/message.json");
  	rename("data/message.json".$id, "data/message.json");
}


/**
 */
function writeMessage($text,$privacy,$name) {
	$message = array();
	$message["id"]=getNextMessageId();
	$message["ip"]=$_SERVER["REMOTE_ADDR"];
	$message["date"]=date('d.m.Y H:i');
	$message["text"]=$text;
	$message["privacy"]=$privacy;
	if (null!=getLoggedInUserId()) {
		$message["uid"]=getLoggedInUserId();
		$message["scoolyear"]=getUScoolYear();
		$message["scoolclass"]=getUScoolClass();
	}
	else {
		$message["name"]=$name;
		$message["privacy"]="world";
	}
	sendHtmlMail(null, $text, " Message");
	prepend($message);
	
}

function prepend($msg) {
  $string=json_encode($msg)."\r\n";
  $context = stream_context_create();
  $filename="data/message.json";
  $fp = fopen($filename, 'r', 1, $context);
  $tmpname = "data/".md5($string);
  file_put_contents($tmpname, $string);
  file_put_contents($tmpname, $fp, FILE_APPEND);
  fclose($fp);
  unlink($filename);
  rename($tmpname, $filename);
}

?>