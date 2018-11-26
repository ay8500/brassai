<?php
//*********************** TABS ***********************************************************
//** Usage
// $tabsCaption=Array("Tab Caption 1","Tab Caption "");
// include("tabs.php");
// if ($tabOpen==0) {......}

$tabOpen=getParam("tabOpen","person");
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
		if ($tab["id"]==$tabOpen)
	        echo '<li class="active"><a href="#">'.$tab["caption"].'</a></li>';
		else
         	echo '<li><a href="javascript:changeTab('."'".$tabUrl.'?tabOpen='.$tab["id"]."'".');" >'.$tab["caption"].'</a></li>';
	}
?>       
</ul>

