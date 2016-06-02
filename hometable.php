<?PHP 
include_once 'sessionManager.php';
include("homemenu.php");
include_once("data.php");
include_once 'ltools.php';

openDatabase(getAktDatabaseName());

$resultDBoperation="";
if (getParam("action","")=="delete_diak" &&  userIsLoggedOn() && (userIsEditor() || userIsAdmin()) ) {
	deleteDiak(getGetParam("uid",""),getGetParam("db",""));
	$resultDBoperation='<div class="alert alert-success">Véndiák sikeresen törölve!</div>';
}

// Title of the page schoolmate or guests
$guests = getParam("guests", "")=="true";
if ($guests )
	echo('<h2 class="sub_title">Tanárok, régi volt osztálytársak, vendégek, jó barátok.</h2>');
else
	echo('<h2 class="sub_title">Osztálytársak</h2>');

	
// Toolbar for new schoolmate or guests
?>
<div class="container-fluid">
	<div style="margin-bottom: 15px;width: 100%;background-color: #E3E3E3;padding: 10px;">
		<form action="editDiak.php">
		<?php if (userIsAdmin() || userIsEditor() ) {?>
			<?php if ($guests) {?>
				<input type="hidden" name="action" value="newguest" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új tanárral,vendéggel, jó baráttal"/>
			<?php } else {?>
				<input type="hidden" name="action" value="newdiak" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új véndiákkal "/>
			<?php }?>
		<?php } else if (!userIsLoggedOn()) { ?>
			<?php if ($guests) {?>
				<input type="hidden" name="action" value="submit_newguest" />
				<input class="btn btn-default" type="submit" value="Bővítsd a névsort" title="Szeretnék én is ezen a listán mit tanár, barát vagy ismerős szerepelni"/>
			<?php } else {?>
				<input type="hidden" name="action" value="submit_newdiak" />
				<input class="btn btn-default" type="submit" value="Bővítsd a névsort" title="Én is ebben az osztályban végeztem, szeretnék én is ezen a listán lenni."/>
			<?php }?>
		<?php }?>
		</form>
		<?php if(!$guests) {?>
			Véndiákok száma:
		<?php  } else { ?>
			Vendégek száma:
		<?php } ?> 
		<?php echo(getCountOfActivePersons($guests));?>
	</div>


	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>

<style>
      .fields div span { font-weight: bold;width: 90px;text-align: right;display: inline-block; }
      .fields {vertical-align: text-top;}
      .element{display: inline-block; background-color: #E5E9EA;  padding: 10px; border-radius: 7px; margin-bottom: 10px; vertical-align: top;}
</style>

<?php
foreach ($data as $l => $d)	
{ 
	if ( $guests == isPersonGuest($d) && isPersonActive($d)) {

		if (userIsLoggedOn() || localhost()) {
			$personLink="editDiak.php?uid=".$d["id"];
		} else {
			$personLink=getPersonLink($d["lastname"],$d["firstname"])."-".getAktDatabaseName()."-".$d["id"];
			
		}
		?>
		
		<div class="element">
		
		<div style="display: inline-block; width:160px;">
			<a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>">
				<img src="images/<?php echo $d["picture"]?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="diak_image_medium" />
			</a>
		</div>
		<div style="display: inline-block;max-width:300px;min-width:300px; vertical-align: top;margin-bottom:10px;">
			<h4>
				<?php echo $d["lastname"].' '.$d["firstname"];
					if(showField($d,"birthname")) echo("&nbsp;(".$d["birthname"].")");
				?>
			</h4>
			<div class="fields"> 
				<?php 
				if(showField($d,"partner")) 	echo "<div><span>Élettárs:</span>".$d["partner"]."</div>";
				if(showField($d,"education")) 	echo "<div><span>Végzettség:</span>".$d["education"]."</div>";
				if(showField($d,"employer")) 	echo "<div><span>Munkahely:</span>".getFieldValue($d["employer"])."</div>";
				if(showField($d,"function")) 	echo "<div><span>Beosztás:</span>".getFieldValue($d["function"])."</div>";
				if(showField($d,"country")) 	echo "<div><span>Ország:</span>".getFieldValue($d["country"])."</div>";
				if(showField($d,"place")) 	echo "<div><span>Város:</span>".$d["place"]."</div>";
				if(showField($d,"email")) 		echo "<div><span>E-Mail:</span><a href=mailto:".getFieldValue($d["email"]).">".getFieldValue($d["email"])."</a></div>";
				?>
	  		</div>
		</div>
		
		</div>
		<?php 
	}
}
?>
</div>
<?php  include ("homefooter.php");?>
