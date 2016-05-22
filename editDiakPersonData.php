<?php
//Submit a new user
$action=getGetParam("action","");
$submit = $action=="submit_newguest" || $action=="submit_newdiak";
$submitsave = $action=="submit_newguest_save" || $action=="submit_newdiak_save";
$guest=$action=="submit_newguest" || $action=="submit_newguest_save";
//Edit or only view variant this page
$edit = (userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser() );


$dataFieldNames 	=array("lastname","firstname","email");
$dataFieldCaption 	=array("Családnév","Keresztnév","E-Mail");
$dataItemProp       =array("","","");
$dataFieldLengths 	=array(40,40,60);
$dataFieldVisible	=array(false,false,true);
if (!$submit && !$submitsave) {
	array_push($dataFieldNames, "birthname","partner","address","zipcode","place","country","phone","mobil","skype","facebook","homepage","education","employer","function","children");
	array_push($dataItemProp,"","","streetAddress","postalCode","addressLocality","addressCountry","","","","","","","","","","","");
	array_push($dataFieldCaption, "Diákkori név","Élettárs","Cím","Irányítószám","Helység","Ország","Telefon","Mobil","Skype","Facebook","Honoldal","Végzettség","Munkahely","Beosztás","Gyerekek");
	array_push($dataFieldLengths, 40,40,70,6,50,50,30,30,50,20,60,60,60,60,60,20,30);
	array_push($dataFieldVisible, false,false,true,true,true,true,true,true,true,true,true,false,true,true, false,false);
}
if (userIsAdmin()) {
	array_push($dataFieldNames, "facebookid","admin","id", "user", "passw", "geolat", "geolng");
	array_push($dataItemProp,"","","","","","","");
	array_push($dataFieldCaption, "FB-ID","Jogok","ID", "Felhasználó", "Jelszó", "X", "Y");
	array_push($dataFieldLengths, 40,40,40,40,40,40,40);
	array_push($dataFieldVisible, false,false,false,false,false,false,false);
}


//create new diak
if ( ($action=="newdiak" || $action=="newguest" || $action=="submit_newguest" || $action=="submit_newdiak") ) {
	$diak = getPersonDummy();
}


//submit new diak
if ( $submitsave ) {
	$diak["lastname"]=getGetParam("lastname", "");
	$diak["firstname"]=getGetParam("firstname", "");;
	$diak["email"]=getGetParam("email", "");
	if($guest)
		$diak["admin"]="guest";
	if (getGetParam("code", "")!=$_SESSION['SECURITY_CODE']) {
		$resultDBoperation='<div class="alert alert-warning">Bíztonsági kód nem helyes!<br/> Probáld még egyszer.</div>';
	} elseif (checkUserEmailExists(null,$diak["email"])) {
		$resultDBoperation='<div class="alert alert-warning">E-Mail cím már létezik az adatbankban <br/>Új adat kimentése sikertelen.</div>';
	} elseif (filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false) {
		$resultDBoperation='<div class="alert alert-warning">E-Mail nem helyes! <br/>Új adat kimentése sikertelen.</div>';
	} elseif ($diak["lastname"]=="" || $diak["firstname"]=="" ) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév üres! <br/>Új adat kimentése sikertelen.</div>';
	} elseif (strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Új adat kimentése sikertelen.</div>';
	} else {
		savePerson($diak);
		sendNewUserMail($diak["firstname"],$diak["lastname"],$diak["email"],$diak["passw"],"",getAKtScoolClass(),getAktScoolYear(),$diak["id"]);
		if (!userIsAdmin())
			saveLogInInfo("SaveData",$uid,$diak["user"],"",true);
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
if (($uid != 0) && getParam("action","")=="changediak" &&  userIsLoggedOn() ) {
	$diak = getPerson($uid,getAktDatabaseName());
	if ($guest)
		$diak["admin"]="guest";
	for ($i=0;$i<sizeof($dataFieldNames);$i++) {
		$tilde="";
		if ($dataFieldVisible[$i]) {
			if (isset($_GET["cb_".$dataFieldNames[$i]])) 
				$tilde="~";
		}
		//save the fields in the person array
		if (isset($_GET[$dataFieldNames[$i]]))
			$diak[$dataFieldNames[$i]]=$tilde.$_GET[$dataFieldNames[$i]];
	}
	if (checkUserEmailExists($diak["id"],$diak["email"])) {
		$resultDBoperation='<div class="alert alert-warning">E-Mail cím már létezik az adatbankban!<br/>Az adatok kimentése sikertelen.</div>';
	} elseif (filter_var($diak["email"],FILTER_VALIDATE_EMAIL)==false) {
		$resultDBoperation='<div class="alert alert-warning">E-Mail nem helyes! <br/>Az adatok kimentése sikertelen.</div>';
	} elseif ($diak["lastname"]=="" || $diak["firstname"]=="" ) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév üres! <br/>Az adatok  sikertelen.</div>';
	} elseif (strlen($diak["lastname"])<3 || strlen($diak["firstname"])<3) {
		$resultDBoperation='<div class="alert alert-warning">Családnév vagy Keresztnév rövidebb mit 3 betű! <br/>Az adatok kimentése sikertelen.</div>';
	} else {
		savePerson($diak);
		$resultDBoperation='<div class="alert alert-success" >Az adatok sikeresen módósítva!</div>';
		if (!userIsAdmin())
			saveLogInInfo("SaveData",$uid,$diak["user"],"",true);
	}
}


