<?php
include_once 'lpfw/sessionManager.php';
include_once 'lpfw/userManager.php';
include_once 'lpfw/ltools.php';
include_once 'lpfw/appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaOpinion.class.php';
include_once 'displayOpinion.inc.php';

use \maierlabs\lpfw\Appl as Appl;
\maierlabs\lpfw\Appl::addCss('css/picture.css',true);

//Delete Picture
if (getParam("action","")=="deletePicture" ) {
	if ($db->getCountOfRequest(changeType::deletepicture,24)<5) {
		if ($db->deletePicture(getIntParam("did"))>=0) {
		    $db->updateRecentChangesList();
            Appl::setMessage("Kép sikeresen törölve","success");
			$db->saveRequest(changeType::deletepicture);
			\maierlabs\lpfw\Logger::_("PictureDelete\t".getLoggedInUserId()."\t".getIntParam("did"));
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
				if ($_FILES['userfile']['size']<3800000) {
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
                                \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\t".$idx);
							} else {
                                Appl::setMessage($fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.","warning");
							}
						} else {
                            Appl::setMessage($fileName[0].".".$fileName[1]." sikeresen feltöltve és felülírva.","success");
						}
					} else {
                        Appl::setMessage($fileName[0].".".$fileName[1]." feltötése sikertelen. Probálkozz újra.","warning");
                        \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\tError: moving picture",\maierlabs\lpfw\LoggerLevel::error);
					}
				}
				else {
                    Appl::setMessage($fileName[0].".".$fileName[1]." A kép file nagysága túlhaladja 3 MByteot.","warning");
                    \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\tError: too big",\maierlabs\lpfw\LoggerLevel::error);
				}
			}
			else {
                Appl::setMessage($fileName[0].".".$fileName[1]." Csak jpg formátumban lehet képeket feltölteni.","warning");
                \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\tError: only jpg",\maierlabs\lpfw\LoggerLevel::error);
			}
		}
	} else {
        Appl::setMessage("A feltöltött képek száma meghaladta a naponta megengedett határt!","warning");
	}
}

