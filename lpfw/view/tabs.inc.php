<?php
include_once __DIR__.'/../appl.class.php';
//*********************** TABS ***********************************************************
//** Usage
// $tabsCaption=Array("Tab Caption 1","Tab Caption "");
// include("tabs.php");
// if ($tabOpen==0) {......}


$tabOpen=getParam("tabOpen",$tabsCaption[0]["id"]);
if (!isset($tabUrl))
	$tabUrl=getenv("SCRIPT_NAME");

    \maierlabs\lpfw\Appl::addJsScript('
    var siteValuesChanged = false;
    
    function fieldChanged() {
    	siteValuesChanged = true;
    }

    function fieldSaved() {
    	siteValuesChanged = false;
    }
    
    function changeTab(link) {
    	if ( siteValuesChanged ) { 
    		if (confirm("'.\maierlabs\lpfw\Appl::__("If you leave before saving, your changes will be lost. Continue?").'" )) {
    			 	window.location=link;
    		}    	
    	}
    	else window.location=link;
    }
    ');
?>

<ul class="nav nav-pills nav-justified" role="tablist">
<?php 
	foreach($tabsCaption as $tab ) {
	    if (!isset($tab["glyphicon"]) || $tab["glyphicon"]=="") {
	        $tab["glyphicon"]='link';
        }
		if ($tab["id"]==$tabOpen)
	        echo '<li title="'.strip_tags($tab["caption"]).'" class="active"><a href="#"><span class="glyphicon glyphicon-'.$tab["glyphicon"].'"></span> <span>'.$tab["caption"].'</span></a></li>';
		else
         	echo '<li title="'.strip_tags($tab["caption"]).'" ><a href="javascript:changeTab('."'".$tabUrl.'?tabOpen='.$tab["id"]."'".');" ><span class="glyphicon glyphicon-'.$tab["glyphicon"].'"></span> <span>'.$tab["caption"].'</span></a></li>';
	}
?>       
</ul>

