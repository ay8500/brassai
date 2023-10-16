	<?php
    include_once 'displayCards.inc.php';
    include_once 'editPersonDataHelper.php';
    global $db, $diak, $showAllPersonalData;
    displayPerson($db,$diak,false,false);
    $schoolList = $db->getSchoolList();
    global $personFields,$anonymousEditor, $edit, $createNewPerson, $personid, $tabOpen, $classId, $person, $action;
    //Person picture?>
    <?php if(isUserLoggedOn() || $anonymousEditor) {?>
        <div style="display: inline-block; background-color: #E5E9EA; padding: 10px;border-radius: 5px; max-width: 900px">
            <div class="diak_picture" style="display: inline-block;margin-bottom: 5px;">
                <img src="<?php echo getPersonPicture($diak,true)?>" alt="" itemprop="image" class="diak_image" title="<?php echo $diak["lastname"]." ".$diak["firstname"]?>" />
                <?php if (isset($diak["deceasedYear"])&& intval($diak["deceasedYear"])>=0) {?>
                    <div style="background-color: black;color: white;height:20px;text-align: center;border-radius: 0 0 10px 10px;position: relative;top: -8px;">
                <?php } else {?>
                    <div style="background-color: white;color: black;height:20px;text-align: center;border-radius: 0 0 10px 10px;position: relative;top: -8px;">
                <?php }?>
                <?php if (isset($diak["birthyear"])) {?>
                    <?php echo "* ".intval($diak["birthyear"]).'&nbsp;&nbsp;'; ?>
                <?php }?>
                <?php if (isset($diak["deceasedYear"])&& intval($diak["deceasedYear"])>=0) {?>
                        <?php echo intval($diak["deceasedYear"])==0?"†":"† ".intval($diak["deceasedYear"]); ?>
                <?php }?>
                </div>
                <?php  if (isUserSuperuser()) {?>
                    <br/><a href="history?table=person&id=<?php echo $diak["id"]?>" title="módosítások" style="position: relative;top: -70px;left: 10px;display:inline-block;">
                        <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("person",$diak["id"]))?></span>
                    </a>
                <?php }?>
            </div>
            <?php //Person picture download only  if person already saved?>
            <?php if (($edit || $createNewPerson) && strlen(trim($diak["lastname"]))>2 && strlen(trim($diak["firstname"]))>2) {  ?>
                <div style="display: inline-block;margin:15px;vertical-align: bottom;max-width: 400px;">
                    <form enctype="multipart/form-data" action="editPerson" method="post">
                        <h4>Profilkép</h4>
                        <div style="margin-bottom: 5px;">A perfekt profilkép ez érettségi tablon felhasznált képed, kicsengetési kártya képe vagy bármilyen privát arckép.</div>
                        <span>Válassz egy új jpg képet max. 2MByte</span>
                        <input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" />
                        <button style="margin-top:5px;" type="submit" class="btn btn-info" title="Feltölti a kivásztott képet" onclick="showWaitMessage();">
                            <span class="glyphicon glyphicon-upload"></span> Feltölt</button>
                        <?php if (isset($diak["picture"]) && $diak["picture"]!=null) {?>
                            <?php  if (isUserAdmin()){
                                $pic=$diak['picture'];
                                $pic=substr($pic,0,strlen($pic)-4)."_o".substr($pic,strlen($pic)-4);
                                ?>
                                <button style="display: inline-block;margin: 5px 10px 0 10px;" class="btn btn-danger" name="overwriteFileName" value="<?php echo $diak["picture"]?>"><span class="glyphicon glyphicon-upload"></span> Kicserél</button>
                                <a class="btn btn-default" target="_download" href="images/<?php echo $pic?>" title="ImageName"><span class="glyphicon glyphicon-download"></span> Letölt</a>
                            <?php }?>
                            <?php if (isUserSuperuser() || getLoggedInUserId()==getRealId($diak)) {?>
                                <button style="display: inline-block;margin: 5px 10px 0 10px;" class="btn btn-danger" name="deletePersonPicture" value="<?php echo $diak["id"]?>"><span class="glyphicon glyphicon-upload"></span> Töröl</button>
                            <?php } ?>
                        <?php } ?>
                        <input type="hidden" value="upload_diak" name="action" />
                        <input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
                        <input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
                    </form>
                </div>
            <?php } ?>
        </div>
    <?php }
    //Buttons
   if (!isset($showAllPersonalData)) { ?>
       <div style="margin:15px;vertical-align: bottom;">
       <?php if ($edit) {  ?>
            <?php //Don't delete myself?>
            <?php if (!(getLoggedInUserId()==$diak["id"] ) && (isUserSuperuser() || isUserAdmin()) ) { ?>
                <button onclick="deleteDiak(<?php echo($diak["id"]);?>);" class="btn btn-danger"><span class="glyphicon glyphicon glyphicon-remove-circle"></span> Véglegesen kitöröl </button>
            <?php } ?>
        <?php } ?>

        <?php //Save button?>
        <?php if ($edit || $createNewPerson) {?>
                <button id="saveButton" onclick="savePerson();" class="btn btn-success"><span class="glyphicon glyphicon-floppy-disk"></span> Kiment</button>
                <button onclick="location.href='editPerson'" class="btn btn-danger"><span class="glyphicon glyphicon-remove-circle"></span> Mégse </button>
        <?php } ?>
        <?php //Anonymous user edit button?>
        <?php if (!$edit && !$createNewPerson) {?>
                <button onclick="document.location='editPerson?anonymousEditor=true';" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> Módosítani szeretnék</button>
        <?php } ?>
        <?php if (!isActionParam("newperson")) {?>
                <button onclick="goGdpr(<?php echo $diak["id"]?>);" class="btn btn-default"><span class="glyphicon glyphicon-exclamation-sign"></span> Személyes adatok védelme</button>
        <?php } ?>
        </div>
   <?php } ?>

