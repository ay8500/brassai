<?php if ( userIsAdmin() || isAktUserTheLoggedInUser()) { ?>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	<table style="width:90%" class="editpagetable">
		<form action="editDiak.php" method="get">
			<tr><td colspan="3"><p style="text-align:left" ><h3>Becenév módosítása</h3> A becenév minimum 6 karakter hosszú kell legyen. </p></td></tr>
			<tr><td class="caption1">Becenév</td><td>&nbsp;</td><td><input type="text" class="input2" name="user" value="<?php  echo $diak["user"] ?>" /></td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="btn btn-default" value="Új becenév!" title="Új becenév kimentése" /></td></tr>
			<input type="hidden" value="changeuser" name="action" />
			<input type="hidden" value="<?php echo $uid; ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>
		<tr><td colspan="3"><hr/> </td></tr>
			<form action="editDiak.php" method="get">
			<tr><td colspan="3"><p style="text-align:left"><h3>Jelszó módosítása</h3> A jelszó minimum 6 karakter hosszú kell legyen. </p></td></tr>
			<tr><td class="caption1">Jelszó</td><td>&nbsp;</td>
			<td>
				<input type="password" xclass="form-control" name="newpwd1" value="" />
				&nbsp;jelszó ismétlése:&nbsp;
				<input type="password" xclass="form-control" name="newpwd2" value="" />
			</td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="btn btn-default" value="Új jelszó!" title="Új jelszó kimentése" /></td></tr>
			<input type="hidden" value="changepassw" name="action" />
			<input type="hidden" value="<?php echo $uid; ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>
		<?php if (isset($_SESSION['FacebookId']) && $diak["facebookid"]==$_SESSION['FacebookId']) : ?>		
		<tr><td colspan="3"><hr/> </td></tr>
		<tr><td colspan="3">
			<h3>Facebook</h3>Jelenleg Facebook kapcsolat létezik közötted és a "<?php echo $_SESSION["FacebookName"] ?>" Facebook felhasználóval.<br />
			<div style="border-style: solid; border-width: 1px; width: 250px;" >
				Facebook kép: <img src="https://graph.facebook.com/<?php echo $_SESSION['FacebookId']; ?>/picture" />
			</div> 
			<br />
			<form action="editDiak.php" method="get">
				<input type="hidden" value="removefacebookconnection" name="action" />
				<input type="hidden" value="<?php echo $uid ?>" name="uid" />
				<input type="hidden" value="<?php echo $tabOpen ?>" name="tabOpen" />
				<input type="submit" class="btn btn-default" value="Facebook kapcsolatot töröl" />
			</form>
		</td></tr>
		<?php endif ?>
	</table>
<?php } else {	?>
	<div class="resultDBoperation" ><div class="alert alert-warning">Hozzáférésí jog hiánzik!</div></div>
<?php }?>	
