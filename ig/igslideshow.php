<?PHP
	//If no picture with this index then index=0
	if (!isset($images_array[$slideshow_index])) $slideshow_index=0;

	echo "<div id=\"statusbar\">";
	echo(' <table border="0" style="width:100%"><tr><td>'."\r\n");
	echo ('<b style="font-size:20px">'.getGalleryDescription()."</b>&nbsp;&nbsp;".$images_count.$TXT["PictCount"]."\r\n");
	//Navigation
	echo(' </td><td style="text-align:right">'."\r\n");
	if ($slideshow_index>0) { 
		echo "<a href=\"$SCRIPT_NAME?view=slideshow&amp;gallery=$gallery&amp;slideshow_index=".($slideshow_index-1)."\" title=\"".$TXT["MenuPrevPictTT"]."\">&lt;&lt; &nbsp;&nbsp;</a>"; 
	}
	echo ($slideshow_index+1)."/$images_count &nbsp;";
	if ($slideshow_index<($images_count-1)) { 
		echo "<a href=\"$SCRIPT_NAME?view=slideshow&amp;gallery=$gallery&amp;slideshow_index=".($slideshow_index+1)."\" title=\"".$TXT["MenuNextPictTT"]."\">&gt;&gt;</a>"; 
	}
	echo "&nbsp;&nbsp;<a href=\"$SCRIPT_NAME?view=thumbnails&gallery=$gallery&thumbnail_index=$thumbnail_index\" title=\"".$TXT["MenuThumbnails"]."\">".$TXT["MenuThumbnails"]."</a>"; 
	echo "&nbsp;&nbsp;<a href=\"$SCRIPT_NAME?view=comment&gallery=$gallery&slideshow_index=$slideshow_index\" title=\"".$TXT["MenuComment"]."\">".$TXT["MenuComment"]."</a>"; 
	if (isset($_SESSION['ADMIN']) && ($_SESSION['ADMIN'])) {
		echo "&nbsp;&nbsp;<a href=\"$SCRIPT_NAME?view=details&gallery=$gallery&slideshow_index=$slideshow_index\" title=\"".$TXT["MenuDetails"]."\">".$TXT["MenuDetails"]."</a>"; 
	}
?>
    <div class="fb-like" data-href="<?php echo('http://'.$_SERVER['SERVER_NAME'].getenv("SCRIPT_NAME").'?'.$_SERVER['QUERY_STRING']);?>" data-layout="button" data-action="like" data-show-faces="false" data-share="false"></div>
	</td></tr></table>
	</div>
	<div id="img_area">
<?php 
	echo '<h2 id="commentList">'.getPictureTitle($images_array[$slideshow_index]).'</h2>';
	if (!is_file("./.modified/$width"."_mod_".$images_array[$slideshow_index].".".$slideshow_filetype)) { 
		create_slideshow_item($images_array[$slideshow_index]); 
	}
	
	if ($link_original_image) {
		echo "<a href=\"$gallery/".$images_array[$slideshow_index]."\"><img src=\"$gallery/.modified/$width"."_mod_".$images_array[$slideshow_index].".".$slideshow_filetype."\" title=\"".$images_array[$slideshow_index]."\" alt=\"".$images_array[$slideshow_index]."\" /></a>";
	}
	else {
		echo "<img src=\"$gallery/.modified/$width"."_mod_".$images_array[$slideshow_index].".".$slideshow_filetype."\" title=\"".$images_array[$slideshow_index]."\" alt=\"".$images_array[$slideshow_index]."\" />";
	}
	echo("<p id=\"metaData\">");
	writeMetaDataShort($images_array[$slideshow_index]); 
	echo($TXT["PictViewCount"].":");echo(incViewCounter($images_array[$slideshow_index],true));
	echo("</p>");
	writeComment($images_array[$slideshow_index]);
	echo("</div>");
?>