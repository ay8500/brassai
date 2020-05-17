<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';

use \maierlabs\lpfw\Appl as Appl;

Appl::setSiteSubTitle("Adatmódosítások");
Appl::addCssStyle('
	.history {margin:10px;}
	.history tr td {padding:5px;}
	.history tr  {border-spacing: 2px};
');
include('homemenu.inc.php');

if (!(userIsAdmin() || userIsSuperuser())) {
    ?><div class="alert alert-danger text-center">Adat hozzáférési jog hiányzik!</div><?php
    include 'homefooter.inc.php';
    return;
} else {

    if (isActionParam("delete")) {
        if ($db->dataBase->deleteHistoryEntry(getParam("did"))) {
            $db->updateRecentChangesList();
            \maierlabs\lpfw\Appl::setMessage("Adatmódosítás törlése sikerült!","success");
        } else {
            \maierlabs\lpfw\Appl::setMessage("Törlés nem sikerült!","danger");
        }
    }
	$history=$db->dataBase->getHistory(getParam("table"), getParam("id"));
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
					<?php displayHistoryElement($db,null,$item);$aktId=$item["entryID"]; ?>
		  		</tr>
			<?php }	?>
	  		<tr>
	   			<?php displayHistoryElement($db,$item,$id<sizeof($history)-1?$history[$id+1]:null);?>
	  	  	</tr>
  		<?php } ?>
	</table>
</div>
<?php } ?>

<?php 
function displayHistoryElement($db,$item,$itemNext) {
    $table = $item!=null?$item["table"]:$itemNext["table"];
	switch ($table) {
		case "person" :
			if ($item==null)
				$person=$db->dataBase->getEntryById($table,$itemNext["entryID"],true);
			else
				$person=json_decode_utf8($item["jsonData"]);
			displayPerson($db, $item,$person,$itemNext);
			break;
		case "picture" :
			if ($item==null)
				$picture=$db->dataBase->getEntryById($table,$itemNext["entryID"],true);
			else
				$picture=json_decode_utf8($item["jsonData"]);
			displayPicture($db, $item, $picture,$itemNext);
			break;
		case "vote" :
			if ($item==null)
				$vote=$db->dataBase->getEntryById($table,$itemNext["entryID"],true);
			else
				$vote=json_decode_utf8($item["jsonData"]);
			displayVote($db, $item, $vote,$itemNext);
			break;
		case "class" :
			if ($item==null)
				$class=$db->dataBase->getEntryById($table,$itemNext["entryID"],true);
			else
				$class=json_decode_utf8($item["jsonData"]);
			displayClass($db, $item,$class,$itemNext);
			break;
	}
}

function displayPerson($db,$item,$person,$itemNext) {
    if($itemNext!=null)
        $personNext=json_decode_utf8($itemNext["jsonData"]);
    else
        $personNext=null;
	displayChangeData($db,$person,$itemNext);
	displayElement(getPersonName($person), getPersonName($personNext),null,"lastname,firstname");
    displayElementObj($person, $personNext,"gender","X");
	displayElementObj($person, $personNext,"picture","Pic");
	displayElementObj($person, $personNext,"partner","P");
    displayElementObj($person, $personNext,"birthyear","+");
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
	displayElement(hash("sha256",isset($person["cv"])?$person["cv"]:''), hash("sha256",isset($personNext["cv"])?$personNext["cv"]:''),"CV","CV");
    displayElement(hash("sha256",isset($person["story"])?$person["story"]:''), hash("sha256",isset($personNext["story"])?$personNext["story"]:''),"T","Történet");
    displayElement(hash("sha256",isset($person["aboutMe"])?$person["aboutMe"]:''), hash("sha256",isset($personNext["aboutMe"])?$personNext["aboutMe"]:''),"M","Magamról");

	displayElementObj($person, $personNext,"user","U");
	displayElementObj($person, $personNext,"passw","P");
	displayElementObj($person, $personNext,"classID","C");
	displayElementObj($person, $personNext,"isTeacher","T");
    displayElementObj($person, $personNext,"role","R");
}

