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

<table class="tab" align="center">
<tr>
<?PHP
foreach($tabsCaption as $key => $Caption ) {
	if ($key==$tabOpen)
		echo('<td class="tabActive">'.$Caption."</td>"."\r\n");
	else
		echo('<td class="tabCaption"><a class="tabCaptionText" href="javascript:changeTab('."'".$SCRIPT_NAME.'?tabOpen='.$key."'".');" >'.$Caption."</a></td>"."\r\n");
}
echo('<td class="tabEmpty"> &nbsp; </td>'."\r\n");
echo('</tr>'."\r\n".'<tr><td  class="tabBody" colspan="'.(sizeof($tabsCaption)+1).'">'."\r\n");
?>
