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
	<?if ($detailAction=="askDeleteImage") {?>
		<tr>
		<td style="text-align:center;"><?PHP echo("<img src=\"$gallery/.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype."\" title=\"thumbnail\" alt=\"thumbnail\" />");?>
		</td>
		<td>
		<form action="<?PHP echo($SCRIPT_NAME);?>" method="get">
			<input type="hidden" name="view" value="thumbnails" />
			<input type="hidden" name="slideshow_index" value="<?=$slideshow_index?>" />
			<input type="hidden" name="gallery" value="<?=$gallery?>" />
			<input type="hidden" name="image" value="<?=$image?>" />
			<input type="hidden" name="action" value="deleteImage" />
			<input type="submit" name="deleteImage" value="<?PHP echo($TXT["DetailAskDelete"]) ?>" />
		</form>
		</td></tr>
	<? } else {?>
		<tr>
		<td style="text-align:center;"><?echo("<img src=\"$gallery/.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype."\" title=\"thumbnail\" alt=\"thumbnail\" />");?>
			<br/><?writeMetaData($image);?></td>
		<td>
			<form action="<?echo($SCRIPT_NAME);?>" method="get">
				<input type="hidden" name="view" value="details" />
				<input type="hidden" name="slideshow_index" value="<?=$slideshow_index?>" />
				<input type="hidden" name="gallery" value="<?=$gallery?>" />
				<input type="hidden" name="paramImage" value="<?=$image?>" />
				<input type="hidden" name="action" value="askDeleteImage" />
				<input type="submit" name="deleteImage" value="<?PHP echo($TXT["DetailDelete"]) ?>" />
			</form>
		</td></tr>
		<tr><td colspan="2">
			<form action="<?echo($SCRIPT_NAME);?>" method="get">
				<input type="hidden" name="view" value="details" />
				<input type="hidden" name="slideshow_index" value="<?=$slideshow_index?>" />
				<input type="hidden" name="gallery" value="<?=$gallery?>" />
				<input type="hidden" name="paramImage" value="<?=$image?>" />
				<input type="hidden" name="action" value="setPictureTitle" />
				<input type="text" size="100"  name="pictureTitle" value="<?PHP echo(getPictureTitle($image));?>" /><br />
				<input type="submit" name="setPictureTitle" value="<?PHP echo($TXT["DetailTitle"]) ?>" />
			</form>
		</td></tr>
		<tr><td colspan="2"><? writeComment($image); ?></td></tr>
	<?}?>
	<tr><td colspan="2">&nbsp;</td></tr>
	</table>

