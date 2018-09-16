<?php 
include_once('tools/userManager.php');
include_once 'tools/appl.class.php';

use \maierlabs\lpfw\Appl as Appl;

if (userIsAdmin()) {
	if (getParam("action")=="deleteMessageChange") {
  		$ret =$db->deleteMessageEntry($id);$show=true;
	} 	
	if (getParam("action")=="acceptMessageChange") {
  		$ret =$db->acceptChangeForMessage($id)>=0;$show=true;
	} 	
	if (getParam("action")=="resetChange") {
  		$ret =$db->deleteRequest(getIntParam("type"),getParam("ip"));$show=true;
	} 	
	if ($show) {
		if ($ret===true) {
			Appl::setMessage('Rendben, a müvelet sikerült','success');
		} else {
			Appl::setMessage('Sajnos nem sikerült a müvelet!','danger');
		}
	}
}  	

Appl::$subTitle='Adatok vizsgálása és jóváhagyása';
include('homemenu.php');

if (userIsAdmin()) {  
	$id=getIntParam("id");
	$ret=false;
	$show=false;
	
	//initialise tabs
	$clist=$db->getClassListToBeChecked();
	$plist=$db->getPersonListToBeChecked();
	$ilist=$db->getPictureListToBeChecked();
	$mlist=$db->getMessageListToBeChecked();
	
	$tabsCaption=Array(	'Osztályok <span class="badge">'.sizeof($clist).'</span>',
						'Személyek <span class="badge">'.sizeof($plist).'</span>',
						'Képek <span class="badge">'.sizeof($ilist).'</span>',
						'Üzenetek <span class="badge">'.sizeof($mlist).'</span>',
						'Hozzáférések');
	include("tabs.php");
	if ($tabOpen==0) {
		generateCheckHtmlTable("Osztályok", "Osztály","Class","text","getClassListToBeChecked",$id,["id"=>0,"graduationYear"=>"","name"=>"","text"=>""],"getClassById","deleteClass","saveClass");
	}
	if ($tabOpen==1) {
		$dummyPerson=getPersonDummy();
		$dummyPerson["classID"]="";$dummyPerson["facebook"]="";$dummyPerson["isTeacher"]="";
		$dummyPerson["address"]="";$dummyPerson["zipcode"]="";$dummyPerson["place"]="";
		$dummyPerson["phone"]="";$dummyPerson["mobil"]="";$dummyPerson["email"]="";
		$dummyPerson["homepage"]="";$dummyPerson["skype"]="";$dummyPerson["education"]="";
		$dummyPerson["employer"]="";$dummyPerson["function"]="";$dummyPerson["children"]="";
		generateCheckHtmlTable("Személyek", "Személy","Person","lastname","getPersonListToBeChecked",$id,$dummyPerson,"getPersonByID","deletePersonEntry","savePerson");
	}
	if ($tabOpen==2) { 
		generateCheckHtmlTable("Képek", "Kép","Picture","file","getPictureListToBeChecked",$id,["id"=>0,"title"=>"","comment"=>"","file"=>"","isVisibleForAll"=>0,"isDeleted"=>0],"getPictureById","deletePictureEntry","savePicture");
	}
	if ($tabOpen==3) {
		$list=$db->getMessageListToBeChecked();
?>
	<p align="center">
	   Üzenetek:<br/>	
	  <table align="center" border="1">
	    <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
	    	<td>Név</td><td>szöveg</td><td>Datum</td><td>Ip</td><td colspan="2">Akció</td>
	    </tr>
	  	<?php
	  		foreach ($list as $i=>$l) {
	   			echo('<tr >');
	   			if (isset($l["changeUserID"]))
	   				echo("<td>".getPersonName($db->getPersonByID($l["changeUserID"]))."</td>");
	   			else
	   				echo("<td>".html_entity_decode($l["name"])."</td>");
	   			echo("<td>".html_entity_decode($l["text"])."</td>");
	   			echo("<td>".$l["changeDate"]."</td>");
	   			echo("<td><a href=\"javascript:showip('".$l["changeIP"]."')\">".$l["changeIP"]."</td>");
	   			if ($l["isDeleted"]==0)
   					echo('<td><button class="btn btn-default" onclick="acceptMessageChange('.$l["id"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
   				else
   					echo("<td></td>");
	   			echo('<td><button class="btn btn-default" onclick="deleteMessageChange('.$l["id"].');"><span class="glyphicon glyphicon-remove"></span></button></td>');
	   			echo("</tr>");
	  		}
	  	?>
	 </table>  
	</p>
<?php }?>
<?php if ($tabOpen==4) { ?>
	<p align="center">
	   Hozzáférések ma:<br/>
   	  <table align="center" border="1">
	    <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
	    	<td>IP</td><td>Datum</td><td>Type</td><td>Számláló</td><td>Akció</td>
	    </tr>
	  	<?php
	  		$list = $db->getListOfRequest(24);
	  		foreach ($list as $i=>$l) {
	   			echo('<tr >');
	   			echo("<td><a href=\"javascript:showip('".$l["ip"]."')\">".$l["ip"]."</td>");
	   			echo("<td>".$l["date"]."</td>");
	   			echo("<td>".getConstantName("changeType",$l["typeID"])."</td>");
	   			echo("<td>".$l["count"]."</td>");
   				if ($l["count"]>0)
   					echo('<td><button class="btn btn-default" onclick="resetChange('."'".$l["ip"]."',".$l["typeID"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
   				else
   					echo("<td></td>");
	   			echo("</tr>");
	  		}
	  	?>
	 </table>  
	 <p style="text-align: center;margin-top:20px">Hozzáférések:</p>
   	 <table align="center" border="1" style="margin-top:20px">
	    <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
	    	<td>IP</td><td>Datum</td><td>Type</td><td>Számláló</td><td>Akció</td>
	    </tr>
	  	<?php
	  		$list = $db->getListOfRequest(0);
	  		foreach ($list as $i=>$l) {
	   			echo('<tr >');
	   			echo("<td><a href=\"javascript:showip('".$l["ip"]."')\">".$l["ip"]."</td>");
	   			echo("<td>".$l["date"]."</td>");
	   			echo("<td>".getConstantName("changeType",$l["typeID"])."</td>");
	   			echo("<td>".$l["count"]."</td>");
   				if ($l["count"]>=1)
   					echo('<td><button class="btn btn-default" onclick="resetChange('."'".$l["ip"]."',".$l["typeID"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
   				else
   					echo("<td></td>");
	   			echo("</tr>");
	  		}
	  	?>
	 </table>  
	   	
	</p>
	<?php }?>
<?php }
else
	echo '<div class="alert alert-danger text-center" >Adat hozzáférési jog hiányzik!</div>';
?>
	
<?php include 'homefooter.php';?>

<script>
	function deleteMessageChange(id) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=deleteMessageChange&id="+id;
	}
	function acceptMessageChange(id) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=acceptMessageChange&id="+id;
	}
	function resetChange(ip,type) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=resetChange&type="+type+"&ip="+ip;
	}

</script>


<?php 
/**
 * Generate php code for check table
 * @param unknown $title 
 * @param unknown $fieldText
 * @param unknown $fieldDb
 * @param unknown $showField
 * @param unknown $functionList
 * @param unknown $id
 * @param unknown $emptyEntry
 * @param unknown $functionGetByID
 * @param unknown $functionDelete
 * @param unknown $functionSave
 */
function generateCheckHtmlTable($title,$fieldText,$fieldDb,$showField,$functionList,$id,$emptyEntry,$functionGetByID,$functionDelete,$functionSave ) {
	global $db;
  	$show=false;
  	if (getParam("action")=="delete".$fieldDb."Change") {
 		$ret =call_user_func_array(array($db,$functionDelete),array($id,false));$show=true;
  	}
  	if (getParam("action")=="accept".$fieldDb."Change") {
 		$ret =$db->acceptChangeForEntry(strtolower($fieldDb),$id);$show=true;
  	}
  	if ($show) {
  		if ($ret===true) {
  			Appl::$resultDbOperation='<div class="alert alert-success" > Rendben, a müvelet sikerült</div>';}
  		else {
  			Appl::$resultDbOperation='<div class="alert alert-danger" > Sajnos nem sikerült a müvelet!</div>';}
  	}
	$list = call_user_func(array($db,$functionList));
?>
<p align="center">
<?php echo($title)?>:<br/>
<?php //** The table of the  changes?>
	  <table align="center" border="1">
	    <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
	    	<td><?php echo $fieldText ?></td><td>Datum</td><td>Ip</td><td colspan="3">Akció</td></tr>
	  	<?php
	  		foreach ($list as $i=>$l) {
	   			echo('<tr >');
	   			echo("<td>".$l[$showField]."</td>");
	   			echo("<td>".$l["changeDate"]."</td>");
	   			echo("<td><a href=\"javascript:showip('".$l["changeIP"]."')\">".$l["changeIP"]."</td>");
	  			if ($l["changeForIDjoin"]!=null) {
	   				echo('<td><button class="btn btn-default" onclick="edit'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-resize-horizontal"></span></button></td>');
	   			}else{
	   				echo('<td><button class="btn btn-default" onclick="edit'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-open"></span></button></td>');
	   			}
	   			echo('<td><button class="btn btn-default" onclick="accept'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
	   			echo('<td><button class="btn btn-default" onclick="delete'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-remove"></span></button></td>');
	   			echo("</tr>");
	  		}
	  	?>
	  	<?php //Parameter read and action handling
	  	if ((getParam("action")=="show".$fieldDb."Change" || getParam("action")=="accept".$fieldDb."FieldChange") && userIsAdmin()) {
  			$cp=call_user_func_array(array($db,$functionGetByID),array($id,true));
  			if ($compare = isset($cp["changeForID"])) 
  				$op=call_user_func_array(array($db,$functionGetByID),array($cp["changeForID"],true));
  			else 
  				$op=$emptyEntry;
	  		if (getParam("action")=="accept".$fieldDb."FieldChange") {
	  			$field=getParam("field");
	  			$op[$field]=$cp[$field];
  				$ret=call_user_func_array(array($db,$functionSave),array($op))>=0;
	  			if ($ret) {
					Appl::setMessage('Rendben, a müvelet sikerült','success');}
				else {
					Appl::setMessage('Sajnos nem sikerült a müvelet!','danger');}
	  		}
		?>
			<?php //** The table of the changes ?>
			<table align="center" border="1" style="margin-top: 20px">
	    		<tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
	    			<td>Mezö</td>
	    			<?php if ($compare) {?><td>Eredeti</td><?php }?>
	    			<td>Módosítás</td>
	    			<?php if ($compare) {?><td>Akció</td><?php }?>
	    		</tr>
	    		<?php foreach ($op as $field=>$value) {
	    			$s=$cp[$field]!=$value?' style="background-color:yellow;"':'';
	    		?>
	    			<tr>
	    				<td ><?php echo $field ?></td>
	    					<?php 
	    					if ($field=="file") {
	    						if ($compare) {
	    							echo('<td ><img class="acceptField" src="'.$value.'" /></td>');
	    						}
	    						echo('<td ><img class="acceptField" src="'.$cp[$field].'" /></td>');
	    					}
	    			    	elseif ($field=="picture") {
	    						if ($compare) {
	    							if (null!=$value && $value!="") 
	    								echo('<td ><img class="acceptField" src="images/'.$value.'" /></td>');
	    							else 
	    								echo('<td ><img class="acceptField" src="images/avatar.jpg" /></td>');
	    						}
	    						echo('<td ><img class="acceptField" src="'.getPersonPicture($cp).'" /></td>');
	    					}
	    					else {
	    						if ($compare) {
	    							echo('<td class="acceptField">'.$value.'</td>');
	    						}
	    						echo('<td class="acceptField" '.$s.'>'.$cp[$field].'</td>');
	    					}
	    					?>
	    				<?php if ($compare) { //Button?>
		    				<td >
		    					<?php if (strstr($field,"change")=="" && $field!="id" && $cp[$field]!=$value) :?>
		    						<button class="btn btn-default" onclick="accept<?php echo $fieldDb?>FieldChange(<?php echo $id ?>,'<?php echo $field?>');"><span class="glyphicon glyphicon-edit"></span></button>
		    					<?php endif;?>
		    				</td>
		    			<?php }?>
	    			</tr>
	    		<?php }	?>
			</table>
		<?php 
	  	}
		?>
	 </table>  
</p>
<script>
	function delete<?php echo $fieldDb?>Change(id) {
		if (confirm("<?php echo $fieldText?> módosítást akarsz törölni. Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=delete<?php echo $fieldDb?>Change&id="+id;
	}
	function edit<?php echo $fieldDb?>Change(id) {
		document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=show<?php echo $fieldDb?>Change&id="+id;
	}
	function accept<?php echo $fieldDb?>Change(id) {
	if (confirm("<?php echo $fieldText?> módosítást akarsz jóváhagyni. Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=accept<?php echo $fieldDb?>Change&id="+id;
	}
	function accept<?php echo $fieldDb?>FieldChange(id,field) {
		document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=accept<?php echo $fieldDb?>FieldChange&id="+id+"&field="+field;
	}
</script>
<?php }?>

