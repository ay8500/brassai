<?php 
$view=getParam("view","table");
Appl::addCssStyle('
.ilbuttonworld {
    background-image: url("images/world-16.png");background-repeat: no-repeat;background-position: 11px 7px;
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
.pi100 {width:100%;}
#list-table { display:inline-block;vertical-align: text-top;}
');
if ($view=="table") 
	Appl::addCssStyle('.pictureframe {padding-bottom: 5px;max-width:395px;background-color: #dddddd;border-radius:10px;display:inline-block;vertical-align: top; margin-bottom: 10px;}');
else 
	Appl::addCssStyle('.pictureframe {padding-bottom: 5px;width:100%;background-color: #dddddd;border-radius:10px;display:inline-block;vertical-align: top; margin-bottom: 10px;}');

//Delete Picture
if (getParam("action","")=="deletePicture" ) {
	if ($db->getCountOfRequest(changeType::deletepicture,24)<5) {
		if (deletePicture(getIntParam("did"))>=0) {
			Appl::$resultDbOperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
			$db->saveRequest(changeType::deletepicture);
			saveLogInInfo("PictureDelete",getAktUserId(),"",getParam("id", ""),true);
		} else {
			Appl::$resultDbOperation='<div class="alert alert-warning" >Kép törlése sikertelen!</div>';
		}
	} else {
		Appl::$resultDbOperation='<div class="alert alert-warning" >Anonim felhasználó jogai nem elegendők a kivánt művelet végrehajtására!</div>';
	}
}

//changeOrder
if (getParam("action","")=="changeOrder" && (userIsAdmin() || userIsSuperuser() || userIsEditor())  )  {
	$db->changePictureOrderValues(getIntParam("id1", -1), getIntParam("id2", -1));
}


//Delete and unlink Picture
if (getParam("action","")=="unlinkPicture" && (userIsAdmin() || userIsSuperuser()) )  {
	if (deletePicture(getIntParam("did"),true)>=0) {
		Appl::$resultDbOperation='<div class="alert alert-success" >Kép sikeresen törölve.</div>';
	} else {
		Appl::$resultDbOperation='<div class="alert alert-warning" >Kép törlése sikertelen!</div>';
	}
}

//Upload Image
if (isset($_POST["action"]) && ($_POST["action"]=="upload")) {
	if ($db->getCountOfRequest(changeType::classupload,24)<10) {
		if (basename( $_FILES['userfile']['name'])!="") {
			$fileName = explode( ".", basename( $_FILES['userfile']['name']));
			$idx=$db->getNextPictureId();
	
			if (userIsAdmin() && null!=getParam("overwriteFileName")) {
				//Overwrite an existing file
				$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/".getParam("overwriteFileName");
				unlink($uploadfile);
				$overwrite=true;
			} else {
				//Create folder is doesn't exists
				$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".getAktClassFolder();
				if (!file_exists($fileFolder)) {
		 	   		mkdir($fileFolder, 0777, true);
				}
				$pFileName="/c-".$idx.".".strtolower($fileName[1]);
				$uploadfile=$fileFolder.$pFileName;
				$overwrite=false;
			}
			
			//JPG
			if (strcasecmp($fileName[1],"jpg")==0) {
				if ($_FILES['userfile']['size']<3100000) {
					if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
						if ($overwrite==false) {
							$upicture = array();
							$upicture["id"]=-1;
							$upicture[$type]=$typeId;
							$upicture["file"]="images/".getAktClassFolder().$pFileName;
							$upicture["isVisibleForAll"]=1;
							$upicture["isDeleted"]=0;
							if (null!=getParam("album") && getParam("album")!="") {
								$upicture["albumName"]=getParam("album");
							}
							$upicture["uploadDate"]=date("Y-m-d H:i:s");
							if ($db->savePicture($upicture)>=0) {
								$db->saveRequest(changeType::personupload);
								resizeImage($uploadfile,1800,1800);
								Appl::$resultDbOperation='<div class="alert alert-success">'.$fileName[0].".".$fileName[1]." sikeresen feltöltve.</div>";
								saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),$idx,true);
							} else {
								Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
							}
						} else {
							Appl::$resultDbOperation='<div class="alert alert-success">'.$fileName[0].".".$fileName[1]." sikeresen feltöltve és felülírva.</div>";
						}
					} else {
						Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.</div>";
					}
				}
				else {
					Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." A kép file nagysága túlhaladja 2 MByteot.</div>";
					saveLogInInfo("PictureUpload",$uid,$diak["user"],"to big",false);
				}
			}
			else {
				Appl::$resultDbOperation='<div class="alert alert-warning">'.$fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.</div>";
				saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),"only jpg",false);
			}
		}
	} else {
		Appl::$resultDbOperation='<div class="alert alert-warning" >Anonim felhasználó jogai nem elegendők a kivánt művelet végrehajtására!</div>';
	}
}

$notDeletedPictures=0;

