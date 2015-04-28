<?php
include_once "sendMail.php";
include_once "data.php";
//Database engime for song database

/**
 * Compare function for the interpret list
 */
function CompareInterpret($a, $b) 
{
    // Sortierung nach dem zweiten Wert des Array (Index: 1)
    $a = strtoupper($a['name']);
    $b = strtoupper($b['name']);
    if ($a == $b) {
         return 0;
    }
    return ($a < $b) ? -1 : +1;
}

/**
 * Compare function for the vote list
 */
function CompareVote($a, $b) 
{
    // Sortierung nach dem zweiten Wert des Array (Index: 1)
    $a = strtoupper($a['interpret']['name']).strtoupper($a['song']['name']);
    $b = strtoupper($b['interpret']['name']).strtoupper($b['song']['name']);
    if ($a == $b) {
         return 0;
    }
    return ($a < $b) ? -1 : +1;
}

/**
 * Compare function for the voters list
 */
function CompareVoters($a, $b) 
{
    $a = strtoupper($a['Name']);
    $b = strtoupper($b['Name']);
    if ($a == $b) {
         return 0;
    }
    return ($a < $b) ? -1 : +1;
}

function CompareTopList($a, $b) 
{
    // Sortierung nach dem zweiten Wert des Array (Index: 1)
    $ca = $a['votes'];
    $cb = $b['votes'];
    if ($ca == $cb) {
    	if ($a['song']['id']==$b['song']['id']) { 
         return 0;
    	} else {
    		return $a['song']['id'] ? $b['song']['id'] -1 : +1;
    	}
    }
    return ($ca > $cb) ? -1 : +1;
}

/**
 * Compare funtion für the song list
 */
function CompareSong($a, $b) 
{
    // Sortierung nach dem zweiten Wert des Array (Index: 1)
    $a = strtoupper($a['name']);
    $b = strtoupper($b['name']);
    if ($a == $b) {
         return 0;
    }
    return ($a < $b) ? -1 : +1;
}

/**
 * read interpret list from the database
 */
function readInterpretList($database)
{
	$data = array();

	$FileName=$database.'interpret.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"r");
		$id=0;
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[1])) { 
				$data[$id]['id']=$b[0];
				$data[$id]['name']=$b[1];
				$id +=1;
			}
		}
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	usort( $data, 'CompareInterpret');
	return $data;
}

/**
 * read interpret from the database
 */
function readInterpret($database,$id)
{
	$data = array();

	$FileName=$database.'interpret.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"r");
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[1]) && ($id==$b[0]) ){
				$data['id']=$b[0];
				$data['name']=$b[1];
			}
		}
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	return $data;
}

/**
 * insert a new interpret in the database
 */
function insertNewInterpret($database,$newinterpret)
{
	$data = readInterpretList($database);
	$newid=0;
	foreach ($data as $i) {
	   if ($i['id']>$newid) $newid=$i['id'];	
	}
	$newid +=1;

	$FileName=$database.'interpret.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"a");
		fwrite($file,$newid.'='.$newinterpret."\r\n");
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	
	return $newid;
}

/**
 * read song list from the database
 * if the interpret id equals 0 the entire list will be readed
 */
function readSongList($database,$interpret)
{
	$data = array();

	$FileName=$database.'song.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"r");
		$id=0;
		while (!feof($file)) {
			$b = explode("|",fgets($file));
			if ( (isset($b[1])) && (($interpret==$b[1])||$interpret==0)   ) {
				$data[$id]['id']=$b[0];
				$data[$id]['interpretId']=$b[1];
				$data[$id]['name']=$b[2];
				if  (isset($b[3])) $data[$id]['video']=$b[3]; else $data[$id]['video']="";
				if  (isset($b[4])) $data[$id]['link']=$b[4]; else $data[$id]['link']="";
				$id +=1;
			}
		}
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	usort( $data, 'CompareSong');
	return $data;
}

/**
 * read song from the database
 */
function readSong($database,$id)
{
	$data = array();

	$FileName=$database.'song.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"r");
		while (!feof($file)) {
			$b = explode("|",fgets($file));
			if ( (isset($b[1])) && ($id==$b[0])) {
				$data['id']=$b[0];
				$data['interpretId']=$b[1];
				$data['name']=$b[2];
				if  (isset($b[3])) $data['video']=$b[3]; else $data['video']="";
				if  (isset($b[4])) $data['link']=$b[4]; else $data['link']="";
			}
		}
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	return $data;
}

/**
 * insert a new song in the database
 */
function insertNewSong($database, $interpretId, $newSong,$newVideo, $newLink) {
	$data = readSongList($database,0); 	
	$newid=0;
	foreach ($data as $i) {
	   if ($i['id']>$newid) $newid=$i['id'];	
	}
	$newid +=1;

	$FileName=$database.'song.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"a");
		fwrite($file,$newid.'|'.$interpretId.'|'.$newSong.'|'.$newVideo.'|'.$newLink."\r\n");
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	
	return $newid;
}
 
/**
 * insert a vote to the database
 */
