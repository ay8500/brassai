
<table class="editpagetable">
	<tr><td colspan="3" style="text-align:center"><?PHP echo( $resultDBoperation ) ?></td></tr>
	<tr><td colspan="3"><p style="text-align:left" ><b>Képes album: ß-Versió még nincs teljesen kész!</b> </p></td></tr>
	<tr><td colspan="3">
	<form action="<?PHP echo($SCRIPT_NAME);?>">
	<?php
		$pictures = getListofPictures($_SESSION['scoolClass'].$_SESSION['scoolYear'],$uid, false) ;
		$i=1;
		foreach ($pictures as $pict) {
			$fileName='images/'.$_SESSION['scoolClass'].$_SESSION['scoolYear'].'/'.$pict["File"];
	?>
			<div style="padding: 10px; display: inline-block;border-radius: 5px;border-style: outset; vertical-align: top;">
			<a title="<?php echo $pict["title"] ?>" href="<?php echo $fileName ?>" >
				<img style="width: 200px; height: 200px;" src="convertImg.php?color=eeeeee&thumb=true&file=<?php  echo $fileName ?>" />
			</a>
			<input type="checkbox" name="pcb<?php echo $pict["id"]?>" />
			<div style="width: 220px;height: 35px; ">
			<b><?php echo $pict["title"] ?></b><br/>
			<?php echo $pict["comment"] ?>
			</div>
			</div>
	<?php 
			if ($i++ % 3 ==0) echo("<br />");
		}
	?>
	</form>
	</td></tr>
	<tr><td colspan="3"><hr/> </td></tr>
	
	<?PHP if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])) { ?>
	<tr><td colspan="3">
			<?PHP if ( isset($_SESSION['UID']) && $_SESSION['UID']>0)  { ?>
			<table>
			<form action="<?PHP echo($SCRIPT_NAME);?>">
				<tr><td colspan="3">Megjelölt kép</td></td>
				<tr><td>A kép címe:</td><td><input type="text" name="title" size="40"/></td></tr>
				<tr>
					<td>A kép tartalma:</td><td><input type="text" name="content" size="40"/></td>
					<td><input type="button" value="kiment"  style="width:70px" class="submit2" /></td></tr>
					<input type="hidden" value="change" name="action" />
					<input type="hidden" value="<?PHP echo($uid) ?>" name="uid" />
					<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
					</form>
			<tr><td colspan="3"><hr>Kép feltöltése</td></td>
			<tr>
				<form enctype="multipart/form-data" action="<?PHP echo($SCRIPT_NAME);?>" method="post">
					<td>Válassz egy képet</td><td><input class="submit2" name="userfile" type="file" size="44"/></td>	
					<td><input class="submit2"  style="width:70px" type="submit" value="feltölt"/></td>
					<input type="hidden" value="upload" name="action" />
					<input type="hidden" value="<?PHP echo($uid) ?>" name="uid" />
					<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
				</form>
			</tr>
				
					

			<?PHP } else { ?>
			<?PHP } ?>
	</td></tr>
	<?PHP } ?>
	
</table>
