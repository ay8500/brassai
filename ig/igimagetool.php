<?PHP

	function scale_image($img, $factor) {
		$temp = imagecreatetruecolor(imagesx($img)*$factor, imagesy($img)*$factor);
		imagecopyresized($temp, $img, 0, 0, 0, 0, imagesx($img)*$factor, imagesy($img)*$factor, imagesx($img), imagesy($img));
		return $temp;
		imagedestroy($temp);
	}
	
	function draw_image_border($img) {
		imagerectangle($img, 0, 0, imagesx($img)-1, imagesy($img)-1, imagecolorresolve($img, $GLOBALS["border_color"][0], $GLOBALS["border_color"][1], $GLOBALS["border_color"][2]));
		return $img; 
	}

	function draw_dropShadow($img, $dropShadow_scale, $dropShadow_offset, $dropShadow_blurRadius, $background_color, $dropShadow_color, $image_offset_x, $image_offset_y) {
		
		$dropShadow_width = intval(imagesx($img)*$dropShadow_scale);
		$dropShadow_height = intval(imagesy($img)*$dropShadow_scale);
		
		$img_dropShadow = imagecreatetruecolor($dropShadow_width+$dropShadow_offset+$dropShadow_blurRadius, $dropShadow_height+$dropShadow_offset+$dropShadow_blurRadius);
		
		// determine drop shadow color gradient
		
		$gradient_steps = array();		// RGB array with the gradient steps
		$temp_color = array();			// temporary RGB array
		$dropShadowGradientColors = array();	// integer array with all gradient colors from dark to bright
		
		for ($i=0; $i<=2; $i++) {
			$gradient_steps[$i] = ($background_color[$i]-$dropShadow_color[$i])/$dropShadow_blurRadius;
		}
		
		$temp_color[0] = $dropShadow_color[0]+$gradient_steps[0]/2;
		$temp_color[1] = $dropShadow_color[1]+$gradient_steps[1]/2;
		$temp_color[2] = $dropShadow_color[2]+$gradient_steps[2]/2;
		$dropShadowGradientColors[0] = imagecolorresolve($img_dropShadow, $temp_color[0], $temp_color[1], $temp_color[2]);
		
		for ($i=1; $i<$dropShadow_blurRadius; $i++) {
			$temp_color[0] = $temp_color[0]+$gradient_steps[0];
			$temp_color[1] = $temp_color[1]+$gradient_steps[1];
			$temp_color[2] = $temp_color[2]+$gradient_steps[2];
			$dropShadowGradientColors[$i] = imagecolorresolve($img_dropShadow, $temp_color[0], $temp_color[1], $temp_color[2]);
		}
		
		// draw drop shadow
		
		imagefill($img_dropShadow, 0, 0, imagecolorresolve($img_dropShadow, $background_color[0], $background_color[1], $background_color[2]));
		imagefilledrectangle($img_dropShadow, $dropShadow_offset+1, $dropShadow_offset+1, $dropShadow_offset+$dropShadow_width-1, $dropShadow_offset+$dropShadow_height-1, imagecolorresolve($img, $dropShadow_color[0], $dropShadow_color[1], $dropShadow_color[2]));
		
		for ($i=0; $i<$dropShadow_blurRadius; $i++) {
			// borders:  top / left / right / bottom
			imageline($img_dropShadow, $dropShadow_offset, $dropShadow_offset-$i, $dropShadow_offset+$dropShadow_width, $dropShadow_offset-$i, $dropShadowGradientColors[$i]);
			imageline($img_dropShadow, $dropShadow_offset-$i, $dropShadow_offset, $dropShadow_offset-$i, $dropShadow_offset+$dropShadow_height, $dropShadowGradientColors[$i]);
			imageline($img_dropShadow, $dropShadow_offset+$dropShadow_width+$i, $dropShadow_offset, $dropShadow_offset+$dropShadow_width+$i, $dropShadow_offset+$dropShadow_height, $dropShadowGradientColors[$i]);
			imageline($img_dropShadow, $dropShadow_offset, $dropShadow_offset+$dropShadow_height+$i, $dropShadow_offset+$dropShadow_width, $dropShadow_offset+$dropShadow_height+$i, $dropShadowGradientColors[$i]);
			// corners: left top / right top / left bottom / right bottom
			imagearc($img_dropShadow, $dropShadow_offset+$dropShadow_blurRadius-$i, $dropShadow_offset+$dropShadow_blurRadius-$i, 2*$dropShadow_blurRadius, 2*$dropShadow_blurRadius, 180, 270, $dropShadowGradientColors[$i]);
			imagearc($img_dropShadow, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_width, $dropShadow_offset+$dropShadow_blurRadius-$i, 2*$dropShadow_blurRadius, 2*$dropShadow_blurRadius, 270, 0, $dropShadowGradientColors[$i]);
			imagearc($img_dropShadow, $dropShadow_offset+$dropShadow_blurRadius-$i, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_height, 2*$dropShadow_blurRadius, 2*$dropShadow_blurRadius, 90,180, $dropShadowGradientColors[$i]);
			imagearc($img_dropShadow, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_width, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_height, 2*$dropShadow_blurRadius, 2*$dropShadow_blurRadius, 0, 90, $dropShadowGradientColors[$i]);
			// redraw all corners enlarged by 1 px to prevent pixel gaps
			imagearc($img_dropShadow, $dropShadow_offset+$dropShadow_blurRadius-$i, $dropShadow_offset+$dropShadow_blurRadius-$i, 2*$dropShadow_blurRadius+1, 2*$dropShadow_blurRadius+1, 180, 270, $dropShadowGradientColors[$i]);
			imagearc($img_dropShadow, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_width, $dropShadow_offset+$dropShadow_blurRadius-$i, 2*$dropShadow_blurRadius+1, 2*$dropShadow_blurRadius+1, 270, 0, $dropShadowGradientColors[$i]);
			imagearc($img_dropShadow, $dropShadow_offset+$dropShadow_blurRadius-$i, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_height, 2*$dropShadow_blurRadius+1, 2*$dropShadow_blurRadius+1, 90,180, $dropShadowGradientColors[$i]);
			imagearc($img_dropShadow, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_width, $dropShadow_offset-$dropShadow_blurRadius+$i+$dropShadow_height, 2*$dropShadow_blurRadius+1, 2*$dropShadow_blurRadius+1, 0, 90, $dropShadowGradientColors[$i]);
		}
		
		imagecopy($img_dropShadow, $img, $image_offset_x, $image_offset_y, 0, 0, imagesx($img), imagesy($img));
		
		return $img_dropShadow;
		imagedestroy($img_dropShadow);
	}

	function create_thumbnail($image) {
		
		$thumbnail_size=$GLOBALS["thumbnail_size"];
		$image_properties=getimagesize($image);
		switch ($image_properties[2]) {
			case 1: $thumb = imagecreatefromgif($image);break;
			case 2: $thumb = imagecreatefromjpeg($image);break;
			case 3: $thumb = imagecreatefrompng($image);break;
			default: die("<br/><br/>unknown file type: $image");
		}

		$temp = imagecreatetruecolor($thumbnail_size, $thumbnail_size);
		imagefill($temp, 0, 0, imagecolorresolve($temp, $GLOBALS["background_color"][0], $GLOBALS["background_color"][1], $GLOBALS["background_color"][2]));
		
		if ($image_properties[0]>$image_properties[1]) {
			$thumb = scale_image($thumb, (1/($image_properties[0]/$thumbnail_size)*0.93)); // *0.93 is an ugly hack to make image in the thumb smaller (reason: $thumbnail_size was first the size of the thumb-image, but now it is the size of the hole thumb, including dropshadow)
			if ($GLOBALS["thumbnail_border"]) { $thumb = draw_image_border($thumb); }
			if ($GLOBALS["thumbnail_dropShadow"]) { $thumb = draw_dropShadow($thumb, $GLOBALS["thumbnail_dropShadow_scale"], $GLOBALS["thumbnail_dropShadow_offset"], $GLOBALS["thumbnail_dropShadow_blurRadius"], $GLOBALS["background_color"], $GLOBALS["dropShadow_color"], $GLOBALS["thumbnail_offset_x"], $GLOBALS["thumbnail_offset_y"]); }
			imagecopy($temp, $thumb, 1, $thumbnail_size/2-imagesy($thumb)/2, 0, 0, imagesx($thumb), imagesy($thumb));
		}
		else {
			$thumb = scale_image($thumb, (1/($image_properties[1]/$thumbnail_size)*0.93)); // *0.93 is an ugly hack to make image in the thumb smaller (reason: $thumbnail_size was first the size of the thumb-image, but now it is the size of the hole thumb, including dropshadow)
			if ($GLOBALS["thumbnail_border"]) { $thumb = draw_image_border($thumb); }
			if ($GLOBALS["thumbnail_dropShadow"]) { $thumb = draw_dropShadow($thumb, $GLOBALS["thumbnail_dropShadow_scale"], $GLOBALS["thumbnail_dropShadow_offset"], $GLOBALS["thumbnail_dropShadow_blurRadius"], $GLOBALS["background_color"], $GLOBALS["dropShadow_color"], $GLOBALS["thumbnail_offset_x"], $GLOBALS["thumbnail_offset_y"]); }
			imagecopy($temp, $thumb, $thumbnail_size/2-imagesx($thumb)/2, 1, 0, 0, imagesx($thumb), imagesy($thumb));
		}
		
		switch ($GLOBALS["thumbnail_filetype"]) {
			case "png": {
				imagepng($temp, "./.thumbs/$thumbnail_size"."_thumb_".$image.".".$GLOBALS["thumbnail_filetype"]);
			}
			break;
			case "jpg": {
				imagejpeg($temp, "./.thumbs/$thumbnail_size"."_thumb_".$image.".".$GLOBALS["thumbnail_filetype"]);
			}
			break;
			default: {
				die("<br/><br/>ERROR: unsupported target filetype for thumbnails: ".$GLOBALS["thumbnail_filetype"]);
			}
		}
		
		imagedestroy($thumb);
		imagedestroy($temp);
		return TRUE;
	}

	function create_slideshow_item($image) {
		$image_properties=getimagesize($image);
		switch ($image_properties[2]) {
			case 1: $img = imagecreatefromgif($image);break;
			case 2: $img = imagecreatefromjpeg($image);break;
			case 3: $img = imagecreatefrompng($image);break;
			default: die("<br/><br/>unknown file type: $image");
		}
		
		if ($image_properties[0]>=($GLOBALS["width"]-2*$GLOBALS["image_margin"])) {
			$img = scale_image($img, ($GLOBALS["width"]-2*$GLOBALS["image_margin"])/$image_properties[0]);
			if ($GLOBALS["image_border"]) { $img = draw_image_border($img); }
			if ($GLOBALS["image_dropShadow"]) { $img = draw_dropShadow($img, $GLOBALS["image_dropShadow_scale"], $GLOBALS["image_dropShadow_offset"], $GLOBALS["image_dropShadow_blurRadius"], $GLOBALS["background_color"], $GLOBALS["dropShadow_color"], $GLOBALS["image_offset_x"], $GLOBALS["image_offset_y"]); }
		}
		else {
			if ($GLOBALS["image_border"]) { $img = draw_image_border($img); }
			if ($GLOBALS["image_dropShadow"]) { $img = draw_dropShadow($img, $GLOBALS["image_dropShadow_scale"], $GLOBALS["image_dropShadow_offset"], $GLOBALS["image_dropShadow_blurRadius"], $GLOBALS["background_color"], $GLOBALS["dropShadow_color"], $GLOBALS["image_offset_x"], $GLOBALS["image_offset_y"]); }
		}
		
		switch ($GLOBALS["slideshow_filetype"]) {
			case "png": {
				imagepng($img, "./.modified/".$GLOBALS["width"]."_mod_".$image.".".$GLOBALS["slideshow_filetype"]);
			}
			break;
			case "jpg": {
				imagejpeg($img, "./.modified/".$GLOBALS["width"]."_mod_".$image.".".$GLOBALS["slideshow_filetype"]);
			}
			break;
			default: {
				die("<br/><br/>ERROR: unsupported target filetype for thumbnails: ".$GLOBALS["slideshow_filetype"]);
			}
		}
		
		imagedestroy($img);
		return TRUE;
	}
	

?>