<?php
//Submit a new user
$action=getGetParam("action","");

//Create a new person
$newperson = $action=="newperson" || $action=="newguest" || $action=="newteacher"; 

$anonymousEditor=getParam("anonymousEditor")=="true";

//Edit or only view variant this page
$edit = (userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() || $anonymousEditor || $action=="changediak");


//create new person in case of submittin a new one
if ( $newperson ) {
	$diak = getPersonDummy();
	$diak["id"] = -1; 
	$diak["classID"] = getAktClass();
	$action=="newteacher" ? $diak["isTeacher"]=1	:	$diak["isTeacher"]=0;
	$action=="newguest"   ? $diak["role"]="guest"	:	$diak["role"]="";
	$newid = $db->savePerson($diak);
	if ($newid>=0)
		$diak["id"]=$newid; 
	else
		$resultDBoperation='<div class="alert alert-danger" >Személy kimetése nem sikerült! Hibakód:4912</div>';
}



//preparation of the field to be edited and the itemprop characteristic
$dataFieldNames 	=array("lastname","firstname","email");
$dataFieldCaption 	=array("Családnév","Keresztnév","E-Mail");
$dataItemProp       =array("","","");
$dataCheckFieldVisible	=array(false,false,true);
$dataFieldObl			=array(true,true,true);
if(true)  { //Name
	array_push($dataFieldNames, "birthname","partner","address","zipcode","place","country");
	array_push($dataItemProp,"","","streetAddress","postalCode","addressLocality","addressCountry");
	array_push($dataFieldCaption, "Diákkori név","Élettárs","Cím","Irányítószám","Helység","Ország");
	array_push($dataCheckFieldVisible, false,false,true,true,false,false);
	array_push($dataFieldObl		, false,false,false,false,false,false);
}
if (true) { //Communication
	array_push($dataFieldNames, "phone","mobil","skype","facebook","twitter","homepage","education","employer","function","children");
	array_push($dataItemProp,"","","","","","","","","","","","");
	array_push($dataFieldCaption,"Telefon","Mobil","Skype","Facebook","Twitter","Honoldal","Végzettség","Munkahely","Beosztás","Gyerekek");
	array_push($dataCheckFieldVisible,true ,true ,true ,false,false,true ,true ,false,true ,true ,false,false);
	array_push($dataFieldObl		, false,false,false,false,false,false,false,false,false,false,false,false);
}
if (userIsAdmin()) { //only for admin
	array_push($dataFieldNames, "facebookid","role","id", "user", "passw", "geolat", "geolng");
	array_push($dataItemProp,"","","","","","","");
	array_push($dataFieldCaption, "FB-ID","Jogok","ID", "Felhasználó", "Jelszó", "X", "Y");
	array_push($dataCheckFieldVisible, false,false,false,false,false,false,false);
	array_push($dataFieldObl	 	 , false,false,false,false,false,false,false);
}
if (isset($classId) && $classId==0 ) { //Teachers
	$dataFieldCaption[16]="Tantárgy";
	$dataFieldCaption[18]="Osztályfönök";
}


//Retrive changed data and save it
if ($action=="changediak") {
	if (checkRequesterIP(changeType::personchange)) {
		$diak = $db->getPersonByID($personid);
		if ($diak!=null) {
			for ($i=0;$i<sizeof($dataFieldNames);$i++) {
				$tilde="";
				if ($dataCheckFieldVisible[$i]) {
					if (isset($_GET["cb_".$dataFieldNames[$i]])) 
						$tilde="~";
				}
				//save the fields in the person array
				if (isset($_GET[$dataFieldNames[$i]]))
					$diak[$dataFieldNames[$i]]=$tilde.$_GET[$dataFieldNames[$i]];
			}
			//No dublicate email address is allowed
			if (checkUserEmailExists($diak["id"],$diak["email"])) {
				$resultDBoperation='<div class="alert alert-warning">E-Mail cím már létezik az adatbankban!<br/>Az adatok kimentése sikertelen.</div>';
			//Validate the mail address if no admin logged on
			} elseif (isset($diak["email"]) && $diak["email"]!="" && filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false && !userIsAdmin()) {
				$resultDBoperation='<div class="alert alert-warning">E-Mail cím nem helyes! <br/>Az adatok kimentése sikertelen.</div>';
			} elseif (($diak["lastname"]=="" || $diak["firstname"]=="" ) && !userIsAdmin()) {
				$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév üres! <br/>Az adatok kimentése sikertelen.</div>';
			} elseif ((strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) && !userIsAdmin()) {
				$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Az adatok kimentése sikertelen.</div>';
			} else {
				$ret = $db->savePerson($diak);
				if ($ret>=0) {
					$resultDBoperation='<div class="alert alert-success" >Az adatok sikeresen módósítva!<br />Köszönük szépen a segítséged.</div>';
					$db->saveRequest(changeType::personchange);
					if (!userIsAdmin())
						sendHtmlMail(null, "Person is changed id:".$diak["id"], " Person is changed");
						saveLogInInfo("SaveData",$personid,$diak["user"],"",true);
				} else {
					$resultDBoperation='<div class="alert alert-warning" >Az adatok kimentése nem sikerült! Hibakód:1631</div>';
				}
			}
		} else {
			$resultDBoperation='<div class="alert alert-warning" >Az adatok kimentése nem sikerült! Hibakód:1034</div>';
		}
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Az adatok módosítása anonim felhasználok részére korlatozva van.<br/>Kérünk jelentkezz be ahoz, hogy tovább tudd folytatni a módosításokat.</div>';
	}
}


