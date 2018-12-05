<?php
/* Parents, Silbling, Life parnter, Children */
$family=array(
    array ("code"=>"x", "text"=>"Távoli rokon","textf"=>"","textm"=>""),
    array ("code"=>"ccc", "text"=>"Dédunokám","textf"=>"","textm"=>""),
    array ("code"=>"ccl", "text"=>"Unokám élettársa", "textf"=>"Unokám felsége","textm"=>"Unokám férje"),
    array ("code"=>"cc", "text"=>"Unokám","textf"=>"","textm"=>""),
    array ("code"=>"cl", "text"=>"Gyerekem élettársa","textf"=>"Menyem","textm"=>"Vöm"),
    array ("code"=>"c", "text"=>"Gyerekem","textf"=>"Lányom","textm"=>"Fiam"),
    array ("code"=>"l", "text"=>"Élettársam","textf"=>"Feleségem","textm"=>"Férjem"),
    array ("code"=>"lp", "text"=>"Élettársam szülei","textf"=>"Anyósom","textm"=>"Apósóm"),
    array ("code"=>"s", "text"=>"Testvérem","textf"=>"Hugom / Növérem","textm"=>"Fivérem"),
    array ("code"=>"ls", "text"=>"Élettársam testvére","textf"=>"Sogorom","textm"=>"Sogornöm"),
    array ("code"=>"sl", "text"=>"Testvérem élettársa","textf"=>"Sogorom","textm"=>"Sogornöm"),
    array ("code"=>"sc", "text"=>"Testvérem gyereke","textf"=>"Unokahugom","textm"=>"Unokaöcsém"),
    array ("code"=>"scc", "text"=>"Testvérem unokája","textf"=>"Dédunokahugom","textm"=>"Dédunokaöcsém"),
    array ("code"=>"p", "text"=>"Szüleim","textf"=>"Anyukám","textm"=>"Apukám"),
    array ("code"=>"ps", "text"=>"Szüleim testvére","textf"=>"Nagynéném","textm"=>"Nagybátyám"),
    array ("code"=>"psc", "text"=>"Szüleim testvérének gyereke=Unokatestvérem","textf"=>"","textm"=>""),
    array ("code"=>"pscc", "text"=>"Szüleim testvérének unokája","textf"=>"Másodunokahugom","textm"=>"Másodunkaöcsém"),
    array ("code"=>"psccc", "text"=>"Szüleim testvérének dédunokája","textf"=>"","textm"=>""),
    array ("code"=>"pp", "text"=>"Nagyszüleim","textf"=>"Nagyanyám","textm"=>"Nagyapám"),
    array ("code"=>"pps", "text"=>"Nagyszüleim testvére","textf"=>"Nagy-nagyanyám","textm"=>"Nagy-nagyapám"),
    array ("code"=>"ppsc", "text"=>"Nagyszüleim testvérének gyereke","textf"=>"Másodnagynéném","textm"=>"Másodnagybátyám"),
    array ("code"=>"ppscc", "text"=>"Nagyszüleim testvérének unokája=Másodunokatestvér","textf"=>"","textm"=>""),
    array ("code"=>"ppsccc", "text"=>"Nagyszüleim testvérének dédunokája","textf"=>"Másodunokahugom","textm"=>"Másodunokaöcsém"),
    array ("code"=>"ppp", "text"=>"Dédszüleim","textf"=>"Dédanyám","textm"=>"Dédapám"),
    array ("code"=>"pppsc", "text"=>"Dédszüleim testvérének gyereke","textf"=>"Harmaddédnéném","textm"=>"Harmaddédédbátyám"),
    array ("code"=>"pppscc", "text"=>"Dédszüleim testvérének unokája","textf"=>"Harmadnagynéném","textm"=>"Harmadnagybátyám"),
    array ("code"=>"pppsccc", "text"=>"Dédszüleim testvérének dédunokája=Harmadunokatestvér","textf"=>"","textm"=>"")
);

use \maierlabs\lpfw\Appl as Appl;
Appl::addJs("js/chosen.jquery.js");
Appl::addCss("css/chosen.css");
include_once 'displayCards.inc.php';

$edit = userIsAdmin() || userIsSuperuser() || userIsEditor() || $diak["id"]==getLoggedInUserId();

if (isActionParam("save")) {
    if ($db->saveRelatives(getParam("uid"),getParam("sid"),getParam("code"),getParam("gender")))
        Appl::setMessage("Rokonsági kapcsolat létrehozva","success");
    else
        Appl::setMessage("Rokonsági kapcsolat létrehozása nem lehetséges, mert már létezik","warning");
}

if (isActionParam("delete")) {
    if ($db->deleteRelatives(getIntParam("id")))
        Appl::setMessage("Rokonsági kapcsolat törölve","success");
    else
        Appl::setMessage("Rokonsági kapcsolat törése nem sikerült","warning");
}

$familyPersonList = $db->getPersonRelativesById($diak["id"]);

