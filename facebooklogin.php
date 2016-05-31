<div style="text-align: center;">
	<div style="width:500px; background-color:#eeeeee">
		<table style="padding: 10px;">
		<tr>
			<td>Facebook felhasználó név:</td><td><?php echo $_SESSION["FacebookName"]?></td>
		</tr><tr>
			<td>Facebook kép:</td><td><img src="https://graph.facebook.com/<?php echo $_SESSION['FacebookId']; ?>/picture" /></td>
		</tr>
		</table>
	</div> 
<?php if ( $scoolClass=="") {?>
	<div style="background-color: #eeeeee; width:500px" >
		<div class="sub_title"><?php echo getTextRes("FacebookLoginOk"); ?> </div>
		<form action="start.php"  >
			<div style="margin: 20px;"> 
				<div><?php echo getTextRes("SelectClass"); ?></div>
				<select name="scoolClassFb" size="1">
					<option value=""><?php echo getTextRes("SelectOneOption"); ?></option>
					<?php 
						$classes=getDatabaseList();
						foreach ($classes as $class) {
							echo('<option>'.$class.'</option>');
						}
					?>
				</select>
			</div>
			<div>
				<input type="submit" value="<?php echo getTextRes("NextPage"); ?>">
			</div>
		</form>
	</div>
<?php }?>
<br/>
<?php if  ($scoolClass!="") {
	openDatabase(substr($scoolClass,5,3).substr($scoolClass,0,4));
	?>
	<div style="background-color: #eeeeee; width:500px;" >
		<br />
		<div><?php echo getTextRes("SelectUser"); ?></div>
		<br />
		<form action="start.php" >
			<input type="hidden" name="scoolClassFb" value="<?php echo $scoolClass?>">
			<input type="hidden" name="action" value="facebooklogin">
			<select name="userId" size="1">
					<option value="-2"><?php echo getTextRes("SelectOneOption"); ?></option>
					<?php foreach ($data as $l => $d) { 
						if ($d["facebookid"]=="") { ?>
							<option value="<?php echo $d["id"] ?>"><?php echo $d["lastname"].' '.$d["firstname"] ?> </option>
					<?php } } ?>	
			</select>
			<br />
			<br />
			<div>
				<input type="submit" value="<?php echo getTextRes("ConnectToFacebook"); ?>">
			</div>
		</form>
		<br />
	</div>
<?php }?>
</div>
