<style>
.ilbuttonworld {
    background-image: url("images/world-16.png");
    background-repeat: no-repeat;
    background-position: 11px 7px;
}

.iluser {
    background-image: url("images/world-16.png");
    background-repeat: no-repeat;
    background-position: 11px 7px;
    padding-left: 40px;
    padding-top:7px;
    margin-top:5px;
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
.iledittitle {	border: none;width:100%;padding:5px;font-weight: bold;}
.ileditcomment {border: none;width:100%;height:100px;padding:5px;}

.pi100 {width:100%};

</style>

<?php 
$resultDBoperation="";



//Delete Picture
if (getParam("action","")=="deletePicture" ) {
	if ($db->getCountOfRequest(changeType::deletepicture,24)<5) {
		if (deletePicture(getIntParam("did"))>=0) {
			$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
			$db->saveRequest(changeType::deletepicture);
			saveLogInInfo("PictureDelete",getAktUserId(),"",getParam("id", ""),true);
		} else {
			$resultDBoperation='<div class="alert alert-warning" >Kép törlése sikertelen!</div>';
		}
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Anonim felhasználó jogai nem elegendők a kivánt művelet végrehajtására!</div>';
	}
}

//Delete and unlink Picture
if (getParam("action","")=="unlinkPicture" && userIsAdmin()) {
	if (deletePicture(getIntParam("did"),true)>=0) {
		$resultDBoperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Kép törlése sikertelen!</div>';
	}
}

//Upload Image
if (isset($_POST["action"]) && ($_POST["action"]=="upload")) {
	if ($db->getCountOfRequest(changeType::classupload,24)<10) {
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
						$upicture = array();
						$upicture["id"]=-1;
						$upicture[$type]=$typeId;
						$upicture["file"]="images/".getAktClassFolder().$pFileName;
						$upicture["isVisibleForAll"]=1;
						$upicture["isDeleted"]=0;
						$upicture["uploadDate"]=date("Y-m-d H:i:s");
						if ($db->savePicture($upicture)>=0) {
							$db->saveRequest(changeType::personupload);
							resizeImage($uploadfile,1800,1800);
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
	} else {
		$resultDBoperation='<div class="alert alert-warning" >Anonim felhasználó jogai nem elegendők a kivánt művelet végrehajtására!</div>';
	}
}




$notDeletedPictures=0;

if (!isset($typeId)) 
	$typeId=null; 

if(isset($picture)) {
	$pictures = array($picture);
	$notDeletedPictures=1;
} else {
	if ($type!="tablo")
		$pictures = $db->getListOfPictures($typeId, $type, 2, 2);
	else
		$pictures = $db->getListOfPicturesWhere("classID is not null and (title like '%Tabló%' or title like '%tabló%') ");
	foreach ($pictures as $pict) {
		if ( $pict["isDeleted"]==0 ) {
			$notDeletedPictures++;
		}
	}
}
?>

	<?php if (($notDeletedPictures<50 || userIsAdmin()) && $type!="tablo") :?>
		<div style="margin-bottom:15px;">
			<button class="btn btn-default" onclick="$('#download').slideDown();"><span class="glyphicon glyphicon-cloud-upload"> </span> Kép feltöltése</button>
			<?php if(isset($picture)) { ?>
				<button class="btn btn-default" onclick="window.location.href=<?php echo "'".$_SERVER["PHP_SELF"].'?type='.$type.'&typeid='.$typeId."'"?>" ><span class="glyphicon glyphicon-hand-right"> </span> Mutasd a többi képet</button>
			<?php  }?>
			<button class="btn btn-default" onclick="toogleListBlock();"><span class="glyphicon glyphicon-eye-open"> </span> Lista/Album</button>
		</div>
		<div id="download" style="margin:15px;display:none;">
			<form enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]?>" method="post">
				<span style="display: inline-block;">Válassz egy jpg képet max. 2MByte</span>
				<span style="display: inline-block;"><input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" /></span>	
				<span style="display: inline-block;"><button class="btn btn-default"  type="submit" ><span class="glyphicon glyphicon-upload"> </span> feltölt</button></span>
				<span style="display: inline-block;"><button class="btn btn-default"  onclick="$('#download').slideUp();return false;" ><span class="glyphicon glyphicon-remove-circle"> </span> mégsem</button></span>
				<input type="hidden" value="upload" name="action" />
				<?php if (isset($personid)):?>
					<input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
				<?php endif;?>
				<input type="hidden" value="<?PHP echo(getIntParam("tabOpen",0)) ?>" name="tabOpen" />
				<input type="hidden" name="type" value="<?php echo ($type)?>" />
				<input type="hidden" name="typeid" value="<?php echo ($typeId)?>" />
			</form>
		</div>
		<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
	<?php endif; ?>
	
	
	<?php if ($notDeletedPictures==0) :?>
			<div class="alert alert-warning" >Jelenleg nincsenek képek feltöltve!</div>
	<?php endif;?>
	
		
	<?php 
	foreach ($pictures as $pict) {
		if ( $pict["isDeleted"]==0  || userIsAdmin() ) {
			$checked="";
			if ($pict["isVisibleForAll"]==1) $checked="checked";
	?>
	
			<div id="pictureframe" style="padding-bottom: 5px;max-width:395px;background-color: #dddddd;border-radius:10px;display:inline-block;vertical-align: top; margin-bottom: 10px;" >
				<div id="list-table">
					<a style="display: inline-block;vertical-align: top; margin:10px" title="<?php echo $pict["title"] ?>" href="javascript:return false" onclick="toggleBigger(this)">
						<img class="img-responsive" style="display: inline-block;cursor: -webkit-zoom-in; cursor: -moz-zoom-in;" src="convertImg.php?width=1800&thumb=false&id=<?php echo $pict["id"] ?>" />
					</a>
				</div>
				<div  id="list-table" >
					<div id="edit_<?php echo $pict["id"] ?>" style="margin:10px;display: none; background-color: white;border-radius: 7px;padding: 5px;" >
							<input type="text" class="iledittitle" id="titleEdit_<?php echo $pict["id"] ?>" value="<?php echo $pict["title"] ?>" placeholder="A kép címe" /><br/>
<textarea class="ileditcomment" id="commentEdit_<?php echo $pict["id"] ?>"  placeholder="A kép tartalma" >
<?php echo $pict["comment"] ?></textarea>
						<div >
							<?php if (userIsLoggedOn()) { ?>
								<span  class="ilbutton ilbuttonworld" ><input <?php echo $checked ?> type="checkbox"  onchange="changeVisibility(<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></span >
							<?php } ?> 
							<button class="btn btn_default"  title="Kimenti a kép módosításait" onclick="savePicture(<?php echo $pict["id"] ?>,this)"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>							
							<?php if ($pict["isDeleted"]!=1): ?>
								<button class="btn btn_default" title="Képet töröl" onclick="deletePicture(<?php echo $pict["id"] ?>)"><span class="glyphicon glyphicon-remove-circle"></span> Töröl</button>
							<?php endif ?>
							<?php if (userIsAdmin()) : ?>
								<button class="btn btn_default" title="Végleges törölés" onclick="unlinkPicture(<?php echo $pict["id"] ?>)"><img src="images/delete.gif" /> Végleges</button>
							<?php endif ?>
							<button onclick="hideedit(<?php echo $pict["id"] ?>);" class="btn btn-default"><span class="glyphicon glyphicon-chevron-up"></span></button>
						</div> 
					</div>
					<div id="show_<?php echo $pict["id"] ?>" style="margin:10px;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;cursor:default;" >
						<div id="text_<?php echo $pict["id"] ?>" style="display: inline-block;margin: 10px 0px 0 0px;">
							<b><span id="titleShow_<?php echo $pict["id"] ?>"><?php echo $pict["title"] ?></span></b><br/>
							<span id="commentShow_<?php echo $pict["id"] ?>"><?php echo $pict["comment"] ?></span>
						</div>
						<button style="display: inline-block;margin: 0px 10px 0 10px;" class="btn btn-default" onclick="displayedit(<?php echo $pict["id"] ?>);"><span class="glyphicon glyphicon-pencil"></span></button>
						<?php if(userIsLoggedOn()) :?>
							<button id="picsort" style="display: none;margin: 0px 10px 0 10px;" class="btn btn-default" onclick="moveup(<?php echo $pict["id"] ?>);"><span class="glyphicon glyphicon-arrow-up"></span></button>
							<button id="picsort" style="display: none;margin: 0px 10px 0 10px;" class="btn btn-default" onclick="movedown(<?php echo $pict["id"] ?>);"><span class="glyphicon glyphicon-arrow-down"></span></button>
						<?php endif;?>
					</div>
					<?php if (!userIsLoggedOn() && $pict["isVisibleForAll"]==0) { ?>
					<br/><span  class="iluser" title="Csak bejelnkezett felhasználok látják ezt a képet élesen.">Ez a kép védve van!</span >
					<?php } ?> 
				</div>
			</div>
	<?php }	}?>
	
</div>

<script type="text/javascript">

function savePicture(id) {
	var t = $('#titleEdit_'+id).val();
	var c = $('#commentEdit_'+id).val();
	$('#titleShow_'+id).html(t);
	$('#commentShow_'+id).html(c);
	if (id>0) {
		$.ajax({
			url:"editDiakPictureTitle.php?id="+id+"&title="+t+"&comment="+c,
			type:"GET",
			dataType: 'json',
			success:function(data){
				hideedit(id);
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
		}
	});
}

	function toggleBigger(o) {
	    var tempScrollTop = $(window).scrollTop();
		
		if ($(o).parent().parent().css("max-width")==="none") {  //Big
		    $(o).parent().parent().css("max-width","395px");
		} else {
		    $("[id=pictureframe]").css("max-width","395px");
		    $(o).parent().parent().css("max-width","none");
		}
		
		$(window).scrollTop(tempScrollTop);
	}

	function toogleListBlock() {
		if ($("#pictureframe").css("max-width")!=="none") {
	    	$("[id=pictureframe]").css("max-width","none");		//List
	    	$("[id=pictureframe]").css("width","100%");
	    	$("[class=img-responsive]").css("height","70px");
	    	$("[id=list-table]").css("display","inline");
	    	$("[id=picsort]").css("display","inline"); 
	    	$("[id^=edit]").css("display","inline");
	    	$("[id^=edit]").hide();
		} else {
		    $("[id=pictureframe]").css("max-width","395px");  //Table
		    $("[id=pictureframe]").css("width","");
		    $("[class=img-responsive]").css("height","");
		    $("[id=list-table]").css("display","auto");
		    $("[id=picsort]").hide();
		}
	}

	function deletePicture(id) {
		if (confirm("Fénykép végleges törölését kérem konfirmálni!")) {
			window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=deletePicture&did="+id+"&tabOpen="+<?php echo(getIntParam("tabOpen",0))?>+"&type=<?php echo $type?>&typeid=<?php echo $typeId?>";
		}
	}

	function moveup(id) {
	    window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=moveup&did="+id+"&tabOpen="+<?php echo(getIntParam("tabOpen",0))?>+"&type=<?php echo $type?>&typeid=<?php echo $typeId?>";
	}

	function movedown(id) {
	    window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=movedown&did="+id+"&tabOpen="+<?php echo(getIntParam("tabOpen",0))?>+"&type=<?php echo $type?>&typeid=<?php echo $typeId?>";
	}

	function hideedit(id) {
		$("#edit_"+id).hide();
		$("#show_"+id).show();
		
	}
	
	function displayedit(id) {
	    $("#show_"+id).hide();
		$("#edit_"+id).show();
	}

	<?php if (userIsAdmin()) :?>
	function unlinkPicture(id) {
		if (confirm("Fénykép végleges törölését kérem konfirmálni!")) {
			window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=unlinkPicture&did="+id+"&tabOpen="+<?php echo(getIntParam("tabOpen",0))?>+"&type=<?php echo $type?>&typeid=<?php echo $typeId?>";
		}
	}
	<?php endif;?>
	

</script>
