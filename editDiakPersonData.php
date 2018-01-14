	<?php //Person picture?>
	<div class="diak_picture" style="display: inline-block;">
		<img src="images/<?php echo($diak["picture"]);?>" border="0" alt="" itemprop="image" class="diak_image" title="<?php echo $diak["lastname"]." ".$diak["firstname"]?>" />
	</div>
	
	<?php //Person picture download only  if person already saved?>
	<?php if (($edit || $createNewPerson) && strlen(trim($diak["lastname"]))>2 && strlen(trim($diak["firstname"]))>2) {  ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<form enctype="multipart/form-data" action="editDiak.php" method="post">
				<span>Válassz egy új képet max. 2MByte</span>
				<input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" />	
				<button style="margin-top:5px;" type="submit" class="btn btn-info" title="Feltölti a kivásztott képet" ><span class="glyphicon glyphicon-save"></span> Feltölt</button>
				<input type="hidden" value="upload_diak" name="action" />
				<input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
				<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
			</form>
		</div>
	<?php } ?>
   <?php if ($edit) {  ?>	
		<?php //Don't delete myself and if no user logged on?>
		<?php if (!(getLoggedInUserId()==$diak["id"] ) && (userIsEditor() || userIsSuperuser() || userIsAdmin()) ) { ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="deleteDiak(<?php echo($diak["id"]);?>);" class="btn btn-danger"><span class="glyphicon glyphicon glyphicon-remove-circle"></span> Véglegesen kitöröl </button>
		</div>
		<?php } ?>
	<?php } ?>
	
	<?php //Save button?>
	<?php if ($edit || $createNewPerson) {?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button id="saveButton" onclick="document.forms['edit_form'].submit();" class="btn btn-success"><span class="glyphicon glyphicon-floppy-disk"></span> Kiment</button>
		</div>
	<?php } ?>
	
	<?php //Anonymous user edit button?>
	<?php if (!$edit && !$createNewPerson) {?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="document.location='editDiak.php?anonymousEditor=true';" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> Módosítani szeretnék</button>
		</div>
	<?php } ?>
	
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
<form action="<?php echo $SCRIPT_NAME ?>" method="get" name="edit_form" >
	
	<?php if (($edit || $createNewPerson) && !$anonymousEditor ) {
		$optionClasses=$db->getClassList(getAktSchoolId());
		unset($optionClasses[0]); //The first class is the teachers list
	?>
		<div style="min-height:30px" class="input-group">
      		<span style="min-width:110px;" class="input-group-addon" >&nbsp;</span>
      		<span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
			<input type="text" readonly  id="highlight" class="form-control" value="Ha azt szeretnéd, hogy az adataidat csak a bejelentkezett diákok/osztálytársak lássák, akkor jelöld meg öket!" />
   		</div>
   		<div style="min-height:30px" class="input-group">
      		<span style="min-width:110px;" class="input-group-addon" >Iskola</span>
      		<span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
			<select class="form-control">
				<option>Brassai Sámuel líceum</option>
			</select>
   		</div>
   		<?php if (getAktClassId()!=0) {?>	
   		<div style="min-height:30px" class="input-group">
      		<span style="min-width:110px;" class="input-group-addon" >Osztály</span>
      		<span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
			<select class="form-control" name="classID" id="classID">
				<option value="-1" >...válassz...</option>
				<?php foreach ($optionClasses as $optionClass) {?>
					<option value="<?php echo $optionClass["id"]?>" <?php echo ($optionClass["id"]==$diak["classID"])?"selected":""?>><?php echo $optionClass["text"]?></option>
				<?php } ?>
			</select>
   		</div>	
   		<?php } else {?>
   			<input type="hidden" name="classID" value="0" />
   		<?php } ?>
   	<?php } ?>
   	
   	
	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress" style="overflow-x: hidden;">
	<?php for ($i=0;$i<sizeof($dataFieldNames);$i++) {?>
		<div class="input-group">
			<?php
			//Placeholder
			$obl=$dataFieldObl[$i];
			$dataFieldObl[$i]===true ? $obl="kötelező mező":false ;
			//Inpufields
			if (($edit ||$createNewPerson) && !$anonymousEditor ) {?>
				<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>	      		
				<span style="width:40px" id="highlight" class="input-group-addon">
		      		<?php if ($dataCheckFieldVisible[$i]) {
		        		echo('<input type="checkbox" name="cb_'.$dataFieldNames[$i].'" '.getFieldChecked($diak,$dataFieldNames[$i]).' title="A megjelölt mezöket csak az osztálytásaid látják." />');
		      		} ?>
	      		</span>
	      		<?php   
	      		$dataFieldNames[$i]=="email" ? $emc=' onkeyup="fieldChanged();validateEmailInput(this);" ' : $emc=' onkeyup="fieldChanged();"';
	      		echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'"'.$emc.' placeholder="'.$obl.'"/>');
	      		if ($dataFieldNames[$i]=="changeUserID") {
	      			$person=$db->getPersonById(getFieldValueNull($diak,$dataFieldNames[$i]));
	      			echo('<span class="input-group-addon"><span class="">'.$person["lastname"]." ".$person["firstname"].'</span></span>');
	      		}?>
			<?php //Inpufields new person
			} elseif ($anonymousEditor) {?>
				<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>	      		
	      		<?php 
	      		if (getFieldChecked($diak,$dataFieldNames[$i])=="") {
	      			$dataFieldNames[$i]=="email" ? $emc=' onkeyup="fieldChanged();validateEmailInput(this);" ' : $emc=' onkeyup="fieldChanged();"';
	      			echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'"'.$emc.' placeholder="'.$obl.'"/>');
	      		} else {
	      			echo('<input type="text" class="form-control" value="" readonly name="" placeholder="Ez a mező védve van, csak osztálytársak láthatják."/>');
	      		}
	      	//Display fields
			} else {
				if (showField($diak,$dataFieldNames[$i])) {
					$itemprop=$dataItemProp[$i]==""?"":'itemprop="'.$dataItemProp[$i].'"';
					?>
					<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>
					<?php
					$fieldString = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "<a target=\"_blank\" href=\"\\0\">\\0</a>",	getFieldValueNull($diak,$dataFieldNames[$i]));
					$fieldString = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/', '<a href="mailto:$1">$1</a>', $fieldString);
					if ($classId==0  && $dataFieldNames[$i]=="children") {
						$c = explode(",", getFieldValueNull($diak,$dataFieldNames[$i]));
						echo('<div  class="form-control" style="height:auto;">');
						foreach ($c as $id=>$cc) {
							if ($id!=0) echo(',');
							$class= $db->getClassByText($cc);
							echo(' <a href="hometable.php?classid='.$class["id"].'">'.$cc.'</a> ');
						}
						echo('</div>');
					} else {
						echo('<div '.$itemprop.' class="form-control" style="height:auto;">'.$fieldString.'</div>');
					}
				 }
			}?>
		</div>	
	<?php } ?>
	</div>
	<?php 
	//Hidden fields action, uid, tabOpen,role
	if ($edit || $createNewPerson) {
		if ($action=="newperson") {
			echo('<input type="hidden" name="action" value="savenewperson" id="form_action" />');
		} 
		else if ($action=="newteacher") {
			echo('<input type="hidden" name="action" value="savenewteacher" id="form_action" />');
		} 
		else if ($action=="newguest") {
			echo('<input type="hidden" name="action" value="savenewguest" id="form_action" />');
		} 
		else {
			echo('<input type="hidden" name="action" value="changediak" id="form_action" />');
		}
		echo('<input type="hidden" name="uid"		value="'.$diak["id"].'"  />');
		echo('<input type="hidden" name="tabOpen"	value="'.$tabOpen.'"  />');
		if (!userIsAdmin())
			echo('<input type="hidden" name="role"	value="'.$diak["role"].'"  />');
	}
?>
</form>

<script>
	function validateEmailInput(sender,button) { 
		if (validateEmail(sender.value)) {
			sender.style.color="green";
			$(button).removeClass("disabled");
		} else {
			sender.style.color="red";
			$(button).addClass("disabled");
		}
	} 

	function validateEmail(mail) {
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(mail);
	}
</script>