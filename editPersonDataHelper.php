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
    $ret = array();
    $ret[] = array("name"=>"gender","caption"=>"Megszólítás","itemProp"=>"gender","json"=>"","canBeHidden"=>false,"required"=>true,"hint"=>"Hölgy/Úr");
    $ret[] = array("name"=>"title","caption"=>"Akad.titulus","itemProp"=>"title","json"=>"","canBeHidden"=>false,"required"=>false,"hint"=>"Akadémia titulus pl: Dr. vagy Dr.Prof.");
    $ret[] = array("name"=>"lastname","caption"=>"Családnév","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>true, "hint"=>"");
    $ret[] = array("name"=>"firstname","caption"=>"Keresztnév","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>true, "hint"=>"");
    $ret[] = array("name"=>"email","caption"=>"E-Mail","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"E-Mail cím");
    $ret[] = array("name"=>"birthname","caption"=>"Diákkori név","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"leánykori családnév");
    $ret[] = array("name"=>"birthyear","caption"=>"* Született","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"születési év pl.1967");
    $ret[] = array("name"=>"deceasedYear","caption"=>"† Elhunyt","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"csak az évszámot kell beírni, ha nem tudod pontosan akkor 0-t írj ebbe a mezőbe. Kimentés után beadhatod a sírhelyet.");

    if (isset($diak["deceasedYear"])){
        $ret[] = array("name"=>"cementery","caption"=>"Temető","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"Temető neve, helység nélkül");
        $ret[] = array("name"=>"gravestone","caption"=>"Sírhely","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"Sírhely száma vagy azonosítója");
    }
    //Address
    $ret[] = array("name"=>"partner","caption"=>"Élettárs","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"ha külömbőzik akkor a családneve is");
    $ret[] = array("name"=>"address","caption"=>"Cím","itemProp"=>"streetAddress","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"útca, házszám, épület, emelet, apartament");
    $ret[] = array("name"=>"zipcode","caption"=>"Irányítószám","itemProp"=>"postalCode","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"");
    $ret[] = array("name"=>"place","caption"=>"Helység","itemProp"=>"addressLocality","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"város, község, falú");
    $ret[] = array("name"=>"country","caption"=>"Ország","itemProp"=>"addressCountry","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"csak külfödi országok megadása lényeges");
     //Communication
    $ret[] = array("name"=>"phone","caption"=>"Telefon","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"+40 123 456789");
    $ret[] = array("name"=>"mobil","caption"=>"Mobil","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"+40 111 123456");
    $ret[] = array("name"=>"skype","caption"=>"Skype","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"");
    $ret[] = array("name"=>"facebook","caption"=>"Facebook","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"https://www.facebook.com/...");
    $ret[] = array("name"=>"homepage","caption"=>"Honoldal","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"http://....");
    $ret[] = array("name"=>"education","caption"=>"Végzettség","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"");
    $ret[] = array("name"=>"children","caption"=>"Gyerekek","itemProp"=>"","json"=>"","canBeHidden"=>true,"required"=>false, "hint"=>"nevük és születési évük pl: Éva 1991, Tamás 2002");
    $ret[] = array("name"=>"wikipedia","caption"=>"Wikipedia","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"https://wikipedia.....");
    $ret[] = array("name"=>"major","caption"=>"Szak","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"Gimnáziumi szak pl: matematika, zongora, turizmus...");
    //Employer
    $ret[] = array("name"=>"employer","caption"=>"Munkahely","itemProp"=>"","json"=>"employer","canBeHidden"=>false,"required"=>false, "hint"=>"");
    $ret[] = array("name"=>"function","caption"=>"Beosztás","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"pl: könyvelő, lakatos, fodrász, kémia/fizika tanár, gyerekorvos, csillagász stb.");

    if ($isUserSuperuser ) {
        $ret[] = array("name"=>"role","caption"=>"Opciók","itemProp"=>"role","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
    }
    if ($isUserAdmin) { //only for admin
        $ret[] = array("name"=>"facebookid","caption"=>"FB-ID","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"user","caption"=>"Felhasználó","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"passw","caption"=>"Jelszó","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"geolat","caption"=>"X","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"geolng","caption"=>"Y","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"userLastLogin","caption"=>"Utolsó login","json"=>"","itemProp"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"changeIP","caption"=>"IP","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"changeUserID","caption"=>"UserID","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
        $ret[] = array("name"=>"changeDate","caption"=>"Datum","itemProp"=>"","json"=>"","canBeHidden"=>false,"required"=>false, "hint"=>"");
    }
    return $ret;
}

Appl::addJsScript('
    function validateYearInput(sender,button,allowZero){
        if ( (sender.value === "0" && allowZero) || (sender.value>1800 && sender.value<2200) ) {
            sender.style.color="green";
            $(button).removeClass("disabled");
        } else {
            sender.style.color="red";
            $(button).addClass("disabled");
        }
    }

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
