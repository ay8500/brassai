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
.iledittitle {
	border: none;
	width:100%;
	padding:5px;
	font-weight: bold;
}
.ileditcomment {
	border: none;
	width:100%;
	height:100px;
	padding:5px;
}
</style>
<?php 
include_once("tools/sessionManager.php");
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once("data.php");
$SiteTitle="Ballagási tabló és csoportképek".getAktClassName();
include("homemenu.php"); 

$resultDBoperation="";

//Delete Picture
if (getParam("action","")=="deletePicture" && userIsLoggedOn()) {
	if (deletePicture(getParam("id", ""))>=0) {
		$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
		saveLogInInfo("PictureDelete",getAktUserId(),"",getParam("id", ""),true);
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Kép törlése sikertelen!</div>';
	}
}


//Upload Image
if (userIsLoggedOn() && isset($_POST["action"]) && ($_POST["action"]=="upload")) {
	if (basename( $_FILES['userfile']['name'])!="") {
		$fileName = explode( ".", basename( $_FILES['userfile']['name']));
		$idx=$db->getNextPictureId();

		//Create folder is doesn't exists
		$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".getAktClassFolder();
		if (!file_exists($fileFolder)) {
 	   		mkdir($fileFolder, 0777, true);
		}
		$pFileName="/c-".$idx.".".strtolower($fileName[1]);
		$uploadfile=$fileFolder.$pFileName;
		
		//JPG
		if (strcasecmp($fileName[1],"jpg")==0) {
			if ($_FILES['userfile']['size']<2000000) {
				if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
					$picture = array();
					$picture["id"]=-1;
					$picture["classID"]=getAktClass();
					$picture["file"]="images/".getAktClassFolder().$pFileName;
					$picture["isVisibleForAll"]=1;
					$picture["isDeleted"]=0;
					$picture["uploadDate"]=date("Y-m-d H:i:s");
					if ($db->savePicture($picture)>=0) {
						$db->saveRequest(changeType::personupload);
						resizeImage($uploadfile,1200,1024);
						$resultDBoperation='<div class="alert alert-success">'.$fileName[0].".".$fileName[1]." sikeresen feltöltve.</div>";
						saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),$idx,true);
					} else {
						$resultDBoperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
					}
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
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($notDeletedPictures==0) :?>
			<div class="alert alert-warning" >Jelenleg nincsenek képek feltöltve!</div>
	<?php endif;?>
	<?php 
	foreach ($pictures as $pict) {
		if ( $pict["isDeleted"]==0  || userIsAdmin() ) {
			$file=$pict["id"];
			$checked="";
			if ($pict["isVisibleForAll"]==1) $checked="checked";
	?>
	
			<div style="margin-bottom: 15px;padding: 10px; display: block;border-radius: 10px;background-color: #e8e8e8" >
				<div class="col-sm-6">
				<a style="display: inline-block;vertical-align: top;" title="<?php echo $pict["title"] ?>" href="#" >
					<img style="display: inline-block;width: 100%; padding-right:20px" src="convertImg.php?width=350&thumb=false&file=<?php echo $pict["file"] ?>" />
				</a>
				</div>
				<div class="col-sm-6">
				<?php if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) { ?>
					<div style="width:100%;display: inline-block;margin: 10px 0px 0 0px; background-color: white;border-radius: 7px;padding: 5px;" id="toolbar_<?php  echo $file ?>" >
							<input class="iledittitle" id="titleEdit_<?php echo $file ?>" value="<?php echo $pict["title"] ?>" placeholder="A kép címe" onkeyup="savePicture(<?php echo $pict["id"] ?>)"/></span><br/>
							<textarea class="ileditcomment" id="commentEdit_<?php echo $file ?>"  placeholder="A kép tartalma" onkeyup="savePicture(<?php echo $pict["id"] ?>)">
<?php echo $pict["comment"] ?>
							</textarea>
						<div >
							<span  class="ilbutton ilbuttonworld" ><input <?php echo $checked ?> type="checkbox"  onchange="changeVisibility(<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></span >
							<button class="btn btn_default"  title="Kimenti a kép módosításait" onclick="savePicture(<?php echo $pict["id"] ?>)">Kiment</button>							
							<?php if ($pict["isDeleted"]!=1): ?>
								<button class="btn btn_default" title="Képet töröl" onclick="deletePicture(<?php echo $pict["id"] ?>)"><img src="images/delete.gif" /> Töröl</button>
							<?php endif ?>
						</div> 
					</div>
				<?php } else { ?>
					<div style="display: inline-block;margin: 10px 0px 0 0px; background-color: white;border-radius: 7px;padding: 5px;" >
						<div id="text_<?php  echo $file ?>">
							<b><span id="pTitle<?php echo $pict["id"] ?>"><?php echo $pict["title"] ?></span></b><br/>
							<span id="pComment<?php echo $pict["id"] ?>"><?php echo $pict["comment"] ?></span>
						</div>
					</div>
				<?php } ?>
				</div>
				<div class="row"></div>
			</div>
	<?php 
		}
	}
	?>
	
</div>

<?php include 'homefooter.php'; ?> 

<script type="text/javascript">

function savePicture(id) {
	var t = $('#titleEdit_'+id).val();
	var c = $('#commentEdit_'+id).val();
	if (id>0) {
		$.ajax({
			url:"editDiakPictureTitle.php?id="+id+"&title="+t+"&comment="+c,
			type:"GET",
			dataType: 'json',
			success:function(data){
				$('#ajaxStatus').html(' Kimetés sikerült. ');
				$('#ajaxStatus').show();
				setTimeout(function(){
				    $('#ajaxStatus').html('');
				    $('#ajaxStatus').hide();
				}, 2000);
			}
		});
	}
}

function changeVisibility(id) {
	var c = $('#visibility'+id).prop('checked')?1:0;
	$.ajax({
		url:"editDiakPictureVisibility.php?id="+id+"&attr="+c,
		type:"GET",
		success:function(data){
			$('#ajaxStatus').html(' Kimetés sikerült. ');
			$('#ajaxStatus').show();
			setTimeout(function(){
			    $('#ajaxStatus').html('');
			    $('#ajaxStatus').hide();
			}, 2000);
		},
});
   
}


	function deletePicture(id) {
		if (confirm("Fénykép végleges törölését kérem konfirmálni!")) {
			window.location.href="tablo.php?action=deletePicture&id="+id;
		}
	}

</script>
