<?php 
include('homemenu.php');
include_once('userManager.php');
if (userIsAdmin()) {
  		 
  	if (getGetParam("deleteChange", "")!="") {
  		$db->deletePersonEntry(intval(getGetParam("deleteChange", "")));
	} 	
  	if (getGetParam("acceptChange", "")!="") {
  		$db->acceptChangeForPerson(intval(getGetParam("acceptChange", "")));
	} 	
}  	
?>
<div class="sub_title">Adatok vizsgálása</div>
<?PHP
if (userIsAdmin()) {  

	//initialise tabs
	$tabsCaption=Array("Személyek","Képek","Üzenetek");
	include("tabs.php");
	?>
	
	<?php if ($tabOpen==0) {
		$list=$db->getPersonListToBeChecked();
	?>
	<p align="center">
	   Személyek:<br/>	
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
	   				echo('<td><button class="btn btn-default" onclick="acceptChange('.$l["id"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
	   			}else{
	   				echo("<td></td>");
	   				echo("<td></td>");
	   			}
	   			echo('<td><button class="btn btn-default" onclick="deleteChange('.$l["id"].');"><span class="glyphicon glyphicon-remove"></span></button></td>');
	   			echo("</tr>");
	  		}
	  	?>
	 </table>  
	</p>
	<?php }?>
	<?PHP if ($tabOpen==1) { ?>
	<p align="center">
	   Képek:<br/>	
	</p>
	<?PHP }?>
	<?PHP if ($tabOpen==2) { ?>
	<p align="center">
	   Üzenetek:<br/>	
	</p>
	<?PHP }?>

<?PHP }
else
	echo "<div>Adat hozzáférési jog hiányzik!</div>";
	
include 'homefooter.php';
?>

<script>
	function deleteChange(id) {
		document.location="dataCheck.php?deleteChange="+id;
	}
	function editChange(id) {
		document.location="dataCheck.php?editChange="+id;
	}
	function acceptChange(id) {
		document.location="dataCheck.php?acceptChange="+id;
	}
</script>


