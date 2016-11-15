<?php // This code is loaded from start.php wenn a facebook user try to log on?>

<div class="sub_title">Bejelentkezni szeretnék!</div>
<div class="container-fluid">
<div class="well">
	<?php // Facebook username and picture?>
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
		
	
	<?php // Show the available classes from the db?>
	<?php if ( $schoolClass=="") {?>
		<div style="background-color: #eeeeee; width:500px" >
			<div class="sub_title"><?php echo getTextRes("FacebookLoginOk"); ?> </div>
			<form action="start.php"  >
				<div style="margin: 20px;"> 
					<div><?php echo getTextRes("SelectClass"); ?></div>
					<select name="scoolClassFb" size="1">
						<option value=""><?php echo getTextRes("SelectOneOption"); ?></option>
						<?php 
							$classes=$db->getClassList();
							foreach ($classes as $class) {
								echo('<option value="'.$class["id"].'">'.$class["text"].'</option>');
							}
						?>
					</select>
				</div>
				<div>
					<input type="submit" value="<?php echo getTextRes("NextPage"); ?>" class="btn btn-default" />
				</div>
			</form>
			<br />
			<button onclick="alert('Ez a funktió még áll rendelkezésre, kérlek küldj egy mailt a brassai@blue-l.de címre. Amilyen gyorsan csak lehet bővítve lessz az oldal a kivánt osztállyal.')" class="btn btn-default">Nem találom végzős osztályom,<br /> szeretném bővítenni ezt az oldalt az én osztályommal.</button>
		</div>
	<?php }?>
	<br/>
	
	<?php // choose a person to be linkt with ?>
	<?php if  ($schoolClass!="") {
		?>
		<div style="background-color: #eeeeee; width:500px;" >
			<br />
			<div><?php echo getTextRes("SelectUser"); ?></div>
			<br />
			<form action="start.php" >
				<input type="hidden" name="scoolClassFb" value="<?php echo $schoolClass?>">
				<input type="hidden" name="action" value="facebooklogin">
				<select name="userId" size="1">
						<option value="-2"><?php echo getTextRes("SelectOneOption"); ?></option>
						<?php 
							$classId = $db->getClassById(intval($schoolClass));
							$data=$db->getPersonListByClassId($classId["id"]);
							foreach ($data as $d) { 
								if (!isset($d["facebookid"]) || $d["facebookid"]=="" || $d["facebookid"]==0) { ?>
									<option value="<?php echo getPersonId($d) ?>"><?php echo $d["lastname"].' '.$d["firstname"] ?> </option>
						<?php  } } ?>	
				</select>
				<br />
				<br />
				<div>
					<input type="submit" value="<?php echo getTextRes("ConnectToFacebook"); ?>" class="btn btn-default" />
				</div>
			</form>
			<br />
			<form action="start.php"  >
				<input type="hidden" name="scoolClassFb" />
				<input type="submit" value="Tévedtem, nem ebben az osztályban végezem" class="btn btn-default" />
			</form>
		</div>
	<?php }?>
	</div>
</div>
</div>
