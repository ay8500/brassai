<?php 
include('homemenu.php');
include_once('tools/userManager.php');
$resultDBoperation="";

if (userIsAdmin()) {
	$id=getIntParam("id"); 
	$ret=false;
	$show=false;
  	if (getParam("action")=="deletePersonChange") {
  		$ret =$db->deletePersonEntry($id);$show=true;
	} 	
  	if (getParam("action")=="acceptPersonChange") {
  		$ret =$db->acceptChangeForPerson($id);$show=true;
	} 	
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
			$resultDBoperation='<div class="alert alert-success" > Rendben, a müvelet sikerült</div>';}
		else {
			$resultDBoperation='<div class="alert alert-danger" > Sajnos nem sikerült a müvelet!</div>';}
	}
}  	
?>
<div class="sub_title">Adatok vizsgálása és jóváhagyása</div>
<?PHP
if (userIsAdmin()) {  

	//initialise tabs
	$tabsCaption=Array("Személyek","Képek","Üzenetek","Hozzáférések");
	include("tabs.php");
	?>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	
	<?php if ($tabOpen==0) {
		$list=$db->getPersonListToBeChecked();
	?>
	<p align="center">
	   Személyek:<br/>	
	   <?php //** The table of the person changes?>
	  <table align="center" border="1">
	    <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;"><td>Név</td><td>Datum</td><td>Ip</td><td colspan="3">Akció</td></tr>
	  	<?php
	  		foreach ($list as $i=>$l) {
	   			echo('<tr >');
	   			echo("<td>".$l["lastname"]."&nbsp;".$l["firstname"]."</td>");
	   			echo("<td>".$l["changeDate"]."</td>");
	   			echo("<td>".$l["changeIP"]."</td>");
	  			if ($l["changeForIDjoin"]!=null) {
	   				echo('<td><button class="btn btn-default" onclick="editChange('.$l["id"].');"><span class="glyphicon glyphicon-edit"></span></button></td>');
	   			}else{
	   				echo('<td><button class="btn btn-default" onclick="editChange('.$l["id"].');"><span class="glyphicon glyphicon-open"></span></button></td>');
	   			}
	   			echo('<td><button class="btn btn-default" onclick="acceptChange('.$l["id"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
	   			echo('<td><button class="btn btn-default" onclick="deleteChange('.$l["id"].');"><span class="glyphicon glyphicon-remove"></span></button></td>');
	   			echo("</tr>");
	  		}
	  	?>
	  	<?php 
	  	if ((getParam("action")=="showPersonChange" || getParam("action")=="acceptPersonFieldChange") && userIsAdmin()) {
	  		$cp=$db->getPersonByID($id,true);
	  		if ($compare = isset($cp["changeForID"])) 
	  			$op=$db->getPersonByID($cp["changeForID"],true);
	  		else 
	  			$op=getPersonDummy();
	  		if (getParam("action")=="acceptPersonFieldChange") {
	  			$field=getParam("field");
	  			$op[$field]=$cp[$field];
	  			if ($db->savePerson($op)>=0) {
					$resultDBoperation='<div class="alert alert-success" > Rendben, a müvelet sikerült</div>';}
				else {
					$resultDBoperation='<div class="alert alert-danger" > Sajnos nem sikerült a müvelet!</div>';}
	  		}
		?>
			<?php //** The table of the changes for a person?>
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
	    				<?php if ($field!="picture") {?>
	    					<?php if ($compare) {?>
	    						<td class="acceptField"><?php echo $value ?></td>
	    					<?php }?>
	    					<td class="acceptField" <?php echo $s?>><?php echo $cp[$field]?></td>
	    				<?php } else {?>
		    				<?php if ($compare) {?>
		    					<td class="acceptField"><img class="acceptField" src="images/<?php echo $value ?>" /></td>
		    				<?php }?>
		    				<td class="acceptField"><img class="acceptField" src="images/<?php echo $cp[$field]?>"/></td>
	    				<?php }?>
	    				<?php if ($compare) {?>
		    				<td >
		    					<?php if (strstr($field,"change")=="" && $field!="id" && $cp[$field]!=$value) :?>
		    						<button class="btn btn-default" onclick="acceptFieldChange(<?php echo $id ?>,'<?php echo $field?>');"><span class="glyphicon glyphicon-edit"></span></button>
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
	<?php }?>
	
	
	<?php if ($tabOpen==1) { ?>
	<p align="center">
	   Képek:<br/>	
	</p>
	<?php }?>
	
	
	<?php if ($tabOpen==2) {
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
	   			echo("<td>".$l["changeIP"]."</td>");
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


	<?php if ($tabOpen==3) { ?>
	<p align="center">
	   Hozzáférések:<br/>
	   	  <table align="center" border="1">
	    <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
	    	<td>IP</td><td>Datum</td><td>Type</td><td>Számláló</td><td>Akció</td>
	    </tr>
	  	<?php
	  		$list = $db->getListOfRequest(0);
	  		foreach ($list as $i=>$l) {
	   			echo('<tr >');
   				echo("<td>".$l["ip"]."</td>");
   				echo("<td>"."</td>");
	   			echo("<td>".getConstantName("changeType",$l["typeID"])."</td>");
	   			echo("<td>".$l["count"]."</td>");
   				if ($l["count"]>1)
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
	echo '<div class="alert alert-danger" >Adat hozzáférési jog hiányzik!</div>';
	
include 'homefooter.php';
?>

<script>
	function deleteChange(id) {
		if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=deletePersonChange&id="+id;
	}
	function editChange(id) {
		document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=showPersonChange&id="+id;
	}
	function acceptChange(id) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=acceptPersonChange&id="+id;
	}
	function acceptFieldChange(id,field) {
		document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=acceptPersonFieldChange&id="+id+"&field="+field;
	}
	function deleteMessageChange(id) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=deleteMessageChange&id="+id;
	}
	function acceptMessageChange(id) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=acceptMessageChange&id="+id;
	}
	function deletePictureChange(id) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=deletePictureChange&id="+id;
	}
	function editPictureChange(id) {
		document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=editPictureChange&id="+id;
	}
	function acceptPictureChange(id) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=acceptPictureChange&id="+id;
	}
	function resetChange(ip,type) {
	    if (confirm("Biztos vagy?"))
			document.location="dataCheck.php?tabOpen=<?php echo getParam("tabOpen")?>&action=resetChange&type="+type+"&ip="+ip;
	}
</script>


