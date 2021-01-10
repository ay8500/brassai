	<?php //Person picture?>
	<div class="diak_picture" style="display: inline-block;margin-bottom: 5px;">
		<img src="<?php echo getPersonPicture($diak)?>" border="0" alt="" itemprop="image" class="diak_image" title="<?php echo $diak["lastname"]." ".$diak["firstname"]?>" />
		<?php if (isset($diak["deceasedYear"])&& intval($diak["deceasedYear"])>=0) {?>
		    <div style="background-color: black;color: white;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">
        <?php } else {?>
            <div style="background-color: white;color: black;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">
        <?php }?>
        <?php if (isset($diak["birthyear"])) {?>
			<?php echo "* ".intval($diak["birthyear"]).'&nbsp;&nbsp;'; ?>
        <?php }?>
        <?php if (isset($diak["deceasedYear"])&& intval($diak["deceasedYear"])>=0) {?>
                <?php echo intval($diak["deceasedYear"])==0?"†":"† ".intval($diak["deceasedYear"]); ?>
        <?php }?>
        </div>
		<?php  if (userIsSuperuser()) {?>
			<br/><a href="history?table=person&id=<?php echo $diak["id"]?>" title="módosítások" style="position: relative;top: -37px;left: 10px;display:inline-block;">
				<span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("person",$diak["id"]))?></span>
			</a>
		<?php }?>
	</div>
	
	<?php //Person picture download only  if person already saved?>
	<?php if (($edit || $createNewPerson) && strlen(trim($diak["lastname"]))>2 && strlen(trim($diak["firstname"]))>2) {  ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<form enctype="multipart/form-data" action="editDiak" method="post">
				<h4>Profiképed</h4>
				<div style="margin-bottom: 5px;">A perfekt profilkép ez érettségi tablon felhasznált képed, kicsengetési kártya képe vagy bármilyen privát arckép.</div>
				<span>Válassz egy új jpg képet max. 2MByte</span>
				<input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" />	
				<button style="margin-top:5px;" type="submit" class="btn btn-info" title="Feltölti a kivásztott képet" onclick="showWaitMessage();">
                    <span class="glyphicon glyphicon-upload"></span> Feltölt</button>
                <?php if (isset($diak["picture"]) && $diak["picture"]!=null) {?>
                    <?php  if (userIsAdmin()){
                        $pic=$diak['picture'];
                        $pic=substr($pic,0,strlen($pic)-4)."_o".substr($pic,strlen($pic)-4);
                        ?>
                        <button style="display: inline-block;margin: 5px 10px 0 10px;" class="btn btn-danger" name="overwriteFileName" value="<?php echo $diak["picture"]?>"><span class="glyphicon glyphicon-upload"></span> Kicserél</button>
                        <a class="btn btn-default" target="_download" href="images/<?php echo $pic?>" title="ImageName"><span class="glyphicon glyphicon-download"></span> Letölt</a>
                    <?php }?>
                    <?php if (userIsSuperuser() || getLoggedInUserId()==getRealId($diak)) {?>
                        <button style="display: inline-block;margin: 5px 10px 0 10px;" class="btn btn-danger" name="deletePersonPicture" value="<?php echo $diak["id"]?>"><span class="glyphicon glyphicon-upload"></span> Töröl</button>
                    <?php } ?>
                <?php } ?>
				<input type="hidden" value="upload_diak" name="action" />
				<input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
				<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
			</form>
		</div>
	<?php } ?>
   <?php if ($edit) {  ?>	
		<?php //Don't delete myself?>
		<?php if (!(getLoggedInUserId()==$diak["id"] ) && (userIsSuperuser() || userIsAdmin()) ) { ?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="deleteDiak(<?php echo($diak["id"]);?>);" class="btn btn-danger"><span class="glyphicon glyphicon glyphicon-remove-circle"></span> Véglegesen kitöröl </button>
		</div>
		<?php } ?>
	<?php } ?>
	
	<?php //Save button?>
	<?php if ($edit || $createNewPerson) {?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button id="saveButton" onclick="savePerson();" class="btn btn-success"><span class="glyphicon glyphicon-floppy-disk"></span> Kiment</button>
            <button onclick="location.href='editDiak'" class="btn btn-danger"><span class="glyphicon glyphicon-remove-circle"></span> Mégse </button>
		</div>
	<?php } ?>
	
	<?php //Anonymous user edit button?>
	<?php if (!$edit && !$createNewPerson) {?>
		<div style="display: inline-block;margin:15px;vertical-align: bottom;">
			<button onclick="document.location='editDiak?anonymousEditor=true';" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> Módosítani szeretnék</button>
		</div>
	<?php } ?>
    <?php if (!isActionParam("newperson")) {?>
        <div style="display: inline-block;margin:15px;vertical-align: bottom;">
            <button onclick="goGdpr(<?php echo $diak["id"]?>);" class="btn btn-default"><span class="glyphicon glyphicon-exclamation-sign"></span> Személyes adatok védelme</button>
        </div>
    <?php } ?>

