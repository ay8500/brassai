<?PHP
	include_once("ltools.php");
	$maxFileSize=1024*1024;
	$action = getParam("action","");
	$new =($action=="newGallery");
	$save =($action=="saveGallery");
	//*** save Gallery Changes ***
	if ($save) {
		if (isset($_GET["paramGroup"])) $paramGroup=$_GET["paramGroup"]; else $paramGroup="";
		if (isset($_GET["paramShortDes"])) $paramShortDes=$_GET["paramShortDes"]; else $paramShortDes="";
		if (isset($_GET["paramLongDes"])) $paramLongDes=$_GET["paramLongDes"]; else $paramLongDes="";
		if (isset($_GET["paramDefault"])) $paramDefault=true; else $paramDefault=false;
		if (isset($_GET["paramDir"])) $paramDir=$_GET["paramDir"]; else $paramDir="";
		if (!empty($paramDir)) {
			mkdir($paramDir);
			chdir($paramDir);
		}
		saveGalleryDescription($paramGroup,$paramShortDes,$paramLongDes,$paramDefault);
	}
	if (!$new) {
		$s = getGalleryData("./pg.txt");
		$paramGroup=$s["group"];
		$paramShortDes=$s["shortDes"];
		$paramLongDes=$s["longDes"];
		$paramUser=$s["user"];
		$paramDate=$s["date"];
		$paramDefault=$s["default"];
	}
	else {
		$paramUser=$_SESSION['USER'];
		$paramDate=date('d.M.Y H:i');
		$paramGroup="";
		$paramShortDes="";
		$paramLongDes="";
		$paramDefault=false;
		$paramDir="";
		chdir("../");
	}
	$uploadResult="";
	if ($action=="Upload") {
		if (basename( $_FILES['userfile']['name'])!="") {
			$fileName = explode( ".", basename( $_FILES['userfile']['name']));
			$uploadfile=dirname($_SERVER["SCRIPT_FILENAME"])."/".getParam("gallery","")."/".$fileName[0].".".$fileName[1];
			//JPG
			if (strcasecmp($fileName[1],"jpg")==0) {
				if ($_FILES['userfile']['size']<$maxFileSize) {
					if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile))
						$uploadResult=$fileName[0].".".$fileName[1].$TXT["DUploadOK"];
					else
						$uploadResult=$fileName[0].".".$fileName[1].$TXT["DUploadError"];
				}
				else {
					$uploadResult=$fileName[0].".".$fileName[1].$TXT["DFileToBig"];
				} 
			
			}
			//ZIP
			else if (strcasecmp($fileName[1],"zip")==0) {
				if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
					$uploadResult=$fileName[0].".".$fileName[1].$TXT["DUploadOK"];
					$za = new ZipArchive();
					
					$za->open($uploadfile);
					//echo "numFile:" . $za->numFiles . "<br>";
					$indx=0;
					for ($i=0; $i<$za->numFiles;$i++) {
						$zentry = $za->statIndex($i);
						if (($zentry["size"]>0) && ($zentry["size"]<$maxFileSize) && 
							(!strpos($zentry["name"],"/")) && (!strpos($zentry["name"],'\\')) ) {
							$files[$indx++]=$zentry["name"];
							$uploadResult=$uploadResult."<br/>OK:".$zentry["name"];
						}
						else
							$uploadResult=$uploadResult."<br/>Error:".$zentry["name"];
					}
					$za->extractTo(dirname($uploadfile), $files);
					$za->close();
					unlink($uploadfile);
				}
				else
					$uploadResult=$fileName[0].".".$fileName[1].$TXT["DUploadError"];
				
			}
			else $uploadResult=$fileName[1].$TXT["DWrongExtension"];
		}
	}
?>
	<?php if(!$new){?>	
		<div id="statusbar">
		<?php echo ($images_count.$TXT["PictCount"]); ?> : <b><?php echo(getGalleryDescription());?></b>
		&nbsp; <?PHP echo('<a href="'.$SCRIPT_NAME.'?view=list&gallery='.$gallery.'">List</a>'); ?>
		</div>
	<?php }?>
	<div id="img_area">
		<?if(!$new){?><div style="font-size:20px"><?=$TXT["GPropertysEdit"]?></div><?}?>
		<?if( $new){?><div style="font-size:20px"><?=$TXT["GNew"]?></div><?}?>
				
		<table border="1"><tr><td>
			<form action="<?php echo($SCRIPT_NAME);?>">
				<input type="hidden" name="view" value="edit" />
				<input type="hidden" name="action" value="saveGallery" />
				<input type="hidden" name="gallery" value="<?php echo $gallery?>" />
				<table id="comment">
					<tr><td><?PHP echo $TXT["GGroup"] ?></td><td><input type="text" name="paramGroup" value="<?php echo $paramGroup;?>" /></td></tr>
						<?php if($new){?>
						<tr><td><?PHP echo $TXT["GGroups"] ?></td><td>
							<select name="paramExistGroup" size="1">
								<option value="">neue Gruppe</option>
								<?php
								$grps=getGalleryGroups();
								foreach($grps as $grp) {
									echo("<option value=\"$grp\">$grp</option>");
								}
								?>
						</select>
						</td></tr>
						<?php if ($new) {?><tr><td><?PHP echo $TXT["GFolder"] ?></td><td><input type="text" name="paramDir" value="<?php echo $paramDir; ?>" /></td></tr><?php }?>
					<?php }?>
					<tr><td><?PHP echo $TXT["GTitle"] ?></td><td><input type="text" name="paramShortDes" value="<?php echo $paramShortDes;?>" /></td></tr>
					<tr><td><?PHP echo $TXT["GDescr"] ?></td><td><input type="text" name="paramLongDes" value="<?php echo $paramLongDes?>" /></td></tr>
					<tr><td><?PHP echo $TXT["GDefault"] ?></td><td><input type="checkbox" name="paramDefault" value="x" <?php if ($paramDefault) {echo(" checked ");} ?>/></td></tr>
					<tr><td><?PHP echo $TXT["GUser"] ?></td><td><?=$paramUser?></td></tr>
					<tr><td><?PHP echo $TXT["GDate"] ?></td><td><?php echo $paramDate?></td></tr>
					<tr><td colspan="2"><input type="submit" value="<?PHP echo $TXT["GSaveChanges"] ?>"/></td></tr>
				</table>
			</form>
		</td>
		<?PHP if (!$new) { ?>
		<td>
			<table id="comment">
			<tr>
				<td>
					<?PHP echo $TXT["GPictureUpload"]; ?>
					<form enctype="multipart/form-data" action="<?php echo($SCRIPT_NAME);?>" method="POST">
						<input type="hidden" name="action" value="Upload">
						<input type="hidden" name="view" value="edit" />
						<input type="hidden" name="gallery" value="<?php echo $gallery?>" />
						<?PHP echo $TXT["GSelectFile"]; ?> <input name="userfile" type="file"><br />
						<input type="submit" value="<?PHP echo $TXT["GUpload"]; ?>">
					</form>
				</td>
			</tr>
			<tr><td>
			   <?PHP echo($uploadResult) ?>
			</td></tr>
			</table>
		</td>
		<?PHP } ?>
		</tr></table>	
	</div>

