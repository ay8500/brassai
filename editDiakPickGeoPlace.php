<?PHP
	if ( isset($_SESSION['UID']) && $_SESSION['UID']>0) {
?>
	<div id="map_canvas" style="width: 520px; height: 400px;"></div>
	<table class="editpagetable">
		<tr><td style="text-align:center"><?PHP echo($resultDBoperation); ?></td></tr>
		<tr><td>
			<p><b>Jel�ld meg a t�rk�pen a lakhelyedet.</b> Ezt a pozici�t csak az oszt�lyt�rsak l�tj�k pontosan.</p>
		</td></tr>
		<tr>
			<td>
				<hr/>
				Helys�gn�v, utca, h�zsz�m keres�s:	<br />
				<input type="text" id="addres" value="Kolozsv�r, Brassai" onKeyPress="if (window.event.keyCode == 13) {doSearch();}	" />
				<input class="submit2" type="button" value="Keres" onclick="doSearch();return true;"/><br />
				<hr/>
				<form action="<?PHP echo($SCRIPT_NAME); ?>" method="get" name="geo">
					<input type="hidden" value="changegeo" name="action" />
					<input type="hidden" value="<?PHP echo($uid)?>" name="uid" />
					<input type="hidden" value="<?PHP echo($tabOpen); ?>" name="tabOpen" />
					Koordin�t�k:<br />
					<input type="text" size="24" name="geolat" class="input2" />&nbsp;Sz�less�g<br/>
					<input type="text" size="24" name="geolng" class="input2" />&nbsp;Hossz�s�g&nbsp;&nbsp;&nbsp;<input type="submit" class="submit2" value="Geokoordin�t�kat m�d�s�t" title="" />
				</form>
			</td>
		</tr>
	</table>	
<?PHP } ?> 