//person data fields
?>
		<div class="diak_picture" style="display: inline-block;">
			<img src="images/<?php echo($diak["picture"]);?>" border="0" alt="" itemprop="image" class="diak_image" />
		</div>
		<?php if ($edit && $action!="newdiak" && $action!="newguest") {   //Change Profile Image?>
			<div style="display: inline-block;margin:15px;vertical-align: bottom;">
				<form enctype="multipart/form-data" action="editDiak.php" method="post">
					<span>Válassz egy új képet max. 2MByte</span>
					<input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" />	
					<button style="margin-top:5px;" type="submit" class="btn btn-default" title="Feltölti a kivásztott képet" ><span class="glyphicon glyphicon-save"></span> Feltölt</button>
					<input type="hidden" value="upload_diak" name="action" />
					<input type="hidden" value="<?PHP echo($uid) ?>" name="uid" />
					<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
				</form>
			</div>
			<?php if (getLoggedInUserId()<>$diak["id"]) {  //Don't delete myself?>
			<div style="display: inline-block;margin:15px;vertical-align: bottom;">
				<button onclick="deleteDiak(<?php echo("'".getAktDatabaseName()."','".$diak["id"]."'");?>);" class="btn btn-default"><span class="glyphicon glyphicon glyphicon-remove-circle"></span> Diákot véglegesen kitöröl!</button>
			</div>
			<?php } ?>
		<?php } ?>
		<?php if ($edit) {?>
			<div style="display: inline-block;margin:15px;vertical-align: bottom;">
				<button onclick="document.forms['edit_form'].submit();" class="btn btn-default"><span class="glyphicon glyphicon-floppy-disk"></span> Kiment</button>
			</div>
		<?php } ?>
		<?php if ($submit) {?>
			<div style="display: inline-block;margin:15px;vertical-align: bottom;">
				<div class="input-group input-group-sl" >
					<span style="min-width:110px; text-align:right" class="input-group-addon" >Biztonsági kód:</span>
					<input id="code" type="text" size="6" value="" placeholder="Kód" class="form-control"/>
					<div class="input-group-btn">
						<img style="vertical-align: middle;" alt="" src="SecurityImage/SecurityImage.php" />
					</div>
				</div>
				<div>&nbsp;</div>
				<button onclick="$('#idcode').val($('#code').val());$('#form_action').val('<?php echo($action);?>_save');document.forms['edit_form'].submit();" class="btn btn-default"><span class="glyphicon glyphicon-floppy-disk"></span> Új személy létrehozása!</button>
			</div>
		<?php }?>
	</div>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	<?php 
	if ($edit || $submit) {
		echo('<div style="min-height:30px" class="input-group">');
      	echo('<span style="min-width:110px;" class="input-group-addon" >&nbsp;</span>');
      	echo('<span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>');
		echo('<input type="text" readonly  id="highlight" class="form-control" value="Ha azt szeretnéd, hogy az adataidat csak az osztálytársaid lássák, akkor jelöld meg öket!" />');
   		echo('</div>');	
		echo('<form action="'.$SCRIPT_NAME.'" method="get" name="edit_form" >');
	}
	?>
	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
	<?php for ($i=0;$i<sizeof($dataFieldNames);$i++) {?>
		<div class="input-group">
			<?php if ($edit || $submit) {?>
				<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>	      		<span style="width:40px" id="highlight" class="input-group-addon">
	      		<?php if ($dataFieldVisible[$i]) {
	        		echo('<input type="checkbox" name="cb_'.$dataFieldNames[$i].'" '.getFieldChecked($diak,$dataFieldNames[$i]).' title="A megjelölt mezöket csak az osztálytásaid látják." >');
	      		} ?>
	      		</span>
	      		<?php   
		    	echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'" />');
			} else {
				if (showField($diak,$dataFieldNames[$i])) {
					$itemprop=$dataItemProp[$i]==""?"":'itemprop="'.$dataItemProp[$i].'"';
					?>
					<span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span>
					<?php
					echo('<input type="text" '.$itemprop.' class="form-control" readonly value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'" />');
				 }
			}?>
		</div>	
	<?php } ?>
	</div>
	<?php 
	if ($edit || $submit) {
		//echo('<button style="margin-top:5px;margin-bottom:5px;" type="submit" class="btn btn-default" title="Adatok kimentése" ><span class="glyphicon glyphicon-floppy-disk"></span>'.getTextRes("Save").'</button>');
		echo('<input id="form_action" type="hidden" value="changediak" name="action" />');
		echo('<input type="hidden" value="'.$diak["id"].'" name="uid" />');
		echo('<input id="idcode" type="hidden" value="" name="code" />');
		echo('<input type="hidden" value="'.$tabOpen.'" name="tabOpen" />');
		echo('</form>');
	}
	echo('</div>');

?>