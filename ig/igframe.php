
<?php
	include_once("igconfig.php");
	include("igdalFileSystem.php");
	include("igimagetool.php");
	
	//*** initialization***
	//logon logoff
	$logOnMessage="";
	if (!isset($_SESSION['USER'])) $_SESSION['USER']="";
	if (isset($_GET["action"]) && ($_GET["action"]=="logon")) {
		if (isset($_GET["paramName"])) $paramName=$_GET["paramName"]; else $paramName="";
		if (isset($_GET["paramPassw"])) $paramPassw=$_GET["paramPassw"]; else $paramPassw="";
		if (($paramName=="") || ($paramPassw=="")) { 
			$logOnMessage=$TXT["LogInError"];$paramName=""; $paramPassw="";
			$_SESSION['ADMIN']=false;$_SESSION['USER']="";
			$logOnMessage=$TXT["LogInUserPassw"];
		}
		if (($paramName=="levi") && ($paramPassw=="lm")) {
		  $_SESSION['ADMIN']=true;$_SESSION['USER']="Levi";$_SESSION['MAIL']="levi@blue-l.de";
		}
		else {
			$logOnMessage=$TXT["LogInUserPasErr"];
			$_SESSION['ADMIN']=false;$_SESSION['USER']="";$_SESSION['MAIL']="";
		}
	}
	if (isset($_GET["action"]) && ($_GET["action"]=="logoff")) {
			$_SESSION['ADMIN']=false;$_SESSION['USER']="";$_SESSION['MAIL']="";
	}
	
	// gallery: the folder name that contains the pictures of one gallery
	if (isset($_GET["gallery"])) 	{ 
		$gallery = $_GET["gallery"];
		if (!is_dir($gallery)) {
			$gallery = "ig/".$gallery; 
			if (!is_dir($gallery)) { 
				die("<br/><br/>ERROR: invalid directory: $gallery");
			} 
		}
	}
	else	{ $gallery = "";}
	//view variant posibble values are: thumbnails, comment, slideshow
	if (isset($_GET["view"])) 	{ $view = $_GET["view"]; } else { $view = "thumbnails"; }
	//index of actual viewed image
	if (isset($_GET["slideshow_index"])) { $slideshow_index = $_GET["slideshow_index"]; } else { $slideshow_index = 0; }
	//index of actual viewed thumbnail page
	if (isset($_GET["thumbnail_index"])) { $thumbnail_index = $_GET["thumbnail_index"]; } else { $thumbnail_index = floor($slideshow_index/$thumbnails_per_page);}

	$SCRIPT_NAME = getenv("SCRIPT_NAME");	//The name of the application used to bild links
	
	
	//writeMetaData
	function writeMetaData($image) {
		global $TXT;
		$pi = pathinfo($image);
		if (strcasecmp($pi["extension"],"jpg")==0) {
					$exif = exif_read_data($image, 0, true);
			if (isset($exif['IFD0']) && isset($exif['EXIF']) && isset($exif['IFD0']['Make']) && isset($exif['IFD0']['DateTime'])) {
				echo("\r\n<table id=\"commentList\" style=\"border-style:solid\">\r\n");
				echo("<tr><td>".$TXT["ExifCamera"].":</td><td>".$exif['IFD0']['Make']." ".$exif['IFD0']['Model']."</td></tr>\r\n");
				echo("<tr><td>".$TXT["ExifDate"].":</td><td>".$exif['IFD0']['DateTime']."</td></tr>\r\n");
				echo("<tr><td>".$TXT["ExifResolution"].":</td><td>".$exif['COMPUTED']['Width']."x".$exif['COMPUTED']['Height']."</td></tr>\r\n");
				if (isset($exif['EXIF']['LightSource'])) {
					if ($exif['EXIF']['LightSource']>0) 
						echo("<tr><td>".$TXT["ExifFlashlight"].":</td><td>".$TXT["ExifYes"]."</td></tr>\r\n");
					else 
						echo("<tr><td>".$TXT["ExifFlashlight"].":</td><td>".$TXT["ExifNo"]."</td></tr>\r\n");
				}
				echo("<tr><td>".$TXT["ExifExposure"].":</td><td>".$exif['EXIF']['ExposureTime']."</td></tr>\r\n");
				echo("<tr><td>".$TXT["ExifDiafragma"].":</td><td>".$exif['EXIF']['FNumber']."</td></tr>\r\n");
				echo("</table>\r\n"); 
				//foreach ($exif as $key => $section) {
					//foreach ($section as $name => $val) {
					//echo "$key.$name: $val<br />\n";
				//}}
			}
		}
	}

	function writeMetaDataShort($image) {
		global $TXT;
		$pi = pathinfo($image);
		if (strcasecmp($pi["extension"],"jpg")==0) {
			$exif = exif_read_data($image, 0, true);
			if (isset($exif['IFD0']) && isset($exif['IFD0']['Make']) && isset($exif['EXIF']) && isset($exif['IFD0']['DateTime']))  {
				echo($TXT["ExifCamera"].":".$exif['IFD0']['Make']." ".$exif['IFD0']['Model']." ");
				echo($TXT["ExifDate"].":".$exif['IFD0']['DateTime']);
			}
		}
	}
	
	
	//writeComment
	function writeComment($imageFileName) {
		global $SCRIPT_NAME;
		global $gallery;
		global $slideshow_index;
		global $view;
		global $TXT;
		$noComments=true;
		$deleteIndex=1;
	    if (file_exists($imageFileName.".txt")) {
			$file=fopen($imageFileName.".txt","r");
			while (!feof($file)) {
				$b = explode("\t",fgets($file));
				if(($b[0]!="")&&($b[0]!="#")) {
					if ($noComments) {
						$noComments=false;
						echo("\r\n<table id=\"commentHeader\">".$TXT["CommentColumns"]."\r\n");
					}
					$imgq="<img src=\"ig/".$b[5].".gif\" alt=\"".$TXT["Ranking"][$b[5]]."\" title=\"".$TXT["Ranking"][$b[5]]."\"";
					$imgc="<img src=\"ig/".$b[6].".gif\" alt=\"".$TXT["Ranking"][$b[6]]."\" title=\"".$TXT["Ranking"][$b[6]]."\"";
					echo("<tr id=\"commentList\"><td>".$b[4]."</td><td>".$b[0]."</td><td>".$imgq."</td><td>".$imgc."</td><td>".$b[1]."</td><td>&nbsp;");
					if (((isset($_SESSION["ADMIN"])) && $_SESSION["ADMIN"]) || ($_SERVER['REMOTE_ADDR']==$b[3])) { echo("<a id=\"commentList\" href=\"$SCRIPT_NAME?view=$view&gallery=$gallery&slideshow_index=$slideshow_index&action=deleteComment&index=$deleteIndex\" title=\"".$TXT["CommentDeleteTT"]."\">".$TXT["CommentDelete"]."</a>");}
					echo("</td></tr>\r\n");
				}
				$deleteIndex++;
			}
			fclose ($file);
		}
		if (!$noComments) { echo("</table>\r\n"); }
		return ;
	}
	
	function writeGallerys() {
		global $gallery;
		global $SCRIPT_NAME;
		$gcount = 0;	//Number of galleries
		$ggroup="";
		$gallerys = getGallerys();
		foreach ($gallerys as $gs) {
				//Group
				if (strncmp($gs["group"],$ggroup,strlen($gs["group"]))!=0) {
					echo ("<br /><b id=\"GGroup\">".$gs["group"]."</b><br />"."\r\n"); 
					$ggroup=trim($gs["group"]);
				}
				//Album
				echo ("<li><a href=\"$SCRIPT_NAME?gallery=".$gs["dir"]."\"  id=\"GLink\" title=\"zeige Bildergalerie ".$gs["longDes"]."\">".$gs["shortDes"]."</a></li>"."\r\n"); 
				//Set firts album
				if (($gallery == "") && $gs["default"]) {
				$gallery = $gs["dir"];
					if (!is_dir($gallery)) { die("<br/><br/>ERROR: invalid default directory:<".$gallery.">"); }
				}
		}
		return $gcount;
	}