?>
	<?php //Person picture?>
	<div class="diak_picture" style="display: inline-block;">
		<img src="images/<?php echo($diak["picture"]);?>" border="0" alt="" itemprop="image" class="diak_image" title="<?php echo $diak["lastname"]." ".$diak["firstname"]?>" />
	</div>
	
	<?php //Person picture download?>
	<?php if (($edit || $newperson) && trim($diak["lastname"])!="") {  ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<form enctype="multipart/form-data" action="editDiak.php" method="post">
				<span>Válassz egy új képet max. 2MByte</span>
				<input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" />	
				<button style="margin-top:5px;" type="submit" class="btn btn-default" title="Feltölti a kivásztott képet" ><span class="glyphicon glyphicon-save"></span> Feltölt</button>
				<input type="hidden" value="upload_diak" name="action" />
				<input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
				<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
			</form>
		</div>
	<?php } ?>
   <?php if ($edit) {  ?>	
		<?php //Don't delete myself and if no user logged on?>
		<?php if (!(getLoggedInUserId()==$diak["id"] ) && getLoggedInUserId()>0) { ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="deleteDiak(<?php echo($diak["id"]);?>);" class="btn btn-default"><span class="glyphicon glyphicon glyphicon-remove-circle"></span> Véglegesen kitöröl </button>
		</div>
		<?php } ?>
	<?php } ?>
	
	<?php //Save button?>
	<?php if ($edit || $newperson) {?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="document.forms['edit_form'].submit();" class="btn btn-default"><span class="glyphicon glyphicon-floppy-disk"></span> Kiment</button>
		</div>
	<?php } ?>
	
	<?php //Anonymous user edit button?>
	<?php if (!$edit && !$newperson) {?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="document.location='editDiak.php?anonymousEditor=true';" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span> Módosítani szeretnék</button>
		</div>
	<?php } ?>
	
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	
	<?php if (($edit || $newperson) && !$anonymousEditor ) { ?>
		<div style="min-height:30px" class="input-group">
      		<span style="min-width:110px;" class="input-group-addon" >&nbsp;</span>
      		<span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
			<input type="text" readonly  id="highlight" class="form-control" value="Ha azt szeretnéd, hogy az adataidat csak a bejelentkezett diákok/osztálytársak lássák, akkor jelöld meg öket!" />
   		</div>	
   	<?php } ?>
   	
   	<form action="<?php echo $SCRIPT_NAME ?>" method="get" name="edit_form" >
	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress" style="overflow-x: hidden;">
	<?php for ($i=0;$i<sizeof($dataFieldNames);$i++) {?>
		<div class="input-group">
			<?php
			//Inpufields
			if (($edit ||$newperson) && !$anonymousEditor ) {?>
				<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>	      		
				<span style="width:40px" id="highlight" class="input-group-addon">
		      		<?php if ($dataCheckFieldVisible[$i]) {
		        		echo('<input type="checkbox" name="cb_'.$dataFieldNames[$i].'" '.getFieldChecked($diak,$dataFieldNames[$i]).' title="A megjelölt mezöket csak az osztálytásaid látják." />');
		      		} ?>
	      		</span>
	      		<?php   
	      		$dataFieldNames[$i]=="email" ? $emc=' onkeyup="fieldChanged();validateEmailInput(this);" ' : $emc=' onkeyup="fieldChanged();"';
	      		$dataFieldObl[$i] ? $obl="kötelező mező" : $obl="";
	      		echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'"'.$emc.' placeholder="'.$obl.'"/>');
			//Inpufields new person
			} elseif ($anonymousEditor) {?>
				<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>	      		
	      		<?php 
	      		if (getFieldChecked($diak,$dataFieldNames[$i])=="") {
	      			$dataFieldNames[$i]=="email" ? $emc=' onkeyup="fieldChanged();validateEmailInput(this);" ' : $emc=' onkeyup="fieldChanged();"';
	      			$dataFieldObl[$i] ? $obl="kötelező mező" : $obl="";
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
						foreach ($c as $cc) {
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
	if ($edit || $newperson) {
		echo('<input type="hidden" name="action"	value="changediak" id="form_action" />');
		echo('<input type="hidden" name="uid"		value="'.$diak["id"].'"  />');
		echo('<input type="hidden" name="tabOpen"	value="'.$tabOpen.'"  />');
		echo('</form>');
	}

?>


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