<?php if ($edit || $createNewPerson || $anonymousEditor || true) {?>
    <form method="get" id="editform" action="editDiak.php">
        <?php
        //Editfields school and class
        if (($edit || $createNewPerson) && !$anonymousEditor && userIsLoggedOn()) {
            $optionClasses=$db->getClassList(getRealId(getAktSchool()),true);
        ?>
            <div style="min-height:30px" class="input-group">
                <span style="min-width:110px;" class="input-group-addon" >&nbsp;</span>
                <span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
                <input type="text" readonly  id="highlight" class="form-control" value="Ha azt szeretnéd, hogy az adataidat csak a bejelentkezett diákok/osztálytársak lássák, akkor jelöld meg öket!" />
            </div>
            <div style="min-height:30px" class="input-group">
                <span style="min-width:110px;" class="input-group-addon" >Iskola</span>
                <span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
                <select class="form-control" name="schoolID" id="schoolID">
                    <option value="1">Brassai Sámuel líceum</option>
                </select>
            </div>
            <?php if (isAktClassStaf()) { ?>
                <input type="hidden" name="classID" value="<?php echo getAktClassId()?>" />
            <?php } else { ?>
                <div style="min-height:30px" class="input-group">
                    <span style="min-width:110px;" class="input-group-addon" >Osztály</span>
                    <span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
                    <select class="form-control" name="classID" id="classID">
                        <option value="-1" >...válassz...</option>
                        <?php foreach ($optionClasses as $optionClass) {?>
                            <option value="<?php echo $optionClass["id"]?>" <?php echo intval($optionClass["id"])==getAktClassId()?"selected":""?>><?php echo $optionClass["text"].' '.($optionClass["eveningClass"]!='0'?"esti tagozat":"nappali tagozat")?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
        <?php } else {?>
            <input type="hidden" name="classID" value="<?php echo getAktClassId()?>" />
            <input type="hidden" name="schoolID" value="<?php echo getAktSchoolId()?>" />
        <?php } ?>


        <div itemtype="http://schema.org/PostalAddress" itemprop="address" itemscope style="overflow-x: hidden;" >
        <?php for ($i=0;$i<sizeof($dataFieldNames);$i++) {?>
            <div class="input-group"><?php
                //Placeholder
                $obl=$dataFieldObl[$i];
                $dataFieldObl[$i]===true ? $obl="kötelező mező":false ;
                //field onclick
                $dataFieldNames[$i]=="email" ? $emc=' onkeyup="fieldChanged();validateEmailInput(this);" ' : $emc=' onkeyup="fieldChanged();"';
                //Inpufields
                if (($edit ||$createNewPerson) && !$anonymousEditor ) {?>
                    <span style="padding: 6px;min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span><?php
                    if ( userIsLoggedOn()) {?>
                        <span style="width:40px" id="highlight" class="input-group-addon">
                            <?php if ($dataCheckFieldVisible[$i]) {?>
                                <input type="checkbox" name="cb_<?php echo $dataFieldNames[$i].'" '.getFieldChecked($diak,$dataFieldNames[$i])?> title="A megjelölt mezöket csak az osztálytásaid látják." />
                            <?php } else { echo '&nbsp'; }?>
                        </span><?php
                    }
                    if ($dataItemProp[$i]==="role") {
                            showRoleField(getFieldValueNull($diak,$dataFieldNames[$i]),$dataFieldNames[$i]);
                    } elseif ($dataItemProp[$i]==="title") {
                        showTitleField(getFieldValueNull($diak, $dataFieldNames[$i]), $dataFieldNames[$i]);
                    } elseif ($dataItemProp[$i]==="gender") {
                            showGenderField(getFieldValueNull($diak,$dataFieldNames[$i]),$dataFieldNames[$i]);
                    } else {
                        echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'"'.$emc.' placeholder="'.$obl.'"/>');
                        if ($dataFieldNames[$i]=="changeUserID") {
                            $person=$db->getPersonById(getFieldValueNull($diak,$dataFieldNames[$i]));
                            echo('<span class="input-group-addon"><span class="">'.$person["lastname"]." ".$person["firstname"].'</span></span>');
                        }
                    }
                //Inpufields anonymous user
                } elseif ($anonymousEditor) {?>
                    <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span><?php
                    if (getFieldChecked($diak,$dataFieldNames[$i])=="") {
                        $dataFieldNames[$i]=="email" ? $emc=' onkeyup="fieldChanged();validateEmailInput(this);" ' : $emc=' onkeyup="fieldChanged();"';
                        if ($dataItemProp[$i]==="title") {
                            showTitleField(getFieldValueNull($diak, $dataFieldNames[$i]), $dataFieldNames[$i]);
                        } elseif ($dataItemProp[$i]==="gender") {
                            showGenderField(getFieldValueNull($diak,$dataFieldNames[$i]),$dataFieldNames[$i]);
                        } else {
                            echo('<input type="text" class="form-control" value="'.getFieldValueNull($diak,$dataFieldNames[$i]).'" name="'.$dataFieldNames[$i].'"'.$emc.' placeholder="'.$obl.'"/>');
                        }
                    } else {
                        echo('<input type="text" class="form-control" value="" readonly name="" placeholder="Ez a mező védve van, csak osztálytársak láthatják."/>');
                    }
                //Display fields no editing just show
                } else {
                    if (showField($diak,$dataFieldNames[$i])) {
                        $itemprop="";
                        if ($dataItemProp[$i]!="" && $dataItemProp[$i]!="combobox")
                            $itemprop='itemprop="'.$dataItemProp[$i].'"';
                        ?>
                        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $dataFieldCaption[$i]?></span><?php
                        if ($db->isClassIdForStaf($classId) && $dataFieldNames[$i]=="children") {
                            $c = explode(",", getFieldValueNull($diak,$dataFieldNames[$i]));
                            echo('<div  class="form-control" style="height:auto;">');
                            foreach ($c as $id=>$cc) {
                                if ($id!=0) echo(',');
                                $class= $db->getClassByText($cc);
                                echo(' <a href="hometable?classid='.$class["id"].'">'.$cc.'</a> ');
                            }
                            echo('</div>');
                        } else {
                            if ($dataItemProp[$i]==="title") {
                                showTitleField(getFieldValueNull($diak, $dataFieldNames[$i]), $dataFieldNames[$i],true);
                            } elseif ($dataItemProp[$i]==="gender") {
                                showGenderField(getFieldValueNull($diak,$dataFieldNames[$i]),$dataFieldNames[$i],true);
                            } else {
                                echo('<div ' . $itemprop . ' class="form-control" style="height:auto;">' . createLink(getFieldValueNull($diak, $dataFieldNames[$i])) . '</div>');
                            }
                        }
                     }
                }?>
            </div><?php
        } ?>
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
            if (!userIsAdmin() && !userIsSuperuser())
                echo('<input type="hidden" name="role"	value="'.$diak["role"].'"  />');
        }
    ?>
    </form>
<?php } else {
    ?>
    <br/><br/><br/><div>
        <?php echo $person["lastname"] ?> <?php echo $person["firstname"] ?> a <?php echo getAktSchool()["name"] ?>-ban végzett.
        Utolsó diákéveit a <?php echo getAktClass()["name"] ?>-ban járta.
        <?php if (isset($person["birthname"])) { ?> Osztálytársai <?php echo $person["birthname"] ?> diákkori nevén ismerik. <?php } ?>
    </div>
<?php } ?>
    <span style="font-size: 10px"><a href="http://ec.europa.eu/justice/smedataprotect/index_hu.htm" title="GDPR az Európai Unió általános adatvédelmi rendelete">GDPR:</a> A tanárok és a véndiákok személyes adatai kizárolag azt e célt szolgálják, hogy ezt az oldalt bővítsék.
	A beadott személyes adatok egy web szerveren vannak tárolva (Karlsruhe Németország) az <a href="https://unternehmen.1und1.de/rechenzentren/">"1 und 1"</a> cég szamitógépközpontjában.
	Biztonsági másolatok a személyes adatokról csak az internetoldal tulajdonos privát számítogépein és az internet szerveren léteznek. Ezek az másolatok maximum 6 hónapig vannak tárolva.
	A személyes adatok és fényképek megjelennek külömbőző internet kereső oldalok találati listáján.
	A védett mezők tartalma valamint egyes megfelelöen megjelölt fényképek anonim látogató és internet kereső oldalok ellen védve vannak. </span><br/>