?>
<table border="0">
<tr>

<?PHP
	//*** The navigation area  on the left site***
	if ($multipleGalleries) {
		echo('<td id="menu" valign="top">'."\r\n");
		echo('<table border="0" height="100%"><tr valign="top"  height="200">'."\r\n");
		echo('<td><div style="font-size:15px"><b>'.$TXT["Title"].'</b></div><br/>'."\r\n");
		$gcount = writeGallerys(); 
		echo('</td></tr><tr><td><hr id="menuhr">'."\r\n\r\n");
	 							//Number of galleries	
	//****** Logon ******
		if ($_SESSION['USER']=="") {
			echo('<form action="'.$SCRIPT_NAME.'" method="get">'."\r\n");
			echo('<input type="hidden" name="action" value="logon" />'."\r\n");
			echo('<input type="hidden" name="view" value="'.$view.'" />'."\r\n");
			echo('<input type="hidden" name="slideshow_index" value="'.$slideshow_index.'" />'."\r\n");
			echo('<input type="hidden" name="gallery" value="'.$gallery.'" />'."\r\n");
			echo('<b>'.$TXT["LogIn"].'</b><br/>'."\r\n");
			echo($TXT["LogInUser"].':<br/><input style="font-size:10px;" type="text" size="12" name="paramName"><br/>'."\r\n");
			echo($TXT["LogInPassw"].':<br/><input style="font-size:10px;" type="password" size="12" name="paramPassw"><br/><br/>'."\r\n");
			echo('<input style="font-size:10px;" type="submit" value="'.$TXT["LogIn"].'" >'."\r\n");
			echo('<div style="font-size:10px;">'.$logOnMessage.'</div>'."\r\n");
			echo('</form>'."\r\n");
		} else {
	//****** Logoff ******
			echo('<form action="'.$SCRIPT_NAME.'" method="get">');
			echo('<input type="hidden" name="action" value="logoff" />');
			echo('<input type="hidden" name="view" value="'.$view.'" />');
			echo('<input type="hidden" name="slideshow_index" value="'.$slideshow_index.'" />');
			echo('<input type="hidden" name="gallery" value="'.$gallery.'" />');
			echo($TXT["LogInUser"].':'.$_SESSION['USER'].'<br/>');
			echo('<input style="font-size:10px;" type="submit" value="'.$TXT["LogOut"].'" /><br/><br/>');
			echo('</form>');
			if (($view!="edit")&&($view!="new")) { 
				echo('<form action="'.$SCRIPT_NAME.'" method="get">');
				echo('<input type="hidden" name="view" value="edit" />');
				echo('<input type="hidden" name="action" value="editGallery" />');
				echo('<input type="hidden" name="gallery" value="'.$gallery.'" />');
				echo($TXT["Album"].':<br/>');echo (getGalleryShortDescription($gallery)); echo('<br/>');
				echo('<input style="font-size:10px;" type="submit" value="'.$TXT["Change"].'" ><br/><br/>');
				echo('</form>');
				echo('<form action="'.$SCRIPT_NAME.'" method="get">');
				echo('<input type="hidden" name="view" value="edit" />');
				echo('<input type="hidden" name="action" value="newGallery" />');
				echo($TXT["NewAlbum"].'<br/>');
				echo('<input style="font-size:10px;" type="submit" value="'.$TXT["Create"].'" >');
				echo('</form>');
			}
		}
		echo("\r\n".'<hr id="menuhr">');
		foreach ($SupportedLang as $Language) {
			if (isset($_SESSION['LANG']) && ($Language!=$_SESSION['LANG']))
				echo('<a href='.$SCRIPT_NAME.'?language='.$Language.'><img src="flag_'.$Language.'.jpg" alt=""/></a>'."\r\n");
		}
		echo('</td></tr>');
		echo('</table></td>'."\r\n\r\n");
	}


