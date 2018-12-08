<?php
include_once ('tools/sessionManager.php');
include_once ('tools/userManager.php');
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once ('dbBL.class.php');
include_once ('displayOpinion.inc.php');

use \maierlabs\lpfw\Appl as Appl;
\maierlabs\lpfw\Appl::addCss('css/picture.css',true);

//Delete Picture
if (getParam("action","")=="deletePicture" ) {
	if ($db->getCountOfRequest(changeType::deletepicture,24)<5) {
		if ($db->deletePicture(getIntParam("did"))>=0) {
		    $db->updateRecentChangesList();
            Appl::setMessage("Kép sikeresen törölve","success");
			$db->saveRequest(changeType::deletepicture);
			saveLogInInfo("PictureDelete",getAktUserId(),"",getParam("id", ""),true);
		} else {
            Appl::setMessage("Kép törlése sikertelen!","warning");
		}
	} else {
        Appl::setMessage("Anonim felhasználó jogai nem elegendők a kivánt művelet végrehajtására!","warning");
	}
}

//change picture order
if (getParam("action","")=="changeOrder" && (userIsAdmin() || userIsSuperuser() || userIsEditor())  )  {
	$db->changePictureOrderValues(getIntParam("id1", -1), getIntParam("id2", -1));
}

//change picture albumname
if (isActionParam("changePictureAlbum") && (userIsAdmin() || userIsSuperuser() || userIsEditor())  )  {
	if ($db->changePictureAlbumName(getIntParam("pictureid", -1), getParam("album", ""))) {
        Appl::setMessage("Kép sikeresen áthelyezve","success");
	} else {
        Appl::setMessage("Kép éthelyezése sikertelen","warning");
	}
}

//change albumname
if (isActionParam("renameAlbum") && (userIsAdmin() || userIsSuperuser() || userIsEditor())  )  {
	if ($db->changeAlbumName($type, $typeId, getParam("oldAlbum", ""), getParam("album", ""))) {
        Appl::setMessage("Album sikeresen étnevezve","success");
	} else {
        Appl::setMessage("Album átnevezése sikertelen","warning");
	}
}

//Delete and unlink Picture
if (isActionParam("unlinkPicture") && (userIsAdmin() || userIsSuperuser()) )  {
	if ($db->deletePicture(getIntParam("did"),true)>=0) {
	    $db->updateRecentChangesList();
        Appl::setMessage("Kép sikeresen véglegesen törölve","success");
	} else {
        Appl::setMessage("Kép végleges törlése sikertelen!","warning");
	}
}

//Upload Image
if (isset($_POST["action"]) && ($_POST["action"]=="upload")) {
	if ($db->checkRequesterIP(changeType::classupload)) {
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
				$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".$db->getAktClassFolder();
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
							$upicture["file"]="images/".$db->getAktClassFolder().$pFileName;
							$upicture["isVisibleForAll"]=1;
							$upicture["isDeleted"]=0;
							if (null!=getParam("album") && getParam("album")!="") {
								$upicture["albumName"]=getParam("album");
							}
							$upicture["uploadDate"]=date("Y-m-d H:i:s");
							if ($db->savePicture($upicture)>=0) {
								$db->saveRequest(changeType::classupload);
								resizeImage($uploadfile,1800,1800);
                                Appl::setMessage($fileName[0].".".$fileName[1]." sikeresen feltöltve.","success");
								saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),$idx,true);
							} else {
                                Appl::setMessage($fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.","warning");
							}
						} else {
                            Appl::setMessage($fileName[0].".".$fileName[1]." sikeresen feltöltve és felülírva.","success");
						}
					} else {
                        Appl::setMessage($fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.","warning");
					}
				}
				else {
                    Appl::setMessage($fileName[0].".".$fileName[1]." A kép file nagysága túlhaladja 2 MByteot.","warning");
					saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),"to big",false);
				}
			}
			else {
                Appl::setMessage($fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.","warning");
				saveLogInInfo("PictureUpload",getLoggedInUserId(),getAktUserId(),"only jpg",false);
			}
		}
	} else {
        Appl::setMessage("A feltöltött képek száma meghaladta a naponta megengedett határt!","warning");
	}
}

