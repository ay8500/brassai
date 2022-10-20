<?php
include_once 'config.class.php';
include_once Config::$lpfw.'appl.class.php';
use \maierlabs\lpfw\Appl as Appl;

function showRoleField($value,$fieldName) {
    $options = array();
    $disabled='';
    array_push($options, array('role' => 'unknown', 'text' => 'nem tudunk róla','disabled'=>$disabled));
    array_push($options, array('role' => 'jmlaureat', 'text' => getActSchool()["awardName"]." díjas",'disabled'=>$disabled));
    if(!isUserAdmin() && !isUserSuperuser())
        $disabled='disabled';
    array_push($options, array('role' => 'editor', 'text' => 'osztályfelelős / szervező','disabled'=>$disabled));
    array_push($options, array('role' => 'guest', 'text' => 'vendég / barát','disabled'=>$disabled));
    if (!isUserAdmin())
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
        array("value"=>"Dr.Dr.","text"=>"Dr.Dr."),
        array("value"=>"Gróf","text"=>"Gróf")
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

function setPersonFields($diak,$isUserSuperuser,$isUserAdmin) {
    global $dataFieldNames, $dataFieldObl, $dataFieldCaption, $dataItemProp, $dataCheckFieldVisible;

    $dataFieldNames 	=array("gender","title","lastname","firstname","email","birthname","birthyear","deceasedYear");
    $dataFieldCaption 	=array("Megszólítás","Akad.titulus","Családnév","Keresztnév","E-Mail","Diákkori név","* Született","† Elhunyt");
    $dataItemProp       =array("gender","title","","","","","","");
    $dataCheckFieldVisible	=array(false,false,false,false,true,false,false,false);
    $dataFieldObl			=array("Hölgy/Úr","Akadémia titulus pl: Dr. Dr.Prof. ",true,true,"fontos mező","leánykori családnév","születési év pl.1967","csak az évszámot kell beírni, ha nem tudod pontosan akkor 0-t írj ebbe a mezőbe. Kimentés után beadhatod a sírhelyet.");

    if (isset($diak["deceasedYear"])){
        array_push($dataFieldNames ,"cementery","gravestone");
        array_push($dataFieldCaption,"Temető","Sírhely");
        array_push($dataItemProp,"","");
        array_push($dataCheckFieldVisible,false,false);
        array_push($dataFieldObl,"Temető neve, helység nélkül","");
    }
    if(true)  { //Address
        array_push($dataFieldNames, "partner","address","zipcode","place","country");
        array_push($dataItemProp,"","streetAddress","postalCode","addressLocality","addressCountry");
        array_push($dataFieldCaption, "Élettárs","Cím","Irányítószám","Helység","Ország");
        array_push($dataCheckFieldVisible, true,true,true,false,false);
        array_push($dataFieldObl		, "ha külömbőzik akkor a családneve is","útca, házszám, épület, emelet, apartament",false,"fontos mező","fontos mező");
    }
    if (true) { //Communication
        array_push($dataFieldNames, "phone","mobil","skype","facebook","homepage","education","children");
        array_push($dataItemProp,"","","","","","","");
        array_push($dataFieldCaption,"Telefon","Mobil","Skype","Facebook","Honoldal","Végzettség","Gyerekek");
        array_push($dataCheckFieldVisible,true ,true ,true ,true,true ,true ,true );
        array_push($dataFieldObl		, '+40 123 456789','+40 111 123456',false,'https://www.facebook.com/...','http://',false,"nevük és születési évük pl: Éva 1991, Tamás 2002");
    }
    if ($diak["schoolIdsAsTeacher"]==null) { //Person is not a teacher
        array_push($dataFieldNames      , "employer","function");
        array_push($dataItemProp        ,"","");
        array_push($dataFieldCaption    ,"Munkahely","Beosztás");
        array_push($dataCheckFieldVisible,true,true );
        array_push($dataFieldObl		    , false,false);
    }
    if ($isUserSuperuser ) {
        array_push($dataFieldNames, "role");
        array_push($dataItemProp,"role");
        array_push($dataFieldCaption, "Opciók");
        array_push($dataCheckFieldVisible, true);
        array_push($dataFieldObl, false);
    }
    if ($isUserAdmin) { //only for admin
        array_push($dataFieldNames, "facebookid","id", "user", "passw", "geolat", "geolng","userLastLogin","changeIP","changeDate","changeUserID","changeForID");
        array_push($dataItemProp,"","","","","","","","","","","");
        array_push($dataFieldCaption, "FB-ID","ID", "Felhasználó", "Jelszó", "X", "Y","Utolsó login","IP","Dátum","User","changeForID");
        array_push($dataCheckFieldVisible, false,false,false,false,false,false,false,false,false,false,false);
        array_push($dataFieldObl	 	 , false,true,true,true,false,false,'2000-01-01',false,'2000-01-01',false,false);
    }
}

Appl::addJsScript('
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
        //concat role strings
        var a = $("#role").children();
        if (a != null) {
            var s = "";
            for (var i = 0; i < a.length; i++) {
                if (a[i].selected )
                    s += a[i].value + " ";
            }
            $("input[name=\'role\']").val(s);
        }
        //concat schoolIdsAsTeacher
        s="";
        $("input[id=\'schoolIdAsTeacher\']").each(function() {
            s += this.checked ?  ("("+$(this).attr(\'data\')+")"):"";
        });
        $("[name=\'schoolIdsAsTeacher\']").val(s);
        //concat period in school
        s="";
        $("input[id=\'schoolTeacherPeriod\']").each(function() {
            s += $(this).val()!="" ?  ("\""+$(this).attr("data")+"\":\""+$(this).val()+"\","):"";
        });
        if (s!="") {
            $("[name=\'teacherPeriod\']").val("{"+s.slice(0,-1)+"}");
        }

        $("#editform").submit();
    }

    function goGdpr(id) {
        document.location="gdpr?id="+id;
    }
');
