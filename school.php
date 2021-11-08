<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once "dbBL.class.php";

use maierlabs\lpfw\Appl as Appl;
global $db;

Appl::setSiteTitle("Kolozsvári középiskola");
Appl::setSiteSubTitle(getActSchoolName());
if (getActSchoolId()==null) {
    include("homemenu.inc.php");
    ?><div class="well">Iskola nincs kiválasztva.</div><?php
    include ("homefooter.inc.php");
    die();
}

$school=getActSchool();
$schoolLogo = "images".DIRECTORY_SEPARATOR.$db->getActSchoolFolder().DIRECTORY_SEPARATOR.$school["logo"];

if (isActionParam("save")) {
    $json = createSchoolJson();
    if (strpos($json,"\\")!==false) {
        Appl::setMessage("Sajnos a szöveg nem tartalmazhat \ (backslash) karaktert!" , "warning");
        $json =str_replace("\\","/",$json);
    }
    if (null==json_decode(($json),true)) {
        Appl::setMessage("Sajnos a szöveg csak olvasható betűk tartalmazhat!", "danger");
        $json =$school["text"];
    } else {
        $school["text"] = $json;
        if ($db->saveSchool($school))
            Appl::setMessage("Kimentés sikerült.Köszönjük a kiegészítést vagy módosítást." . getParam("nr"), "success");
        else
            Appl::setMessage("Kimentés nem sikerült, probálkozz még egyszer!", "danger");
    }
}

if (isActionParam("cancel")) {
    //Appl::setMessage("Cancel:".getParam("nr"),"success");
}

if (isActionParam("delete")) {
    $a = createSchoolArray();
    $idx = getIntParam("nr");
    if ($idx>=0 && $idx<=sizeof($a)-1) {
        unset($a[$idx]);
        $school["text"]= createSchoolJson($a);
        if ($db->saveSchool($school))
            Appl::setMessage("Felyezet sikeresen törölve.", "success");
        else
            Appl::setMessage("Sajnos az áthelyezés kimentése nem sikerült.", "danger");
    } else
        Appl::setMessage("Sajnos az áthelyezés nem sikerült.", "danger");
}

if (isActionParam("new")) {
    $a = createSchoolArray();
    $paragraphObject = new stdClass();
    $paragraphObject->title ="Új bekedzés";
    $paragraphObject->text ="";
    $paragraphObject->source ="";
    $paragraph = array();
    $paragraph[] = $paragraphObject;
    array_splice( $a, getIntParam("nr")+1, 0,$paragraph  );
    $school["text"] = createSchoolJson($a);
    if ($db->saveSchool($school))
        Appl::setMessage("Új bekezdés létrehozva. Köszönjük a kiegészítést.","success");
    else
        Appl::setMessage("Sajnos egy új bekezdés létrehozása nem sikerült.","danger");
}

if (isActionParam("up")) {
    $a = createSchoolArray();
    $idx = getIntParam("nr");
    if ($idx>0 && $idx<=sizeof($a)-1) {
        array_swap($a, $idx, $idx-1);
        $school["text"]= createSchoolJson($a);
        if ($db->saveSchool($school))
            Appl::setMessage("Felyezet sikeresen egy pozicióval feljebb lett tolva.", "success");
        else
            Appl::setMessage("Sajnos az áthelyezés kimentése nem sikerült.", "danger");
    } else
        Appl::setMessage("Sajnos az áthelyezés nem sikerült.", "danger");
}

if (isActionParam("down")) {
    $a = createSchoolArray();
    $idx = getIntParam("nr");
    if ($idx>=0 && $idx<sizeof($a)-1) {
        array_swap($a, $idx, $idx+1);
        $school["text"]= createSchoolJson($a);
        if ($db->saveSchool($school))
            Appl::setMessage("Felyezet sikeresen egy pozicióval lejjebb lett tolva.", "success");
        else
            Appl::setMessage("Sajnos az áthelyezés kimentése nem sikerült.", "danger");
    } else
        Appl::setMessage("Sajnos az áthelyezés nem sikerült.", "danger");
}

$content = json_decode(($school["text"]),true);
if ($content==null )
    if (!isset($school["text"]) || strlen($school["text"])<5)
        $content = json_decode('[{"title":"","text":"","source":""}]',true);
    else
        $content = json_decode('[{"title":"ERROR","text":"ERROR","source":"ERROR"}]',true);
include("homemenu.inc.php");
?>