<?php
function showRoleField($value,$fieldName) {
    $options = array();
    $disabled='';
    array_push($options, array('role' => 'unknown', 'text' => 'nem tudunk róla','disabled'=>$disabled));
    array_push($options, array('role' => 'jmlaureat', 'text' => "Juhász Máthé díjas",'disabled'=>$disabled));
    if(!userIsAdmin() && !userIsSuperuser())
        $disabled='disabled';
    array_push($options, array('role' => 'editor', 'text' => 'osztályfelelős / szervező','disabled'=>$disabled));
    array_push($options, array('role' => 'guest', 'text' => 'vendég / barát','disabled'=>$disabled));
    if (!userIsAdmin())
        $disabled='disabled';
    array_push($options, array('role' => 'superuser', 'text' => "rendszerfelelős",'disabled'=>$disabled));
    array_push($options, array('role' => 'admin', 'text' => "rendszergazda",'disabled'=>'disabled'));
    showChosenField($value,$fieldName,$options);
}

function showChosenField($value,$fieldName,$options)
{
    echo('<select class="form-control chosen" multiple="true" data-placeholder="...válassz..." id="'.$fieldName.'">');
    foreach ($options as $option) {
        $selected = (strstr($value,$option["role"])!==false)?"selected":"";
        echo('<option value="'.$option["role"].'" '.$selected.' '.$option["disabled"].' >' . $option["text"] . '</option>');
    }
    echo('</select>');
    echo('<input type="hidden" name="'.$fieldName.'"/>');
}

