

<table class="editpagetable">
	<tr><td colspan="3" style="text-align:center"><?PHP echo( $resultDBoperation ) ?></td></tr>
	<tr><td colspan="3"><p style="text-align:left" ><b>K�pes album</b> </p></td></tr>
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
				<tr><td colspan="3">Megjel�lt k�p</td></td>
				<tr><td>A k�p c�me:</td><td><input type="text" name="title" size="40"/></td></tr>
				<tr>
					<td>A k�p tartalma:</td><td><input type="text" name="content" size="40"/></td>
					<td><input type="button" value="kiment"  style="width:70px" class="submit2" /></td></tr>
				<tr><td colspan="3"><hr>K�p felt�lt�se</td></td>
				<tr>
					<td>V�lassz egy k�pet</td><td><input class="submit2" name="userfile" type="file" size="44"/></td>	
					<td><input class="submit2"  style="width:70px" type="submit" value="felt�lt"/></td>
				</tr>
				<tr><td colspan="3"><hr>Megjel�lt k�p t�rl�se</td></td>
				<tr>
					<td>&nbsp;</td><td>&nbsp;</td>	
					<td><input class="submit2" style="width:70px" type="button" value="t�rli"/></td>
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
