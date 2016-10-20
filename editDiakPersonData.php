<?php
//Submit a new user
$action=getGetParam("action","");

//Attempt to subnit a new person 
$submit = $action=="submit_newguest" || $action=="submit_newdiak";

//Save the new person
$submitsave = $action=="submit_newguest_save" || $action=="submit_newdiak_save";

//Guest or normal user (Guest have in the admin field the value guest)
$guest=$action=="submit_newguest" || $action=="submit_newguest_save";

//Edit or only view variant this page
$edit = (userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() );

//preparation of the field to be edited and the itemprop characteristic
$dataFieldNames 	=array("lastname","firstname","email");
$dataFieldCaption 	=array("Családnév","Keresztnév","E-Mail");
$dataItemProp       =array("","","");
$dataCheckFieldVisible	=array(false,false,true);
if (!$submit && !$submitsave) {
	array_push($dataFieldNames, "birthname","partner","address","zipcode","place","country","phone","mobil","skype","facebook","twitter","homepage","education","employer","function","children");
	array_push($dataItemProp,"","","streetAddress","postalCode","addressLocality","addressCountry","","","","","","","","","","","","");
	array_push($dataFieldCaption, "Diákkori név","Élettárs","Cím","Irányítószám","Helység","Ország","Telefon","Mobil","Skype","Facebook","Twitter","Honoldal","Végzettség","Munkahely","Beosztás","Gyerekek");
	array_push($dataCheckFieldVisible, false,false,true,true,false,false,true,true,true,false,false,true,true,false,true,true, false,false);
}
if (userIsAdmin()) {
	array_push($dataFieldNames, "facebookid","admin","id", "user", "passw", "geolat", "geolng");
	array_push($dataItemProp,"","","","","","","");
	array_push($dataFieldCaption, "FB-ID","Jogok","ID", "Felhasználó", "Jelszó", "X", "Y");
	array_push($dataCheckFieldVisible, false,false,false,false,false,false,false);
}
if ($classId==0 ) {
	$dataFieldCaption[16]="Tantárgy";
	$dataFieldCaption[18]="Osztályfönök";
}


//create new person in case of submittin a new one
if ( ($action=="newdiak" || $action=="newguest" || $action=="submit_newguest" || $action=="submit_newdiak") || $action=="submit_newguest_save" || $action=="submit_newdiak_save")  {
	$diak = createNewPerson(getAktClassName(),$guest);
}


//save the person data
if ( $submitsave ) {
	$diak["lastname"]=getGetParam("lastname", "");
	$diak["firstname"]=getGetParam("firstname", "");;
	$diak["email"]=getGetParam("email", "");
	//while submiting a new person no user is logged on so lets check if the user is human
	if (getGetParam("code", "")!=$_SESSION['SECURITY_CODE']) {
		$resultDBoperation='<div class="alert alert-warning">Bíztonsági kód nem helyes!<br/> Probáld még egyszer.</div>';
	//No dublicate email address is allowed
	} elseif (checkUserEmailExists(null,$diak["email"]) ) {
		$resultDBoperation='<div class="alert alert-warning">E-Mail cím már létezik az adatbankban <br/>Új adat kimentése sikertelen.</div>';
	} elseif (filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false && !userIsAdmin()) {
		$resultDBoperation='<div class="alert alert-warning">E-Mail nem helyes! <br/>Új adat kimentése sikertelen.</div>';
	} elseif (($diak["lastname"]=="" || $diak["firstname"]=="" )&& !userIsAdmin()) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév üres! <br/>Új adat kimentése sikertelen.</div>';
	} elseif ((strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3)&& !userIsAdmin()) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Új adat kimentése sikertelen.</div>';
	} else {
		savePerson($diak);
		sendNewUserMail($diak["firstname"],$diak["lastname"],$diak["email"],$diak["passw"],"",getAKtScoolClass(),getAktScoolYear(),$diak["id"]);
		if (!userIsAdmin())
			saveLogInInfo("SaveData",$personid,$diak["user"],"",true);
	}
	//Something went wrong we stay on the submit page
	if ($resultDBoperation!="") {
		$action=substr($action, 0,strlen($action)-5);
		$submit = true;
		$submitsave = false;
	//New user is saved we leave the submit page.
	} else {
		$submit = false;
		$submitsave = false;
		$resultDBoperation='<div class="alert alert-success" >Személy sikeresen kimetve!<br/>Köszönjük szépen a bizalmadat a Véndiákok oldala iránt<br/>Bejelentkezési adatok a megadott mailcímre el vannak küldve.</div>';
	}
}