function showGenderField($value,$fieldName,$readOnly=false) {
    $options = array(
            array("value"=>" ","text"=>"...válassz..."),
            array("value"=>"f","text"=>"Hölgy"),
            array("value"=>"m","text"=>"Úr")
    );
    showOptionsField($value,$fieldName,$options,$readOnly);
}

function showTitleField($value,$fieldName,$readOnly=false) {
    $options = array(
            array("value"=>" ","text"=>"...válassz..."),
            array("value"=>"Dr.","text"=>"Dr."),
            array("value"=>"Dr.Med.","text"=>"Dr.Med."),
            array("value"=>"Prof.","text"=>"Prof."),
            array("value"=>"Dr.Prof.","text"=>"Dr.Prof."),
            array("value"=>"Dr.Dr.","text"=>"Dr.Dr.")
    );
    showOptionsField($value,$fieldName,$options,$readOnly);
}

function showOptionsField($value,$fieldName,$options,$readOnly=false) {
    echo('<select class="form-control" name="'.$fieldName.'" '.($readOnly?"disabled=disabled":"").'>');
    foreach ($options as $option) {
        $selected = (strstr($value,$option["value"])!==false)?"selected":"";
        echo('<option value="'.$option["value"].'" '.$selected.' >' . $option["text"] . '</option>');
    }
    echo('</select>');
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

	function savePerson() {
        showWaitMessage();
        var a = $("#role").children();
        if (a != null) {
            var s = '';
            for (var i = 0; i < a.length; i++) {
                if (a[i].selected )
                    s += a[i].value + ' ';
            }
            $('input[name="role"]').val(s);
        }
        $('#editform').submit();
    }

    function goGdpr(id) {
        document.location="gdpr?id="+id;
    }
</script>