//View 
$albumParam = getParam("album","");
$view=getParam("view","table");
$sort=getParam("sort",(getParam("type")=='schoolID'?"alphabet-desc":"order-desc"));
$sortOrder=(strpos($sort,"order")!==false?"secundary":"default");
$sortAlphabet=(strpos($sort,"alphabet")!==false?"secundary":"default");
$sortDate=(strpos($sort,"date")!==false?"secundary":"default");
$alt=(strpos($sort,"desc")!==false?"-alt":"");
$desc=(strpos($sort,"desc")!==false?"":"-desc");
$limit=24;
$offset=getIntParam("start",0)*$limit;
$link="picture.inc.php?action=showmore&view=".$view."&typeid=".getParam("type")."&type=".getParam("typeid")."&album=".getParam("album")."&sort=".$sort."&start=";
if ($view=="table") {
    Appl::addCssStyle('.pictureframe {padding-bottom: 5px;max-width:395px;background-color: #dddddd;border-radius:10px;display:inline-block;vertical-align: top; margin: 0 10px 10px 0;}');
} else {
    Appl::addCssStyle('.pictureframe {padding-bottom: 5px;width:100%;background-color: #dddddd;border-radius:10px;display:inline-flex;vertical-align: top; margin-bottom: 10px;}');
}
//The list of pictures
$notDeletedPictures=0;
if(isset($picture)) {
	$pictures = array($picture);	//Only one picture => convert to array
	$notDeletedPictures=1;
} else {
    if (strpos($sort,"order")!==false)  $sortSql="orderValue";
    if (strpos($sort,"alphabet")!==false)  $sortSql="title";
    if (strpos($sort,"date")!==false)  $sortSql="uploadDate";
    if (strpos($sort,"desc")!==false)  $sortSql .=" desc";
	if ($albumParam=="_tablo_") {
	    $where="classID is not null and (title like '%Tabló%' or title like '%tabló%') ";
        $pictures = $db->getListOfPicturesWhere($where,$sortSql, $limit, $offset );
        $countPictures = $db->getTableCount("picture",$where);
    } elseif ($albumParam=="_mark_") {
	    $where = "id in (select pictureID from personInPicture where personID=".$typeId.") ";
        $pictures = $db->getListOfPicturesWhere($where, $sortSql, $limit, $offset);
        $countPictures = $db->getTableCount("picture",$where);
    } elseif ($albumParam=="_card_") {
        $where = "classID is not null and (title like '%icsengetési%') ";
        $pictures = $db->getListOfPicturesWhere($where, $sortSql, $limit, $offset);
        $countPictures = $db->getTableCount("picture",$where);
    } else {
	    if (!isset($typeId)) $typeId=getParam("typeid");
	    if (!isset($type)) $type=getParam("type");
        $pictures = $db->getListOfPictures($typeId, $type,  $albumParam, $sortSql, $limit,$offset);
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
$startAlbumList=array(array("albumName"=>"","albumText"=>Appl::__("Főalbum")));
if (getParam("type")=="schoolID") {
	$startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"picture.php?type=schoolID&typeid=".getParam("typeid")."&album=_tablo_","albumText"=>Appl::__("Tablók"),"albumName"=>"_tablo_")));
    $startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"picture.php?type=schoolID&typeid=".getParam("typeid")."&album=_card_","albumText"=>Appl::__("Kicsengetési kártyák"),"albumName"=>"_card_")));
}
if (getParam("type")=="personID" || getParam("tabOpen")=="pictures")  {
    $startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"editDiak.php?type=personID&typeid=".getParam("typeid")."&album=_mark_","albumText"=>Appl::__("Megjelölések"),"albumName"=>"_mark_")));
}
$albumList = $db->getListOfAlbum($type, $typeId, $startAlbumList);

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
	<div class="well"><?php
        foreach ($albumList as $alb) {
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
					<input name="album" class="form-control" placeholder=<?php Appl::__("Album címe")?>/><br/>
					<button class="btn btn-default" style="margin-top: -15px;"><?php Appl::_("Új albumot létrehoz")?></button>
				</form>
			</div>
		<?php } ?>
		<?php if ($albumParam!="" && substr($albumParam,0,1)!="_" && 	!$newAlbum && (userIsAdmin() || userIsSuperuser() || userIsEditor()) ) {?>
			<div style="display:inline-block">
				<form action="<?php echo $_SERVER["PHP_SELF"]?>" method="post">
					<?php if (getParam("tabOpen")!=null) {?>
						<input name="tabOpen" type="hidden" value="<?php echo getParam('tabOpen')?>" /> 
					<?php } ?>
					<input name="oldAlbum" type="hidden" value="<?php echo getParam('album')?>" />
					<input name="album" class="form-control" value="<?php echo getParam('album')?>"/><br/>
					<button name="action" value="renameAlbum" class="btn btn-default" style="margin-top: -15px;"><?php Appl::_("Albumot átnevez")?></button>
				</form>
			</div>
		<?php }?>
	</div>
	
	<form enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]?>" method="post">
        <input type="hidden" name="album" value="<?php echo getParam("album","")?>"/>
	<?php if (($notDeletedPictures<50 || userIsAdmin()) && substr($albumParam,0,1)!="_" && $albumParam!="_card_") {?>
		<div style="margin-bottom:15px;">
			<button class="btn btn-info" onclick="$('#download').slideDown();return false;"><span class="glyphicon glyphicon-cloud-upload"> </span> <?php Appl::_("Kép feltöltése")?></button>
            <button class="btn btn-default" onclick="return toogleListBlock();"><span class="glyphicon glyphicon-eye-open"> </span> <?php Appl::_("Lista/Album")?></button>
			<?php if(isset($picture)) { ?>
				<button class="btn btn-default" onclick="window.location.href=<?php echo "'".$_SERVER["PHP_SELF"].'?sort='.$sort.'&type='.$type.'&typeid='.$typeId.'&album='.$picture["albumName"]."'; return false;"?>" ><span class="glyphicon glyphicon-hand-right"> </span> <?php Appl::_("Mutasd a többi képet")?></button>
			<?php  } else { ?>
                <button class="btn btn-<?php echo $sortOrder ?>" onclick="return sortPictures('order<?php echo $desc ?>')" title="<?php Appl::_("Beálított sorrend")?>"><span class="glyphicon glyphicon-sort-by-order<?php echo $alt ?>"> </span></button>
                <button class="btn btn-<?php echo $sortAlphabet ?>" onclick="return sortPictures('alphabet<?php echo $desc ?>')" title="<?php Appl::_("ABC szerint")?>"><span class="glyphicon glyphicon-sort-by-alphabet<?php echo $alt ?>"> </span></button>
                <button class="btn btn-<?php echo $sortDate ?>" onclick="return sortPictures('date<?php echo $desc ?>')" title="<?php Appl::_("Dátum szerint")?>"><span class="glyphicon glyphicon-sort-by-attributes<?php echo $alt ?>"> </span></button>
            <?php  } ?>
        </div>
		<div id="download" style="margin:15px;display:none;">
			<div><?php Appl::_("Bővitsd a véndiákok oldalát képekkel! Válsszd ki a privát fényképid közül azokat az értékes felvételeket amelyeknek mindenki örvend ha látja.")?><span></span></div>
			<span style="display: inline-block;"><?php Appl::_("Válassz egy jpg képet max. 3MByte")?></span>
			<span style="display: inline-block;"><input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" /></span>	
			<span style="display: inline-block;"><button class="btn btn-default"  type="submit" onclick="showWaitMessage();"><span class="glyphicon glyphicon-upload"> </span> <?php Appl::_("feltölt")?></button></span>
			<span style="display: inline-block;"><button class="btn btn-default"  onclick="$('#download').slideUp();return false;" ><span class="glyphicon glyphicon-remove-circle"> </span> <?php Appl::_("mégsem")?></button></span>
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
		<div class="alert alert-warning" ><?php Appl::_("Jelenleg nincsenek képek feltöltve. Légy te az első aki képpet tőlt fel!")?></div>
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
	<?php displayPictureList($db,$pictures,$albumList,$albumParam,$view); ?>
    <span id="more"></span>
</form>
<?php if (getParam("id")==null) {?>
    <button id="buttonmore" class="btn btn-success" style="margin:10px;" onclick="return showmore()"><?php Appl::_("Többet szeretnék látni")?></button>
<?php }?>

<?php \maierlabs\lpfw\Appl::addCssStyle('
    .pdialog {width: 90%;}
    @media only screen and (max-width: 700px){
        .pdialog {
            width: 100%;
        }
    }
');
?>

<!-- Modal -->
<div class="modal" id="pictureModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered pdialog" style="background-color: white;border-radius: 7px;">
        <div class="modal-content" style="position: relative;padding: 5px;">
            <img class="img-responsive" id="thePicture" title="" style="position: relative; min-height: 100px;min-width: 100px" onmousedown="newPerson(event);"/>
            <div style="position: absolute; top: 10px; left:10px;">
                <button title="<?php Appl::_("Bezár")?>" class="pbtn" id="modal-close" data-dismiss="modal"><span class="glyphicon glyphicon-remove-circle"></span></button>
                <button title="<?php Appl::_("Arc felismerés")?>" class="pbtn" onclick="return showFaceRecognition();"><span class="glyphicon glyphicon-user"></span></button>
                <button title="<?php Appl::_("Jelölések")?>" class="pbtn" onmouseover="personShowAll(true);" onmouseout="personShowAll(false);"><span class="glyphicon glyphicon-eye-open"></span></button>
            </div>
        </div>
        <div id="personlist"></div>
    </div>
</div>






<?php

\maierlabs\lpfw\Appl::addCssStyle('
    .pbtn {background-color: white;box-shadow: 0 0 14px 3px black;border-radius: 20px;outline: none;border: none;min-width:35px;height:24px;font-weight: bold;display:inline-block;vertical-align: middle;text-align: center;}
    .pdiv {position: absolute;top: 10px;left: 10px;display:none}
    .pbtn:active .sbtn:active{ outline: none;border: none;}
    .pbtn:hover {background-color: lightgrey;}
    .ibtn:hover + .pdiv, .pdiv:hover {display:inline-block;}
    .face                       {position: absolute; border: 2px solid #ec971f;box-shadow: 1px 1px 1px 0px black; opacity:0;z-index:300;}
    .recognition                {position: absolute; border: 2px solid #ff2020;box-shadow: 1px 1px 1px 0px black;z-index:200;}
    .newperson, .personmodify   {position: absolute; border: 2px solid #ff2020;box-shadow: 1px 1px 1px 0px black;border-radius:10px;}
    .face:hover , .facename:hover {display:inline-block; opacity:1;}
    .facename {position:absolute;background-color: white;opacity: 0.7;font-size: 10px;padding:2px;border-radius:3px;color:black;opacity:0;}
    .personlist {margin:3px;padding:3px}
    .personlist:hover {background-color:lightgray;}
    #personlist{padding:5px;}
    tr:hover {background-color:floralwhite;}
    td {padding:4px}
    
    .personsearch {position:absolute; background-color:lightgray;width:280px;padding:5px;border-radius: 5px;box-shadow: 1px 1px 12px 3px black;z-index:500;}
');

function displayPictureList($db,$pictures,$albumList,$albumParam,$view) {
    if (sizeof($pictures)==0) {
        \maierlabs\lpfw\Appl::addJsScript('$("#buttonmore").hide();');
    }
    foreach ($pictures as $idx=>$pict) {
        if ( $pict["isDeleted"]==0  || userIsAdmin() ) {
            ?><div class="pictureframe" <?php echo $pict["isDeleted"]==1?'style="background-color: #ffbcac;"':'' ?>><?php
            displayPicture($db,$pictures,$idx,$albumList,$albumParam,$view);

            if ( $albumParam=="_mark_") {
                ?>
                <div style="position: relative; bottom:95px;right:-295px;z-index: 10;height: 0px;">
                <img style="box-shadow: 2px 2px 17px 6px black;border-radius:60px; " src="imageTaggedPerson.php?pictureid=<?php echo $pict["id"] ?>&personid=<?php echo getParam("typeid") ?>&size=90&padding=90"/>
                </div><?php
            }
            ?></div><?php
        }
    }
}

function  displayPicture($db,$pictures,$idx,$albumList,$albumParam,$view) {
    $pict=$pictures[$idx];
    $checked= ($pict["isVisibleForAll"]==1)?"checked":"";
    $dbOpinion = new dbDaOpinion($db); ?>

    <div id="list-table">

        <?php if ($view=="table") {?>
            <img class="img-responsive ibtn" data-id="<?php echo $pict["id"] ?>"  style="min-height:100px;position: relative;" src="imageConvert.php?id=<?php echo $pict["id"] ?>" />
            <div class="pdiv">
                <button title="Nagyít" class="pbtn" onclick="return pictureModal(this,'<?php echo $pict["file"] ?>',<?php echo $pict["id"] ?>);" ><span class="glyphicon glyphicon-search"></span></button>
                <button title="Módosít" class="pbtn" onclick="return displayedit(<?php echo $pict["id"] ?>);" ><span class="glyphicon glyphicon-pencil"></span></button><?php
                if (userIsAdmin()){?>
                    <button title="Kicserél" class="pbtn" name="overwriteFileName" value="<?php echo $pict["file"]?>"><span class="glyphicon glyphicon-refresh"></span></button>
                <a title="Letölt" class="pbtn " target="_download" href="<?php echo $pict['file']?>" ><span style="vertical-align: middle;" class="glyphicon glyphicon-download-alt"></span></a><?php
                } ?>
            </div>
            <span id="imgspan<?php echo $pict["id"] ?>" style="display: none"></span>
        <?php } else {?>
            <div style="vertical-align: top; margin:10px" >
                <img class="img-responsive" src="imageConvert.php?width=80&thumb=true&id=<?php echo $pict["id"] ?>" />
            </div>
        <?php } ?>

        <?php  if (userIsAdmin() || userIsSuperuser()) {?>
            <a href="history.php?table=picture&id=<?php echo $pict["id"]?>" title="módosítások" style="position: absolute;bottom:28px;left: 10px;">
                <span class="badge"><?php echo sizeof($db->getHistoryInfo("picture",$pict["id"]))?></span>
            </a>
        <?php }?>
    </div>

    <div  id="" >
        <div id="edit_<?php echo $pict["id"] ?>" style="width:auto;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;cursor:default;margin: 0 10px 0 10px;display: none" >
            <input type="text" class="iledittitle" id="titleEdit_<?php echo $pict["id"] ?>" value="<?php echo $pict["title"] ?>" placeholder="A kép címe" style="width: 320px;"/><br/>
            <textarea class="ileditcomment" id="commentEdit_<?php echo $pict["id"] ?>"  placeholder="Írj egy pár sort a kép tartarmáról." >
<?php echo $pict["comment"] ?></textarea>
            <div >
                <?php if (userIsLoggedOn()) { ?>
                    <span  class="ilbutton ilbuttonworld" ><input <?php echo $checked ?> type="checkbox"  onchange="changeVisibility(<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></span >
                <?php }?>
                <button class="btn btn-default"  title="Kimenti a kép módosításait" onclick="return savePicture(<?php echo $pict["id"] ?>);"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
                <?php if ($pict["isDeleted"]!=1) { ?>
                    <button class="btn btn-default" title="Képet töröl" onclick="deletePicture(<?php echo $pict["id"] ?>);return false;"><span class="glyphicon glyphicon-remove-circle"></span> Töröl</button>
                <?php } ?>
                <?php if (userIsAdmin()) { ?>
                    <button class="btn btn-danger" title="Végleges törölés" onclick="unlinkPicture(<?php echo $pict["id"] ?>);return false;"><img src="images/delete.gif" /> Végleges</button>
                <?php }?>
                <?php if (userIsAdmin() || userIsEditor() ||userIsSuperuser() || userIsEditor()) { ?>
                    <select id="changeAlbum<?php echo $pict["id"] ?>" name="album" class="form-control inline" title="Áthelyezi egy másik abumba" style="margin-top: 5px">
                        <?php foreach ($albumList as $alb) {?>
                            <?php if ($alb["albumName"]!=$albumParam && substr($alb["albumName"],0,1)!="_" ) { ?>
                                <option value="<?php echo $alb["albumName"]?>"><?php echo $alb["albumText"]?></option>
                            <?php }?>
                        <?php }?>
                    </select>
                    <button onclick="changePictureAlbum(<?php echo $pict["id"]?>);return false;" class="btn btn-default inline" style="margin-top: 5px;">Áthelyezz!</button>
                <?php }?>
                <button onclick="hideedit(<?php echo $pict["id"] ?>);return false;" class="btn btn-default" style="margin-top: 5px;"><span style="color:red;" class="glyphicon glyphicon-remove-circle"></span></button>
            </div>
        </div>
    </div>

    <div  id="" >
        <div id="show_<?php echo $pict["id"] ?>" style="width:auto;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;margin: 10px 10px 0 10px;cursor:default;" >
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
            </div><?php
            displayPictureOpinion($dbOpinion,$pict["id"]);?>
        </div>
        <?php if (!userIsLoggedOn() && $pict["isVisibleForAll"]==0) { ?>
            <br/><span  class="iluser" title="<?php Appl::_("Csak bejelnkezett felhasználok látják ezt a képet élesen.")?>"><?php Appl::_("Ez a kép védve van!")?></span >
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
    <?php }
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
	    	        $('#buttonmore').html('".Appl::__("Több kép nincs!")."');
	    	    }
		    },
		    error:function(error) {
		        showMessage('".Appl::__("Több kép nincs!")."');
		        $('#buttonmore').html(picturesButton);
		    }
        });
        return false;
    }
");


\maierlabs\lpfw\Appl::addJs("js/picture.js",true);
\maierlabs\lpfw\Appl::addJs("js/jquery.facedetection.js");

\maierlabs\lpfw\Appl::addJsScript('
    function toogleListBlock() {
        var url = "'.$view.'"=="table"?"view=list":"view=table";
        url +="'.(isset($tabOpen)?"&tabOpen=".$tabOpen:"").'";
        url +="'.(isset($type)?"&type=".$type:"").'";
        url +="'.(isset($typeId)?"&typeid=".$typeId:"").'";
        url +="&sort='.$sort.'";
        url +="'.(null!=getParam("album")?"&album=".getParam("album"):"").'";
        window.location.href="'.$_SERVER["PHP_SELF"].'?"+url;
        return false;
    }

    function sortPictures(sort) {
        var url = "view='.$view.'";
        url +="'.(isset($tabOpen)?"&tabOpen=".$tabOpen:"").'";
        url +="'.(isset($type)?"&type=".$type:"").'";
        url +="'.(isset($typeId)?"&typeid=".$typeId:"").'";
        url +="&sort="+sort;
        url +="'.(null!=getParam("album")?"&album=".getParam("album"):"").'";
        window.location.href="'.$_SERVER["PHP_SELF"].'?"+url;
        return false;
    }


    function changeOrder(id1,id2) {
        var url = "'.($view=="table"?"view=table":"view=list").'";
        url +="'.(isset($tabOpen)?"&tabOpen=".$tabOpen:"").'";
        url +="'.(isset($type)?"&type=".$type:"").'";
        url +="'.(isset($typeId)?"&typeid=".$typeId:"").'";
        url +="'.(null!=getParam("album")?"&album=".getParam("album"):"").'";
        url +="'.(isset($id)?"&id=".$id:"").'";
        url +="&action=changeOrder";
        window.location.href="'.$_SERVER["PHP_SELF"].'?"+url+"&id1="+id1+"&id2="+id2;
    }
    
    function changePictureAlbum(id) {
        var url = "?pictureid="+id;
        url +="&tabOpen='.getParam('tabOpen',0).'";
        var a="'.getParam('type','').'";
        if (a!="") url +="&type="+a;
        a="'.getParam('typeid','').'";
        if (a!="") url +="&typeid="+a;
        url +="&album="+$("#changeAlbum"+id).val();
        url +="&action=changePictureAlbum";
        window.location.href="'.$_SERVER["PHP_SELF"].'"+url;
    }

    function deletePicture(id) {
        if (confirm("'.Appl::__("Fénykép végleges törölését kérem konfirmálni!").'")) {
            showWaitMessage();
            window.location.href="'.$_SERVER["PHP_SELF"].'?action=deletePicture&did="+id+"&tabOpen='.getParam("tabOpen","pictures").'&type='.$type.'&typeid='.$typeId.'&album='.getParam("album").'";
        }
    }
');
if (userIsAdmin()) {
    \maierlabs\lpfw\Appl::addJsScript('
        function unlinkPicture(id) {
            if (confirm("'.Appl::__("Fénykép törölését kérem konfirmálni!").'")) {
                showWaitMessage();
                window.location.href = "'.$_SERVER["PHP_SELF"].'?action=unlinkPicture&did=" + id + "&tabOpen='.getParam("tabOpen","pictures").'&type='.$type.'&typeid='.$typeId.'&album='.getParam("album").'";
            }
        }
    ');
}