if(isset($picture)) {
	$pictures = array($picture);	//Only one picture
	$notDeletedPictures=1;
} else {
	if ($type!="tablo")
		$pictures = $db->getListOfPictures($typeId, $type, 2, 2, getParam("album"));
	else
		$pictures = $db->getListOfPicturesWhere("classID is not null and (title like '%Tabló%' or title like '%tabló%') ");
	foreach ($pictures as $pict) {
		if ( $pict["isDeleted"]==0 ) {
			$notDeletedPictures++;
		}
	}
}
?>
	<form enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]?>" method="post">
	<?php if (($notDeletedPictures<50 || userIsAdmin()) && $type!="tablo") :?>
		<div style="margin-bottom:15px;">
			<button class="btn btn-info" onclick="$('#download').slideDown();return false;"><span class="glyphicon glyphicon-cloud-upload"> </span> Kép feltöltése</button>
			<?php if(isset($picture)) { ?>
				<button class="btn btn-default" onclick="window.location.href=<?php echo "'".$_SERVER["PHP_SELF"].'?type='.$type.'&typeid='.$typeId.'&album='.$picture["albumName"]."'; return false;"?>" ><span class="glyphicon glyphicon-hand-right"> </span> Mutasd a többi képet</button>
			<?php  }?>
			<button class="btn btn-default" onclick="toogleListBlock();return false;"><span class="glyphicon glyphicon-eye-open"> </span> Lista/Album</button>
		</div>
		<div id="download" style="margin:15px;display:none;">
			<div>Bővitsd a véndiákok oldalát képekkel! Válsszd ki a privát fényképid közül azokat az értékes felvételeket amelyeknek mindenki örvend ha látja.<span></span></div>
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
			<?php if(null!=getParam("album")) {?>
				<input type="hidden" name="album" value="<?php echo (getParam("album"))?>" />
			<?php }?>
			<input type="hidden" name="typeid" value="<?php echo ($typeId)?>" />
		</div>
	<?php endif; ?>
	
	
	<?php if ($notDeletedPictures==0) :?>
		<div class="alert alert-warning" >Jelenleg nincsenek képek feltöltve!</div>
	<?php endif;?>
	
		
	<?php 
	foreach ($pictures as $idx=>$pict) {
		if ( $pict["isDeleted"]==0  || userIsAdmin() ) {
			$checked="";
			if ($pict["isVisibleForAll"]==1) $checked="checked";
	?>
	
			<div class="pictureframe" >
				<div id="list-table">
					<?php if ($view=="table") {?>
						<a style="display:block;vertical-align: top; margin:10px" title="<?php echo $pict["title"] ?>" href="javascript:return false" onclick="toggleBigger(this)">
							<img class="img-responsive" style="cursor: -webkit-zoom-in; cursor: -moz-zoom-in;" src="convertImg.php?width=1800&thumb=false&id=<?php echo $pict["id"] ?>" />
						</a>
					<?php } else {?>
						<div style="vertical-align: top; margin:10px" >
							<img class="img-responsive" src="convertImg.php?width=80&thumb=true&id=<?php echo $pict["id"] ?>" />
						</div>
					<?php } ?>
					<?php  if (userIsAdmin() || userIsSuperuser()) {?>
						<br/><a href="history.php?table=picture&id=<?php echo $pict["id"]?>" title="módosítások" style="position: relative;top: -50px;left: 17px;display:inline-block;">
							<span class="badge"><?php echo sizeof($db->getHistoryInfo("picture",$pict["id"]))?></span>
						</a>
					<?php }?>
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
							<button class="btn btn_default"  title="Kimenti a kép módosításait" onclick="savePicture(<?php echo $pict["id"] ?>,this);return false;"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>							
							<?php if ($pict["isDeleted"]!=1): ?>
								<button class="btn btn_default" title="Képet töröl" onclick="deletePicture(<?php echo $pict["id"] ?>);return false;"><span class="glyphicon glyphicon-remove-circle"></span> Töröl</button>
							<?php endif ?>
							<?php if (userIsAdmin()) : ?>
								<button class="btn btn_default" title="Végleges törölés" onclick="unlinkPicture(<?php echo $pict["id"] ?>);return false;"><img src="images/delete.gif" /> Végleges</button>
							<?php endif ?>
							<button onclick="hideedit(<?php echo $pict["id"] ?>);return false;" class="btn btn-default"><span class="glyphicon glyphicon-chevron-up"></span></button>
						</div> 
					</div>
					<div id="show_<?php echo $pict["id"] ?>" style="margin:10px;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;cursor:default;" >
						<?php //change Order buttons?>
						<?php if($view!="table" && ( userIsAdmin() || userIsEditor() || userIsSuperuser()) ) :?>
							<?php  if ($idx!=0) {?>
								<button id="picsort" style="margin: 0px 5px 0 10px;" class="btn btn-default" onclick="changeOrder(<?php echo $pict["id"] ?>,<?php echo $pictures[$idx-1]["id"] ?>);return false;" title="eggyel előrébb"><span class="glyphicon glyphicon-arrow-up"></span></button>
							<?php } else {?>
								<span style="margin: 0px 40px 0 10px;" >&nbsp</span>
							<?php } if ($idx+1<sizeof($pictures)) {?>
								<button id="picsort" style="margin: 0px 10px 0 5px;" class="btn btn-default" onclick="changeOrder(<?php echo $pictures[$idx+1]["id"] ?>,<?php echo $pictures[$idx]["id"] ?>);return false;" title="eggyel hátrébb"><span class="glyphicon glyphicon-arrow-down"></span></button>
							<?php } else {?>
								<span style="margin: 0px 5px 0 40px;" >&nbsp</span>
							<?php } ?>
						<?php endif;?>
						<div id="text_<?php echo $pict["id"] ?>" style="display: inline-block;margin: 10px 0px 0 0px;max-width: 320px;">
							<b><span id="titleShow_<?php echo $pict["id"] ?>"><?php echo $pict["title"] ?></span></b><br/>
							<span id="commentShow_<?php echo $pict["id"] ?>"><?php echo $pict["comment"] ?></span>
						</div>
						<button style="display: inline-block;margin: 0px 10px 0 10px;" class="btn btn-default" onclick="displayedit(<?php echo $pict["id"] ?>);return false;"><span class="glyphicon glyphicon-pencil"></span></button>
						<?php  if (userIsAdmin()){?>
							<button style="display: inline-block;margin: 0px 10px 0 10px;" class="btn btn-danger" name="overwriteFileName" value="<?php echo $pict["file"]?>"><span class="glyphicon glyphicon-upload"></span> Kicserél</button>
							<a class="btn btn-default" target="_download" href="<?php echo $pict['file']?>" title="ImageName"><span class="glyphicon glyphicon-download"></span> Letölt</a>
						<?php } ?>
					</div>
					<?php if (!userIsLoggedOn() && $pict["isVisibleForAll"]==0) { ?>
					<br/><span  class="iluser" title="Csak bejelnkezett felhasználok látják ezt a képet élesen.">Ez a kép védve van!</span >
					<?php } ?> 
				</div>
				<?php if ($view!="table" && userIsAdmin()) {?>
				<div  id="list-table" ><div style="margin:10px;">
					id=<?php echo $pict["id"]?>
					orderValue=<?php echo $pict["orderValue"]?><br/>
					orderValue=<?php echo $pict["file"]?><br/>
					uploaded=<?php echo date("Y.m.d H:i:s",strtotime($pict["uploadDate"]));?></br>
					changed=<?php echo date("Y.m.d H:i:s",strtotime($pict["changeDate"]));?></br>
					user=<?php echo '('.$pict["changeUserID"].') '.getPersonName($db->getPersonByID($pict["changeUserID"]))?>
				</div></div>
				<?php } ?>
			</div>
	<?php }	}?>
	</form>
	

