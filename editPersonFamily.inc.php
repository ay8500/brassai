<?php
include_once 'dbDaCandle.class.php';
include_once 'dbDaFamily.class.php';
global $db;
$dbFamily = new dbDaFamily($db);


use \maierlabs\lpfw\Appl as Appl;
Appl::addJs("js/chosen.jquery.js");
Appl::addCss("css/chosen.css");
include_once 'displayCards.inc.php';
global $diak;
$edit =  isUserSuperuser() || isUserEditor() || $diak["id"]==getLoggedInUserId();

if (isActionParam("save")) {
    $relatives = $dbFamily->getPersonRelativesById(getIntParam("uid"));
    if (array_search(getParam("sid"),array_column($relatives,"id2"))===false ) {
        if ($dbFamily->saveRelatives(getIntParam("uid"), getIntParam("sid"), getParam("code"), getParam("gender"))) {
            $db->updateRecentChangesList();
            Appl::setMessage("Rokonsági kapcsolat létrehozva", "success");
        } else {
            Appl::setMessage("Rokonsági kapcsolat létrehozása nem lehetséges.", "danger");
        }
    } else {
        Appl::setMessage("Rokonsági kapcsolat létrehozása nem lehetséges, mert már létezik", "warning");
    }
}

if (isActionParam("delete")) {
    if ($dbFamily->deleteRelatives(getIntParam("id"))) {
        Appl::setMessage("Rokonsági kapcsolat törölve", "success");
        $db->updateRecentChangesList();
    } else
        Appl::setMessage("Rokonsági kapcsolat törése nem sikerült","warning");
}

$familyPersonList = $dbFamily->getPersonRelativesById($diak["id"]);

?>
    <h4>Uj Rokonsági kapcsolat megjelölése</h4>
    <div class="input-group shadowbox">
        <span style="min-width:160px; text-align:right" class="input-group-addon" id="basic-addon1">Rokon neme</span>
        <select class="form-control" onchange="setGender()" id="relativeGender">
            <option value="">... válassz ...</option>
            <option value="f">Lány / Nő</option>
            <option value="m">Férfi / Fiú</option>
        </select>
    </div>

    <div class="input-group shadowbox" style="display: none" id="divRealtiveGrad">
        <span style="min-width:160px; text-align:right" class="input-group-addon" id="basic-addon1">Rokonsági kapcsolat</span>
        <select class="form-control"  id="selectRelativGrad" onchange="$('#divSearchName').show('fast');">
            <option value="">... válassz ...</option>
            <?php foreach ($dbFamily->family as $f) {
                $text=$f["text"];
                echo('<option value="'.$f["code"].'">'.$text.'</option>');
            }?>
        </select>
        <span class="input-group-addon"><a href="https://hu.wikipedia.org/wiki/Rokons%C3%A1g#/media/File:Rokoni_kapcsolatok.jpg" target="_blank" class="btn btn-sm btn-success" style="padding: 6px; margin: -10px; ">?</a></span>
    </div>

    <div class="input-group shadowbox" style="display:none" id="divSearchName">
        <span style="min-width:160px; text-align:right" class="input-group-addon" id="basic-addon1">Rokomom</span>
        <input class ="form-control" id="searchName" placeholder="család vagy keresztnév, 'tanár' vagy évszám szükíti a találatokat " onkeyup="searchPerson()"/>
    </div>
    <div id="divNames" style="display: none">
        <ul class="input-group shadowbox"  id="selectPerson" style="max-width: 600px" >
            <li></li>
        </ul>
    </div>

    <div class="container-fluid" style="margin-top: 20px;"><?php
        foreach ($familyPersonList as $familyPerson) {
            $person = $db->getPersonByID($familyPerson["id2"]);
            ?><div class="element"><?php
            echo("<h4>".$dbFamily->getKinship($familyPerson["coderec"],$person["gender"],$familyPerson["gender"],$familyPerson["deap"]));
            if ($edit)
                echo ('<span onclick="deleteRelative('.$familyPerson["id"].')" style="float: right;cursor: pointer" class="glyphicon glyphicon-remove-circle"></span>');
            echo('</h4>');
            if (isUserAdmin())
                echo("<br/>".$familyPerson["coderec"]." Dir: ".$familyPerson["direction"]);
            displayPerson($db,$person, true,false);
            ?></div><?php
        }
        ?></div>

<?php
Appl::addJsScript('
    var family = JSON.parse(\''.json_encode($dbFamily->family).'\');

    $(document).ready(function(){
        $(".chosen").chosen();
    });
    
    function setGender() {
        
        family.forEach ( function(e) {
            if ($("#relativeGender").val()=="f")
                $(\'#selectRelativGrad option[value=\'+e.code+\']\').text(e.text+" ("+e.textf+")");
            else
                $(\'#selectRelativGrad option[value=\'+e.code+\']\').text(e.text+" ("+e.textm+")");
        });
        $("#divRealtiveGrad").show("fast");
        
    }
    
    function searchPerson () {
        $.ajax({
    		url:\'ajax/getPersonByName?name=\'+$("#searchName").val(),
    		success:function(data){
    		    $("#selectPerson").empty();
    		    data.forEach(function(e){
    		        var l = $(\'<li class="form-control" style="display:inline-table"/>\')
    		        var c="";
    		        if (e.schoolID!=null)
    		            c ="<img src=\"images/school"+e.schoolID+"/"+e.schoolLogo+"\" style=\"height:33px;border-radius:15px;\" />";
    		        if (e.schoolIdsAsTeacher!==null)
    		            c+="Tanár "
    		        else
    		            c+=e.schoolYear+" "+e.schoolClass+" ";
    		        c +="<a target=\"_blank\" href=\"editPerson?uid="+e.id+"\">";  
    		        var name= e.lastname+" "+e.firstname; 
    		        c +="<b>"+name+((e.birthname!=null && e.birthname!="")?" ("+e.birthname+")":"")+"</b>";
    		        c +="</a>";
    		        c +="<button onclick=\"saveRelative("+e.id+",\'"+name+"\')\" style=\"float: right;padding: 3px; margin: -2px;\" class=\"btn btn-sm btn-success\">Kiment</button>";
    		        l.html(c);
    		        $("#selectPerson").append(l);
    		    });
                $("#divNames").show("fast");
		    },
		    error:function(error) {
                $("#divNames").hide();
		    }
        });
    }
 ');

Appl::addJsScript('    
    function saveRelative(id,name) {
        if (confirm("Megszeretnéd jelölni: "+name+" "+$("#selectRelativGrad option:selected").text())) {
            showWaitMessage();
            document.location="editPerson?action=save&tabOpen=family&uid=' . $diak["id"] . '&sid="+id+"&code="+$("#selectRelativGrad").val()+"&gender="+$("#relativeGender").val();
        }
    }
');


Appl::addJsScript('
    function deleteRelative(id) {
        if (confirm("Ki szeretnéd törölni a rokonsági kapcsolatot?")) {
            showWaitMessage();
            document.location="editPerson?action=delete&tabOpen=family&id="+id;
        }
    }
');


    
?>