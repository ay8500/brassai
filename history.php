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
		  			<td>O <?php echo $item["table"]?></td>	
					<?php displayHistoryElement($db,$item,$item,true);$aktId=$item["entryID"]; ?>		          			
		  		</tr>
			<?php }	?>
	  		<tr>
	  			<td>C <?php echo $item["table"]?></td>	
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
	displayElement($person["email"], $personNext["email"]);
	displayElement($person["country"], $personNext["country"]);
	displayElement($person["place"], $personNext["place"]);
}

function displayClass($db,$class,$classNext) {
	displayChangeData($db,$class);
	displayElement($class["schoolID"], $classNext["schoolID"]);
	displayElement($class["name"], $classNext["name"]);
	displayElement($class["graduationYear"], $classNext["graduationYear"]);
	displayElement(getPersonName($db->getPersonByID($class["headTeacherID"])), getPersonName($db->getPersonByID($classNext["headTeacherID"])));
}

function displayPicture($db,$picture,$pictureNext) {
	displayChangeData($db,$picture);
	displayElement($picture["title"], $pictureNext["title"]);
	displayElement($picture["comment"], $pictureNext["comment"]);
	displayElement($picture["isVisibleForAll"], $pictureNext["isVisibleForAll"]);
	displayElement($picture["isDeleted"], $pictureNext["isDeleted"]);
}


function displayChangeData($db,$item) {
	$changePerson=$db->getPersonByID($item["changeUserID"]);
	?><td><?php echo date("Y.m.d H:i:s",strtotime($item["changeDate"]))?></td>
	<td><?php echo $item["changeIP"]?></td>
	<td><a href="editDiak.php?uid=<?php echo $item["changeUserID"] ?>"><?php echo $changePerson["lastname"]." ".$changePerson["firstname"]?></a></td><?php
}

function displayElement($text,$nextText) {
	if (!strcmp(trim($text),trim($nextText))) {
		$style='style="background-color:white"';
	} else {
		$style='style="background-color:yellow"';
	}
	echo('<td '.$style.'>'.$text.'</td>');
}

function json_decode_utf8($json) {
	if(null!=$json) {
		$js=json_decode($json,true);
		if(null!=$js) {
			foreach ($js as $idx=>$j) {
				$js[$idx]=preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $js[$idx]);
			}
		}
		return $js;
	}
	return null;
}

?>

<script type="text/javascript">


</script>