<script type="text/javascript">

function savePicture(id) {
	var t = $('#titleEdit_'+id).val();
	var c = $('#commentEdit_'+id).val();
	$('#titleShow_'+id).html(t);
	$('#commentShow_'+id).html(c);
	if (id>0) {
		$.ajax({
			url:encodeURI("editDiakPictureTitle.php?id="+id+"&title="+t+"&comment="+c),
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
	    $("[class*=pictureframe]").css("max-width","395px");
	    $(o).parent().parent().css("max-width","none");
	}
	$(window).scrollTop(tempScrollTop);
}

function toogleListBlock() {
	<?php 
		if ($view=="table") {	
			$url="view=list";
		} else {
			$url="view=table";
		} 
		$url .=isset($tabOpen)?"&tabOpen=".$tabOpen:"";
		$url .=isset($type)?"&type=".$type:"";
		$url .=isset($typeId)?"&typeid=".$typeId:"";
		$url .=null!=getParam("album")?"&album=".getParam("album"):"";
		$url .=isset($id)?"&id=".$id:"";
		?>
		window.location.href="<?php echo $_SERVER["PHP_SELF"].'?',$url ?>";
}
	
function deletePicture(id) {
	if (confirm("Fénykép végleges törölését kérem konfirmálni!")) {
		window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=deletePicture&did="+id+"&tabOpen="+<?php echo(getIntParam("tabOpen",0))?>+"&type=<?php echo $type?>&typeid=<?php echo $typeId?>&album=<?php echo getParam("album")?>";
	}
}

function changeOrder(id1,id2) {
<?php 
	$url =$view=="table"?"view=table":"view=list";
	$url .=isset($tabOpen)?"&tabOpen=".$tabOpen:"";
	$url .=isset($type)?"&type=".$type:"";
	$url .=isset($typeId)?"&typeid=".$typeId:"";
	$url .=isset($id)?"&id=".$id:"";
	$url .=null!=getParam("album")?"&album=".getParam("album"):"";
	$url .="&action=changeOrder";
	?>
	window.location.href="<?php echo $_SERVER["PHP_SELF"].'?',$url ?>"+"&id1="+id1+"&id2="+id2;
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
		window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=unlinkPicture&did="+id+"&tabOpen="+<?php echo(getIntParam("tabOpen",0))?>+"&type=<?php echo $type?>&typeid=<?php echo $typeId?>&album=<?php echo getParam("album")?>";
	}
}
<?php endif;?>
</script>