//***  Content on the right site ***?>
<td>
<div id="page">
<?PHP
	if ($gallery!="") {
		if (!is_dir($gallery."/.thumbs")) if (!mkdir($gallery."/.thumbs")) { die("<br/><br/>ERROR: can't create thumbnail directory in $gallery"); }
		if (!is_dir($gallery."/.modified")) if (!mkdir($gallery."/.modified")) { die("<br/><br/>ERROR: can't create directory for modified images in $gallery"); }
		$thumbnail_size = intval($width/$thumbnails_per_row);
		$images_array = getGalleryImages($gallery);
		
		$images_count = count($images_array);
		
		//*** generate all for this gallery ***
		if (isset($_GET["action"]) && ($_GET["action"]=="generate_all")) {
			$count1=0;
			$count2=0;
			echo "<p>";
			foreach ($images_array as $image) {
				if (!is_file("./.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype)) {
					create_thumbnail($image);
					echo "generated thumbnail for <i>$image</i><br/>";
					$count1++;
				}
				if (!is_file("./.modified/$width"."_mod_".$image.".".$slideshow_filetype)) {
					create_slideshow_item($image);
					echo "generated slideshow picture for <i>$image</i><br/>";
					$count2++;
				}
			}
			echo "<br/><br/>generated <b>$count1</b> thumbnails and <b>$count2</b> slideshow pictures for gallery <i>$gallery_name</i></p>";
		}
	}
	
	//*** delete one image comment ***
	if (isset($_GET["action"]) && ($_GET["action"]=="deleteComment") && isset($_GET["index"])) {
	   deleteComment($images_array[$slideshow_index],$_GET["index"]);
	}

	//*** delete one image  ***
	if (isset($_GET["action"]) && ($_GET["action"]=="deleteImage") && isset($_GET["image"])) {
		deleteImage($_GET["image"]);
		chdir("..");
		$images_array = getGalleryImages($gallery);
		$images_count = count($images_array);
	}

	switch ($view) {
		case "thumbnails": 	{include("igthumbnails.php");		} break;
		case "details":		{include("igdetails.php");			} break;
		case "comment": 	{include("igcomment.php");			} break;
		case "list": 		{include("iglist.php");				} break;
		case "slideshow": 	{include("igslideshow.php");		} break;
		case "edit": 		{include("igeditsavegallery.php"); 	} break;
	}
	echo "</div>";
?>
<?PHP if ($operationMessage!="") {?>
<table width="100%" border="0"><tr>
	<td width="50%" style="text-align:left;font-size:11px">
		<?PHP echo($operationMessage); ?>&nbsp;
	</td>
	<td width="50%" style="text-align:right;">
		<?PHP if ($multipleGalleries) {?>
		<a  style="font-size:10px" href="mailto:code@blue-l.de"><?PHP echo ($TXT["Footer"]) ?></a>
		<?PHP } ?>&nbsp;
	</td>
	</tr>
</table>
<?PHP } ?>
</div>
</td>
</tr></table>
<?PHP
	echo("<table><tr>");
	$i=1;
	chdir("..");
	$images_array = getGalleryImages($gallery);
	foreach ($images_array as $image) {
		echo("<td>");
		//if (!is_file("./.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype)) { create_thumbnail($image); }
		echo "<a style=\"font-size:9px\" title=\"".getPictureTitle($image)."\" href=\"$SCRIPT_NAME?view=slideshow&amp;gallery=$gallery&amp;slideshow_index=".($i-1)."\">".$i."</a>";
		echo("</td>");
		$i++;
		if (fmod($i,55)==0) echo("</tr><tr>"); 
	}
	echo("</tr></table>");
?>
