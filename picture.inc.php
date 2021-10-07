<?php

include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaOpinion.class.php';
include_once 'displayOpinion.inc.php';

use \maierlabs\lpfw\Appl as Appl;
global $db;
$limitOfPicturesPerPage=24;
if (!isset($type)) $type=getParam("type");
if (!isset($typeId)) $typeId=getParam("typeid");

//Delete picture
if (getParam("action","")=="deletePicture" ) {
	if ($db->getCountOfRequest(changeType::deletepicture,24)<5) {
		if ($db->deletePicture(getIntParam("did"))>=0) {
		    $db->updateRecentChangesList();
            Appl::setMessage("Kép sikeresen törölve. Köszönjük szépen.","success");
			$db->saveRequest(changeType::deletepicture);
			\maierlabs\lpfw\Logger::_("PictureDelete\t".getLoggedInUserId()."\t".getIntParam("did"));
		} else {
            Appl::setMessage("Kép törlése sikertelen!","warning");
		}
	} else {
        Appl::setMessage("Anonim felhasználó jogai nem elegendők a kivánt művelet végrehajtására!","warning");
	}
}

//Change picture order
if (getParam("action","")=="changeOrder" && (isUserSuperuser() || isUserEditor())  )  {
	$db->changePictureOrderValues(getIntParam("id1", -1), getIntParam("id2", -1));
}

//Change picture albumname
if (isActionParam("changePictureAlbum") && (isUserSuperuser() || isUserEditor())  )  {
	if ($db->changePictureAlbumName(getIntParam("pictureid", -1), getParam("album", ""))) {
        Appl::setMessage("Kép sikeresen áthelyezve. Köszönjük szépen.","success");
	} else {
        Appl::setMessage("Kép éthelyezése sikertelen","warning");
	}
}

//Change albumname
if (isActionParam("renameAlbum") && (isUserSuperuser() || isUserEditor())  )  {
	if ($db->changeAlbumName($type, $typeId, getParam("oldAlbum", ""), getParam("album", ""))) {
        Appl::setMessage(" Album sikeresen étnevezve. Köszönjük szépen.","success");
	} else {
        Appl::setMessage("Album átnevezése sikertelen","warning");
	}
}

//Delete and unlink picture
if (isActionParam("unlinkPicture") && (isUserSuperuser()) )  {
	if ($db->deletePicture(getIntParam("did"),true)>=0) {
	    $db->updateRecentChangesList();
        Appl::setMessage("Kép sikeresen véglegesen törölve","success");
	} else {
        Appl::setMessage("Kép végleges törlése sikertelen!","warning");
	}
}

//Delete and not unlink picture
if (isActionParam("notUnlinkPicture") && (isUserSuperuser()) )  {
    if ($db->notUnlinkPicture(getIntParam("did"))) {
        $db->updateRecentChangesList();
        Appl::setMessage("Kép törlése sikeresen vissza állítva","success");
    } else {
        Appl::setMessage("Kép törlésének vissza állítása sikertelen!","warning");
    }
}

