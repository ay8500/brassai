	<span style="font-size: 10px"><a href="http://ec.europa.eu/justice/smedataprotect/index_hu.htm" title="GDPR az Európai Unió általános adatvédelmi rendelete">GDPR:</a> A személyek személyes adatai kizárolag azt e célt szolgálják, hogy ezt az oldalt bővítsék. 
	A beadott személyes adatok egy web szerveren vannak tárolva (Karlsruhe Németország) az <a href="https://unternehmen.1und1.de/rechenzentren/">"1 und 1"</a> cég szamitógépközpontjában. 
	Biztonsági másolatok a személyes adatokról csak a internetoldal tulajdonos privát számítogépein és az internet szerveren léteznek. Ezek az adatok maximum 6 hónapig vannak tárolva. 
	A személyes adatok megjelennek külömbőző internet kereső oldalok találati listáján. 
	A védett mezők tartalma anonim felhasználok ellen védve vannak. </span><br/>
	
	<?php //Person picture?>
	<div class="diak_picture" style="display: inline-block;">
		<img src="<?php echo getPersonPicture($diak)?>" border="0" alt="" itemprop="image" class="diak_image" title="<?php echo $diak["lastname"]." ".$diak["firstname"]?>" />
		<?php if (isset($diak["deceasedYear"])&& intval($diak["deceasedYear"])>=0) {?>
		<div style="background-color: black;color: white;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">
			<?php echo intval($diak["deceasedYear"])==0?"†":"† ".intval($diak["deceasedYear"]); ?>
		</div>
		<?php }?>
		<?php  if (userIsAdmin() || userIsSuperuser()) {?>
			<br/><a href="history.php?table=person&id=<?php echo $diak["id"]?>" title="módosítások" style="position: relative;top: -37px;left: 10px;display:inline-block;">
				<span class="badge"><?php echo sizeof($db->getHistoryInfo("person",$diak["id"]))?></span>
			</a>
		<?php }?>
	</div>
	
	<?php //Person picture download only  if person already saved?>
	<?php if (($edit || $createNewPerson) && strlen(trim($diak["lastname"]))>2 && strlen(trim($diak["firstname"]))>2) {  ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<form enctype="multipart/form-data" action="editDiak.php" method="post">
				<h4>Profiképed</h4>
				<div style="margin-bottom: 5px;">A perfekt profilkép ez érettségi tablon felhasznált képed, kicsengetési kártya képe vagy bármilyen privát arckép.</div>
				<span>Válassz egy új jpg képet max. 2MByte</span>
				<input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" />	
				<button style="margin-top:5px;" type="submit" class="btn btn-info" title="Feltölti a kivásztott képet" ><span class="glyphicon glyphicon-save"></span> Feltölt</button>
				<?php  if (userIsAdmin()){?>
					<button style="display: inline-block;margin: 5px 10px 0 10px;" class="btn btn-danger" name="overwriteFileName" value="<?php echo $diak["picture"]?>"><span class="glyphicon glyphicon-upload"></span> Kicserél</button>
				<?php }?>					
				<input type="hidden" value="upload_diak" name="action" />
				<input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
				<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
			</form>
		</div>
	<?php } ?>
   <?php if ($edit) {  ?>	
		<?php //Don't delete myself?>
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
	<div style="display: inline-block;margin:15px;vertical-align: bottom;">
		<button onclick="document.location='gdpr.php?id=<?php echo($diak["id"]);?>';" class="btn btn-default"><span class="glyphicon glyphicon-exclamation-sign"></span> Személyes adatok védelme</button>
	</div>
	
<form method="get" name="edit_form" >	
	<?php if (($edit || $createNewPerson) && !$anonymousEditor && userIsLoggedOn()) {
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
				<?php if ( userIsLoggedOn()) {?>	      		
				<span style="width:40px" id="highlight" class="input-group-addon">
		      		<?php if ($dataCheckFieldVisible[$i]) {
		        		echo('<input type="checkbox" name="cb_'.$dataFieldNames[$i].'" '.getFieldChecked($diak,$dataFieldNames[$i]).' title="A megjelölt mezöket csak az osztálytásaid látják." />');
		      		} ?>
	      		</span>
	      		<?php }?>
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
					if ($db->isClassIdForStaf($classId) && $dataFieldNames[$i]=="children") {
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