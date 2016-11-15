<?php
include_once 'tools/sessionManager.php';
include("homemenu.php");
include_once("data.php");
include_once 'tools/ltools.php';


$resultDBoperation="";
if (getParam("action","")=="delete_diak" &&  userIsLoggedOn() && ((userIsEditor() && getAktClass()==getLoggedInUserClassId()) || userIsAdmin()) ) {
	if ($db->deletePersonEntry(getIntParam("uid" )))
		$resultDBoperation='<div class="alert alert-success">Véndiák sikeresen törölve!</div>';
	else
		$resultDBoperation='<div class="alert alert-warning">Véndiák törölése sikertelen! Hibakód:9811</div>';
}

// Title of the page schoolmate or guests
$guests = getParam("guests", "")=="true";
if (getAktClass()==0) {
	echo('<h2 class="sub_title">Tanáraink</h2>');
} else {
	if ($guests )
		echo('<h2 class="sub_title">Nem végzős osztálytársak, vendégek, jó barátok.</h2>');
	else
		echo('<h2 class="sub_title">Osztálytársak</h2>');
}
	
// Toolbar for new schoolmate or guests
?>
<div class="container-fluid">
	<div style="margin-bottom: 15px;width: 100%;background-color: #E3E3E3;padding: 10px;">
		<form action="editDiak.php">
			<?php if ($guests) {?>
				<input type="hidden" name="action" value="newguest" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új vendéggel, jó baráttal"/>
				Vendégek száma:
			<?php } else if (getAktClass()!=0) {?>
				<input type="hidden" name="action" value="newperson" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új véndiákkal "/>
				Véndiákok száma:
			<?php } else  {?>
				<input type="hidden" name="action" value="newteacher" />
				<input class="btn btn-default" type="submit" value="Névsor bővítése új tanárral "/>
				Tanárok száma:
			<?php } ?>
		</form>
		<?php echo($db->getCountOfPersons(getAktClass(), $guests));?>
	</div>


	<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>

<style>
      .fields div span { font-weight: bold;width: 90px;text-align: right;display: inline-block; }
      .fields {vertical-align: text-top;}
      .element{display: inline-block; background-color: #E5E9EA;  padding: 10px; border-radius: 7px; margin-bottom: 10px; vertical-align: top;}
      .rip {border-top-style: solid; border-width: 10px; border-color: black;}
</style>


<?php
$personList=$db->getPersonListByClassId(getAktClass());
foreach ($personList as $d)	
{ 
	if ( $guests == isPersonGuest($d) ) {

		if (userIsLoggedOn() || localhost()) {
			$personLink="editDiak.php?uid=".$d["id"];
		} else {
			$personLink=getPersonLink($d["lastname"],$d["firstname"])."-".$d["id"];
		}
		if (strstr($d["role"],"rip")!="")
			$rip="rip";
		else 
			$rip="";
		?>
		
		<div class="element">
		
		<div style="display: inline-block; width:160px;">
			<a href="<?php echo $personLink?>" title="<?php echo ($d["lastname"]." ".$d["firstname"])?>">
				<img src="images/<?php echo $d["picture"]?>" border="0" title="<?php echo $d["lastname"].' '.$d["firstname"]?>" class="diak_image_medium <?php echo $rip?>" />
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
				if (getAktClass()!=0) {
					if(showField($d,"partner")) 	echo "<div><span>Élettárs:</span>".$d["partner"]."</div>";
					if(showField($d,"education")) 	echo "<div><span>Végzettség:</span>".$d["education"]."</div>";
					if(showField($d,"employer")) 	echo "<div><span>Munkahely:</span>".getFieldValue($d["employer"])."</div>";
					if(showField($d,"function")) 	echo "<div><span>Beosztás:</span>".getFieldValue($d["function"])."</div>";
				} else {
					if (isset($d["function"]))		echo "<div><span>Tantárgy:</span>".getFieldValue($d["function"])."</div>";
					if (isset($d["children"])) {	echo "<div><span>Osztályfönök:</span>";
													$c = explode(",", getFieldValue($d["children"]));
													foreach ($c as $cc) {
														$class= $db->getClassByText($cc);
														echo(' <a href="hometable.php?classid='.$class["id"].'">'.$cc.'</a> ');
													}
													echo "</div>";
					}
				}
				if(showField($d,"country")) 	echo "<div><span>Ország:</span>".getFieldValue($d["country"])."</div>";
				if(showField($d,"place")) 		echo "<div><span>Város:</span>".getFieldValue($d["place"])."</div>";
				echo('<div style="margin-top:5px"><span></span>');
					if(showField($d,"email"))
						echo '<a href="mailto:'.getFieldValue($d["email"]).'"><img src="images/email.png" /></a>';
					if (isset($d["facebook"]) && strlen($d["facebook"])>8)
						echo '&nbsp;<a target="_new" href='.getFieldValue($d["facebook"]).'><img src="images/facebook.png" /></a>';
					if (isset($d["twitter"]) && strlen($d["twitter"])>8)
						echo '&nbsp;<a target="_new" href='.getFieldValue($d["twitter"]).'><img src="images/twitter.png" /></a>';
					if (isset($d["homepage"]) && strlen($d["homepage"])>8)
						echo '&nbsp;<a target="_new" href='.getFieldValue($d["homepage"]).'><img src="images/www.png" /></a>';
				echo("</div>");
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