//Upload image
if (isset($_POST["action"]) && ($_POST["action"]=="upload")) {
	if ($db->checkRequesterIP(changeType::classupload)) {
		if (basename( $_FILES['userfile']['name'])!="") {
			$fileName = explode( ".", basename( $_FILES['userfile']['name']));
			$idx=$db->getNextPictureId();
	
			if (isUserAdmin() && null!=getParam("overwriteFileName")) {
				//Overwrite an existing file
				$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/".getParam("overwriteFileName");
				unlink($uploadfile);
				$overwrite=true;
			} else {
				//Create folder is doesn't exists
				$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/".$db->getActClassFolder();
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
                            $upicture["schoolID"] = getActSchoolId();
							$upicture[$type]=$typeId;
							$upicture["file"]="images/".$db->getActClassFolder().$pFileName;
							$upicture["isVisibleForAll"]=1;
							$upicture["isDeleted"]=0;
							if (null!=getParam("album") && getParam("album")!="") {
								$upicture["albumName"]=getParam("album");
							}
							$upicture["uploadDate"]=date("Y-m-d H:i:s");
							if ($db->savePicture($upicture)>=0) {
								$db->saveRequest(changeType::classupload);
								resizeImage($uploadfile,1800,1800);
                                Appl::setMessage($fileName[0].".".$fileName[1]." Köszönjük szépen a kép feltöltését.","success");
                                \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\t".$idx);
							} else {
                                Appl::setMessage($fileName[0].".".$fileName[1]." Köszönjük szépen a kép feltöltését. Sajnos a feltötés sikertelen volt. Probálkozz újra.","warning");
							}
						} else {
                            Appl::setMessage($fileName[0].".".$fileName[1]." Köszönjük szépen a kép feltöltését.","success");
						}
					} else {
                        Appl::setMessage($fileName[0].".".$fileName[1]." Köszönjük szépen a kép feltöltését. Sajnos a feltötés sikertelen. Probálkozz újra.","warning");
                        \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\tError: moving picture",\maierlabs\lpfw\LoggerLevel::error);
					}
				}
				else {
                    Appl::setMessage($fileName[0].".".$fileName[1]." Köszönjük szépen a kép feltöltését. Sajnos a kép adat nagysága túlhaladja 3 MByteot. Próbáld kissebb formátumban újból.","warning");
                    \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\tError: too big",\maierlabs\lpfw\LoggerLevel::error);
				}
			}
			else {
                Appl::setMessage($fileName[0].".".$fileName[1]." Köszönjük szépen a kép feltöltését. Sajnos csak jpg formátumban lehet képeket feltölteni. Próbáld a képet konvertálni és probáld újból.","warning");
                \maierlabs\lpfw\Logger::_("PictureUpload\t".getLoggedInUserId()."\tError: only jpg",\maierlabs\lpfw\LoggerLevel::error);
			}
		}
	} else {
        Appl::setMessage("Köszönjük szépen a képek feltöltését. Sajnos a feltöltött képek száma meghaladta az anonim látogatok számára naponta megengedett határt! Jentkezz be és folytasd a képfeltöltést.","warning");
	}
}

//View 
$albumParam = getParam("album","");
$view=getParam("view","table");

//Sort: default sort is the sort order by sordid in the database
$defaulSort="order-desc";
if (getParam("type")=='schoolID' && $albumParam!=="") {
    $defaulSort="alphabet-desc";
}
$sort=getParam("sort",$defaulSort);
$sortOrder=(strpos($sort,"order")!==false?"secundary":"default");
$sortAlphabet=(strpos($sort,"alphabet")!==false?"secundary":"default");
$sortDate=(strpos($sort,"date")!==false?"secundary":"default");
$alt=(strpos($sort,"desc")!==false?"-alt":"");
$desc=(strpos($sort,"desc")!==false?"":"-desc");
$link="picture.inc.php?view=".$view."&typeid=".getParam("type")."&type=".getParam("typeid")."&album=".getParam("album")."&sort=".$sort;
$url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'];

//Sortparamteter
if (strpos($sort,"order")!==false)  $sortSql="orderValue";
if (strpos($sort,"alphabet")!==false)  $sortSql="title";
if (strpos($sort,"date")!==false)  $sortSql="uploadDate";
if (strpos($sort,"desc")!==false)  $sortSql .=" desc";

//The list of pictures
$wherePictureList=' true ';
if (!isUserAdmin()) {
    $wherePictureList .=" and isDeleted<>'1'";
}
if ($albumParam=="_tablo_") {
    $wherePictureList .=" and tag like 'tabl%' and schoolID =".getActSchoolId();
} elseif ($albumParam=="_mark_") {
    $wherePictureList .= "and id in (select pictureID from personInPicture where personID=".$typeId.") ";
} elseif ($albumParam=="_card_") {
    $wherePictureList .= "and tag like 'kicsenget%' and schoolID =".getActSchoolId();
} elseif ($albumParam=="_sport_") {
    $wherePictureList .= "and tag like '%sport%' and schoolID =".getActSchoolId();
} else {
    $wherePictureList .= ' and '.$type.'='.$typeId;
    if ($type=="schoolID") {
        $wherePictureList .= " and personID is null and classID is null ";
        $wherePictureList .= " and (tag is null or tag ='') ";
    }
    if ($albumParam!=null) {
        $wherePictureList.=" and albumName='".$albumParam."'";
    } else {
        if (isset($picture) &&  isset($picture["albumName"])) {
            $wherePictureList.=" and albumName='".$picture["albumName"]."'";
        } else {
            $wherePictureList.=" and (albumName is null or albumName='')";
        }
    }
}
$pictures = $db->getListOfPicturesWhere($wherePictureList, $sortSql, $limitOfPicturesPerPage, getIntParam("start",0)*$limitOfPicturesPerPage);
$countPictures = $db->getTableCount("picture",$wherePictureList);

