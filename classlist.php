<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';

use maierlabs\lpfw\Appl as Appl;

Appl::addCssStyle('
	.classdiv {
        margin: 3px 2px 3px 2px;
    	width: 154px;
    	height: 67px;
    	vertical-align: top;
    	background-color: #e0e0e0;
    	padding: 5px;
    	border-radius: 10px;
	}
	.classdiv:hover		{background-color: #f8f8f8;}
	.classdiv-a 		{background-color: #f0f0d0;}
	.classdiv-a:hover	{background-color: #ffffd8;}
	.classdiv-b 		{background-color: #e0d0d0;}
	.classdiv-b:hover	{background-color: #f8e8e8;}
');

$isEveningClass=strpos(getParam("tabOpen","day"),"day")===false;
$isTwentyfirstcentury=strpos(getParam("tabOpen","xx"),"xxi")!==false;
if (!$isEveningClass && !$isTwentyfirstcentury) {
    Appl::setSiteSubTitle("Nappali osztályok a XX. században");
    Appl::setSiteDesctiption(getActSchoolName()." nappali tagozat osztályai XX. században");
    Appl::setSiteTitle(getActSchoolName()." nappali tagozat osztályai a XX. században");
}
if (!$isEveningClass && $isTwentyfirstcentury) {
    Appl::setSiteSubTitle("Nappali osztályok a XXI. században");
    Appl::setSiteDesctiption(getActSchoolName()." nappali tagozat osztályai XXI. században");
    Appl::setSiteTitle(getActSchoolName()." nappali tagozat osztályai a XXI. században");
}
if ($isEveningClass) {
    Appl::setSiteSubTitle("Esti tagozat osztályai");
    Appl::setSiteDesctiption(getActSchoolName()." esti tagozat osztályai");
    Appl::setSiteTitle(getActSchoolName()." esti tagozat osztályai");
}

include("homemenu.inc.php");
/** @var array $classes */
global $db;
$classes = $db->getClassList(getRealId(getActSchool()),false,$isEveningClass,$isTwentyfirstcentury,!isUserSuperuser());
$tabsCaption = array();
$tabsTranslate["search"] = array(".php");$tabsTranslate["replace"] = array("");
array_push($tabsCaption ,array("id" => "day", "caption" => 'század nappali tagozat',"iconText"=>"XX.", "glyphicon" => "*wb_sunny"));
array_push($tabsCaption ,array("id" => "dayxxi", "caption" => 'század nappali tagozat',"iconText"=>"XXI.", "glyphicon" => "*wb_sunny"));
array_push($tabsCaption ,array("id" => "night", "caption" => 'tagozat',"iconText"=>"esti", "glyphicon" => "*brightness_3"));
?>

<div class="container-fluid">
    <?php include Config::$lpfw.'view/tabs.inc.php';?>
    <div class="panel panel-default " >
		<div class="panel-heading">
			<a class="btn btn-default" href="editSchoolClass?action=newclass">Új osztály</a></h4>
		</div>
		<div class="panel-body">
			<?php displayClassList($db,$classes);?>
		</div>
	</div>
<?php

showTabFooterLinks();
include("homefooter.inc.php");

/**
 * get scholl class list
 * @param dbDAO $db
 * @param array $classes
 * @param int $eveningClass
 */
function displayClassList($db, $classes) {
	foreach($classes as $cclass) {
	    if (($cclass["name"]!="Todo" || isUserSuperuser()) && $cclass["name"]!="Staf"  ) {
            if (getActClassId() == $cclass["id"])
                $aktualClass = "classdiv actual_class_in_menu";
            else
                $aktualClass = "classdiv";
            if (substr($cclass["name"], -1) === "A") {
                $aktualClass .= " classdiv-a";
            }
            if (substr($cclass["name"], -1) === "B") {
                $aktualClass .= " classdiv-b";
            }
            ?>
            <div style="display: inline-block;" class="<?php echo($aktualClass); ?>">
                <a style="font-size: large;" href="hometable?classid=<?php echo($cclass["id"]); ?>">
                    <?php echo $cclass["text"]?>
                </a>
                <?php $stat = $db->getClassStatistics($cclass["id"], isUserAdmin()); ?>
                <span class="badge" title="diákok száma"><?php echo $stat->personCount?></span>
                <?php if (isUserAdmin()) { ?>
                    <span class="badge"
                          title="képek száma"><?php echo $stat->personWithPicture + $stat->personPictures + $stat->classPictures ?></span>
                <?php } ?>
                <div>
                    <?php if (isset($stat->teacher->id)) echo(getPersonLinkAndPicture((array)$stat->teacher)); ?>
                </div>
            </div>
            <?php
        }
	}
}


