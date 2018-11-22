<?php
include_once 'tools/sessionManager.php';
include_once 'tools/userManager.php';
include_once 'tools/appl.class.php';

use \maierlabs\lpfw\Appl as Appl;

Appl::$subTitle="Adatmódosítások";
Appl::addCssStyle('
	.history {margin:10px;}
	.history tr td {vertical-align: top;padding:5px;}
	.history tr  {border-spacing: 2px};
');
include('homemenu.inc.php');
?>

<?php if (userIsAdmin() || userIsSuperuser()) {
    if (isActionParam("delete")) {
        $db->deleteHistoryEntry(getParam("did"));
    }
	$history=$db->getHistory(getParam("table"), getParam("id"));
?>

<div class="panel panel-default">
	<div class="panel-heading">
		<label id="dbDetails">Adat</label> 
		&nbsp;&nbsp;&nbsp;&nbsp;Színek jelentése:<span style="background-color:#e0ffe0" > Adat nem üres</span>
		<span style="background-color:yellow" > Adat módosítás</span>
		
	</div>
	<table class="history">
		<?php 
		$aktId=-1;
		foreach ($history as $id=>$item) {
			if ($aktId!=$item["entryID"]) {
				if($id!=0) {?>
		  			<tr><td colspan="45"><hr/></td></tr>
				<?php } ?>
		  		<tr>
					<?php displayHistoryElement($db,$item,$item,true);$aktId=$item["entryID"]; ?>		          			
		  		</tr>
			<?php }	?>
	  		<tr>
	   			<?php displayHistoryElement($db,$item,$id<sizeof($history)-1?$history[$id+1]:null,false,$id==sizeof($history)-1);?>
	  	  	</tr>
  		<?php } ?>
	</table>
</div>

<?php } else { ?>
	<div class="alert alert-danger text-center" >Adat hozzáférési jog hiányzik!</div>
<?php } ?>

<?php 
function displayHistoryElement($db,$item,$itemNext,$original=false,$lastElement=false) {
	switch ($item["table"]) {
		case "person" :
			if ($original) 
				$person=$db->getEntryById($item["table"],$item["entryID"],true);
			else
				$person=json_decode_utf8($item["jsonData"]);
			displayPerson($db, $item,$person,json_decode_utf8($itemNext["jsonData"]),$original,$lastElement);
			break;
		case "picture" :
			if ($original) 
				$picture=$db->getEntryById($item["table"],$item["entryID"],true);
			else
				$picture=json_decode_utf8($item["jsonData"]);
			displayPicture($db, $item, $picture,json_decode_utf8($itemNext["jsonData"]));
			break;
		case "vote" :
			if ($original) 
				$vote=$db->getEntryById($item["table"],$item["entryID"],true);
			else
				$vote=json_decode_utf8($item["jsonData"]);
			displayVote($db, $item, $vote,json_decode_utf8($itemNext["jsonData"]));
			break;
		case "class" :
			if ($original) 
				$class=$db->getEntryById($item["table"],$item["entryID"],true);
			else
				$class=json_decode_utf8($item["jsonData"]);
			displayClass($db, $item,$class,json_decode_utf8($itemNext["jsonData"]));
			break;
	}
}

function displayPerson($db,$item, $person,$personNext,$original,$lastElement) {
	displayChangeData($db,$person,$item["id"]);
	displayElement(getPersonName($person), getPersonName($personNext));
	displayElementObj($person, $personNext,"picture","Pic");
	displayElementObj($person, $personNext,"partner","P");
	displayElementObj($person, $personNext,"deceasedYear","†");
    displayElementObj($person, $personNext,"cementery","†");

	displayElementObj($person, $personNext,"phone","P");
	displayElementObj($person, $personNext,"mobil","M");
	displayElementObj($person, $personNext,"email","E");
	displayElementObj($person, $personNext,"homepage","W");
	displayElementObj($person, $personNext,"skype","S");
	displayElementObj($person, $personNext,"twitter","T");
	displayElementObj($person, $personNext,"facebook","F");

	displayElementObj($person, $personNext,"geolat","XY");
	displayElementObj($person, $personNext,"country","C");
	displayElementObj($person, $personNext,"zipcode","Z");
	displayElementObj($person, $personNext,"place","P");
	displayElementObj($person, $personNext,"address","A");

	displayElementObj($person, $personNext,"education","O");
	displayElementObj($person, $personNext,"employer","E");
	displayElementObj($person, $personNext,"function","F");
	displayElementObj($person, $personNext,"children","K");
	displayElement(hash("sha256",$person["cv"]), hash("sha256",$personNext["cv"]),"CV","CV");
	displayElement(hash("sha256",$person["story"]), hash("sha256",$personNext["story"]),"T","Történet");
	displayElement(hash("sha256",$person["aboutMe"]), hash("sha256",$personNext["aboutMe"]),"R","Magamról");
	
	displayElementObj($person, $personNext,"user","U");
	displayElementObj($person, $personNext,"passw","P");
	displayElementObj($person, $personNext,"classID","C");
	displayElementObj($person, $personNext,"isTeacher","T");
	if ($item["changeUserID"]!=$person["changeUserID"] && !$lastElement && userIsAdmin())
		displayChangeData($db,$item,0);
}

function displayClass($db,$item,$class,$classNext) {
	displayChangeData($db,$class,$item["id"]);
	displayElementObj($class, $classNext, "schoolID","S");
	displayElementObj($class, $classNext, "name");
	displayElementObj($class, $classNext, "graduationYear");
    displayElementObj($class, $classNext, "eveningClass","E");
    displayElementObj($class, $classNext, "text");
    displayElementObj($class, $classNext, "teachers");
	displayElement(getPersonName($db->getPersonByID(array_get($class,"headTeacherID",null))), getPersonName($db->getPersonByID(array_get($classNext,"headTeacherID",null))));
}

function displayPicture($db,$item,$picture,$pictureNext) {
	displayChangeData($db,$picture,$item["id"]);
	displayElementObj($picture, $pictureNext, "title" );
	displayElementObj($picture, $pictureNext, "comment");
	displayElementObj($picture, $pictureNext, "isVisibleForAll" );
	displayElementObj($picture, $pictureNext, "isDeleted");
}

function displayVote($db,$item,$vote,$voteNext) {
	displayChangeData($db,$vote,$item["id"]);
	displayElementObj($vote, $voteNext, "eventDay", "D");
	displayElementObj($vote, $voteNext, "isSchool", "I");
	displayElementObj($vote, $voteNext, "isCemetery", "T");
	displayElementObj($vote, $voteNext, "isDinner", "V");
	displayElementObj($vote, $voteNext, "isExcursion", "K");
	displayElementObj($vote, $voteNext, "place", "H");
}

/**
 * Display: ChangeDate, IP, Username
 */
function displayChangeData($db,$item,$historyId) {
    ?><td><?php echo Appl::dateTimeAsStr(array_get($item,"changeDate"))?> </td>
	<?php if (userIsAdmin()) {?>
		<td onclick="showip('<?php echo array_get($item,"changeIP")?>');" class="btn">IP</td>
        <td><button onclick="deleteHistory(<?php echo $historyId.",'".getParam("table")."',".getParam("id")?>)">Töröl</button></td>
	<?php }
        if (isset($item["changeUserID"])) {
            $changePerson=$db->getPersonByID($item["changeUserID"]);
    ?>
    	    <td><a href="editDiak.php?uid=<?php echo $item["changeUserID"] ?>"><?php echo $changePerson["lastname"]." ".$changePerson["firstname"]?></a></td>
    <?php
        } else {
            echo('<td></td>');
        }
}

function displayElement($text,$nextText,$title=null,$field="") {
	
	if (trim($text)===trim($nextText)) {
		if ($text==="e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855")
			$text="";
		if (trim($text)==="" ) { 
			$style='style="background-color:white"';
		} else {
			$style='style="background-color:#e0ffe0"';
		}
	} else {
		$style='style="background-color:yellow"';
	}
	if (null==$title) {
		echo('<td '.$style.'>'.$text.'</td>');
	} else {
		echo('<td title="'.$field.':'.$text.'"'.$style.'>'.$title.'</td>');
	}
}


function displayElementObj($text,$nextText,$field,$title=null) {
	if (!isset($nextText[$field]))
		$nextText[$field]=null;
	if (!isset($text[$field]))
		$text[$field]=null;
	displayElement($text[$field], $nextText[$field],$title,$field);
}

function json_decode_utf8($json) {
	if(null!=$json) {
		$json = str_replace('null','""', $json);
		$jsonArray =explode('","',substr($json,2,-1));
		
		$js=array();
		foreach ($jsonArray as $jsonElement) {
			$e=explode('":"', $jsonElement);
			if (sizeof($e)==2)
				$js[$e[0]]=html_entity_decode(preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $e[1]));
		}
		return $js;
	}
	return null;
}

function array_get($array,$field,$default='') {
    if (isset($array[$field]))
        return $array[$field];
    return $default;
}

Appl::addJsScript('
    function deleteHistory(id,table,tid) {
        document.location="history.php?table="+table+"&id="+tid+"&action=delete&did="+id;
    }
');

include 'homefooter.inc.php';

?>