if (isActionParam("showmore")  ) {
    displayPictureList($db,$pictures,null,null,$view);
    return;
}

if ($view=="table") {
    Appl::addCssStyle('.pictureframe {padding-bottom: 5px;max-width:395px;background-color: #dddddd;border-radius:10px;display:inline-block;vertical-align: top; margin: 0 10px 10px 0;}');
} else {
    Appl::addCssStyle('.pictureframe {padding-bottom: 5px;width:100%;background-color: #dddddd;border-radius:10px;display:inline-flex;vertical-align: top; margin-bottom: 10px;}');
}
\maierlabs\lpfw\Appl::addCss('css/picture.css',true);
\maierlabs\lpfw\Appl::addCss('//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',false);
\maierlabs\lpfw\Appl::addJs('//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');

//Create standard albumlist
$startAlbumList=$db->getMainAlbumCount($type,$typeId,Appl::__("Főalbum"),getParam("type")=="schoolID");
if (getParam("type")=="schoolID") {
	$startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"picture?type=schoolID&typeid=".getParam("typeid")."&album=_tablo_","albumText"=>Appl::__("Tablók"),"albumName"=>"_tablo_","count"=>$db->getPictureTagCount("tabl"))));
    $startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"picture?type=schoolID&typeid=".getParam("typeid")."&album=_card_","albumText"=>Appl::__("Kicsengetési kártyák"),"albumName"=>"_card_","count"=>$db->getPictureTagCount("kicsengetési"))));
    $startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"picture?type=schoolID&typeid=".getParam("typeid")."&album=_sport_","albumText"=>Appl::__("Sportolók"),"albumName"=>"_sport_","count"=>$db->getPictureTagCount("sportolóink"))));
}
if (getParam("type")=="personID" || getParam("tabOpen")=="pictures")  {
    $countMark = $db->getPersonMarksCount($typeId);
    if( $countMark>0 ) {
        $startAlbumList=array_merge($startAlbumList,array(array("albumLink"=>"editDiak?type=personID&typeid=".getParam("typeid")."&album=_mark_","albumText"=>Appl::__("Megjelölések"),"albumName"=>"_mark_","count"=>$countMark)));
    }
}