//View 
$albumParam = getParam("album","");
$view=getParam("view","table");
$limit=24;
$offset=getIntParam("start",0)*$limit;
$link="picture.inc.php?action=showmore&type=".getParam("type")."&typeid=".getParam("typeid")."&album=".getParam("album")."&start=";
if ($view=="table") {
    Appl::addCssStyle('.pictureframe {padding-bottom: 5px;max-width:395px;background-color: #dddddd;border-radius:10px;display:inline-block;vertical-align: top; margin-bottom: 10px;}');
} else {
    Appl::addCssStyle('.pictureframe {padding-bottom: 5px;width:100%;background-color: #dddddd;border-radius:10px;display:inline-block;vertical-align: top; margin-bottom: 10px;}');
}
//The list of pictures
$notDeletedPictures=0;
if(isset($picture)) {
	$pictures = array($picture);	//Only one picture => convert to array
	$notDeletedPictures=1;
} else {
	if ($albumParam=="_tablo_") {
	    $where="classID is not null and (title like '%Tabló%' or title like '%tabló%') ";
        $pictures = $db->getListOfPicturesWhere($where, $limit, $offset);
        $countPictures = $db->getTableCount("picture",$where);
    } elseif ($albumParam=="_card_") {
	    $where = "classID is not null and (title like '%icsengetési%') ";
        $pictures = $db->getListOfPicturesWhere($where, $limit, $offset);
        $countPictures = $db->getTableCount("picture",$where);
    } else {
	    if (!isset($typeId)) $typeId=getParam("typeid");
	    if (!isset($type)) $type=getParam("type");
        $pictures = $db->getListOfPictures($typeId, $type,  $albumParam,$limit,$offset);
        $where = $type.'='.$typeId;
        if ($albumParam!=null) {
            $where.=" and albumName='".$albumParam."'";
        } else {
            $where.=" and (albumName is null or albumName='')";
        }
        $countPictures = $db->getTableCount("picture",$where);
	}
	foreach ($pictures as $pict) {
		if ( $pict["isDeleted"]==0 ) {
			$notDeletedPictures++;
		}
	}
}

//Albumlist
$startAlbumList=array(array("albumName"=>"","albumText"=>"Főalbum"));
if (getParam("type")=="schoolID") {
	$startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"picture.php?type=schoolID&typeid=".getParam("typeid")."&album=_tablo_","albumText"=>"Tablók","albumName"=>"_tablo_")));
    $startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"picture.php?type=schoolID&typeid=".getParam("typeid")."&album=_card_","albumText"=>"Kicsengetési kártyák","albumName"=>"_card_")));
}
$albumList = $db->getListOfAlbum(getParam("type"), getParam("typeid"), $startAlbumList);

