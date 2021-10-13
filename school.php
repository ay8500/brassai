<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once "dbBL.class.php";

use maierlabs\lpfw\Appl as Appl;
global $db;

Appl::setSiteTitle("Kolzsvári középiskola");
Appl::setSiteSubTitle(getActSchoolName());
include("homemenu.inc.php");
if (getActSchoolId()==null) {
    ?><div class="well">Iskola nincs kiválasztva.</div><?php
    include ("homefooter.inc.php");
    die();
}

$school=getActSchool();
$schoolLogo = "images".DIRECTORY_SEPARATOR.$db->getActSchoolFolder().DIRECTORY_SEPARATOR.$school["logo"];

if (isActionParam("save")) {
    $school["text"] = createSchoolJson();
    if ($db->saveSchool($school))
        Appl::setMessage("Kimentés sikerült.Köszönjük a kiegészítést vagy módosítást.".getParam("nr"),"success");
    else
        Appl::setMessage("Hiba:".getParam("nr"),"danger");
}

if (isActionParam("cancel")) {
    Appl::setMessage("Cancel:".getParam("nr"),"success");
}

if (isActionParam("delete")) {
    Appl::setMessage("Delete:".getParam("nr"),"success");
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
        Appl::setMessage("Hiba:".getParam("nr"),"danger");
}

if (isActionParam("up")) {
    Appl::setMessage("Up:".getParam("nr"),"success");
}

if (isActionParam("down")) {
    Appl::setMessage("Down:".getParam("nr"),"success");
}
$content = json_decode(($school["text"]),true);
if ($content==null )
    if (!isset($school["text"]) || strlen($school["text"])<5)
        $content = json_decode('[{"title":"","text":"","source":""}]',true);
    else
        $content = json_decode('[{"title":"ERROR","text":"ERROR","source":"ERROR"}]',true);

?>
    <div class="well">
        <h2><img style="height: 60px" src="<?php echo $schoolLogo ?>"/> <?php echo $school["name"] ?></h2>
        <?php  if (isUserAdmin()) {?>
            <a href="history?table=school&id=<?php echo $school["id"]?>" style="display:inline-block;" title="módosítások">
                <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("school",$school["id"]))?></span>
            </a>
        <?php }?>

        <?php if (isActionParam("edit") || isActionParam("cancel")  || isActionParam("up") || isActionParam("down") || isActionParam("delete") || isActionParam("new")) { ?>
            <form action="school" method="post">
            <?php foreach ($content as $idx=>$paragraph) { ?>
                <div class="row" id="paragraph-<?php echo $idx ?>">
                    <div class="col-md-7">
                        Bekezdés:<input  placeholder="bekezdés címe" title="bekezdés címe"
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
                            <img src="<?php echo $paragraph["picture"]["url"] ?>"><br/>
                        <?php } ?>
                        <input  placeholder="url"
                                name="picture-url-<?php echo $idx ?>"
                                value="<?php echo isset($paragraph["picture"]["url"])?$paragraph["picture"]["url"]:"" ?>"
                                style="width:100%"/>
                        <input  placeholder="kép címe"
                                name="picture-text-<?php echo $idx ?>"
                                value="<?php echo isset($paragraph["picture"]["text"])?$paragraph["picture"]["text"]:"" ?>"
                                style="width:100%"/>
                        <input  placeholder="forrás"
                                name="picture-source-<?php echo $idx ?>"
                                value="<?php echo isset($paragraph["picture"]["source"])?$paragraph["picture"]["source"]:"" ?>"
                                style="width:100%"/>
                    </div>
                </div>
                <div class="row col-md-11" style="padding: 15px;">
                        <button name="action" value="save" class="btn btn-success" onclick="return schoolSubmit(<?php echo $idx ?>,0);">kiment</button>
                        <button name="action" value="cancel" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,1);">elözö versió</button>
                        <button name="action" value="delete" class="btn btn-danger" onclick="return schoolSubmit(<?php echo $idx ?>,2);">töröl</button>
                        <button name="action" value="new" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,0);">új bekedzés</button>
                        <button name="action" value="up" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,0);">feljebb</button>
                        <button name="action" value="down" class="btn btn-warning" onclick="return schoolSubmit(<?php echo $idx ?>,0);">lejjebb</button>
                    <hr/>
                </div>
            <?php } ?>
                <input type="hidden" name="schoolid" value="<?php echo $school["id"] ?>">
            </form>
            <div class="row"></div>
        <?php } else { ?>



            <div class="row">
                Utoljára módosította: <?php echo getPersonLinkAndPicture($db->getPersonByID($school["changeUserID"])) ?>
                <?php echo Appl::dateTimeAsStr($school["changeDate"]) ?>
                <form action="school">
                    <input type="hidden" name="schoolid" value="<?php echo $school["id"] ?>">
                    <button name="action" value="edit" class="btn btn-success">módosítom / kiegészítem</button>
                </form>
            </div>
            <?php foreach ($content as $paragraph) { ?>
                <div class="row">
                    <h3><?php echo $paragraph["title"] ?></h3>
                    <?php if (isset($paragraph["picture"]) && isset($paragraph["picture"]["url"]) ) {?>
                        <div class="col-md-7"><?php echo ($paragraph["text"]) ?><br />
                        <?php echo strlen($paragraph["source"])>0?("Forrás:".$paragraph["source"]):""?></div>
                        <div class="col-md-4" style="border: 1px solid lightgrey; padding: 10px;margin: 10px;">
                            <img src="<?php echo $paragraph["picture"]["url"] ?>"><br/>
                            <?php echo $paragraph["picture"]["text"] ?>
                            <br/><?php echo strlen($paragraph["picture"]["source"])>0?("Forrás:".$paragraph["picture"]["source"]):""?>
                        </div>
                    <?php } else { ?>
                        <div class="col-md-11"><?php echo ($paragraph["text"]) ?></div>
                        <div><?php echo strlen($paragraph["source"])>0?("Forrás:".$paragraph["source"]):""?></div>
                    <?php } ?>
                </div>
            <?php } ?>
            <hr />
            <form action="school">
                <input type="hidden" name="schoolid" value="<?php echo $school["id"] ?>">
                <button name="action" value="edit" class="btn btn-success">módosítom / kiegészítem</button>
            </form>
        <?php } ?>
    </div>

<script>
    function schoolSubmit(formid,checkValue) {
        if (checkValue===1) {
            if (!confirm("Biztos vissza szeretnéd állítani az elözö szöveget?")) {
                return  false;
            }
        } else if (checkValue===2) {
            if (!confirm("Biztos ki szeretnéd törölni ezt a felyezetet?")) {
                return false;
            }
        }
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
            $paragraph->text = addslashes(str_replace("\n","",str_replace("\r","<br />",getParam("text-".$idx,""))));
            $paragraph->source = getParam("source-".$idx,"");
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

/**
 * create the school json from each paraph or an array
 * @param $returnJson
 * @return string
 */
function createSchoolJson($array=null) {
    if ($array==null)
        $array = createSchoolArray();
    return json_encode($array,JSON_UNESCAPED_UNICODE);
}
include("homefooter.inc.php");