if (!isActionParam("showmore") ) {
    $albumList = $db->getListOfAlbum($type, $typeId, $startAlbumList);
    //Check if a new album want to be created
    if (getParam("album") != null) {
        $newAlbum = true;
        foreach ($albumList as $alb) {
            if ($alb["albumName"] == getParam("album")) {
                $newAlbum = false;
                break;
            }
        }
        if ($newAlbum) {
            $albumList = array_merge($albumList, array(array("albumName" => getParam("album"), "albumText" => getParam("album"))));
        }
    }
}
?>
	<div class="well"><?php
        $serverPhpSelf = substr($_SERVER["PHP_SELF"],0,-4);
        //Albumlist
        foreach ($albumList as $alb) {
			if (isset($alb["albumName"])) {
				$albumLink = basename($_SERVER['SCRIPT_FILENAME'], ".php").".php?type=".$type."&typeid=".$typeId."&album=".$alb["albumName"].(getParam("tabOpen")!=null?"&tabOpen=".getParam("tabOpen"):"");
			} else { 
				$albumLink =$alb["albumLink"]; 
			}?> 
			<a href="<?php echo $albumLink?>" style="text-decoration:none !important;">
                <span class="badgep"><?php echo isset($alb["count"])?$alb["count"]:"0" ?></span>
                <?php if ($albumParam==$alb["albumName"]) {?>
				    <span class="folderdivwhite"><?php echo $alb["albumText"]?></span>
			    <?php } else {?>
    				<span class="folderdiv"><?php echo $alb["albumText"]?></span>
    			<?php } ?>
			</a>
		<?php }
        //New album
		if ( (isUserSuperuser() || isUserEditor()) ) {?>
			<div style="display:inline-block">
				<form action="<?php echo $serverPhpSelf?>" method="post">
					<?php if (getParam("tabOpen")!=null) {?>
						<input name="tabOpen" type="hidden" value="<?php echo getParam('tabOpen')?>" /> 
					<?php } ?>
					<?php if (getParam("type")!=null) {?>
						<input name="type" type="hidden" value="<?php echo getParam('type')?>" /> 
					<?php } ?>
					<?php if (getParam("typeid")!=null) {?>
						<input name="typeid" type="hidden" value="<?php echo getParam('typeid')?>" /> 
					<?php } ?>
					<input name="album" class="form-control" placeholder="<?php Appl::_("Az új album címe")?>"><br/>
					<button class="btn btn-default" style="margin-top: -15px;"><?php Appl::_("Új albumot létrehoz")?></button>
				</form>
			</div>
		<?php }
		//Rename album
		if ($albumParam!="" && substr($albumParam,0,1)!="_" && 	!$newAlbum && (isUserSuperuser() || isUserEditor()) ) {?>
			<div style="display:inline-block">
				<form action="<?php echo $serverPhpSelf?>" method="post">
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
    <?php
        $scriptArray=explode("/",$_SERVER["SCRIPT_NAME"]);
        $script=pathinfo($scriptArray[sizeof($scriptArray)-1],PATHINFO_FILENAME);
    ?>
	<form enctype="multipart/form-data" action="<?php echo $script?>" method="post">
        <input type="hidden" name="album" value="<?php echo getParam("album","")?>"/>
		<div style="margin-bottom:15px;">
            <?php if (substr($albumParam,0,1)!="_" && ($countPictures<50 || isUserAdmin())) {?>
                <button class="btn btn-info" onclick="$('#download').slideDown();return false;"><span class="glyphicon glyphicon-cloud-upload"> </span> <?php Appl::_("Kép feltöltése")?></button>
            <?php } else {?>
                <button class="btn btn-info" type="button" onclick="showModalMessage('Képek feltöltése','<b>Örvendünk mert képpel szeretnéd bővíteni az oldalt!</b><br/>Ebben az albumban a diákok és az osztályok képei jelennek meg tartalmuk beállítása szerint. Ha szeretnél képeket feltölteni akkor keresd meg az osztályt vagy a diákot amihez a kép legjobban passzol, majd ott töltsd fel a képet és jelöld meg a tartalmát. Köszönjük szépen.');"><span class="glyphicon glyphicon-cloud-upload"> </span> <?php Appl::_("Kép feltöltése")?></button>
            <?php }?>
            <button class="btn btn-default" onclick="return toogleListBlock();"><span class="glyphicon glyphicon-eye-open"> </span> <?php Appl::_("Lista/Album")?></button>
            <?php if ((substr($albumParam,0,1)!="_" && !($type=="schoolID" && $albumParam=="")) || isUserAdmin()) {?>
                <button class="btn btn-<?php echo $sortOrder ?>" onclick="return sortPictures('order<?php echo $desc ?>')" title="<?php Appl::_("Beálított sorrend")?>"><span class="glyphicon glyphicon-sort-by-order<?php echo $alt ?>"> </span></button>
            <?php } ?>
            <button class="btn btn-<?php echo $sortAlphabet ?>" onclick="return sortPictures('alphabet<?php echo $desc ?>')" title="<?php Appl::_("ABC szerint")?>"><span class="glyphicon glyphicon-sort-by-alphabet<?php echo $alt ?>"> </span></button>
            <button class="btn btn-<?php echo $sortDate ?>" onclick="return sortPictures('date<?php echo $desc ?>')" title="<?php Appl::_("Dátum szerint")?>"><span class="glyphicon glyphicon-sort-by-attributes<?php echo $alt ?>"> </span></button>
        </div>
        <?php if ($countPictures<50 || isUserAdmin()) {?>
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

	
	<?php if ($countPictures==0) :?>
		<div class="alert alert-warning" ><?php Appl::_("Jelenleg nincsenek képek feltöltve. Légy te az első aki képet tőlt fel!")?></div>
	<?php endif;?>
	<?php displayPictureList($db,$pictures,$albumList,$albumParam,$view); ?>
    <span id="more"></span>
</form>
<?php if (getParam("id")==null && $countPictures>((getIntParam("start",0)+1)*$limitOfPicturesPerPage) ) {?>
    <button id="buttonmore" class="btn btn-success" style="margin:10px;" onclick="return showmore()"><?php Appl::_("Többet szeretnék látni")?></button>
<?php }?>

<?php \maierlabs\lpfw\Appl::addCssStyle('
    .pdialog {width: 90%;}
    @media only screen and (max-width: 700px){
        .pdialog {width: 100%;}
        .modal-dialog {margin:0px;}
    }
');
?>

<!-- Modal -->
<div class="modal" id="pictureModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered pdialog" style="background-color: white;border-radius: 7px;">
        <div class="modal-content" style="position: relative;padding: 5px;min-height: 200px; ">
            <div id="thePictureDiv" style="overflow: hidden;">
                <img class="img-responsive" id="thePicture" title="" style="position: relative; min-height: 100px;min-width: 100px; display: inline-block;" onmousedown="newPerson(event);"/>
                <img id="thePictureFaceRecognition" style="display: none" />
            </div>
            <div style="position: absolute; top: 10px; left:10px;">
                <button title="<?php Appl::_("Bezár")?>" class="pbtn" id="modal-close" data-dismiss="modal"><span class="glyphicon glyphicon-remove-circle"></span></button>
                <button title="<?php Appl::_("Arc felismerés")?>" class="pbtn" id="facebutton" onclick="return toggleFaceRecognition();"><span class="glyphicon glyphicon-user"></span></button>
                <button title="<?php Appl::_("Jelölések")?>" class="pbtn" onmouseover="personShowAll(true);" onmouseout="personShowAll(false);"><span class="glyphicon glyphicon-eye-open"></span></button>
                <button title="<?php Appl::_("Kép link a clipbordba")?>" class="pbtn" onclick="showModalMessage('A kép linkje','<?php echo($url.pathinfo(parse_url($_SERVER['SCRIPT_NAME'])["path"])["dirname"]."/picture?id=") ?>'+$('#thePicture').attr('data-id'));return false;" onmouseout="personShowAll(false);"><span class="glyphicon glyphicon-link"></span></button>
                <?php if(isUserAdmin() ) {?>
                    <button title="<?php Appl::_("Beállítások")?>" class="pbtn" onclick="showImageSettings();"><span class="glyphicon glyphicon-cog"></span></button>
                <?php }?>
            </div>
            <div style="width:100%; padding:0 10px 0 0; position: absolute;top: 50%;">
                <div style="display: inline-block">
                    <button title="<?php Appl::_("elöző kép")?>" id="prevpicture" class="pbtn" onclick="return slideToNextPicture(false);"><span class="glyphicon glyphicon-arrow-left"></span></button>
                </div>
                <div style="display: inline-block;float: right">
                    <button title="<?php Appl::_("következő kép")?>" id="nextpicture" class="pbtn" onclick="return slideToNextPicture(true);"><span class="glyphicon glyphicon-arrow-right"></span></button>
                </div>
            </div>
        </div>
        <div id="personlist"></div>
    </div>
</div>


<?php \maierlabs\lpfw\Appl::addCssStyle('
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

/**
 * Display picture list
 * @param dbBL $db
 * @param array $pictures
 * @param int $idx
 * @param array $albumList
 * @param string $albumParam
 * @param string $view
 */
function displayPictureList($db,$pictures,$albumList,$albumParam,$view) {
    if (sizeof($pictures)==0) {
        \maierlabs\lpfw\Appl::addJsScript('$("#buttonmore").hide();');
    }
    foreach ($pictures as $idx=>$pict) {
        if ( $pict["isDeleted"]==0  || isUserAdmin() ) {
            ?><div class="pictureframe" <?php echo $pict["isDeleted"]==1?'style="background-color: #ffbcac;"':'' ?>><?php
            displayPicture($db,$pictures,$idx,$albumList,$albumParam,$view);

            if ( $albumParam=="_mark_") {
                ?>
                <div style="position: relative; bottom:130px;right:-265px;z-index: 10;height: 0px;">
                <img style="box-shadow: 2px 2px 17px 6px black;border-radius:35px; " src="imageTaggedPerson?pictureid=<?php echo $pict["id"] ?>&personid=<?php echo getParam("typeid") ?>&size=120"/>
                </div><?php
            }
            ?></div><?php
        }
    }
}

/**
 * Display a picture div
 * @param dbBL $db
 * @param array $pictures
 * @param int $idx
 * @param array $albumList
 * @param string $albumParam
 * @param string $view
 */
function  displayPicture($db,$pictures,$idx,$albumList,$albumParam,$view) {
    $pict=$pictures[$idx];
    $checked= ($pict["isVisibleForAll"]==1)?"checked":"";
    $dbOpinion = new dbDaOpinion($db);
    $typeArray = $db->getPictureTypeText($pict);?>

    <div id="list-table">

        <?php if ($view=="table") {?>
            <?php if ($typeArray["text"]!='' && substr($albumParam,0,1)=="_") {?>
                <span><?php echo $typeArray["text"]?></span>
            <?php }?>
            <div style="position: relative">
                <img class="img-responsive ibtn" data-id="<?php echo $pict["id"] ?>"  style="min-height:100px;position: relative;" src="imageConvert?id=<?php echo $pict["id"] ?>" style="position: relative"/>
                <div class="pdiv">
                    <button title="Nagyít" class="pbtn" onclick="return pictureModal('<?php echo $pict["file"] ?>',<?php echo $pict["id"] ?>);" type="button"><span class="glyphicon glyphicon-search"></span></button>
                    <button title="Módosít" class="pbtn" onclick="return displayedit(<?php echo $pict["id"] ?>);" type="button"><span class="glyphicon glyphicon-pencil"></span></button><?php
                    if (isUserAdmin()){?>
                        <button title="Kicserél" class="pbtn" name="overwriteFileName" value="<?php echo $pict["file"]?>"><span class="glyphicon glyphicon-refresh"></span></button>
                    <a title="Letölt" class="pbtn " target="_download" href="<?php echo $pict['file']?>" ><span style="vertical-align: middle;" class="glyphicon glyphicon-download-alt"></span></a><?php
                    } ?>
                </div>
            </div>
            <span id="imgspan<?php echo $pict["id"] ?>" style="display: none"></span>
        <?php } else {?>
            <div style="vertical-align: top; margin:10px" >
                <img class="img-responsive" src="imageConvert?width=80&thumb=true&id=<?php echo $pict["id"] ?>" />
            </div>
        <?php } ?>

        <?php  if (isUserSuperuser()) {?>
            <a href="history?table=picture&id=<?php echo $pict["id"]?>" title="módosítások" style="position: absolute;bottom:28px;left: 10px;">
                <span class="badge"><?php echo sizeof($db->dataBase->getHistoryInfo("picture",$pict["id"]))?></span>
            </a>
        <?php }?>
    </div>

    <div>
        <div id="edit_<?php echo $pict["id"] ?>" style="width:auto;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;cursor:default;margin: 0 10px 0 10px;display: none" >
            <input type="text" class="iledittitle" id="titleEdit_<?php echo $pict["id"] ?>" value="<?php echo html_entity_decode(html_entity_decode($pict["title"])) ?>" placeholder="A kép címe" style="width: 320px;"/><br/>
            <textarea class="ileditcomment" id="commentEdit_<?php echo $pict["id"] ?>"  placeholder="Írj egy pár sort a kép tartarmáról." >
<?php echo html_entity_decode(html_entity_decode($pict["comment"])) ?></textarea>
            <div>
                <?php if (isUserAdmin()) {?>
                    <input class="form-control" value="<?php echo $pict["tag"]?>" id="tagEdit_<?php echo $pict["id"] ?>"/>
                <?php } else {?>
                    <select  class="chosen" multiple="true" data-placeholder="Mi a kép tartalma?" id="tagEdit_<?php echo $pict["id"] ?>" >
                        <?php foreach ($db->getListOfPictureTags() as $tag) {
                            $selected =(strstr($pict["tag"],$tag["tag"])!==false)?'selected="selected"':'' ?>
                            <option value="<?php echo $tag["tag"]?>" <?php echo $selected ?>><?php echo $tag["tag"]?></option>
                        <?php }?>
                    </select>
                    <input type="hidden" name="tagEdit_<?php echo $pict["id"] ?>"/>
                <?php }?>
                <?php if (isUserLoggedOn()) { ?>
                    <span  class="ilbutton ilbuttonworld" ><input <?php echo $checked ?> type="checkbox"  onchange="changeVisibility(<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></span >
                <?php }?>
                <button class="btn btn-info"  title="Kimenti a kép módosításait" onclick="return savePicture(<?php echo $pict["id"] ?>);"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
                <?php if ($pict["isDeleted"]!=1) { ?>
                    <button class="btn btn-warning" title="Képet töröl" onclick="deletePicture(<?php echo $pict["id"] ?>);return false;"><span class="glyphicon glyphicon-remove-circle"></span> Töröl</button>
                <?php } ?>
                <?php if (isUserAdmin()) { ?>
                    <button class="btn btn-danger" title="Végleges törlés" onclick="unlinkPicture(<?php echo $pict["id"] ?>);return false;"><img src="images/delete.gif" /> Végleges</button>
                    <?php if ($pict["isDeleted"]==1) {?>
                        <button class="btn btn-warning" title="Törlés vissza" onclick="notUnlinkPicture(<?php echo $pict["id"] ?>);return false;"><span class="glyphicon glyphicon-remove"></span> Maradhat</button>
                    <?php }?>
                <?php }?>
                <?php if (isUserEditor() ||isUserSuperuser() || isUserEditor()) { ?>
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

    <div >
        <div id="show_<?php echo $pict["id"] ?>" style="width:auto;display: inline-block; background-color: white;border-radius: 7px;padding: 5px;margin: 10px 10px 0 10px;cursor:default;" >
            <?php //change Order buttons?>
            <?php if($view!="table" && (isUserEditor() || isUserSuperuser()) ) :?>
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
                <b><span id="titleShow_<?php echo $pict["id"] ?>"><?php echo html_entity_decode($pict["title"]) ?></span></b><br/>
                <span id="commentShow_<?php echo $pict["id"] ?>"><?php echo createLink(html_entity_decode($pict["comment"]),true) ?></span>
                <?php if($pict["tag"]!=null && $pict["tag"]!='') {?>
                    <br>Tartalom:<span><?php echo ($pict["tag"]) ?></span>
                <?php } ?>
            </div><?php
            displayPictureOpinion($dbOpinion,$pict["id"]);?>
        </div>
        <?php if (!isUserLoggedOn() && $pict["isVisibleForAll"]==0) { ?>
            <br/><span  class="iluser" title="<?php Appl::_("Csak bejelnkezett felhasználok látják ezt a képet élesen.")?>"><?php Appl::_("Ez a kép védve van!")?></span >
        <?php } ?>
    </div>
    <?php if ($view!="table" && isUserAdmin()) {?>
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

//Show more button
Appl::addJsScript("
    var picturesStart=1;
    var countPictures=".$countPictures.";
    var limitPictures=".$limitOfPicturesPerPage.";
    var picturesButton='';
    function showmore(showNext) {
        picturesButton=$('#buttonmore').html();
        $('#buttonmore').html('Pillanat...<img src=\"images/loading.gif\" />');
        $.ajax({
    		url:'".$link."'+'&action=showmore&start='+picturesStart,
	    	type:'GET',
    		success:function(data){
    		    picturesStart++;
    		    var s = document.documentElement.scrollTop || document.body.scrollTop;
	    	    $('#more').replaceWith(data+'<span id=\"more\"></span>');
	    	    document.documentElement.scrollTop = document.body.scrollTop = s;
	    	    if(countPictures>picturesStart*limitPictures) {
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


\maierlabs\lpfw\Appl::addJs("js/picture.js",true, true);
\maierlabs\lpfw\Appl::addJs("js/jquery.facedetection.js");

\maierlabs\lpfw\Appl::addJsScript('
    function toogleListBlock() {
        var url = "'.$view.'"=="table"?"view=list":"view=table";
        url +="'.(isset($tabOpen)?"&tabOpen=".$tabOpen:"").'";
        url +="'.(isset($type)?"&type=".$type:"").'";
        url +="'.(isset($typeId)?"&typeid=".$typeId:"").'";
        url +="&sort='.$sort.'";
        url +="'.(null!=getParam("album")?"&album=".getParam("album"):"").'";
        window.location.href="'.$serverPhpSelf.'?"+url;
        return false;
    }

    function sortPictures(sort) {
        var url = "view='.$view.'";
        url +="'.(isset($tabOpen)?"&tabOpen=".$tabOpen:"").'";
        url +="'.(isset($type)?"&type=".$type:"").'";
        url +="'.(isset($typeId)?"&typeid=".$typeId:"").'";
        url +="&sort="+sort;
        url +="'.(null!=getParam("album")?"&album=".getParam("album"):"").'";
        window.location.href="'.$serverPhpSelf.'?"+url;
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
        window.location.href="'.$serverPhpSelf.'?"+url+"&id1="+id1+"&id2="+id2;
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
        window.location.href="'.$serverPhpSelf.'"+url;
    }

    function deletePicture(id) {
        if (confirm("'.Appl::__("Fénykép törölését kérem konfirmálni!").'")) {
            showWaitMessage();
            window.location.href="'.$serverPhpSelf.'?action=deletePicture&did="+id+"&tabOpen='.getParam("tabOpen","pictures").'&type='.$type.'&typeid='.$typeId.'&album='.getParam("album").'";
        }
    }
');

//javascript delete picture
if (isUserSuperuser()) {
    \maierlabs\lpfw\Appl::addJsScript('
        function unlinkPicture(id) {
            if (confirm("'.Appl::__("Fénykép törlését kérem konfirmálni!").'")) {
                showWaitMessage();
                window.location.href = "'.$serverPhpSelf.'?action=unlinkPicture&did=" + id + "&tabOpen='.getParam("tabOpen","pictures").'&type='.$type.'&typeid='.$typeId.'&album='.getParam("album").'";
            }
        }
        function notUnlinkPicture(id) {
            if (confirm("'.Appl::__("Fénykép törlésének vissza állítását kérem konfirmálni!").'")) {
                showWaitMessage();
                window.location.href = "'.$serverPhpSelf.'?action=notUnlinkPicture&did=" + id + "&tabOpen='.getParam("tabOpen","pictures").'&type='.$type.'&typeid='.$typeId.'&album='.getParam("album").'";
            }
        }
    ');
}

//Open picture modal if only one picture should  be viewed
if(isset($picture)) {
    \maierlabs\lpfw\Appl::addJsScript('
        $(function() {
            pictureModal("'.$picture['file'].'",'.intval($picture['id']).');
        });
    ');
}

//Send the list of album pictures to javascript, for face recognition
\maierlabs\lpfw\Appl::addJsScript('var pictures = Array();');
foreach ($pictures as $idx=>$pict) {
    //if ($pict["isDeleted"] == 0) {
        \maierlabs\lpfw\Appl::addJsScript('pictures['.$idx.']=Array(); pictures['.$idx.'].file ="'.$pict["file"].'"; pictures['.$idx.'].id='.$pict["id"].';');
    //}
};
