<?php $activities=$db->getPersonActivities($personid); ?>
<?php if ( userIsAdmin() || isAktUserTheLoggedInUser() || userIsSuperuser()) { ?>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	<div>
		Személy módosítások: <?php echo $activities["personChange"].' x 1 ='.$activities["personChange"]?> <br/>
		Új személyek: <?php echo $activities["newPerson"].' x 3 ='.$activities["newPerson"]*3?> <br/>
		Új képek: <?php echo $activities["newPicture"].' x 5 ='.$activities["newPicture"]*5?> <br/>
		Meggyújtott gyertyák: <?php echo $activities["lightedCandles"].' x 2 ='.$activities["lightedCandles"]*2?> <br/>
		Utolsó bejelentkezés pontok:<?php echo $activities["lastLoginPoints"]?><br/>

		Zene szavazatok:<?php echo $activities["songVotes"].' x 7 ='.$activities["songVotes"]*7?><br/>
		Zene darabok:<?php echo $activities["songs"].' x 7 ='.$activities["songs"]*7?><br/>
		Zene előadók:<?php echo $activities["interprets"].' x 7 ='.$activities["interprets"]*7?><br/>

		Összesen:<?php echo $activities["lastLoginPoints"]+$activities["lightedCandles"]*2+$activities["newPicture"]*5+$activities["newPerson"]*3+$activities["personChange"]+$activities["songVotes"]*7+$activities["songs"]*7+$activities["interprets"]*7?><br/>

	</div>
<?php } else {	?>
	<div class="resultDBoperation" ><div class="alert alert-warning">Hozzáférésí jog hiánzik!</div></div>
<?php }?>	