?>
    <div class="container-fluid"><?php
        foreach ($familyPersonList as $familyPerson) {
            $person = $db->getPersonByID($familyPerson["id2"]);
            ?><div class="element"><?php
                $relative= array_search($familyPerson["coderec"],array_column($family,"code"));
                if ($familyPerson["gender"]=="f")
                    $textGender=($family[$relative]["textf"]!='')?'='.$family[$relative]["textf"]:"";
                elseif ($familyPerson["gender"]=="m")
                    $textGender=($family[$relative]["textm"]!='')?'='.$family[$relative]["textm"]:"";
                else
                    $textGender=($family[$relative]["textf"]!='')?'='.$family[$relative]["textf"]." / ".$family[$relative]["textm"]:"";
                if ($family[$relative]["code"]=='x') {
                    if (strpos($familyPerson["coderec"],'lp')!==false)
                        $textGender = " anyóson vagy apóson keresztül";
                    elseif (strpos($familyPerson["coderec"],'ls')!==false)
                        $textGender = " sogornön vagy sogoron keresztül";
                    elseif (strpos($familyPerson["coderec"],'l')!==false)
                        $textGender = " élettársamon keresztül";
                }
                echo("<h4>".$family[$relative]["text"].$textGender);
                if ($edit)
                    echo ('<span onclick="deleteRelative('.$familyPerson["id"].')" style="float: right;cursor: pointer" class="glyphicon glyphicon-remove-circle"></span>');
                echo('</h4>');
                if (userIsAdmin())
                    echo("<br/>".$familyPerson["coderec"]." Dir: ".$familyPerson["direction"]);
                displayPerson($db,$person, true,false);
            ?></div><?php
        }
    ?></div>

    <h4>Rokon megjelölése</h4>
    <div class="input-group shadowbox">
        <span style="min-width:180px; text-align:right" class="input-group-addon" id="basic-addon1">Rokon neme</span>
        <select class="form-control" onchange="setGender()" id="relativeGender">
            <option value="">... válassz ...</option>
            <option value="f">Lány / Nö</option>
            <option value="m">Férfi / Fiú</option>
        </select>
    </div>

    <div class="input-group shadowbox" style="display: none" id="divRealtiveGrad">
        <span style="min-width:180px; text-align:right" class="input-group-addon" id="basic-addon1">Rokonsági kapcsolat</span>
        <select class="form-control"  id="selectRelativGrad" onchange="$('#divSearchName').show('fast');">
            <option value="">... válassz ...</option>
            <?php foreach ($family as $f) {
                $text=$f["text"];
                echo('<option value="'.$f["code"].'">'.$text.'</option>');
            }?>
        </select>
        <span class="input-group-addon"><a href="https://hu.wikipedia.org/wiki/Rokons%C3%A1g#/media/File:Rokoni_kapcsolatok.jpg" target="_blank" class="btn btn-sm btn-success" style="padding: 6px; margin: -10px; ">?</a></span>
    </div>

    <div class="input-group shadowbox" style="display:none" id="divSearchName">
        <span style="min-width:180px; text-align:right" class="input-group-addon" id="basic-addon1">Rokomom</span>
        <input class ="form-control" id="searchName" placeholder="írd be család vagy keresztnavét" onkeyup="searchPerson()"/>
    </div>
    <div id="divNames" style="display: none">
        <ul class="input-group shadowbox"  id="selectPerson" style="max-width: 600px" >
            <li>Levente Maier</li>
        </ul>
    </div>


<?php
Appl::addJsScript('
    var family = JSON.parse(\''.json_encode($family).'\');

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
    		url:\'ajax/getPersonByName.php?name=\'+$("#searchName").val(),
    		success:function(data){
    		    $("#selectPerson").empty();
    		    data.forEach(function(e){
    		        var l = $(\'<li class="form-control" style="display:inline-table"/>\')
    		        var c ="";
    		        if (e.isTeacher=="1")
    		            c="Tanár "
    		        else
    		            c=e.scoolYear+" "+e.scoolClass+" ";
    		        c +="<a target=\"_blank\" href=\"editDiak.php?uid="+e.id+"\">";  
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

if($edit) {
    Appl::addJsScript('    
    function saveRelative(id,name) {
        if (confirm("Megszeretnéd jelölni: "+name+" "+$("#selectRelativGrad option:selected").text())) {
            document.location="editDiak.php?action=save&tabOpen=family&uid=' . $diak["id"] . '&sid="+id+"&code="+$("#selectRelativGrad").val()+"&gender="+$("#relativeGender").val();
        }
    }
');
} else {
    Appl::addJsScript('    
    function saveRelative(id,name) {
        alert("Rokonság bejegyzése csak bejelentkezett látogatok részére lehetséges. Jelentkezz be vagy regisztráld magad mert ez az oldal nagyon jó, barátságos, ingyenes és reklámmentes!");
    }
');

}

if($edit) {
    Appl::addJsScript('
    function deleteRelative(id) {
        if (confirm("Ki szeretnéd törölni a rokonsági kapcsolatot?")) {
            document.location="editDiak.php?action=delete&tabOpen=family&id="+id;
        }
    }
');
}

    
?>