<style>
	#activities {
		background-color: white;margin:10px;padding:10px;
	}
	#activities>tbody>tr>td {
		padding: 10px; text-align: right;
	}
</style>
<?php $activities=$db->getPersonActivities($personid); ?>
<?php if ( userIsAdmin() || isAktUserTheLoggedInUser() || userIsSuperuser()) { ?>
	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	<div>
		<h4>Aktívitási pontszámok</h4>
		<table id="activities">
		<tr style="background-color: lightgray"><td>Aktió					</td><td> Végrehajtások		</td><td> Szorzó </td><td> Összesen</td></tr>
		<tr><td>Személy módosítások		</td><td> <?php echo $activities["personChange"]?>		</td><td> 1 </td><td><?php echo $activities["personChange"]?> </td></tr>
		<tr><td>Új személyek			</td><td> <?php echo $activities["newPerson"]?>			</td><td> 3 </td><td><?php echo $activities["newPerson"]*3?> </td></tr>
		<tr><td>Új képek				</td><td> <?php echo $activities["newPicture"]?>		</td><td> 5 </td><td><?php echo $activities["newPicture"]*5?> </td></tr>
		<tr><td>Meggyújtott gyertyák	</td><td> <?php echo $activities["lightedCandles"]?>	</td><td> 2 </td><td><?php echo $activities["lightedCandles"]*2?> </td></tr>
		<tr><td>Utolsó bejelentkezés 	</td><td> <?php echo $activities["lastLoginPoints"]?>	</td><td> 1 </td><td><?php echo $activities["lastLoginPoints"]?></td></tr>

		<tr><td>Zene szavazatok			</td><td> <?php echo $activities["songVotes"]?>			</td><td> 7 </td><td><?php echo $activities["songVotes"]*7?></td></tr>
		<tr><td>Zene darabok			</td><td> <?php echo $activities["songs"]?>				</td><td> 7 </td><td><?php echo $activities["songs"]*7?></td></tr>
		<tr><td>Zene előadók			</td><td> <?php echo $activities["interprets"]?>		</td><td> 7 </td><td><?php echo $activities["interprets"]*7?></td></tr>

		<tr style="background-color: lightgray;font-weight: bold;"><td>Összesen				</td><td></td><td></td><td>
			 <?php echo $activities["lastLoginPoints"]+$activities["lightedCandles"]*2+$activities["newPicture"]*5+$activities["newPerson"]*3+$activities["personChange"]+$activities["songVotes"]*7+$activities["songs"]*7+$activities["interprets"]*7?>
		</td></tr>
		</table>
	</div>
<?php } else {	?>
	<div class="resultDBoperation" ><div class="alert alert-warning">Hozzáférésí jog hiánzik!</div></div>
<?php }?>	
