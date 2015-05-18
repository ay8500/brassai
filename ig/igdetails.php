<?PHP
	$image=$images_array[$slideshow_index];
	if (isset($_GET["action"])) $detailAction=$_GET["action"]; else $detailAction="";
	//Set picture title
	if  (($detailAction=="setPictureTitle") && (isset($_GET["pictureTitle"]))) {
		incViewCounter($image,false);  //create picture textfile
		setPictureTitle($image,$_GET["pictureTitle"]);
	}
	echo "<div id=\"statusbar\">";
	echo(' <table border="0" style="width:100%"><tr><td>'."\r\n");
	echo ('<h2 style="font-size:20px">'.getGalleryDescription()."</h2>&nbsp;&nbsp;".$images_count.$TXT["PictCount"]."\r\n");
	echo(' </td><td style="text-align:right">'."\r\n");
	echo "  <a href=\"$SCRIPT_NAME?view=slideshow&gallery=$gallery&slideshow_index=$slideshow_index\" title=\"".$TXT["BackTT"]."\">".$TXT["MenuBack"]."</a>"; 
	echo(' </td></tr></table>'."\r\n");
	echo "</div>";
	echo "<div id=\"img_area\">";

	?><table  id="commentArea">
	<tr><td colspan="2">&nbsp;</td></tr>
	<?php if ($detailAction=="askDeleteImage") {?>
		<tr>
		<td style="text-align:center;"><?php echo("<img src=\"$gallery/.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype."\" title=\"thumbnail\" alt=\"thumbnail\" />");?>
		</td>
		<td>
		<form action="<?php echo($SCRIPT_NAME);?>" method="get">
			<input type="hidden" name="view" value="thumbnails" />
			<input type="hidden" name="slideshow_index" value="<?php echo $slideshow_index?>" />
			<input type="hidden" name="gallery" value="<?php echo $gallery?>" />
			<input type="hidden" name="image" value="<?php echo $image?>" />
			<input type="hidden" name="action" value="deleteImage" />
			<input type="submit" name="deleteImage" value="<?PHP echo($TXT["DetailAskDelete"]) ?>" />
		</form>
		</td></tr>
	<?php } else {?>
		<tr>
		<td style="text-align:center;"><?php echo("<img src=\"$gallery/.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype."\" title=\"thumbnail\" alt=\"thumbnail\" />");?>
			<br/><?php writeMetaData($image);?></td>
		<td>
			<form action="<?php echo ($SCRIPT_NAME);?>" method="get">
				<input type="hidden" name="view" value="details" />
				<input type="hidden" name="slideshow_index" value="<?php echo $slideshow_index?>" />
				<input type="hidden" name="gallery" value="<?php echo $gallery?>" />
				<input type="hidden" name="paramImage" value="<?php echo $image?>" />
				<input type="hidden" name="action" value="askDeleteImage" />
				<input type="submit" name="deleteImage" value="<?PHP echo($TXT["DetailDelete"]) ?>" />
			</form>
		</td></tr>
		<tr><td colspan="2">
			<form action="<?php echo($SCRIPT_NAME);?>" method="get">
				<input type="hidden" name="view" value="details" />
				<input type="hidden" name="slideshow_index" value="<?php echo $slideshow_index?>" />
				<input type="hidden" name="gallery" value="<?php echo $gallery?>" />
				<input type="hidden" name="paramImage" value="<?php echo $image?>" />
				<input type="hidden" name="action" value="setPictureTitle" />
				<input type="text" size="100"  name="pictureTitle" value="<?PHP echo(getPictureTitle($image));?>" /><br />
				<input type="submit" name="setPictureTitle" value="<?PHP echo($TXT["DetailTitle"]) ?>" />
			</form>
		</td></tr>
		<tr><td colspan="2"><?php writeComment($image); ?></td></tr>
	<?php }?>
	<tr><td colspan="2">&nbsp;</td></tr>
	</table>

