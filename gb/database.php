<?php

$dom=new DOMDocument();
$xmlEntrys = null;
$dbEntrys=0;	//Entrys in the database
$dbMaxID=0;	//Maximal entry id		

initDB();

function initDB()
{
	global $dom;
	global $dbEntrys;	
	global $dbMaxID;
	global $xmlEntrys;

	$dom->load(CFileNameXmlData,LIBXML_DTDLOAD);
	$xmlEntrys = $dom->getElementsByTagName('entry');	

	$dbEntrys=0;	
	$dbMaxID=0;				
	foreach ($xmlEntrys as $xmlEntry) {
		//Count only not deleted entrys	
		if ($xmlEntry->getAttribute('status')!='d') {$dbEntrys++;}
		//Find out the max ID for new entrys
		if ($xmlEntry->getAttribute('id')>$dbMaxID) { $dbMaxID=$xmlEntry->getAttribute('id'); }
	}
}

function getNumberOfEntrys()
{
    global $dbEntrys;
    return $dbEntrys;
}

function getNextId()
{
    global $dbMaxID;
    return $dbMaxID+1;
}

function getEntryByIndex($idx)
{
    global $xmlEntrys ;
	$dbEntrys=0;
	foreach ($xmlEntrys as $xmlEntry) {
        if ($dbEntrys==$idx) { $aktEntry=$xmlEntry; }	
		if ($xmlEntry->getAttribute('status')!='d') //Ignore the deleted entrys
			$dbEntrys++;
	}
	return FillEntryArray($aktEntry);
}


function getEntryNodeById($id)
{
    global $xmlEntrys ;
	foreach ($xmlEntrys as $xmlEntry) {
        if ($xmlEntry-> getAttribute('id')==$id) { $aktEntry=$xmlEntry; }
		$dbEntrys++;
	}
	return $aktEntry;
}

function getEntryById($id)
{
	return FillEntryArray(getEntryNodeById($id));
}

function setEntrycommentById($id, $comment)
{
	global $dom;
	$aktNode = getEntryNodeById($id);
	$aktNode->setAttribute('comment',$comment);
	$dom->normalizeDocument();
	$dom->save(CFileNameXmlData);
}

function setEntryStatusById($id, $status)
{
	global $dom;
	$aktNode = getEntryNodeById($id);
	$aktNode->setAttribute('status',$status);
	$dom->normalizeDocument();
	$dom->save(CFileNameXmlData);
	initDB();
}

function FillEntryArray($aktEntry)
{
	if (isset($aktEntry)) 
	{
	$entry['id']	=$aktEntry-> getAttribute('id');
	$entry['name']	=$aktEntry-> getAttribute('name');
	$entry['city']	=$aktEntry-> getAttribute('city');
	$entry['date']	=$aktEntry-> getAttribute('date');
	$entry['mail']	=$aktEntry-> getAttribute('mail');
	$entry['www']	=$aktEntry-> getAttribute('www');
	$entry['text']	=toHtmlCode($aktEntry-> getAttribute('text'));
	$entry['ip']	=$aktEntry-> getAttribute('ip');
	$entry['status']=$aktEntry-> getAttribute('status');
	$entry['comment']=toHtmlCode($aktEntry-> getAttribute('comment'));
	}
	else
	{
	$entry['id']	='???';
	$entry['name']	='';
	$entry['city']	='';
	$entry['date']	='';
	$entry['mail']	='';
	$entry['www']	='';
	$entry['text']	='';
	$entry['ip']	='';
	$entry['status']='';
	$entry['comment']='';

	}
	return $entry;
}

function toHtmlCode($text)
{
	$xx   = array("\r\n", "\n", "\r");
	$replace = '<br />';
	//$replace = array('<br />','<br />','<br />');
	$rr = str_replace($xx,$replace,$text);
	return $rr;
}

function insertEntry($ID,$Name,$City,$Mail,$WWW,$Text)
{
	global $dom;
	$ddate = date('d.m.Y H:i:s');
	$new_item = $dom->createElement('entry');
	$new_item->setAttribute("id", $ID); 
	$new_item->setAttribute("date", $ddate  ); 
	$new_item->setAttribute("ip", $_SERVER['REMOTE_ADDR'] ); 
	$new_item->setAttribute("status", 'new' ); 
	$new_item->setAttribute("name", $Name); 
	$new_item->setAttribute("city", $City); 
	$new_item->setAttribute("mail", $Mail); 
	$new_item->setAttribute("www", $WWW); 
	$new_item->setAttribute("text", htmlspecialchars($Text)); 
	$dom->documentElement -> appendChild($new_item);
	$dom->normalizeDocument();
	$dom->save(CFileNameXmlData);
	initDB();
}

?>