<?php echo $_SESSION["FacebookName"]?>
<?php if ($scoolYear=="" || $scoolYear=="") {?>
	<div style="text-align: center;">
	<div style="width:500px">
		<div class="sub_title"><?php echo getTextRes("FacebookLoginOk"); ?> </div>
		<form action="start.php" >
			<div>
				<?php echo getTextRes("SelectYear"); ?>
				<select name="scoolYearFb" size="1">
					<option value=""><?php echo getTextRes("SelectOneOption"); ?></option>
					<option>1985</option>
				</select>
			</div>
			<div> <?php echo getTextRes("SelectClass"); ?>
				<select name="scoolClassFb" size="1">
					<option value=""><?php echo getTextRes("SelectOneOption"); ?></option>
					<option>12A</option>
					<option>12B</option>
				</select>
			</div>
			<div>
				<input type="submit" value="<?php echo getTextRes("NextPage"); ?>">
			</div>
		</form>
	</div>
	</div>
<?php }?>
<?php if ($scoolYear!="" && $scoolClass!="") {?>
	<div> <?php echo getTextRes("SelectClass"); ?>
		<form action="start.php" >
			<input type="hidden" name="scoolYearFb" value="<?php echo $scoolYear?>">
			<input type="hidden" name="scoolClassFb" value="<?php echo $scoolClass?>">
			<select name="userId" size="1">
					<option value="-2"><?php echo getTextRes("SelectOneOption"); ?></option>
					<?php foreach ($data as $l => $d) { ?>
						<option value="<?php echo $l ?>"><?php echo $d["lastname"].' '.$d["firstname"] ?> </option>
					<?php } ?>	
					<option value="-3"><?php echo getTextRes("NotFoundCreateNewUser"); ?></option>
			</select>
			<div>
				<input type="submit" value="<?php echo getTextRes("ConnectToFacebook"); ?>">
			</div>
		</form>
	</div>
<?php }?>
