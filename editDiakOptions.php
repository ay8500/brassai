<?PHP
	if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])) {
?>		
<table class="editpagetable">
	
	<tr><td colspan="3" style="text-align:center"> <?PHP echo($resultDBoperation); ?> </td></tr>
	<tr><td colspan="3"><p style="text-align:left" ><b>Beállítások módosítása</b> </p></td></tr>
	<tr><td colspan="3"><hr/> </td></tr>
	<tr><td colspan="3">Jelenleg nem állnak beállítások rendelkezésre.</td></tr>
	<input type="hidden" value="changeoptions" name="action" />

</table>
<?PHP
}
else {
  getTextRes("OnlyForLoggedInUser");
}
?>