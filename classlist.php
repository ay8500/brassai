<?php
include_once 'tools/sessionManager.php';
include_once 'tools/appl.class.php';
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
Appl::setSiteSubTitle("Osztályok");
Appl::setSiteDesctiption(getAktSchoolName()." osztályai");
include("homemenu.inc.php");

/** @var array $classes */
$classes = $db->getClassList(getAktSchoolId());

?>
<div class="container-fluid">
	<div class="panel panel-default " >
		<div class="panel-heading">
			<h4><span class="glyphicon glyphicon-asterisk"></span> Nappali tagozat
			<a class="btn btn-default" href="editclass.php?action=newclass">Új osztály</a></h4>
		</div>
		<div class="panel-body">
			<?php displayClassList($db,$classes,0);?>
		</div>
		<div class="panel-heading">
			<h4><span class="glyphicon glyphicon-star"></span> Esti tagozat
			<a class="btn btn-default" href="editclass.php?action=newclass">Új osztály</a></h4>
		</div>
		<div class="panel-body">
			<?php displayClassList($db,$classes,1);?>
		</div>
	</div>


<?php
/**
 * get scholl class list
 * @param dbDAO $db
 * @param array $classes
 * @param int $eveningClass
 */
function displayClassList($db, $classes, $eveningClass) {
	foreach($classes as $cclass) {
		if ($eveningClass==$cclass["eveningClass"]) {
			if (getAktClassId()==$cclass["id"])
				$aktualClass="classdiv actual_class_in_menu";
			else
				$aktualClass="classdiv";
			if (substr($cclass["name"],-1)==="A") {
				$aktualClass .=" classdiv-a";
			}
			if (substr($cclass["name"],-1)==="B") {
				$aktualClass .=" classdiv-b";
			}
			?>
  			<div style="display: inline-block;" class="<?php echo($aktualClass);?>" >
  				<a style="font-size: large;" href="hometable.php?classid=<?php echo($cclass["id"]);?>">
  					<?php echo($cclass["text"]); ?>
  				</a>
   	  			<?php  $stat=$db->getClassStatistics($cclass["id"],userIsAdmin());?>
   		  			<span class="badge" title="diákok száma"><?php echo $stat->personCount?></span>
   		  		<?php if (userIsAdmin()) {?>
   			  		<span class="badge" title="képek száma"><?php echo $stat->personWithPicture+$stat->personPictures+$stat->classPictures?></span>
   		  		<?php } ?>
   		  		<div>
   		  		<?php 
   		  		if (isset($stat->teacher->picture) && strlen($stat->teacher->picture)>1) {
   		  			echo('<div style="display:inline"><img src="images/'.$stat->teacher->picture.'" class="diak_image_sicon"/></div>');
   		  		}
   		  		if (isset($stat->teacher->lastname)) {
   		  			echo ('<div style="display:inline">'.$stat->teacher->lastname." ".$stat->teacher->firstname.'</div>');
				}?>
				</div>
  			</div>
  		<?php 
		}
	}
}

include("homefooter.inc.php");
?>