function displayClass($db,$item,$class,$itemNext) {
    if($itemNext!=null)
        $classNext=json_decode_utf8($itemNext["jsonData"]);
    else
        $classNext=null;
    displayChangeData($db,$class,$itemNext);
	displayElementObj($class, $classNext, "schoolID","S");
	displayElementObj($class, $classNext, "name");
	displayElementObj($class, $classNext, "graduationYear");
    displayElementObj($class, $classNext, "eveningClass","E");
    displayElementObj($class, $classNext, "text");
	displayElement(getPersonName($db->getPersonByID(array_get($class,"headTeacherID",null))), getPersonName($db->getPersonByID(array_get($classNext,"headTeacherID",null))));
    displayElementObj($class, $classNext, "teachers");
}

function displayPicture($db,$item,$picture,$itemNext) {
    if($itemNext!=null)
        $pictureNext=json_decode_utf8($itemNext["jsonData"]);
    else
        $pictureNext=null;
	displayChangeData($db,$picture,$itemNext);
	displayElementObj($picture, $pictureNext, "title" );
	displayElementObj($picture, $pictureNext, "comment");
	displayElementObj($picture, $pictureNext, "isVisibleForAll" );
	displayElementObj($picture, $pictureNext, "isDeleted");
    displayElementObj($picture, $pictureNext, "orderValue");
    displayElementObj($picture, $pictureNext, "albumName");
    displayElementObj($picture, $pictureNext, "tag");
}

/**
 * @param dbDAO $db
 * @param $item
 * @param $vote
 * @param $voteNext
 */
function displayVote($db,$item,$vote,$itemNext) {
    if($itemNext!=null)
        $voteNext=json_decode_utf8($itemNext["jsonData"]);
    else
        $voteNext=null;
    displayChangeData($db,$vote,$itemNext);
	if ($vote!=null) {
        $person = $db->getPersonByID($vote["personID"]);
        displayElement(getPersonName($person),getPersonName($person));
    } else {
	    displayElement("","");
    }
	displayElementObj($vote, $voteNext, "eventDay", );
	displayElementObj($vote, $voteNext, "isSchool", );
	displayElementObj($vote, $voteNext, "isCemetery", );
	displayElementObj($vote, $voteNext, "isDinner", );
	displayElementObj($vote, $voteNext, "isExcursion", );
	displayElementObj($vote, $voteNext, "place", );
    displayElementObj($vote, $voteNext, "meetAfterYear", );
}

/**
 * Display: ChangeDate, IP, Username, delete button
 * @param dbDAO $db
 * @param array $item the actual element
 * @param array $historyItem history element
 */
function displayChangeData($db,$item,$historyItem) {
    ?><td><?php echo Appl::dateTimeAsStr(array_get($item,"changeDate"))?> </td>
	<?php if (userIsSuperuser() ) {?>
        <td><button onclick="showip('<?php echo array_get($item,"changeIP")?>');" class="btn">IP</button></td>
        <?php if ($historyItem!=null) {?>
            <td><button onclick="deleteHistory(<?php echo $historyItem["id"].",'".getParam("table")."',".getParam("id")?>)" class="btn btn-danger btn-sm" title="<?php echo $historyItem["id"]?>">Töröl</button></td>
        <?php } else { ?>
            <td></td>
        <?php } ?>
	<?php }
	if (isset($item["changeUserID"])) {
	    $changePerson=$db->getPersonByID($item["changeUserID"]); ?>
    	<td><a href="editDiak?uid=<?php echo $item["changeUserID"] ?>"><?php echo $changePerson["lastname"]." ".$changePerson["firstname"]?></a></td>
    <?php } else { ?>
        <td></td>
    <?php }
	if (userIsAdmin()) {
        if ($historyItem != null && $item["changeUserID"] != $historyItem["changeUserID"]) {
            $changePerson=$db->getPersonByID($historyItem["changeUserID"]); ?>
            <td> <?php echo Appl::dateTimeAsStr($historyItem["changeDate"]) ?></td>
            <td><button onclick="showip('<?php echo array_get($historyItem,"changeIP")?>');" class="btn">IP</button></td>
            <td><a href="editDiak?uid=<?php echo $historyItem["changeUserID"] ?>"><?php echo $changePerson["lastname"]." ".$changePerson["firstname"]?></a></td>
        <?php } else {
            echo("<td></td><td></td><td></td>");
        }
    }
}

function displayElement($text,$nextText="",$title=null,$field="") {
	
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
		echo('<td title="'.$field.'"'.$style.'>'.$text.'</td>');
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
        showWaitMessage();
        document.location="history?table="+table+"&id="+tid+"&action=delete&did="+id;
    }
');

include 'homefooter.inc.php';

?>

