<?php	
	if (!empty($images_array)) {
?>	
		<div id="statusbar">
		<table border="0" style="width:100%"><tr><td>
<?php 
		//Display gallery name and picture cout 
		echo ('<b style="font-size:20px">'.getGalleryDescription()."</b>&nbsp;&nbsp;");
		echo(' </td><td style="text-align:right">'."\r\n");
		//Display paging link
		
		if ($thumbnail_indexing) {
			$images_array = array_chunk($images_array, $thumbnails_per_page);
			$images_array = $images_array[$thumbnail_index];
			if ($images_count>$thumbnails_per_page) {
				if ($thumbnail_index>0) { 
					echo "<a href=\"$SCRIPT_NAME?view=thumbnails&amp;gallery=$gallery&amp;thumbnail_index=".($thumbnail_index-1)."\" title=\"".$TXT["MenuPrevPageTT"]."\">&nbsp;&nbsp;<button class=\"glyphicon glyphicon-chevron-left\"></button>&nbsp;&nbsp;</a>"; 
				}
				echo ($thumbnail_index+1)."/".ceil($images_count/$thumbnails_per_page)." &nbsp;";
				if ($thumbnail_index<(ceil($images_count/$thumbnails_per_page)-1)) { 
					echo "<a href=\"$SCRIPT_NAME?view=thumbnails&amp;gallery=$gallery&amp;thumbnail_index=".($thumbnail_index+1)."\" title=\"".$TXT["MenuNextPageTT"]."\"><button class=\"glyphicon glyphicon-chevron-right\"></button>&nbsp;&nbsp;</a>"; 
				}
			}
		}
		//Display Slideshow link
		echo "<a href=\"$SCRIPT_NAME?view=slideshow&gallery=$gallery\" title=\"".$TXT["MenuSlideshow"]."\">".$TXT["MenuSlideshow"]."</a>"; 
		?>
		
		</td></tr></table>
		</div>
		<div id="img_area">
		<?php 
		$i = 1;
		$row = 0;
		foreach ($images_array as $image) {
			
			if (!is_file("./.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype)) { create_thumbnail($image); }
			
			echo "<a href=\"$SCRIPT_NAME?view=slideshow&amp;gallery=$gallery&amp;slideshow_index=".($i-1+$row*$thumbnails_per_row+$thumbnail_index*$thumbnails_per_page)."\"><img src=\"$gallery/.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype."\" title=\"".getPictureTitle($image)."\" alt=\"\"  /></a>";
			//if (hasComment($image)) echo "<a href=\"\"><img src=\"./comment.jpg\" /></a>";
			//echo("</td>");
			if ($i==$thumbnails_per_row) {
				$i = 1;
				//echo "<br/>";
				$row++;
			}
			else { $i++; }
			next($images_array);
		}
	}
?>