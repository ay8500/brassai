<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include 'displayCards.inc.php';
include_once 'dbBL.class.php';

use \maierlabs\lpfw\Appl as Appl;

Appl::setSiteSubTitle("Iskola módosítás");
if ( !isUserSuperuser()) {
    Appl::setMessage("Hozzáféresi jog hiányzik!","danger");
    include_once "homefooter.inc.php";
    die();
}
unsetActSchool();
include_once "homemenu.inc.php";

global $schoolList, $db;
$person = $db->getPersonByID(getParam("id"));
if (getIntParam("classid",-1)>-1) {
    $person["classID"] = getIntParam("classid");
    if ($db->savePersonField($person['id'],'classID',getIntParam("classid"),true)>=0)
        Appl::setMessage("Sikeres iskola modosítás","success");
    else
        Appl::setMessage("Sikertelen iskola modosítás","danger");
    $person = $db->getPersonByID(getParam("id"));
}
displayPerson($db,$person,true, false);
echo('<div class="well">');
if (getParam("schoolid")!=NULL) {
    Appl::_("Második lépes: válassz osztályt");
} else {
    Appl::_("Elsö lépes: válassz új iskolát");
}
echo("</div>");
foreach ($schoolList as $school) {
    if ($person["schoolID"]!=$school["id"]) { ?>
        <br/>
        <button class="btn btn-default" onclick="document.location='editPersonSchool?id=<?php echo $person["id"].'&schoolid='.$school['id']?>'">
            <img src="images/school<?php echo $school['id'].'/'.$school['logo']?>" style="height:22px;margin-right:10px;" />
            <?php echo $school["name"] ?>
        </button>
        <br/>
    <?php }
    if ($school['id']==getIntParam("schoolid")) {
        $classList = $db->getClassList($school['id']);
        foreach ($classList as $class) {
            $headTeacher = $db->getPersonByID($class['headTeacherID']);
            $link = $headTeacher!=NULL?(" Osztályfönök: ".getPersonLinkAndPicture($headTeacher)):"";
            if ($class['eveningClass']==="1")
                $link = ' esti '.$link;
            echo('<div style="margin: 5px 5px 0px 15px;"><button onclick="changeSchool('.$person["id"].','.$class["id"].');" class="btn btn-default">'.$class['text'].'</button>'.$link.'</div>');
        }
    }
}

?>
<script>
    function changeSchool(personid,classid) {
        document.location="editPersonSchool?id="+personid+"&classid="+classid;
    }
</script>
<?php
    include_once "homefooter.inc.php";
