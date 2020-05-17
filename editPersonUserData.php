<?php $diak = $db->getPersonByID($personid);?>
<?php if ( userIsAdmin() || isAktUserTheLoggedInUser()) { ?>
	<table style="width:90%" class="editpagetable">

        <form action="editDiak" method="get">
			<tr><td colspan="3"><p style="text-align:left" ><h3><span class="glyphicon glyphicon-user"></span> Becenév módosítása</h3> A becenév minimum 6 karakter hosszú kell legyen. </p></td></tr>
			<tr><td class="caption1">Becenév</td><td>&nbsp;</td><td><input type="text" class="input2" name="user" value="<?php  echo $diak["user"] ?>" /></td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="btn btn-default" value="Új becenév!" title="Új becenév kimentése" /></td></tr>
			<input type="hidden" value="changeuser" name="action" />
			<input type="hidden" value="<?php echo getAktUserId(); ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>

        <tr><td colspan="3"><hr/> </td></tr>
			<form action="editDiak" method="get">
			<tr><td colspan="3"><p style="text-align:left"><h3><span class="glyphicon glyphicon-sunglasses"></span> Jelszó módosítása</h3> A jelszó minimum 6 karakter hosszú kell legyen. </p></td></tr>
			<tr><td class="caption1">Jelszó</td><td>&nbsp;</td>
			<td>
				<input type="password" xclass="form-control" name="newpwd1" value="" />
				&nbsp;jelszó ismétlése:&nbsp;
				<input type="password" xclass="form-control" name="newpwd2" value="" />
			</td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" class="btn btn-default" value="Új jelszó!" title="Új jelszó kimentése" /></td></tr>
			<input type="hidden" value="changepassw" name="action" />
			<input type="hidden" value="<?php echo getAktUserId(); ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>

        <tr><td colspan="3"><p style="text-align:left" ><h3><span class="glyphicon glyphicon-wrench"></span> Direkt link az adataimhoz</h3> Ezzel a linkkel becenév és jelszó nélkül lehet bejelentkezni.</p></td></tr>
        <tr><td class="caption1">Link</td><td>&nbsp;</td><td><a href="editDiak?key=<?php echo generateUserLoginKey(getAktUserId())?> "> <?php echo $diak["lastname"]." ".$diak["firstname"]?></a></td></tr>

        <tr><td colspan="3"><p style="text-align:left" ><h3><span class="glyphicon glyphicon-time"></span> Utolsó bejelentkezés</p></td></tr>
        <tr><td class="caption1">Dátum</td><td>&nbsp;</td><td><?php echo $diak["userLastLogin"]?></a></td></tr>

        <?php if (isset($diak["facebookid"]) && $diak["facebookid"]!='0' && (userIsAdmin() || (isset($_SESSION['FacebookId']) && $diak["facebookid"]==$_SESSION['FacebookId']))) : ?>
		<tr><td colspan="3"><hr/> </td></tr>
		<tr><td colspan="3">
			<h3>Facebook</h3>Jelenleg Facebook kapcsolat létezik közötted és "<?php echo isset($_SESSION["FacebookName"])?$_SESSION["FacebookName"]:"nem bejelentkezett" ?>" Facebook felhasználóval.<br />
			<div style="border-style: solid; border-width: 1px; width: 250px;" >
				Facebook kép: <img src="https://graph.facebook.com/<?php echo $diak['facebookid']; ?>/picture" />
			</div> 
			<br />
			<form action="editDiak" method="get">
				<input type="hidden" value="removefacebookconnection" name="action" />
				<input type="hidden" value="<?php echo getAktUserId() ?>" name="uid" />
				<input type="hidden" value="<?php echo $tabOpen ?>" name="tabOpen" />
				<input type="submit" class="btn btn-default" value="Facebook kapcsolatot töröl" />
			</form>
		</td></tr>
		<?php endif ?>
	</table>
<?php } else {
	\maierlabs\lpfw\Appl::setMessage('Oldal hozzáférésí jog hiánzik!',"warning");
}?>
