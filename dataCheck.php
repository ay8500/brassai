<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';

use \maierlabs\lpfw\Appl as Appl;

Appl::setSiteSubTitle('Adatok vizsgálása és jóváhagyása');
Appl::addCssStyle('
    td, th { padding: 4px; border: 1px solid}
    table { margin-left: auto; margin-right:auto }
    tr:first-child {height: 39px;font-size: 18px;background-color: lightgray; }
');
include('homemenu.inc.php');
global $db, $tabOpen;

$id=getIntParam("id");
$ret=false;
$show=false;

if (!isUserAdmin()) {
    echo '<div class="alert alert-danger text-center" >Adat hozzáférési jog hiányzik!</div>';
    include_once "homefooter.inc.php";
}

if (isActionParam("deleteMessageChange")) {
    $ret =$db->deleteMessageEntry($id);$show=true;
}
if (isActionParam("acceptMessageChange")) {
    $ret =$db->acceptChangeForMessage($id);
    $show=true;
}
if (isActionParam("resetChange")) {
    $ret =$db->deleteRequest(getIntParam("type"),getParam("ip"));$show=true;
}
if ($show) {
    if ($ret===true) {
        Appl::setMessage('Rendben, a müvelet sikerült','success');
    } else {
        Appl::setMessage('Sajnos nem sikerült a müvelet!','danger');
    }
}


//initialise tabs
$tabsCaption = array();
$tabsTranslate["search"] = array(".php");$tabsTranslate["replace"] = array("");
array_push($tabsCaption ,array("id" => "school", "caption" => 'Iskolák <span class="badge">'.$db->getCountToBeChecked('school').'</span>', "glyphicon" => "align-justify"));
array_push($tabsCaption ,array("id" => "class", "caption" => 'Osztályok <span class="badge">'.$db->getCountToBeChecked('class').'</span>', "glyphicon" => "align-justify"));
array_push($tabsCaption ,array("id" => "person", "caption" => 'Személyek <span class="badge">'.$db->getCountToBeChecked('person').'</span>', "glyphicon" => "user"));
array_push($tabsCaption ,array("id" => "picture", "caption" => 'Képek <span class="badge">'.$db->getCountToBeChecked('picture').'</span>', "glyphicon" => "picture"));
array_push($tabsCaption ,array("id" => "mark", "caption" => 'Jelölések <span class="badge">'.$db->getCountToBeChecked('personInPicture').'</span>', "glyphicon" => "tag"));
array_push($tabsCaption ,array("id" => "message", "caption" => 'Üzenetek <span class="badge">'.$db->getCountToBeChecked('message').'</span>', "glyphicon" => "blackboard"));
array_push($tabsCaption ,array("id" => "action", "caption" => 'Hozzáférések'));

include Config::$lpfw.'view/tabs.inc.php';
if ($tabOpen=="school") {
    generateCheckHtmlTable($db,"Iskolák", "Iskola","School","name",$id,["id"=>0,"name"=>"","address"=>"","mail"=>"","www"=>"","phone"=>"","text"=>""],"getSchoolById","deleteSchoolEntry","saveSchool");
} else if ($tabOpen=="class") {
    generateCheckHtmlTable($db,"Osztályok", "Osztály","Class","text",$id,["id"=>0,"graduationYear"=>"","name"=>"","text"=>""],"getClassById","deleteClass","saveClass");
} else if ($tabOpen=="person") {
    $dummyPerson=$db->getPersonDummy();
    $dummyPerson["id"]="";$dummyPerson["picture"]="";
    $dummyPerson["classID"]="";$dummyPerson["facebook"]="";$dummyPerson["schoolIdsAsTeacher"]=NULL;
    $dummyPerson["address"]="";$dummyPerson["zipcode"]="";$dummyPerson["place"]="";
    $dummyPerson["phone"]="";$dummyPerson["mobil"]="";$dummyPerson["email"]="";
    $dummyPerson["homepage"]="";$dummyPerson["skype"]="";$dummyPerson["education"]="";
    $dummyPerson["employer"]="";$dummyPerson["function"]="";$dummyPerson["children"]="";
    generateCheckHtmlTable($db,"Személyek", "Személy","Person","lastname",$id,$dummyPerson,"getPersonByID4DataCheck","deletePersonEntry","savePerson");
} else if ($tabOpen=="picture") {
    generateCheckHtmlTable($db,"Képek", "Kép","Picture","file",$id,["id"=>0,"title"=>"","comment"=>"","albumName"=>"","tag"=>"","file"=>"","isVisibleForAll"=>0,"isDeleted"=>0,"personID"=>0,"schoolID"=>0,"classID"=>0],"getPictureById","deletePictureEntry","savePicture");
} else if ($tabOpen=="mark") {
    $list=$db->getListToBeChecked('personInPicture');?>
    <p align="center">
        Személy jelölések:<br/>
    <table>
        <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
            <td>Személy</td><td>Kép</td><td>Személy</td><td>Dátum</td><td>IP</td><td colspan="3">Akció</td>
        </tr>
        <?php
        foreach ($list as $i=>$l) {
            echo('<tr >');
            echo("<td>".getPersonName($db->getPersonByID($l["personID"]))."</td>");
            $picture = $db->getPictureById($l["pictureID"]);
            if (NULL!==$picture) {
                echo('<td><img src="' . $picture["file"] . '" style="height:75px" /></td>');
                echo('<td><img src="imageTaggedPerson?personid=' . $l["personID"] . '&pictureid=' . $l["pictureID"] . '" style="height:75px" /></td>');
            }
            else
                echo("<td>No Image</td><td></td>");
            echo("<td>".\maierlabs\lpfw\Appl::dateTimeAsStr($l["changeDate"])."</td>");
            echo("<td><a href=\"javascript:showip('".$l["changeIP"]."')\">".$l["changeIP"]."</td>");
            echo('<td><button class="btn btn-success" onclick="return setPersonMark(this,'.$l["personID"].','.$l["pictureID"].',false);"><span class="glyphicon glyphicon-ok-circle"></span></button></td>');
            echo('<td><button class="btn btn-danger" onclick="return setPersonMark(this,'.$l["personID"].','.$l["pictureID"].',true);"><span class="glyphicon glyphicon-alert"></span></button></td>');
            echo('<td><a class="btn btn-default" href="https://brassai.blue-l.de//picture?id='.$l["pictureID"].'$personid='.$l["personID"].'" target="picture"><span class="glyphicon glyphicon-edit"></span></a></td>');
            echo("</tr>");
        }
        ?>
    </table>
    </p>
<?php
} else if ($tabOpen=="message") {
    $list=$db->getListToBeChecked('message');?>
    <p align="center">
       Üzenetek:<br/>
    <table align="center" border="1">
        <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
            <td>Név</td><td>szöveg</td><td>Datum</td><td>Ip</td><td colspan="2">Akció</td>
        </tr>
        <?php
            foreach ($list as $i=>$l) {
                echo('<tr >');
                if (isset($l["changeUserID"]))
                    echo("<td>".getPersonName($db->getPersonByID($l["changeUserID"]))."</td>");
                else
                    echo("<td>".html_entity_decode($l["name"])."</td>");
                echo("<td>".html_entity_decode($l["text"])."</td>");
                echo("<td>".\maierlabs\lpfw\Appl::dateTimeAsStr($l["changeDate"])."</td>");
                echo("<td><a href=\"javascript:showip('".$l["changeIP"]."')\">".$l["changeIP"]."</td>");
                if ($l["isDeleted"]==0)
                    echo('<td><button class="btn btn-default" onclick="acceptMessageChange('.$l["id"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
                else
                    echo("<td></td>");
                echo('<td><button class="btn btn-default" onclick="deleteMessageChange('.$l["id"].');"><span class="glyphicon glyphicon-remove"></span></button></td>');
                echo("</tr>");
            }
        ?>
    </table>
    </p>
<?php
} else if ($tabOpen=="action") { ?>
    <p align="center">
        Hozzáférések ma:<br/>
        <table align="center" border="1">
            <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
                <td>IP</td><td>Datum</td><td>Type</td><td>Számláló</td><td>Akció</td>
            </tr>
            <?php
            $list = $db->getListOfRequest(24);
            foreach ($list as $i=>$l) {
                echo('<tr >');
                echo("<td><a href=\"javascript:showip('".$l["ip"]."')\">".$l["ip"]."</td>");
                echo("<td>".\maierlabs\lpfw\Appl::dateTimeAsStr($l["date"])."</td>");
                echo("<td>".getConstantName("changeType",$l["typeID"])."</td>");
                echo("<td>".$l["count"]."</td>");
                if ($l["count"]>0)
                    echo('<td><button class="btn btn-default" onclick="resetChange('."'".$l["ip"]."',".$l["typeID"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
                else
                    echo("<td></td>");
                echo("</tr>");
            }
            ?>
        </table>
        <p style="text-align: center;margin-top:20px">Hozzáférések:</p>
        <table align="center" border="1" style="margin-top:20px">
            <tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
                <td>IP</td><td>Datum</td><td>Type</td><td>Számláló</td><td>Akció</td>
            </tr>
            <?php
            $list = $db->getListOfRequest(0);
            foreach ($list as $i=>$l) {
                echo('<tr >');
                echo("<td><a href=\"javascript:showip('".$l["ip"]."')\">".$l["ip"]."</td>");
                echo("<td>".\maierlabs\lpfw\Appl::dateTimeAsStr($l["date"])."</td>");
                echo("<td>".getConstantName("changeType",$l["typeID"])."</td>");
                echo("<td>".$l["count"]."</td>");
                if ($l["count"]>=1)
                    echo('<td><button class="btn btn-default" onclick="resetChange('."'".$l["ip"]."',".$l["typeID"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
                else
                    echo("<td></td>");
                echo("</tr>");
            }?>
        </table>
    </p>
<?php }


/**
 * Generate php code for check table
 * @param dbDAO $db DB
 * @param string $title Title
 * @param string $fieldText Fieldtext
 * @param string $fieldDb DB Field
 * @param boolean $showField
 * @param int $id
 * @param array $emptyEntry
 * @param string $functionGetByID
 * @param string $functionDelete
 * @param string $functionSave
 */
function generateCheckHtmlTable($db,$title,$fieldText,$fieldDb,$showField,$id,$emptyEntry,$functionGetByID,$functionDelete,$functionSave ) {
  	$show=false;
  	if (isActionParam("delete".$fieldDb."Change")) {
 		$ret =call_user_func_array(array($db,$functionDelete),array($id,false));$show=true;
  	}
  	if (isActionParam("accept".$fieldDb."Change")) {
 		$ret =$db->dataBase->acceptChangeForEntry(strtolower($fieldDb),$id);$show=true;
  	}
  	if ($show) {
  		if ($ret===true) {
            Appl::setMessage("Rendben, a müvelet sikerült", "success");
        } else {
            Appl::setMessage("Sajnos nem sikerült a müvelet!","danger");
  		}
  	}
	$list = $db->getListToBeChecked(strtolower($fieldDb));

?>

<div class="panel">
<h2 style="text-align: center"><?php echo($title)?></h2>
<?php //** The table of the  changes?>
   <div style="display: inline-block; vertical-align: top; margin: 15px;">
	  <table>
	    <tr>
	    	<td><?php echo $fieldText ?></td><td>Dátum</td><td>IP cím</td><td colspan="3">Akció</td></tr>
	  	<?php
	  		foreach ($list as $i=>$l) {
	   			if ($l["id"]==getIntParam("id"))
                    echo('<tr style="background-color: #c1e2b3">');
                else
                    echo('<tr >');
	   			echo("<td>".$l[$showField]."</td>");
	   			echo("<td>".\maierlabs\lpfw\Appl::dateTimeAsStr($l["changeDate"])."</td>");
	   			echo("<td><a href=\"javascript:showip('".$l["changeIP"]."')\">".$l["changeIP"]."</td>");
	  			if ($l["changeForIDjoin"]!=null) {
	   				echo('<td><button class="btn btn-default" onclick="edit'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-resize-horizontal"></span></button></td>');
	   			}else{
	   				echo('<td><button class="btn btn-default" onclick="edit'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-open"></span></button></td>');
	   			}
	   			echo('<td><button class="btn btn-success" onclick="accept'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-ok"></span></button></td>');
	   			echo('<td><button class="btn btn-danger" onclick="delete'.$fieldDb.'Change('.$l["id"].');"><span class="glyphicon glyphicon-remove"></span></button></td>');
	   			echo("</tr>");
	  		}
	  	?>
      </table>
   </div>
    <?php //Parameter read and action handling
    if ((isActionParam("show".$fieldDb."Change") || isActionParam("accept".$fieldDb."FieldChange")) && isUserAdmin()) {
        $cp=call_user_func_array(array($db,$functionGetByID),array($id,true));
        if ($compare = isset($cp["changeForID"]))
            $op=call_user_func_array(array($db,$functionGetByID),array($cp["changeForID"],true));
        else
            $op=$emptyEntry;
        if (getParam("action")=="accept".$fieldDb."FieldChange") {
            $field=getParam("field");
            $op[$field]=$cp[$field];
            $ret=call_user_func_array(array($db,$functionSave),array($op))>=0;
            if ($ret) {
                Appl::setMessage('Rendben, a müvelet sikerült','success');}
            else {
                Appl::setMessage('Sajnos nem sikerült a müvelet!','danger');}
        }
    ?>
    <?php //** The table of the changes ?>
    <div style="display: inline-block">
		<table style="margin-top: 20px">
	    	<tr style="height: 39px;font-size: 18px;background-color: lightgray; text-align: center;">
	    		<td>Mező</td>
	    		<?php if ($compare) {?><td>Eredeti</td><?php }?>
	    		<td>Módosítás</td>
	    		<?php if ($compare) {?><td>Akció</td><?php }?>
	    	</tr>
	    		<?php foreach ($op as $field=>$value) {
	    			$s=$cp[$field]!=$value?' style="background-color:yellow;"':'';
	    		?>
	    			<tr>
	    				<td ><?php echo $field ?></td>
	    					<?php 
	    					if ($field=="file") {
	    						if ($compare) {
	    							echo('<td ><img class="acceptField" src="'.$value.'" /></td>');
	    						}
	    						echo('<td ><img class="acceptField" src="'.$cp[$field].'" /></td>');
	    					}
	    			    	elseif ($field=="picture") {
	    						if ($compare) {
	    							if (null!=$value && $value!="") 
	    								echo('<td ><img class="acceptField" src="images/'.$value.'" /></td>');
	    							else 
	    								echo('<td ><img class="acceptField" src="images/avatar.jpg" /></td>');
	    						}
	    						echo('<td ><img class="acceptField" src="'.getPersonPicture($cp).'" /></td>');
	    					}
	    					else {
	    						if ($compare) {
	    							echo('<td class="acceptField">'.$value.'</td>');
	    						}
	    						echo('<td class="acceptField" '.$s.'>'.$cp[$field].'</td>');
	    					}
	    					?>
	    				<?php if ($compare) { //Button?>
		    				<td >
		    					<?php if (strstr($field,"change")=="" && $field!="id" && $cp[$field]!=$value) :?>
		    						<button class="btn btn-success" onclick="accept<?php echo $fieldDb?>FieldChange(<?php echo $id ?>,'<?php echo $field?>');"><span class="glyphicon glyphicon-edit"></span></button>
		    					<?php endif;?>
		    				</td>
		    			<?php }?>
	    			</tr>
	    		<?php }	?>
		</table>
    </div>
	<?php }?>
</div>
<?php
    Appl::addJsScript('
        function delete'.$fieldDb.'Change(id) {
            if (confirm("'.$fieldText.' módosítást akarsz törölni. Biztos vagy?"))
                document.location="dataCheck?tabOpen='.getParam("tabOpen").'&action=delete'.$fieldDb.'Change&id="+id;
        }
        
        function edit'.$fieldDb.'Change(id) {
            document.location="dataCheck?tabOpen='.getParam("tabOpen").'&action=show'.$fieldDb.'Change&id="+id;
        }
        
        function accept'.$fieldDb.'Change(id) {
        if (confirm("'.$fieldText.' módosítást akarsz jóváhagyni. Biztos vagy?"))
                document.location="dataCheck?tabOpen='.getParam("tabOpen").'&action=accept'.$fieldDb.'Change&id="+id;
        }
        
        function accept'.$fieldDb.'FieldChange(id,field) {
            document.location="dataCheck?tabOpen='.getParam("tabOpen").'&action=accept'.$fieldDb.'FieldChange&id="+id+"&field="+field;
        }
    ');
}

Appl::addJsScript('
        function deleteMessageChange(id) {
            if (confirm("Üzenetet törölsz, biztos vagy?"))
                document.location="dataCheck?tabOpen='.getParam("tabOpen").'&action=deleteMessageChange&id="+id;
        }
        function acceptMessageChange(id) {
            if (confirm("Üzenet módosítást jóváhagysz, biztos vagy?"))
                document.location="dataCheck?tabOpen='.getParam("tabOpen").'&action=acceptMessageChange&id="+id;
        }
        function resetChange(ip,type) {
            if (confirm("Müvelet erre az IP címre visszaálítsz, biztos vagy?"))
                document.location="dataCheck?tabOpen='.getParam("tabOpen").'&action=resetChange&type="+type+"&ip="+ip;
        }
        
        function setPersonMark(o,personid,pictureid,deleteMark) {
            $.ajax({
                url:"ajax/setPicturePersonUser?personid="+personid+"&pictureid="+pictureid+"&delete="+deleteMark,
                success:function(data){
                    $(o).parent().parent("tr:first").remove()
                },
                error:function(error) {
                    showMessage("'.Appl::__("Nem sikerült!!").'","danger");
                }
            });
            return false;
        }
');

include 'homefooter.inc.php';