//Retrive changed data and save it
if (($personid != 0) && getParam("action","")=="changediak" &&  userIsLoggedOn() ) {
	$diak = getPerson($personid,getAktDatabaseName());
	if ($diak==null) {
		$diak = createNewPerson(getAktDatabaseName(),$guest);
	}
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
	} elseif (filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false && !userIsAdmin()) {
		$resultDBoperation='<div class="alert alert-warning">E-Mail nem helyes! <br/>Az adatok kimentése sikertelen.</div>';
	} elseif (($diak["lastname"]=="" || $diak["firstname"]=="" ) && !userIsAdmin()) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév üres! <br/>Az adatok  sikertelen.</div>';
	} elseif ((strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) && !userIsAdmin()) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Az adatok kimentése sikertelen.</div>';
	} else {
		savePerson($diak);
		$resultDBoperation='<div class="alert alert-success" >Az adatok sikeresen módósítva!</div>';
		if (!userIsAdmin())
			saveLogInInfo("SaveData",$personid,$diak["user"],"",true);
	}
}



?>
	<?php //Person picture?>
	<div class="diak_picture" style="display: inline-block;">
		<img src="images/<?php echo($diak["picture"]);?>" border="0" alt="" itemprop="image" class="diak_image" title="<?php echo $diak["lastname"]." ".$diak["firstname"]?>" />
	</div>
	
	<?php //Person picture download?>
	<?php if ($edit && $action!="newdiak" && $action!="newguest") {  ?>
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
		<?php  //Don't delete myself?>
		<?php if (!(getLoggedInUserId()==$diak["id"] && getAktClass()==getLoggedInUserClassId())) { ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="deleteDiak(<?php echo("'".getAktDatabaseName()."','".$diak["id"]."'");?>);" class="btn btn-default"><span class="glyphicon glyphicon glyphicon-remove-circle"></span> Véglegesen kitöröl </button>
		</div>
		<?php } ?>
	<?php } ?>
	
	<?php //Save Button?>
	<?php if ($edit) {?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="document.forms['edit_form'].submit();" class="btn btn-default"><span class="glyphicon glyphicon-floppy-disk"></span> Kiment</button>
		</div>
	<?php } ?>
	
	<?php //Secutiry code and create new person button?>
	<?php if ($submit) {?>
		<div style="display: inline-block;margin-bottom:15px;vertical-align: bottom; width:295px">
			<div class="input-group input-group-sl" >
				<span style="min-width:80px; text-align:right" class="input-group-addon" >Biztonsági kód:</span>
				<input id="code" type="text"  value="" placeholder="Kód" class="form-control"/>
				<div class="input-group-btn">
					<img style="width:100px" class="form-control" alt="security code" src="SecurityImage/SecurityImage.php" />
				</div>
			</div>
			<div>&nbsp;</div>
			<button onclick="$('#idcode').val($('#code').val());$('#form_action').val('<?php echo($action);?>_save');document.forms['edit_form'].submit();" class="btn btn-default"><span class="glyphicon glyphicon-floppy-disk"></span> Új személy létrehozása!</button>
		</div>
	<?php }?>
	
	
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	
	<?php 
	if ($edit || $submit) {
		echo('<div style="min-height:30px" class="input-group">');
      	echo('<span style="min-width:110px;" class="input-group-addon" >&nbsp;</span>');
      	echo('<span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>');
		echo('<input type="text" readonly  id="highlight" class="form-control" value="Ha azt szeretnéd, hogy az adataidat csak a bejelentkezett diákok/osztálytársak lássák, akkor jelöld meg öket!" />');
   		echo('</div>');	
		echo('<form action="'.$SCRIPT_NAME.'" method="get" name="edit_form" >');
	}
	?>
	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress" style="overflow-x: hidden;">
	<?php for ($i=0;$i<sizeof($dataFieldNames);$i++) {?>
		<div class="input-group">
			<?php if ($edit || $submit) {?>
				<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>	      		
				<span style="width:40px" id="highlight" class="input-group-addon">
		      		<?php if ($dataCheckFieldVisible[$i]) {
		        		echo('<input type="checkbox" name="cb_'.$dataFieldNames[$i].'" '.getFieldChecked($diak,$dataFieldNames[$i]).' title="A megjelölt mezöket csak az osztálytásaid látják." />');
		      		} ?>
	      		</span>
	      		<?php   
	      		$dataFieldNames[$i]=="email" ? $emc=' onkeyup="fieldChanged();validateEmailInput(this);" ' : $emc=' onkeyup="fieldChanged();"';
	      		echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'"'.$emc.'/>');
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
						foreach ($c as $cc)
							echo('<a href="hometable.php?scoolYear='.substr($cc,3,4).'&scoolClass='.substr($cc,0,3).'">'.$cc.'</a> ');
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
	if ($edit || $submit) {
		echo('<input type="hidden" name="action"	value="changediak" id="form_action" />');
		echo('<input type="hidden" name="uid"		value="'.$diak["id"].'"  />');
		echo('<input type="hidden" name="code"		value=""  id="idcode" />');
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