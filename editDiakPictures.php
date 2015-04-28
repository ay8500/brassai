
<table class="editpagetable">
	<tr><td colspan="3" style="text-align:center"><?PHP echo( $resultDBoperation ) ?></td></tr>
	<tr><td colspan="3"><p style="text-align:left" ><b>Képes album</b> </p></td></tr>
	<tr><td colspan="3">
	<?PHP
		$pictures = getListofPictures($_SESSION['scoolClass'].$_SESSION['scoolYear'],$uid, false) ;
		foreach ($pictures as $pict) {
			echo('<a title="'.$pict["title"].'" href="images/'.$_SESSION['scoolClass'].$_SESSION['scoolYear'].'/'.$pict["File"].'" rel="lightbox[roadtrip]"><img class="instant" src="convertImg.php?color=eeeeee&thumb=true&file=images/'.$_SESSION['scoolClass'].$_SESSION['scoolYear'].'/'.$pict["File"].'" /></a> &nbsp;<input type="radio" />');
		}
	?>
	</td></tr>
	<tr><td colspan="3"><hr/> </td></tr>
	
	<?PHP if (false) { ?>
	<tr><td colspan="3">
		<form enctype="multipart/form-data" action="<?PHP echo($SCRIPT_NAME);?>" method="post">
			<?PHP if ( isset($_SESSION['UID']) && $_SESSION['UID']>0)  { ?>
				<table>
				<tr><td colspan="3">Megjelölt kép</td></td>
				<tr><td>A kép címe:</td><td><input type="text" name="title" size="40"/></td></tr>
				<tr>
					<td>A kép tartalma:</td><td><input type="text" name="content" size="40"/></td>
					<td><input type="button" value="kiment"  style="width:70px" class="submit2" /></td></tr>
				<tr><td colspan="3"><hr>Kép feltöltése</td></td>
				<tr>
					<td>Válassz egy képet</td><td><input class="submit2" name="userfile" type="file" size="44"/></td>	
					<td><input class="submit2"  style="width:70px" type="submit" value="feltölt"/></td>
				</tr>
				<tr><td colspan="3"><hr>Megjelölt kép törlése</td></td>
				<tr>
					<td>&nbsp;</td><td>&nbsp;</td>	
					<td><input class="submit2" style="width:70px" type="button" value="törli"/></td>
				</tr>
				
					

			<?PHP } else { ?>
			<?PHP } ?>
		<input type="hidden" value="upload" name="action" />
		<input type="hidden" value="<?PHP echo($uid) ?>" name="uid" />
		<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
		</form>
	</td></tr>
	<?PHP } ?>
	
</table>