<div class="well">
    <div class="col-md-6">
        <h2>
            <?php  if (isUserAdmin() ) {?>
                <a href="history?table=school&id=<?php echo $school["id"]?>" style="display:inline-block;" title="módosítások">
                    <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("school",$school["id"]))?></span>
                </a>
            <?php }?>
            <img style="height: 60px" src="<?php echo $schoolLogo ?>"/> <?php echo $school["name"] ?>
        </h2>

        <div>
            <b>Tartalom:</b><ul>
            <?php foreach ($content as $idx=>$paragraph) { ?>
                <li><a href="#section-<?php echo $idx ?>"><?php echo isset($paragraph["title"])?$paragraph["title"]:"" ?></a></li>
            <?php } ?>
            </ul>
        </div>
    </div>
    <div class="col-md-5" style="padding: 20px;border: 1px lightgrey solid;">
        <span style="width: 100px; text-align: right; padding: 3px; display: inline-block">Telefon:</span><?php echo $school["phone"] ?><br />
        <span style="width: 100px; text-align: right; padding: 3px; display: inline-block">E-Mail:</span><?php echo $school["mail"] ?><br />
        <span style="width: 100px; text-align: right; padding: 3px; display: inline-block">Intenet:</span><?php echo $school["homepage"] ?><br />
        <span style="width: 100px; text-align: right; padding: 3px; display: inline-block">Cím:</span><?php echo $school["addressZipCode"]."  ".$school["addressCity"] ?><br />
        <span style="width: 100px; text-align: right; padding: 3px; display: inline-block"></span><?php echo $school["addressStreet"] ?>
    </div>
    <div class="row"></div>


    <?php if (isActionParam("edit") || isActionParam("cancel")  || isActionParam("up") || isActionParam("down") || isActionParam("delete") || isActionParam("new")) { ?>
        <form action="school" method="post">
        <?php foreach ($content as $idx=>$paragraph) { ?>
            <div class="row" id="paragraph-<?php echo $idx ?>">
                <div class="col-md-7">
                    <span  id="section-<?php echo $idx ?>">Bekezdés:</span><input  placeholder="bekezdés címe" title="bekezdés címe"
                            name="title-<?php echo $idx ?>"
                            value="<?php echo isset($paragraph["title"])?$paragraph["title"]:"" ?>"
                            style="width:100%"/>
                    Szöveg:
<textarea name="text-<?php echo $idx ?>" style="width: 100%; height:250px"><?php echo str_replace("<br />","\r\n",$paragraph["text"]) ?></textarea>
                    Forrás:<input  placeholder="forrás"
                            name="source-<?php echo $idx ?>"
                            value="<?php echo isset($paragraph["source"])?$paragraph["source"]:"" ?>"
                            style="width:100%"/>

                </div>
                <div class="col-md-4" style="border: 1px solid lightgrey; padding: 10px;margin: 10px;">
                    <?php if (isset($paragraph["picture"]["url"])) { ?>
                        <img src="<?php echo $paragraph["picture"]["url"] ?>" style="max-width: 100%;"><br/>
                    <?php } ?>
                    <input  placeholder="url" name="picture-url-<?php echo $idx ?>" <?php echo (isUserAdmin()?"":'disabled="disabled"') ?>
                            value="<?php echo isset($paragraph["picture"]["url"])?$paragraph["picture"]["url"]:"" ?>"
                            style="width:100%"/>
                    <input  placeholder="kép címe" name="picture-text-<?php echo $idx ?>"
                            value="<?php echo isset($paragraph["picture"]["text"])?$paragraph["picture"]["text"]:"" ?>"
                            style="width:100%"/>
                    <input  placeholder="forrás" name="picture-source-<?php echo $idx ?>"
                            value="<?php echo isset($paragraph["picture"]["source"])?$paragraph["picture"]["source"]:"" ?>"
                            style="width:100%"/>
                </div>
            </div>
            <div class="row col-md-11" style="padding: 15px;">
                    <button name="action" value="save" class="btn btn-success" onclick="return schoolSubmit(<?php echo $idx ?>,0);"><span class="glyphicon glyphicon-floppy-disk"></span> kiment</button>
                    <button name="action" value="cancel" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,1);"><span class="glyphicon glyphicon-refresh"></span> vissza állít</button>
                    <button name="action" value="delete" class="btn btn-danger" onclick="return schoolSubmit(<?php echo $idx ?>,2);"><span class="glyphicon glyphicon-remove"></span> töröl</button>
                    <button name="action" value="new" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,0);"><span class="glyphicon glyphicon-plus"></span> új bekedzés</button>
                    <?php if ($idx>0) { ?>
                        <button name="action" value="up" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,0);"><span class="glyphicon glyphicon-menu-up"></span> feljebb</button>
                    <?php } if ($idx<=sizeof($paragraph)-1) { ?>
                        <button name="action" value="down" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,0);"><span class="glyphicon glyphicon-menu-down"></span> lejjebb</button>
                    <?php } ?>
                <hr/>
            </div>
        <?php } ?>
            <input type="hidden" name="schoolid" value="<?php echo $school["id"] ?>">
            <input type="hidden" id="formid" name="nr" />
        </form>
        <div class="row"></div>
    <?php } else { ?>



        <div class="row" style="margin: 20px;">
            <?php if (isUserAdmin()) { ?>
            <form action="school">
                <input type="hidden" name="schoolid" value="<?php echo $school["id"] ?>" />
                <button name="action" value="edit" class="btn btn-success"><span class="glyphicon glyphicon-pencil"></span> módosítom / kiegészítem</button>
            </form>
            <?php } ?>
            Utoljára módosította: <?php echo getPersonLinkAndPicture($db->getPersonByID($school["changeUserID"])) ?>
            <?php echo Appl::dateTimeAsStr($school["changeDate"]) ?>
        </div>
        <?php foreach ($content as $idx=> $paragraph) { ?>
            <div class="row">
                <h3 id="section-<?php echo $idx ?>"><?php echo $paragraph["title"] ?> <a href="#top"><span class="glyphicon glyphicon-menu-up"></span></a></h3>
                <?php if (isset($paragraph["picture"]) && isset($paragraph["picture"]["url"]) ) {?>
                    <div class="col-md-7">
                        <?php echo ($paragraph["text"]) ?><br />
                        <span style="font-size:smaller; font-style: italic">
                            <?php echo strlen($paragraph["source"])>0?("Forrás:".$paragraph["source"]):""?>
                        </span>
                    </div>
                    <div class="col-md-4" style="border: 1px solid lightgrey; padding: 10px;margin: 10px;">
                        <img src="<?php echo $paragraph["picture"]["url"] ?>" style="max-width: 100%;"><br/>
                        <?php echo $paragraph["picture"]["text"] ?>
                        <br/><span style="font-size:smaller; font-style: italic"><?php echo strlen($paragraph["picture"]["source"])>0?("Forrás:".$paragraph["picture"]["source"]):""?></span>
                    </div>
                <?php } else { ?>
                    <div class="col-md-11">
                        <?php echo ($paragraph["text"]) ?>
                        <br/><span style="font-size:smaller; font-style: italic">
                            <?php echo strlen($paragraph["source"])>0?("Forrás:".$paragraph["source"]):""?>
                        </span>
                    </div>
                <?php } ?>
            </div>
            <hr />
        <?php } ?>
    <?php } ?>