<?php if ($edit || $createNewPerson || $anonymousEditor || true ) {?>
    <form method="get" id="editform" action="editPerson">
        <?php
        //Editfields school and class
        if (($edit || $createNewPerson) && !$anonymousEditor && isUserLoggedOn() && !isActionParam("newteacher")) {
            $optionClasses=$db->getClassList(getRealId(getActSchool()),true);

            ?>
            <div style="min-height:30px" class="input-group">
                <span style="min-width:110px;" class="input-group-addon" >&nbsp;</span>
                <span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
                <input type="text" readonly  id="highlight" class="form-control" value="Ha azt szeretnéd, hogy az adataidat csak a bejelentkezett diákok/osztálytársak lássák, akkor jelöld meg öket!" />
            </div>
            <div style="min-height:30px" class="input-group">
                <span style="min-width:110px;" class="input-group-addon" >Iskola</span>
                <span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
                <select disabled="disabled" class="form-control" name="schoolID" id="schoolID" >
                    <?php foreach ($schoolList as $school) {
                        $selected = $school["id"]==getActSchoolId()?"selected=selected":""?>
                        <option value="<?php echo $school["id"] ?>" <?php echo $selected ?>><?php echo $school["name"] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div style="min-height:30px" class="input-group">
                <span style="min-width:110px;" class="input-group-addon" >Osztály</span>
                <span style="width:40px" id="highlight" class="input-group-addon">&nbsp;</span>
                <select class="form-control" name="classID" id="classID">
                    <option value="-1" >...válassz...</option>
                    <option value="<?php echo \maierlabs\lpfw\Appl::getMember("staffClass")["id"]?>" <?php echo (intval(\maierlabs\lpfw\Appl::getMember("staffClass")["id"])==intval($diak["classID"]))?"selected":""?> >Iskola személyzete</option>
                    <?php foreach ($optionClasses as $optionClass) {?>
                        <option value="<?php echo $optionClass["id"]?>" <?php echo intval($optionClass["id"])==$diak["classID"]?"selected":""?>><?php echo $optionClass["text"].' '.($optionClass["eveningClass"]!='0'?"esti tagozat":"nappali tagozat")?></option>
                    <?php } ?>
                </select>
            </div>
        <?php } else {?>
            <input type="hidden" name="classID" value="<?php echo getActClassId()?>" />
            <input type="hidden" name="schoolID" value="<?php echo getActSchoolId()?>" />
        <?php } ?>


        <div itemtype="http://schema.org/PostalAddress" itemprop="address" itemscope style="overflow-x: hidden;" >
        <?php foreach ($personFields as $field) {?>
            <div class="input-group"><?php
                //Inputfields for
                if (($edit || $createNewPerson) && !$anonymousEditor ) {?>
                    <span style="padding: 6px;min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $field["caption"]?></span><?php
                    if ( isUserLoggedOn()) {?>
                        <span style="width:40px" id="highlight" class="input-group-addon">
                            <?php if ($field["canBeHidden"]) {?>
                                <input type="checkbox" name="cb_<?php echo $field["name"].'" '.getFieldChecked($diak,$field["name"])?> title="A megjelölt mezöket csak az osztálytásaid látják." />
                            <?php } else { echo '&nbsp'; }?>
                        </span><?php
                    }
                    if ($field["itemProp"]==="role") {
                            showRoleField(getFieldValueNull($diak,$field["name"]),$field["name"]);
                    } else {
                        if (showOptionFields($diak,$field))
                            showInputField($db,$diak,$field,false);
                        if ($field["name"]=="changeUserID") {
                            $person=$db->getPersonById(getFieldValueNull($diak,$field["name"]));
                            echo('<span class="input-group-addon"><span class="">'.$person["lastname"]." ".$person["firstname"].'</span></span>');
                        }
                    }
                //Inpufields anonymous user
                } elseif ($anonymousEditor) {?>
                    <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $field["caption"]?></span><?php
                    if (getFieldChecked($diak,$field["name"])=="") {
                        if (showOptionFields($diak,$field))
                            showInputField($db,$diak,$field,false);
                    } else {
                        if (showOptionFields($diak,$field,true))
                            showInputField($db,$diak,$field,true);
                    }
                //Display fields no editing just show
                } else {
                    if (showField($diak,$field["name"])) { ?>
                        <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1"><?php echo $field["caption"]?></span><?php
                        showInputField($db,$diak,$field,false,true);
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
            if (!isUserAdmin() && !isUserSuperuser())
                echo('<input type="hidden" name="role"	value="'.$diak["role"].'"  />');
            echo('<input type="hidden" name="schoolIdsAsTeacher" value="'.$diak["schoolIdsAsTeacher"].'"  />');
            echo('<input type="hidden" name="teacherPeriod"	value="'.$diak["employer"].'"  />');
        }
        ?>
        <div class="panel" style="margin-top: 20px">
            <?php if ($edit || $createNewPerson) { ?>
                <h5>Kolozsvári iskolákban tanár </h5>
                <?php /*
                <div style="margin-top: 10px;min-height:30px" class="input-group">
                    <span style="min-width:300px;" class="input-group-addon" >Tantárgy</span>
                    <input name="field" placeholder="tantárgyak pl: matematika, biologia, magyar" value="<?php echo $diak["function"] ?>" class="form-control" />
                </div>
                */?>
                <?php foreach ($schoolList as $school) {
                    $selected = strpos($diak["schoolIdsAsTeacher"],"(".$school["id"].")")!==false?"checked=checked":""?>
                    <div style="margin-top: 10px;min-height:30px" class="input-group" id="schoolid_<?php echo $school["id"] ?>">
                        <span style="min-width:300px;" class="input-group-addon" ><?php echo $school["name"]?></span>
                        <span style="width:40px"  class="input-group-addon"> <input class="schoolIdAsTeacher" id="schoolIdAsTeacher-<?php echo $school["id"] ?>" data="<?php echo $school["id"] ?>" type="checkbox" <?php echo $selected ?>  /></span>
                        <input class="schoolTeacherPeriod" id="schoolTeacherPeriod-<?php echo $school["id"] ?>" data="<?php echo $school["id"] ?>" placeholder="mettöl meddig pl: 1970-1989" value="<?php echo $db->getTeacherPeriod($diak,$school["id"])?>" class="form-control" />
                    </div>
                <?php }?>
            <?php } else if ($diak["schoolIdsAsTeacher"]!=null) {?>
                <h5>Kolozsvári iskolákban tanár </h5>
                <div style="margin-top: 10px;margin-bottom: 10px;min-height:30px" class="input-group">
                    <span style="min-width:300px;" class="input-group-addon" >Tantárgy</span>
                    <input name="field" placeholder="tantárgyak pl: matematika, biologia, magyar" value="<?php echo $diak["function"] ?>" class="form-control" disabled="disabled" />
                </div>
                <?php foreach ($schoolList as $school) {
                    if ( strpos($diak["schoolIdsAsTeacher"],"(".$school["id"].")")!==false) { ?>
                        <div style="min-height:30px" class="input-group" id="schoolid_<?php echo $school["id"] ?>">
                            <span style="min-width:300px;" class="input-group-addon" ><?php echo $school["name"]?></span>
                            <input class="schoolTeacherPeriod" data="<?php echo $school["id"] ?>" value="<?php echo $db->getTeacherPeriod($diak,$school["id"])?>" class="form-control" disabled="disabled" />
                        </div>
                    <?php }?>
                <?php }?>
            <?php } ?>
        </div>
    </form>
<?php } else {
    ?>
    <div style="font-size: larger; margin: 20px;">
        <?php
            echo fillPersonTemplate(
                '<p>{{p.title}} {{p.lastname}} {{p.firstname}} utolsó diákéveit a {{s.name}}ban a {{c.name}} osztályban {{c.graduationYear}}ban/ben}} járta.</p>
                {?eval"{{p.birthyear}}"!=""?}Született {{p.birthyear}}ban/ben}} ?}
                {?eval"{{p.deceasedYear}}"==""?}, ebben az évben '.(intval(date('Y'))-intval($diak['birthyear'])).'. születésnapját ünnepli.?} 
                {?eval"{{p.deceasedYear}}"!=""?}, meghalt {{p.deceasedYear}}ban/ben}} ?}{?eval"{{p.birthyear}}"!=""?} '.($diak['deceasedYear']-$diak['birthyear']).'. életévében.?}',
                $diak,getActClass(),getActSchool());
        ?>
    </div>
<?php } ?>
<?php if (!isset($showAllPersonalData)) { ?>
    <span style="font-size: 10px"><a href="http://ec.europa.eu/justice/smedataprotect/index_hu.htm" title="GDPR az Európai Unió általános adatvédelmi rendelete">GDPR:</a> A tanárok és a véndiákok személyes adatai kizárolag azt e célt szolgálják, hogy ezt az oldalt bővítsék.
	A beadott személyes adatok egy web szerveren vannak tárolva (Karlsruhe Németország) az <a href="https://cloud.ionos.de/rechenzentren">"ionos"</a> cég szamitógépközpontjában.
	Biztonsági másolatok a személyes adatokról csak az internetoldal tulajdonos privát számítogépein és az internet szerveren léteznek. Ezek az másolatok maximum 6 hónapig vannak tárolva.
	A személyes adatok és fényképek megjelennek külömbőző internet kereső oldalok találati listáján.
	A védett mezők tartalma valamint egyes megfelelöen megjelölt fényképek anonim látogató és internet kereső oldalok ellen védve vannak. </span><br/>
<?php }


