<?php
// The text version of the database

$dbEntrys=0;	//Entrys in the database
$dbMaxID=0;		//Maximal entry id		
$Entrys = null;

initDB();

function initDB()
{
	global $dbEntrys;	
	global $Entrys;	
	global $dbMaxID;

	$file=fopen(CFileNameXmlData,"r");
	$i = 0;
	while(!feof($file))
	{
		$Lines[$i++] = fgets($file);
	}
	fclose($file);


	$dbEntrys=0;	
	$dbMaxID=0;				
	foreach ($Lines as $line) {
	  $beg=strpos($line,'<entry '); $end=strpos($line,'/>');
	  if (($beg>=0) && ($end>10) && !(strpos($line,'status="d"')))
	  {
		$Entrys[$dbEntrys] =  $line; 
		$dbEntrys++;
		$maxID = getStrAttribute($line,"id");
		if ($maxID > $dbMaxID) { $dbMaxID=$maxID;}
	  }
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
	global $Entrys;	
	return FillEntryArray($Entrys[$idx]);
}


function getEntryNodeById($id)
{
	global $Entrys;
	$i=0;
	foreach ($Entrys as $Entry)
	{
		$aktId=getStrAttribute($Entry,"id");
		if ($aktId==$id) return $i;
		$i++;
	}
	return -1;
}

function getEntryById($id)
{
	global $Entrys;
	if ($id>=0)
	{
		return FillEntryArray($Entrys[getEntryNodeById($id)]);
	}
	else
	{
		return FillEntryArray('name="Error entry not found"');
	}
}

function setEntrycommentById($id, $comment)
{
	global $Entrys;
	$comment=toHTMLCode(myhtmlspecialchars($comment));
	$idx=getEntryNodeById($id);
	$Entrys[$idx] = setStrAttribute($Entrys[$idx],'comment',$comment);
	save();initDB();
}

function setEntryStatusById($id, $status)
{
	global $Entrys;
	global $admin;
	$idx=getEntryNodeById($id);
	if ((getStrAttribute($Entrys[$idx],'ip') == $_SERVER['REMOTE_ADDR']) || ($admin))
	{
		$Entrys[$idx] = setStrAttribute($Entrys[$idx],'status',$status);
	}
	save();initDB();
}

function FillEntryArray($aktEntry)
{
	$entry['id']	=getStrAttribute($aktEntry,"id");
	$entry['name']	=getStrAttribute($aktEntry,"name");
	$entry['city']	=getStrAttribute($aktEntry,"city");
	$entry['date']	=getStrAttribute($aktEntry,"date");
	$entry['mail']	=getStrAttribute($aktEntry,"mail");
	$entry['www']	=getStrAttribute($aktEntry,"www");
	$entry['text']	=toHtmlCode(getStrAttribute($aktEntry,"text"));
	$entry['ip']	=getStrAttribute($aktEntry,"ip");
	$entry['status']=getStrAttribute($aktEntry,"status");
	$entry['comment']=toHtmlCode(getStrAttribute($aktEntry,"comment"));
	return $entry;
}

function toHtmlCode($text)
{
	$xx   = array("\r\n", "\n", "\r","&#13;&#10;","&#13;","&#10;");
	$replace = '<br />';
	$rr = str_replace($xx,$replace,$text);
	$rr = str_replace('\\'," ",$rr);
	return $rr;
}

function myhtmlspecialchars( $string )
{
  $string = str_replace ( '&'	,'&amp;'	,$string );
  $string = str_replace ( '\''	,'&#039;'	,$string );
  $string = str_replace ( '"'	,'&quot;'	,$string );
  $string = str_replace ( '<'	,'&lt;'		,$string );
  $string = str_replace ( '>'	,'&gt;'		,$string );
  $string = str_replace ( 'ü',	'&uuml;'	,$string );
  $string = str_replace ( 'Ü',	'&Uuml;'	,$string );
  $string = str_replace ( 'ä',	'&auml;'	,$string );
  $string = str_replace ( 'Ä',	'&Auml;'	,$string );
  $string = str_replace ( 'ö',	'&ouml;'	,$string );
  $string = str_replace ( 'Ö',	'&Ouml;'	,$string );    
  
  return $string;
} 

function insertEntry($ID,$Name,$City,$Mail,$WWW,$Text)
{
	global $Entrys;
	$Name=myhtmlspecialchars($Name);
	$City=myhtmlspecialchars($City);
	$Text=toHTMLCode(myhtmlspecialchars($Text));
	$ddate = date('d.m.Y H:i:s');
	$Entrys[sizeof($Entrys)+1]="\t".'<entry id="'.$ID.'" date="'.$ddate.'" ip="'.$_SERVER['REMOTE_ADDR'].'" status="new" name="'.$Name.'" city="'.$City.'" mail="'.$Mail.'" www="'.$WWW.'" text="'.$Text.'" comment="" />'."\r\n";
	save();initDB();
}

//Only for the text version

function Save()
{
	global $Entrys;
	$file=fopen(CFileNameXmlData,"w");
	fwrite($file,'<?xml version="1.0"?>'."\r\n");
	fwrite($file,"\r\n");
	fwrite($file,'<root>'."\r\n");
	foreach ($Entrys as $Entry) {
		fwrite($file,$Entry);
	}
	fwrite($file,'</root>'."\r\n");
	fclose($file);
}

function setStrAttribute($str,$attr,$value)
{
   $i=strpos($str,$attr.'="');
   if ($i>0) 
   {
      $e=strpos($str,'"',$i+strlen($attr)+2);
	  if ($e>=0) 
	  {
	    return substr($str,0,$i+strlen($attr)+2).$value.substr($str,$e,16000);
	  }
	  else return $str;
   }
   else // new attribute
   {
		//return substr($str,0,-4).' '.$attr.'="'.$value.'" />'."\r\n";
	}
}

function getStrAttribute($str,$attr)
{
   $i=strpos($str,$attr.'="');					//Position of Attribute
   if ($i>0) 
   {
      $e=strpos($str,'"',$i+strlen($attr)+2);	//Position of end quote
	  if ($e>=0) 
	  {
	    return substr($str,$i+strlen($attr)+2,$e-($i+strlen($attr)+2));
	  }
	  else return "";
   }
   else return "";
}


?>