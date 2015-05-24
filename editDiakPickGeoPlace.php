<?PHP
	if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) {
?>
	<div id="map_canvas" style="width: 100%x; height: 400px;"></div>
	<table class="editpagetable">
		<tr><td style="text-align:center"><?PHP echo($resultDBoperation); ?></td></tr>
		<tr><td>
			<p><b>Jelöld meg a térképen a lakhelyedet.</b> Ezt a poziciót csak az osztálytársak látják pontosan.</p>
		</td></tr>
		<tr>
			<td>
				<hr/>
				Helységnév, utca, házszám keresés:	<br />
				<input type="text" id="addres" value="Kolozsvár, Brassai" onKeyPress="if (window.event.keyCode == 13) {doSearch();}	" />
				<input class="submit2" type="button" value="Keres" onclick="doSearch();return true;"/><br />
				<hr/>
				<form action="<?PHP echo($SCRIPT_NAME); ?>" method="get" name="geo">
					<input type="hidden" value="changegeo" name="action" />
					<input type="hidden" value="<?PHP echo($uid)?>" name="uid" />
					<input type="hidden" value="<?PHP echo($tabOpen); ?>" name="tabOpen" />
					Koordináták:<br />
					<input type="text" size="24" name="geolat" class="input2" />&nbsp;Szélesség<br/>
					<input type="text" size="24" name="geolng" class="input2" />&nbsp;Hosszúság&nbsp;&nbsp;&nbsp;<input type="submit" class="submit2" value="Geokoordinátákat módósít" title="" />
				</form>
			</td>
		</tr>
	</table>	
<?PHP } ?> 
