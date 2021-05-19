<?php
include_once 'dbDaStatistic.class.php';

$dbStatistic = new dbDaStatistic($db);

$activities=$dbStatistic->getPersonActivities($personid);


\maierlabs\lpfw\Appl::addCssStyle('
	#activities {
		background-color: white;margin:10px;padding:10px;
	}
	#activities>tbody>tr>td {
		padding: 10px; text-align: left;
	}
');
?>
<?php if ( isUserAdmin() || isAktUserTheLoggedInUser() || isUserSuperuser()) { ?>
	<div>
		<h4>Aktívitási pontszámok</h4>
        <a href="start?&tabOpen=user&userid=<?php echo getAktUserId()?>" class="btn btn-default">
            <span class="glyphicon glyphicon-eye-open"></span>  Mutasd az aktivításokat
        </a>
		<table id="activities">
		<tr style="background-color: lightgray"><td>Akció					</td><td> Végrehajtások		</td><td> Szorzó </td><td> Összesen</td></tr>
            <tr><td><span class="glyphicon glyphicon-king"></span> Személy módosítások		</td><td> <?php echo $activities["personChange"]?>		</td><td> 1 </td><td><?php echo $activities["personChange"]?> </td></tr>
            <tr><td><span class="glyphicon glyphicon-queen"></span> Új személyek			</td><td> <?php echo $activities["newPerson"]?>			</td><td> 3 </td><td><?php echo $activities["newPerson"]*3?> </td></tr>
            <tr><td><span class="glyphicon glyphicon-picture"></span> Új képek				</td><td> <?php echo $activities["newPicture"]?>		</td><td> 5 </td><td><?php echo $activities["newPicture"]*5?> </td></tr>
            <tr><td><span class="glyphicon glyphicon-blackboard"></span> Meggyújtott gyertyák	</td><td> <?php echo $activities["lightedCandles"]?>	</td><td> 2 </td><td><?php echo $activities["lightedCandles"]*2?> </td></tr>
            <tr><td><span class="glyphicon glyphicon-log-in"></span> Utolsó bejelentkezés 	</td><td> <?php echo $activities["lastLoginPoints"]?>	</td><td> 1 </td><td><?php echo $activities["lastLoginPoints"]?></td></tr>
            <tr><td colspan="4"><hr/></td></tr>
            <tr><td><span class="glyphicon glyphicon-sd-video"></span> Zene klippek			</td><td> <?php echo $activities["songs"]?>				</td><td> 7 </td><td><?php echo $activities["songs"]*7?></td></tr>
            <tr><td><span class="glyphicon glyphicon-music"></span> Zene előadók			</td><td> <?php echo $activities["interprets"]?>		</td><td> 7 </td><td><?php echo $activities["interprets"]*7?></td></tr>
            <tr><td colspan="4"><hr/></td></tr>
            <tr><td><span class="glyphicon glyphicon-stats"></span> Vélemények			    </td><td> <?php echo $activities["opinions"]?>		    </td><td> 1</td><td><?php echo $activities["opinions"]*1?></td></tr>

            <tr style="background-color: lightgray;font-weight: bold;"><td>Összesen				</td><td></td><td></td><td>
	    		 <?php echo $activities["lastLoginPoints"]+$activities["lightedCandles"]*2+$activities["newPicture"]*5+$activities["newPerson"]*3+$activities["personChange"]+$activities["songs"]*7+$activities["interprets"]*7+$activities["opinions"]*1?>
    		</td></tr>
		</table>
	</div>
<?php } else {	?>
	<div class="resultDBoperation" ><div class="alert alert-warning">Hozzáférésí jog hiánzik!</div></div>
<?php }?>