</div>

<script>
    function schoolSubmit(formid,checkValue) {
        if (checkValue===1) {
            if (!confirm("Biztos vissza szeretnéd állítani az előző szöveget?")) {
                return  false;
            }
        } else if (checkValue===2) {
            if (!confirm("Biztos ki szeretnéd törölni ezt a felyezetet?")) {
                return false;
            }
        }
        $("#formid").val(formid);
        return true;
    }
</script>


<?php

/**
 * create the school array from each paraph
 * @param $returnJson
 * @return array
 */
function createSchoolArray() {
    $ret = array();
    foreach ($_POST as $name => $post) {
        if (strpos($name,"title-")!==false) {
            $paragraph = new stdClass();
            $idx = intval(substr($name,6));
            $paragraph->title =getParam("title-".$idx,"");
            $paragraph->source = getParam("source-".$idx,"");
            $text = getParam("text-".$idx,"");
            $text = str_replace("\n","",str_replace("\r","<br />",$text));
            $text = str_replace("''''","'",$text);
            $text = str_replace("\"","'",$text);
            $paragraph->text = $text;
            if (strlen(getParam("picture-url-".$idx,""))>5) {
                $picture = new stdClass();
                $picture->url = getParam("picture-url-".$idx);
                $picture->text = getParam("picture-text-".$idx,"");
                $picture->source = getParam("picture-source-".$idx,"");
                $paragraph->picture = $picture;
            }
            $ret[] = $paragraph;
        }
    }
    return $ret;
}


function array_swap(&$array,$swap_a,$swap_b){
    list($array[$swap_a],$array[$swap_b]) = array($array[$swap_b],$array[$swap_a]);
}

/**
 * create the school json from each paraph or an array
 * @param $returnJson
 * @return string
 */
function createSchoolJson($array=null) {
    if ($array==null)
        $array = createSchoolArray();
    return json_encode($array,JSON_UNESCAPED_UNICODE| JSON_UNESCAPED_SLASHES);
}
include("homefooter.inc.php");