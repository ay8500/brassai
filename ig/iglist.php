<?PHP
	echo "<div id=\"img_area\">";
	$i = 1;
	echo("<table>");
	foreach ($images_array as $image) {
		echo("<tr><td>");
		if (!is_file("./.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype)) { create_thumbnail($image); }

		echo "<a href=\"$SCRIPT_NAME?view=slideshow&amp;gallery=$gallery&amp;slideshow_index=".($i-1)."\"><img src=\"$gallery/.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype."\" title=\"thumbnail\" alt=\"thumbnail\" /></a>";
		echo("</td><td style=\"text-align:left\">");
		writeMetaData($image);
		echo("<div style=\"font-size:12px\">");
		echo(" Angesehen:");echo(incViewCounter($image,false));
		echo("</div></td><td style=\"text-align:left\">");
		writeComment($image);
		$i++; 
		next($images_array);
		echo("</td></tr>");
	}
	echo("</table>");
?>