//Check if a new album want to be created
if (getParam("album")!=null) {
	$newAlbum = true;
	foreach ($albumList as $alb) {
		if ($alb["albumName"]==getParam("album")) {
            $newAlbum=false;
            break;
        }
	}
	if ($newAlbum) {
		$albumList = array_merge($albumList,array(array("albumName"=>getParam("album"),"albumText"=>getParam("album"))));
	}
}
if (isActionParam("showmore") ) {
    displayPictureList($db,$pictures,$albumList,$albumParam,$view);
    die();
}
?>
	<div class="well">
		<?php foreach ($albumList as $alb) {
			if (isset($alb["albumName"])) {
				$albumLink = basename($_SERVER['SCRIPT_FILENAME'], ".php").".php?type=".$type."&typeid=".$typeId."&album=".$alb["albumName"].(getParam("tabOpen")!=null?"&tabOpen=".getParam("tabOpen"):"");
			} else { 
				$albumLink =$alb["albumLink"]; 
			}?> 
			<a href="<?php echo $albumLink?>">
			<?php if ($albumParam==$alb["albumName"]) {?>
				<span class="folderdivwhite"><?php echo $alb["albumText"]?></span>
			<?php } else {?>
				<span class="folderdiv"><?php echo $alb["albumText"]?></span>
			<?php } ?>
			</a>
		<?php }?>
		<?php if ( (userIsAdmin() || userIsSuperuser() || userIsEditor()) ) {?>
			<div style="display:inline-block">
				<form action="<?php echo $_SERVER["PHP_SELF"]?>" method="post">
					<?php if (getParam("tabOpen")!=null) {?>
						<input name="tabOpen" type="hidden" value="<?php echo getParam('tabOpen')?>" /> 
					<?php } ?>
					<?php if (getParam("type")!=null) {?>
						<input name="type" type="hidden" value="<?php echo getParam('type')?>" /> 
					<?php } ?>
					<?php if (getParam("typeid")!=null) {?>
						<input name="typeid" type="hidden" value="<?php echo getParam('typeid')?>" /> 
					<?php } ?>
					<input name="album" class="form-control" placeholder="Album címe"/><br/>
					<button class="btn btn-default" style="margin-top: -15px;">Új albumot létrehoz</button>
				</form>
			</div>
		<?php } ?>
		<?php if ($albumParam!="" && $albumParam!="_tablo_" && $albumParam!="_card_" && 	!$newAlbum && (userIsAdmin() || userIsSuperuser() || userIsEditor()) ) {?>
			<div style="display:inline-block">
				<form action="<?php echo $_SERVER["PHP_SELF"]?>" method="post">
					<?php if (getParam("tabOpen")!=null) {?>
						<input name="tabOpen" type="hidden" value="<?php echo getParam('tabOpen')?>" /> 
					<?php } ?>
					<input name="oldAlbum" type="hidden" value="<?php echo getParam('album')?>" />
					<input name="album" class="form-control" value="<?php echo getParam('album')?>"/><br/>
					<button name="action" value="renameAlbum" class="btn btn-default" style="margin-top: -15px;">Albumot átnevez</button>
				</form>
			</div>
		<?php }?>
	</div>
	
	<form enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]?>" method="post">
        <input type="hidden" name="album" value="<?php echo getParam("album","")?>"/>
	<?php if (($notDeletedPictures<50 || userIsAdmin()) && $albumParam!="_tablo_" && $albumParam!="_card_") {?>
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
			<span style="display: inline-block;"><button class="btn btn-default"  type="submit" onclick="showWaitMessage();"><span class="glyphicon glyphicon-upload"> </span> feltölt</button></span>
			<span style="display: inline-block;"><button class="btn btn-default"  onclick="$('#download').slideUp();return false;" ><span class="glyphicon glyphicon-remove-circle"> </span> mégsem</button></span>
			<input type="hidden" value="upload" name="action" />
			<?php if (isset($personid)):?>
				<input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
			<?php endif;?>
			<input type="hidden" value="<?PHP echo(getParam("tabOpen",0)) ?>" name="tabOpen" />
			<input type="hidden" name="type" value="<?php echo ($type)?>" />
			<?php if(null!=getParam("album")) {?>
				<input type="hidden" name="album" value="<?php echo (getParam("album"))?>" />
			<?php }?>
			<input type="hidden" name="typeid" value="<?php echo ($typeId)?>" />
		</div>
	<?php } ?>

	
	<?php if ($notDeletedPictures==0) :?>
		<div class="alert alert-warning" >Jelenleg nincsenek képek feltöltve. Légy te az első aki képpet tőlt fel!</div>
	<?php endif;?>
	<?php if(false) {?>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item"><span class="page-link" >Képek száma:<?php echo ($countPictures)?></span></li>
                <li class="page-item"><a class="page-link" href="<?php echo $link."0" ?>"><span class="glyphicon glyphicon-fast-backward"></span></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo $offset>0?$link.(floor($offset/$limit)-1):"#" ?>"><span class="glyphicon glyphicon-step-backward"></span></a></li>
                    <li class="page-item"><a class="page-link" href="#"><?php echo ($offset+1).'-'.(($offset+$limit<$countPictures)?$offset+$limit:$countPictures) ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo (($offset+$limit)<$countPictures)?$link.(floor($offset/$limit)+1):"#" ?>"><span class="glyphicon glyphicon-step-forward"></span></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo $link.floor($countPictures/$limit) ?>"><span class="glyphicon glyphicon-fast-forward"></span></a></li>
            </ul>
        </nav>
    <?php } ?>
	<?php displayPictureList($db,$pictures,$albumList,$albumParam,$view) ?>
        <span id="more"></span>
