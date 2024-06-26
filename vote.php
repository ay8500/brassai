<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbDaSongVote.class.php';
use \maierlabs\lpfw\Appl as Appl;
include_once("dbBL.class.php");

global $db;
$dbVote = new dbDaSongVote($db);

$sTitle="Következő érettségi találkozónk";
$SiteDescription="A következő érettségi találkozónk szavazati listája";

Appl::setSiteTitle($sTitle,$sTitle,$SiteDescription);

Appl::addCssStyle('
	.votetable:nth-child(odd){background-color: #f0f0f0;};
	.votetable:nth-child(even){background-color: #e0e0e0;};
');
Appl::setSiteSubTitle('A következő érettségi találkozónk');

include("homemenu.inc.php");

$class=Appl::getMember("actClass");
if ($class!=null) {
    $classMeetingCount=date("Y")-intval($class["graduationYear"]);

    if (($classMeetingCount % 5)!=0) $classMeetingCount += 5 - ($classMeetingCount % 5);
    if ($classMeetingCount<10) $classMeetingCount=10;
    $classMeetingYear = intval(Appl::getMember("actClass")["graduationYear"])+$classMeetingCount;

    $data=$db->getPersonListByClassId(Appl::getMemberId("actClass"));


    //Save vote data
    if (isActionParam("vote")) {
        //Save all data for admins, superusers and editors
        if ( isUserEditor() || isUserSuperuser() || (isUserLoggedOn() && getActUserId()==getParam("personID"))) {
            $vote=array();
            $vote["personID"]=getParam("personID");
            $vote["classID"]=getParam("classID");
            $vote["meetAfterYear"]=getParam("meetAfterYear");
            $vote["eventDay"]=getParam("eventDay","");
            $vote["isSchool"]=getParam("isSchool","")=="on"?1:0;
            $vote["isCemetery"]=getParam("isCemetery","")=="on"?1:0;
            $vote["isDinner"]=getParam("isDinner","")=="on"?1:0;
            $vote["isExcursion"]=getParam("isExcursion","")=="on"?1:0;
            $vote["place"]=getParam("place","");;
            $vote["id"]=intval(getParam("id","-1"));
            $ret=$dbVote->saveVote($vote)>=0?0:1;
            if ($ret==0)
                Appl::setMessage('Sikeresen kimentve. Köszönjük bejegyzésed.', 'success');
            else
                Appl::setMessage('Szavazat kimentése nem sikerült!', 'warning');
        }
    }
    ?>

    <div class="container-fluid">
    <div class="well well-lg">
        <b>A következő <?php echo $classMeetingCount?> éves talákozonk <?php echo $classMeetingYear?> ben lesz megtartva.</b>
        Légyszíves
        <?PHP if (!isUserLoggedOn()) echo(' jelenkezz be '); ?>
        és töltsd ki a táblázatot egyszerübb organizáció miatt.
    </div>

    <table class="pannel" style="width:auto" >
    <tr style="font-weight:bold">
        <td></td>
        <td style="min-width:133px"  class="hidden-xs hidden-sm">Név</td>
        <td>Dátum javaslat</td>
        <td style="width: 80px;text-align: center;" class="hidden-xs">Osztály-<br>főnöki</td>
        <td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-education"></span></td>
        <td style="width: 80px;text-align: center;" class="hidden-xs">Temető</td>
        <td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-plus"></span></td>
        <td style="width: 80px;text-align: center;" class="hidden-xs">Vacsora</td>
        <td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-cutlery"></span></td>
        <td style="width: 80px;text-align: center;" class="hidden-xs">Kirán-<br>dulás</td>
        <td style="width: 25px;text-align: center;" class="visible-xs"><span class="glyphicon glyphicon-tree-conifer"></span></td>
        <td class="hidden-xs">Kirándulás, hova?</td>
    </tr>
    <?php
        foreach ($data as $d)
        {
            if (!isset($d["deceasedYear"])) {
                $vote = $dbVote->getVote(getRealId($d), $classMeetingCount);
                ?>
                <form method="post"">
                <tr class="votetable">
                    <td>
                        <img src="<?php echo getPersonPicture($d) ?>" style="height:30px; border-radius:3px; margin:2px;"/>
                    </td>
                    <td class="hidden-xs hidden-sm">
                        <?php echo $d["lastname"] . ' ' . $d["firstname"];
                        if (showField($d, "birthname")) echo(' (' . $d["birthname"] . ')'); ?>
                    </td>
                    <?php
                    if (isUserEditor() || isUserSuperuser() || $d["id"] == getLoggedInUserId() && getRealId(getActClass()) == $db->getLoggedInUserClassId()) {
                        $dis = "";
                        $ro = "";
                    } else {
                        $dis = "disabled";
                        $ro = "readonly";
                    }
                    ?>
                    <td><input style="text" class="form-control" <?php echo $ro ?> size="10" name="eventDay"
                               value="<?php echo $vote["eventDay"] ?>"/></td>
                    <td><input type="checkbox" size="4" <?php echo $dis ?>
                               name="isSchool" <?php echo $vote["isSchool"] == 1 ? "checked" : "" ?> /></td>
                    <td><input type="checkbox" size="4" <?php echo $dis ?>
                               name="isCemetery" <?php echo $vote["isCemetery"] == 1 ? "checked" : "" ?> /></td>
                    <td><input type="checkbox" size="4" <?php echo $dis ?>
                               name="isDinner" <?php echo $vote["isDinner"] == 1 ? "checked" : "" ?> /></td>
                    <td><input type="checkbox" size="4" <?php echo $dis ?>
                               name="isExcursion" <?php echo $vote["isExcursion"] == 1 ? "checked" : "" ?> /></td>
                    <td class="hidden-xs"><input class="form-control" <?php echo $ro ?> style="text" size="40" name="place"
                                                 value="<?php echo $vote["place"] ?>"/></td>
                    <td>
                        <?php if ($dis == "") : ?>
                            <?php if (isset($vote["id"])) { ?>
                                <button value="<?php echo $vote["id"] ?>" name="id" type="submit" class="btn btn-default">
                                    <span class="glyphicon glyphicon-save"></span> Kiment
                                </button>
                                <?php if (isUserEditor() || isUserSuperuser()) { ?>
                                    <a title="módosítások" href="history?table=vote&id=<?php echo $vote["id"] ?>"
                                       style="display:inline-block;">
                                        <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("vote", $vote["id"])) ?></span>
                                    </a>
                                <?php } else { ?>
                                    <span title="módosítások"
                                          class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("vote", $vote["id"])) ?></span>
                                <?php } ?>
                            <?php } else { ?>
                                <button value="-1" name="id" type="submit" class="btn btn-default"><span
                                            class="glyphicon glyphicon-save"></span> Kiment
                                </button>
                            <?php } ?>
                            </a>
                        <?php endif; ?>
                    </td>
                    <input type="hidden" value="vote" name="action"/>
                    <input type="hidden" value="<?php echo $d["id"] ?>" name="personID"/>
                    <input type="hidden" value="<?php echo getRealId(getActClass()) ?>" name="classID"/>
                    <input type="hidden" value="<?php echo $classMeetingCount ?>" name="meetAfterYear"/>
                </tr>
                </form>
                <?php
            }
        }
    ?>
    </table>
    </div>
    <div>&nbsp;</div>
<?php }?>
<?php include 'homefooter.inc.php';?>