<?PHP
	if ( isset($_SESSION['UID']) && $_SESSION['UID']>0) {
?>		
<table class="editpagetable">
	
	<tr><td colspan="3" style="text-align:center"> <?PHP echo($resultDBoperation); ?> </td></tr>
	<tr><td colspan="3"><p style="text-align:left" ><b>Be�ll�t�sok m�dos�t�sa</b> </p></td></tr>
	<tr><td colspan="3"><hr/> </td></tr>
	<tr><td colspan="3">Jelenleg nem �llnak be�ll�t�sok rendelkez�sre.</td></tr>
	<input type="hidden" value="changeoptions" name="action" />

</table>
<?PHP
}
?>