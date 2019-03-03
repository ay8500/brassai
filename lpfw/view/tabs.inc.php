<?php
include_once __DIR__.'/../appl.class.php';
//*********************** TABS ***********************************************************
//** Usage
// include 'lpfw/view/tabs.inc.php';
// $tabsCaption = array(array("id" => "person", "caption" => "Person", "glyphicon" => "user"));
// array_push($tabsCaption ,array("id" => "candles", "caption" => "Candle", "glyphicon" => "plus"));
// if ($tabOpen=="person") {......}


$tabOpen=getParam("tabOpen",$tabsCaption[0]["id"]);
	$tabUrl=getenv("SCRIPT_NAME")."?";
	if ($tabUrl=="/dc.php?")
	    $tabUrl ="/editDiak.php?";
    $params=explode("&",getenv("QUERY_STRING"));
    foreach ($params as $param) {
        if (strpos($param,"tabOpen")===false)
            $tabUrl .=$param."&";
    }

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
         	echo '<li title="'.strip_tags($tab["caption"]).'" ><a href="javascript:changeTab('."'".$tabUrl.'tabOpen='.$tab["id"]."'".');" ><span class="glyphicon glyphicon-'.$tab["glyphicon"].'"></span> <span>'.$tab["caption"].'</span></a></li>';
	}
?>       
</ul>

