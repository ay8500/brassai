<?PHP
if (isset($_GET["tabOpen"])) $tabOpen=$_GET["tabOpen"]; 
else if (isset($_POST["tabOpen"])) $tabOpen=$_POST["tabOpen"]; 
else $tabOpen=0;
if ( $tabOpen> sizeof($tabsCaption)) $tabOpen=0;
?>

<script language="JavaScript" type="text/javascript">
    var changed = false;
    
    function fieldChanged() {
    	changed = true;
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


<ul class="nav nav-tabs nav-justified" role="tablist">
<?php 
	foreach($tabsCaption as $key => $Caption ) {
		if ($key==$tabOpen)
	        echo '<li class="active"><a href="#">'.$Caption.'</a></li>';
		else
         	echo '<li><a href="javascript:changeTab('."'".$SCRIPT_NAME.'?tabOpen='.$key."'".');" >'.$Caption.'</a></li>';
	}
?>       
</ul>

