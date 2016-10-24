<style>
.ilbuttonworld {
    background-image: url("images/world-16.png");
    background-repeat: no-repeat;
    background-position: 11px 7px;
}

.ilbutton {
    border-style: none;
    border-radius: 6px;
    background-color: #dddddd;
    width: 51px;
    height: 31px;
    text-align: right;
    vertical-align: middle;
    padding: 5px 6px;
    display: inline-block;
}
.iledit {
	border: none;
}
</style>
<?php 
include_once("sessionManager.php");
include_once ('userManager.php');
include_once 'ltools.php';
include_once("data.php");
$SiteTitle="Ballagási tabló és csoportképek".getAktClassName();
include("homemenu.php"); 

$resultDBoperation="";

//Delete Picture
if (getParam("action","")=="deletePicture" && userIsLoggedOn()) {
	if (deletePicture($uid,getParam("id", ""))>=0) {
		$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
		saveLogInInfo("PictureDelete",$uid,$diak["user"],getParam("id", ""),true);
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Kép törlése sikertelen!</div>';
	}
}


//Upload Image
if (userIsLoggedOn() && isset($_POST["action"]) && ($_POST["action"]=="upload")) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = explode( ".", basename( $_FILES['userfile']['name']));
		$idx=$db->getNextPictureId();

		$uploadfile="./images/".getAktClassFolder()."/c-".$idx.".".strtolower($fileName[1]);
		$db->savePictureField(null, null,getAktClass(),null, $uploadfile, 1, "", "", date("Y-m-d H:i:s"));
		
		//JPG
		if (strcasecmp($fileName[1],"jpg")==0) {
			if ($_FILES['userfile']['size']<2000000) {
				if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
					resizeImage($uploadfile,1200,1024);
					$resultDBoperation='<div class="alert alert-success">'.$fileName[0].".".$fileName[1]." sikeresen feltöltve.</div>";
					saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),$idx,true);
				} else {
					$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
				}
			}
			else {
				$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." A kép file nagysága túlhaladja 2 MByteot.</div>";
				saveLogInInfo("PictureUpload",$uid,$diak["user"],"to big",false);
			}
		}
		else {
			$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.</div>";
			saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),"only jpg",false);
		}
	}
}

$notDeletedPictures=0;

$pictures = $db->getListOfPictures(getAktClass(), "class", 2, 2);
foreach ($pictures as $pict) {
	if ( $pict["isDeleted"]==0 ) {
		$notDeletedPictures++;
	}
}

?>

<div class="container-fluid">

	<h2 class="sub_title">A tanárok és diákok együtt a ballagási tablón és csoportképeken.</h2>

	<?php if (userIsAdmin() || isAktUserTheLoggedInUser()) : ?>
		<?php if ($notDeletedPictures<24 || userIsAdmin()) :?>
		<div style="margin-bottom:15px;"><button class="btn btn_default" onclick="$('#download').slideDown();">Kép feltöltése</button></div>
		<div id="download" style="margin:15px;display:none;">
			<form enctype="multipart/form-data" action="tablo.php" method="post">
				<span style="display: inline-block;">Válassz egy jpg képet max. 2MByte</span>
				<span style="display: inline-block;"><input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" /></span>	
				<span style="display: inline-block;"><button class="btn btn-default"  type="submit" >feltölt</button></span>
				<span style="display: inline-block;"><button class="btn btn-default"  onclick="$('#download').slideUp();return false;" >mégsem</button></span>
				<input type="hidden" value="upload" name="action" />
				<input type="hidden" value="<?PHP echo(getLoggedInUserId()) ?>" name="uid" />
			</form>
		</div>
		<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
		<?php endif ?>
	<?php endif ?>
	
	<?php 
		foreach ($pictures as $pict) {
			if ( $pict["isDeleted"]==0  || userIsAdmin() ) {
				$file=$pict["file"];
				$checked="";
				if ($pict["isVisibleForAll"]==1) $checked="checked";
	?>
	
			<div style="margin-bottom: -25px;padding: 10px; display: block;border-radius: 10px;background-color: #e8e8e8" >
				<a style="display: inline-block;vertical-align: top;" title="<?php echo $pict["title"] ?>"  >
					<img style="display: inline-block;min-width: 350px" src="convertImg.php?width=350&thumb=false&file=<?php echo $file ?>" />
				</a>
				<div style="display: inline-block;width: 350px;margin: 0 20px 0 20px; background-color: white;border-radius: 7px;padding: 5px;" onmouseover="showToolbar('<?php  echo $file ?>');" onmouseout="hideToolbar('<?php  echo $file ?>');">
					<div id="text_<?php  echo $file ?>">
					<b><span id="pTitle<?php echo $pict["id"] ?>"><?php echo $pict["title"] ?></span></b><br/>
					<span id="pComment<?php echo $pict["id"] ?>"><?php echo $pict["comment"] ?></span>
				</div>
				<?php if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) : ?>
					<div style="display:none;" id="toolbar_<?php  echo $file ?>" >
						<div style="display: inline-block;">
							<input class="iledit" id="titleEdit_<?php echo $file ?>" value="<?php echo $pict["title"] ?>" placeholder="A kép címe"/></span><br/>
							<input class="iledit" id="commentEdit_<?php echo $file ?>" value="<?php echo $pict["comment"] ?>" placeholder="A kép tartalma"/></span>
						</div>
						<div style="display: inline-block; vertical-align: bottom;">
							<span  class="ilbutton ilbuttonworld" ><input <?php echo $checked ?> type="checkbox"  onchange="changeVisibility('all',<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></span >
							<?php if ($pict["deleted"]!="true"): ?>
								<span class="ilbutton" ><a onclick="deletePicture('all',<?php echo $pict["id"] ?>);" title="Képet töröl"><img src="images/delete.gif" /></a></span >
							<?php endif ?>
						</div> 
					</div>
				<?php endif ?>
			</div>
	<?php 
		}
	}
	?>
	
</div>

<?php include 'homefooter.php'; ?> 

<script type="text/javascript">

	function hideToolbar(id) {
	    $("#text_"+id).show();
		$("#toolbar_"+id).hide();    
	}
	
	function showToolbar(id) {
	    $("#titleEdit_"+id).width($("#text_"+id).width()-150);
	    $("#commentEdit_"+id).width($("#text_"+id).width()-150);
	    $("#text_"+id).hide();
		$("#toolbar_"+id).show();    
	}

</script>