function insertVote($database,$userID,$song) {
  if (($database<>"") && ($userID>0) && ($song>0)) {	
	  $data=readVoteList($database,$userID);
	  $songFound = false;
	  foreach($data as $index => $vote ) {
	  	if ($vote['song']['id']==$song) {
	  		$songFound = true;
	  		$data[$index]['user']=rtrim($data[$index]['user'],"\r\n");
	  		if ($data[$index]['user']=="")
	  			$data[$index]['user']=$userID."\r\n";
	  		else 
	  			$data[$index]['user']=$userID.','.$data[$index]['user'];
	  		//remove duplicates
	  		$userArray=explode(",",$data[$index]['user']);
	  		$userArray=array_unique($userArray);
	 		$newUserList="";
  			foreach($userArray as $user) {
	  			$user=rtrim($user,"\r\n");
  				if ($newUserList=="") $separator=""; else $separator=",";
  				$newUserList= $newUserList.$separator.$user;
  			}
  			$data[$index]['user']=$newUserList;
	  	}
	  } 
	  if (!$songFound) {
	  	$newSong = array();
	  	$newSong['id']=$song;
	  	$newVote = array();
	  	$newVote['song']=$newSong;
	  	$newVote['user']=$userID;
	  	$data[]=$newVote;
	  } 
	  
	   	$FileName=$database.'songvote.txt'; 
		$file=fopen($FileName ,"w");
		sort($data);
		foreach($data as $vote) {
			fwrite($file,$vote['song']['id']."=".rtrim($vote['user'],"\r\n")."\r\n");
		}
		fclose($file);
		$mailSong=readSong($database,$song);
		$mailInterpret=readInterpret($database,$mailSong['interpretId']);
		$mailPerson=getPersonLogedOn();
		sendTheMail('code@blue-l.de',$database."<br/>".$mailPerson["firstname"].$mailPerson["lastname" ]."<br/>".$mailInterpret["name"]."<br/>".$mailSong["name"]."<br/>");
  } 
}

/**
 * delete vote from the database
 */
function deleteVote($database,$userID,$song) {
  $data=readVoteList($database,$userID);
  $songFound = false;
  // find song in the vote list
  foreach($data as $index => $vote ) {
  	if ($vote['song']['id']==$song) {
  		$songFound = true;
  		$userList = explode(",",$vote['user']);
  		$newUserList="";
  		foreach($userList as $user) {
  			$user=rtrim($user,"\r\n");
  			//only the vote from other user are relevant
  			if ($user<>$userID) {
  				if ($newUserList=="") $separator=""; else $separator=",";
  				$newUserList= $newUserList.$separator.$user;
  			}
  		}
  		$data[$index]['user']=$newUserList;
  	}
  } 
  
   	$FileName=$database.'songvote.txt'; 
	$file=fopen($FileName ,"w");
	sort($data);
	foreach($data as $vote) {
		if ($vote['user']<>"")
			fwrite($file,$vote['song']['id']."=".rtrim($vote['user'],"\r\n")."\r\n");
	}
	fclose($file);
 
  	
}


/**
 * get the count of user votes
 */
function getUserSongVoteCount($database,$userID){
	$data=readVoteList($database,$userID);
	$count=0;
	foreach ($data as $vote) {
		if ( $vote['voted'] ) $count +=1;
	}
	return $count;
}

/**
 * read the vote list
 * 
 */
function readVoteList($database,$userId)
{
	$data = array();

	$FileName=$database.'songvote.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"r");
		$id=0;
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[1])) { 
				$data[$id]['song']= readSong($database,$b[0]);
				$data[$id]['interpret']=readInterpret($database,$data[$id]['song']['interpretId']);
				$data[$id]['user']=$b[1];
				$data[$id]['voted']=false;
				$userIds = explode (",",$b[1]);
				$countVotesPerUser=0;
				foreach($userIds as $uid) {
					$countVotesPerUser++;
					if ( rtrim($uid,"\r\n")==$userId) {
						$data[$id]['voted']=true;
					}
				}
				$data[$id]['votes']=$countVotesPerUser;
				$id++;
			}
		}
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	usort( $data, 'CompareVote');
	return $data;
}


/**
 * readTopList Read the vote list and sort it by the occurence of votes
 */
function readTopList($database,$userId)
{
	$data = readVoteList($database,$userId);
	usort( $data, 'CompareTopList');
	return $data;
}	

/**
 * read the list of the voters
 */
function readVotersList($database)
{
	$votersList = array();

	$FileName=$database.'songvote.txt'; 
    if (file_exists($FileName)) {
		$file=fopen($FileName ,"r");
		$id=0;
		while (!feof($file)) {
			$b = explode("=",fgets($file));
			if (isset($b[1])) { 
				$userIds = explode (",",$b[1]);
				foreach($userIds as $uid) {
					$uid = rtrim($uid,"\r\n");
					
					$foundInTheVotersList=false;
					foreach ($votersList as $index =>$voter)
					{
						if ($voter["UID"]==$uid) {
							$foundInTheVotersList=true;
							$votersList[$index]["VotesCount"]++;
						}
					}
					
					if ($foundInTheVotersList==false) {
						$newVoter["UID"]=$uid;
						$newVoter["VotesCount"]=1;
						$person=getPerson($uid);
						if ($person["admin"]=="") {
					   	  $newVoter["Name"]=$person["lastname"].' '.$person["firstname"];
						  $votersList[$id++]=$newVoter;
						}
					}
					
				}
			}
		}
		fclose($file);
	}
	else 
		echo ("Error:open database ".$FileName);
	usort( $votersList, 'CompareVoters');
	return $votersList;
}
