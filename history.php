<?php 
include('homemenu.php');
include_once('tools/userManager.php');
$resultDBoperation="";

//if (userIsAdmin()) {$resultDBoperation='<div class="alert alert-warning" >Ok</div>';}  	
?>
<style>
	.history {margin:10px;}
	.history tr td {vertical-align: top;padding:5px;}
	.history tr  {border-spacing: 2px};
</style>
<div class="container-fluid">   
	<div class="sub_title">Adatmódosítások</div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
</div>


<?php if (userIsAdmin()) {
	$history=$db->getHistory(getParam("table"), getParam("id"));
?>

<div class="panel panel-default">
	<div class="panel-heading">
		<label id="dbDetails">Adat</label> 
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
		  			<td>A <?php echo $item["table"]?></td>	
					<?php displayHistoryElement($db,$item,$item,true);$aktId=$item["entryID"]; ?>		          			
		  		</tr>
			<?php }	?>
	  		<tr>
	  			<td>O <?php echo $item["table"]?></td>	
	   			<?php displayHistoryElement($db,$history[$id],$id<sizeof($history)-1?$history[$id+1]:null,false);?>
	  	  	</tr>
  		<?php } ?>
	</table>
</div>

<?php } else { ?>
	<div class="alert alert-danger text-center" >Adat hozzáférési jog hiányzik!</div>
<?php } ?>
<?php include 'homefooter.php';?>

<?php 
function displayHistoryElement($db,$item,$itemNext,$original=false) {
	switch ($item["table"]) {
		case "person" :
			if ($original) 
				$person=$db->getEntryById($item["table"],$item["entryID"],true);
			else
				$person=json_decode_utf8($item["jsonData"]);
			displayPerson($db, $person,json_decode_utf8($itemNext["jsonData"]));
			break;
		case "picture" :
			if ($original) 
				$picture=$db->getEntryById($item["table"],$item["entryID"],true);
			else
				$picture=json_decode_utf8($item["jsonData"]);
			displayPicture($db, $picture,json_decode_utf8($itemNext["jsonData"]));
			break;
		case "class" :
			if ($original) 
				$class=$db->getEntryById($item["table"],$item["entryID"],true);
			else
				$class=json_decode_utf8($item["jsonData"]);
			displayClass($db, $class,json_decode_utf8($itemNext["jsonData"]));
			break;
	}
}

function displayPerson($db,$person,$personNext) {
	displayChangeData($db,$person);
	displayElement(getPersonName($person), getPersonName($personNext));
	displayElementObj($person, $personNext,"picture","Pic");
	displayElementObj($person, $personNext,"partner","P");

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
	
}

function displayClass($db,$class,$classNext) {
	displayChangeData($db,$class);
	displayElementObj($class, $classNext, "schoolID");
	displayElementObj($class, $classNext, "name");
	displayElementObj($class, $classNext, "graduationYear");
	displayElement(getPersonName($db->getPersonByID($class["headTeacherID"])), getPersonName($db->getPersonByID($classNext["headTeacherID"])));
}

function displayPicture($db,$picture,$pictureNext) {
	displayChangeData($db,$picture);
	displayElementObj($picture, $pictureNext, "title");
	displayElementObj($picture, $pictureNext, "comment");
	displayElementObj($picture, $pictureNext, "isVisibleForAll");
	displayElementObj($picture, $pictureNext, "isDeleted");
}


function displayChangeData($db,$item) {
	$changePerson=$db->getPersonByID($item["changeUserID"]);
	?><td><?php echo date("Y.m.d H:i:s",strtotime($item["changeDate"]))?></td>
	<td><?php echo $item["changeIP"]?></td>
	<td><a href="editDiak.php?uid=<?php echo $item["changeUserID"] ?>"><?php echo $changePerson["lastname"]." ".$changePerson["firstname"]?></a></td><?php
}

function displayElement($text,$nextText,$title=null,$field="") {
	
	if (trim($text)===trim($nextText)) {
		$style='style="background-color:white"';
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

?>

<script type="text/javascript">


</script>
