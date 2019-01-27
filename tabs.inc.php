<?php
//*********************** TABS ***********************************************************
//** Usage
// $tabsCaption=Array("Tab Caption 1","Tab Caption "");
// include("tabs.php");
// if ($tabOpen==0) {......}


$tabOpen=getParam("tabOpen",$tabsCaption[0]["id"]);
if (!isset($tabUrl))
	$tabUrl=getenv("SCRIPT_NAME");
?>

<script language="JavaScript" type="text/javascript">
    var changed = false;
    
    function fieldChanged() {
    	changed = true;
    }

    function fieldSaved() {
    	changed = false;
    }
    
    function changeTab(link) {
    	if ( changed ) { 
    		if (confirm("Adatok még nincsenek kimentve! Ha oldalt változtatsz elveszíted módosításaid. Folytatni akarod?" )) {
    			 	window.location=link;
    		}    	
    	}
    	else window.location=link;
    }
</script> 


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