</form>
<button id="buttonmore" class="btn btn-success" style="margin:10px;" onclick="return showmore()">Többet szeretnék látni</button>

<?php
function displayPictureList($db,$pictures,$albumList,$albumParam,$view) {
    if (sizeof($pictures)==0) {
        \maierlabs\lpfw\Appl::addJsScript('$("#buttonmore").hide();');
    }
    foreach ($pictures as $idx=>$pict) {
        if ( $pict["isDeleted"]==0  || userIsAdmin() ) {
            $checked= ($pict["isVisibleForAll"]==1)?"checked":"";
            ?>
            <div class="pictureframe" <?php echo $pict["isDeleted"]==1?'style="background-color: #ffbcac;"':'' ?>>
                <div id="list-table">
                    <?php if ($view=="table") {?>
                        <a style="display:block;vertical-align: top;min-height:300px" title="<?php echo $pict["title"] ?>" href="javascript:return false" onclick="toggleBigger(this)">
                            <img class="img-responsive" style="cursor: -webkit-zoom-in; cursor: -moz-zoom-in;" src="convertImg.php?width=1800&thumb=false&id=<?php echo $pict["id"] ?>" />
                        </a>
                    <?php } else {?>
                        <div style="vertical-align: top; margin:10px" >
                            <img class="img-responsive" src="convertImg.php?width=80&thumb=true&id=<?php echo $pict["id"] ?>" />
                        </div>
                    <?php } ?>
                    <?php  if (userIsAdmin() || userIsSuperuser()) {?>
                        <a href="history.php?table=picture&id=<?php echo $pict["id"]?>" title="módosítások" style="position: relative;bottom:35px;left: 17px;display:inline-block;">
                            <span class="badge"><?php echo sizeof($db->getHistoryInfo("picture",$pict["id"]))?></span>
                        </a>
                    <?php }?>
                </div>
                <div  id="list-table" >
                    <div id="edit_<?php echo $pict["id"] ?>" style="width:auto;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;cursor:default;display: none" >
                        <input type="text" class="iledittitle" id="titleEdit_<?php echo $pict["id"] ?>" value="<?php echo $pict["title"] ?>" placeholder="A kép címe" style="width: 320px;"/><br/>
                        <textarea class="ileditcomment" id="commentEdit_<?php echo $pict["id"] ?>"  placeholder="Írj egy pár sort a kép tartarmáról." >
<?php echo $pict["comment"] ?></textarea>
                        <div >
                            <?php if (userIsLoggedOn()) { ?>
                                <span  class="ilbutton ilbuttonworld" ><input <?php echo $checked ?> type="checkbox"  onchange="changeVisibility(<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></span >
                            <?php }?>
                            <button class="btn btn-default"  title="Kimenti a kép módosításait" onclick="savePicture(<?php echo $pict["id"] ?>,this);return false;"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
                            <?php if ($pict["isDeleted"]!=1) { ?>
                                <button class="btn btn-default" title="Képet töröl" onclick="deletePicture(<?php echo $pict["id"] ?>);return false;"><span class="glyphicon glyphicon-remove-circle"></span> Töröl</button>
                            <?php } ?>
                            <?php if (userIsAdmin()) { ?>
                                <button class="btn btn-danger" title="Végleges törölés" onclick="unlinkPicture(<?php echo $pict["id"] ?>);return false;"><img src="images/delete.gif" /> Végleges</button>
                            <?php }?>
                            <?php if (userIsAdmin() || userIsEditor() ||userIsSuperuser() || userIsEditor()) { ?>
                                <select id="changeAlbum<?php echo $pict["id"] ?>" name="album" class="form-control inline" title="Áthelyezi egy másik abumba">
                                    <?php foreach ($albumList as $alb) {?>
                                        <?php if ($alb["albumName"]!=$albumParam && $alb["albumName"]!="_tablo_" ) { ?>
                                            <option value="<?php echo $alb["albumName"]?>"><?php echo $alb["albumText"]?></option>
                                        <?php }?>
                                    <?php }?>
                                </select>
                                <button onclick="changePictureAlbum(<?php echo $pict["id"]?>);return false;" class="btn btn-default inline">Áthelyezz!</button>
                            <?php }?>
                            <button onclick="hideedit(<?php echo $pict["id"] ?>);return false;" class="btn btn-default"><span class="glyphicon glyphicon-chevron-up"></span></button>
                        </div>
                    </div>
                </div>
                <div  id="list-table" >
                    <div id="show_<?php echo $pict["id"] ?>" style="width:auto;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;cursor:default;" >
                        <?php //change Order buttons?>
                        <?php if($view!="table" && ( userIsAdmin() || userIsEditor() || userIsSuperuser()) ) :?>
                            <?php  if ($idx!=0) {?>
                                <button id="picsort" style="margin: 0px 5px 0 10px;" class="btn btn-default" onclick="changeOrder(<?php echo $pict["id"] ?>,<?php echo $pictures[$idx-1]["id"] ?>);return false;" title="eggyel előrébb"><span class="glyphicon glyphicon-arrow-up"></span></button>
                            <?php } else {?>
                                <span style="margin: 0px 40px 0 10px;" >&nbsp;</span>
                            <?php } if ($idx+1<sizeof($pictures)) {?>
                                <button id="picsort" style="margin: 0px 10px 0 5px;" class="btn btn-default" onclick="changeOrder(<?php echo $pictures[$idx+1]["id"] ?>,<?php echo $pictures[$idx]["id"] ?>);return false;" title="eggyel hátrébb"><span class="glyphicon glyphicon-arrow-down"></span></button>
                            <?php } else {?>
                                <span style="margin: 0px 5px 0 40px;" >&nbsp;</span>
                            <?php } ?>
                        <?php endif;?>
                        <div id="text_<?php echo $pict["id"] ?>" style="display: inline-block;margin: 10px 0px 0 5px;width: 320px;">
                            <b><span id="titleShow_<?php echo $pict["id"] ?>"><?php echo $pict["title"] ?></span></b><br/>
                            <span id="commentShow_<?php echo $pict["id"] ?>"><?php echo createLink($pict["comment"],true) ?></span>
                        </div>
                        <button style="display: inline-block;margin: 0px 10px 10px 10px;" class="btn btn-default" onclick="displayedit(<?php echo $pict["id"] ?>);return false;"><span class="glyphicon glyphicon-pencil"></span></button>
                        <?php displayPictureOpinion($db,$pict["id"]); ?>
                        <?php  if (userIsAdmin()){?>
                            <div style="margin-top: 5px;">
                                <button style="display: inline-block;margin: 0px 10px 0 10px;" class="btn btn-warning" name="overwriteFileName" value="<?php echo $pict["file"]?>"><span class="glyphicon glyphicon-upload"></span> Kicserél</button>
                                <a class="btn btn-default" target="_download" href="<?php echo $pict['file']?>" title="ImageName"><span class="glyphicon glyphicon-download"></span> Letölt</a>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (!userIsLoggedOn() && $pict["isVisibleForAll"]==0) { ?>
                        <br/><span  class="iluser" title="Csak bejelnkezett felhasználok látják ezt a képet élesen.">Ez a kép védve van!</span >
                    <?php } ?>
                </div>
                <?php if ($view!="table" && userIsAdmin()) {?>
                    <div  id="list-table" >
                        <div style="margin:10px;">
                            id=<?php echo $pict["id"]?>
                            orderValue=<?php echo $pict["orderValue"]?><br/>
                            filename=<?php echo $pict["file"]?><br/>
                            uploaded=<?php echo \maierlabs\lpfw\Appl::dateTimeAsStr($pict["uploadDate"]);?><br/>
                            changed=<?php echo \maierlabs\lpfw\Appl::dateTimeAsStr($pict["changeDate"]);?><br/>
                            user=<?php echo '('.$pict["changeUserID"].') '.getPersonName($db->getPersonByID($pict["changeUserID"]))?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php
        }
    }
}

Appl::addJsScript("
    var picturesStart=1;
    var picturesButton='';
    function showmore(date) {
        picturesButton=$('#buttonmore').html();
        $('#buttonmore').html('Pillanat...<img src=\"images/loading.gif\" />');
        $.ajax({
    		url:'".$link."'+picturesStart++,
	    	type:'GET',
    		success:function(data){
	    	    $('#more').replaceWith(data+'<span id=\"more\"></span>');
	    	    if(data.length>200) {
	    	        $('#buttonmore').html(picturesButton);
	    	    } else {
	    	        $('#buttonmore').html('Több kép nincs');
	    	    }
		    },
		    error:function(error) {
		        showMessage('Több bejegyzés nincs!');
		        $('#buttonmore').html(picturesButton);
		    }
        });
        return false;
    }
");

?>


<script type="text/javascript">


function savePicture(id) {
	var t = $('#titleEdit_'+id).val();
	var c = $('#commentEdit_'+id).val();
	$('#titleShow_'+id).html(t);
	$('#commentShow_'+id).html(c);
    showWaitMessage();
    if (id>0) {
		$.ajax({
			url:encodeURI("ajax/setPictureTitle.php?id="+id+"&title="+t+"&comment="+c),
			type:"GET",
			dataType: 'json',
			success:function(data){
			    clearModalMessage();
				hideedit(id);
			},
            error:function() {

            }
		});
	}
}

function changeVisibility(id) {
	var c = $('#visibility'+id).prop('checked')?1:0;
	$.ajax({
		url:"ajax/setPictureVisibility.php?id="+id+"&attr="+c,
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
		window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=deletePicture&did="+id+"&tabOpen=<?php echo(getParam("tabOpen","pictures"))?>&type=<?php echo $type?>&typeid=<?php echo $typeId?>&album=<?php echo getParam("album")?>";
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

function changePictureAlbum(id) {
 	var url = 'pictureid='+id;
 	url +='&tabOpen=<?php echo getParam('tabOpen',0)?>';
 	var a="<?php echo getParam('type','')?>";
 	if (a!='') url +='&type='+a;
 	a="<?php echo getParam('typeid','')?>";
 	if (a!='') url +='&typeid='+a;
 	url +='&album='+$("#changeAlbum"+id).val();
 	url +='&action=changePictureAlbum';
    window.location.href="<?php echo $_SERVER["PHP_SELF"].'?'?>"+url;
}

function hideedit(id) {
	$("#edit_"+id).hide();
	$("#show_"+id).show();
	return false;
}

function displayedit(id) {
    $("#show_"+id).hide();
	$("#edit_"+id).show();
	return false;
}

<?php if (userIsAdmin()) :?>
    function unlinkPicture(id) {
        if (confirm("Fénykép végleges törölését kérem konfirmálni!")) {
            window.location.href="<?php echo $_SERVER["PHP_SELF"]?>?action=unlinkPicture&did="+id+'&tabOpen=<?php echo(getParam("tabOpen","pictures"))?>&type=<?php echo $type?>&typeid=<?php echo $typeId?>&album=<?php echo getParam("album")?>';
        }
    }
<?php endif;?>
</script>
