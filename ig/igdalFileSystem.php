<?PHP

	//Succes of error message from data acces operation
	$operationMessage="";
	
	//compare funktion to sort the gallerys by group and short description
	function cmpGalleryConfig($a, $b)
	{
		return strcasecmp($a["group"].$a["shortDes"], $b["group"].$b["shortDes"]);
	}

	//Get the list of gallerys
	function getGallerys() {
		$dirlist = glob("*",GLOB_ONLYDIR);			//Direktorylist each subdirectory can be a gallery if the file pg.txt exists 
		$gcount=0;
		$gallerys=array() ;
		foreach ($dirlist as $tempDir) {
			$ss = getGalleryData($tempDir."/pg.txt");
			if ($ss["group"]!="") {
				$ss["dir"]=$tempDir;
				$gallerys[$gcount++] = $ss;
			}
		}
		if (!empty($gallerys)) usort($gallerys,"cmpGalleryConfig");
		return $gallerys;
	}

	//Get list of gallery groups
	function getGalleryGroups() {
		$gallerys=getGallerys();
		$ggroup="";
		$i=0;
		$group=array();
		foreach ($gallerys as $gs) {
			if (strncmp($gs["group"],$ggroup,strlen($gs["group"]))!=0) {
				$ggroup=trim($gs["group"]);
				$group[$i++]=$ggroup;
			}
		}
		return $group;
	}
	
	//Reads the config data of an album
	function getGalleryData($fileName) {
		if  (file_exists($fileName)) { 
			$file=fopen($fileName, "r");
			$s = explode("\t",fgets($file));
			$r["default"]=($s[0]=="dd");
			$r["group"]=$s[1];
			$r["shortDes"]=$s[2];
			if (isset($s[3])) $r["longDes"]=$s[3] ;else $r["longDes"]=$s[2];
			if (isset($s[4])) $r["user"]=$s[4] ;else $r["user"]="";
			if (isset($s[5])) $r["date"]=$s[5] ;else $r["date"]="";
			fclose($file);
		}
		else {
			$r["default"]=false;
			$r["group"]="";
			$r["shortDes"]="";
			$r["user"]="";
			$r["longDes"]="";
			$r["date"]="";
		}
		return $r;
	}
	
	//gallery decription
	function getGalleryDescription() {
		$s = getGalleryData("./pg.txt");
		return $s["longDes"];
	}

	function getGalleryShortDescription($p) {
		$s = getGalleryData("./".$p."/pg.txt");
		return $s["shortDes"];
	}

	function CompareImages($a, $b) 
	{
	    $a = strtoupper($a);
	    $b = strtoupper($b);
	    if ($a == $b) {
	         return 0;
	    }
	    return ($a < $b) ? -1 : +1;
	}
	
	//get gallery images names
	function getGalleryImages($gallery) {
		$images_array = array();
		if ($gallery!="") {
			if (!is_dir($gallery)) {chdir('..');}
			chdir($gallery);
			$directory = dir("./");	
			while ($file = $directory->read()) {
				if (is_file($file) and in_array(strtolower(substr($file, -4)), array(".png", ".jpg", ".gif"))) {
					$images_array[] = $file;
				}
			}
			$directory->close();
			usort( $images_array, 'CompareImages');
		}
		return $images_array;
	}
	
	//delete image from gallery
	function deleteImage($image) {
		global $operationMessage;
		global $thumbnail_size;
		global $width;
		global $slideshow_filetype;
		global $gallery;
		global $TXT; 
		global $LANG;
		$thumb="./.thumbs/".$thumbnail_size."_thumb_".$image.".".$slideshow_filetype;
		$slide="./.modified/".$width."_mod_".$image.".".$slideshow_filetype;
		$image="./".$image;
		$operationMessage="Bild: $image wurde gelöscht!";
		if (file_exists($image) && file_exists($thumb) && file_exists($slide)) {
			if(!unlink("$thumb")) {
				$operationMessage=$TXT["DBErrorDeleteFile"].$image;exit;
			}
			if(!unlink("$slide")) {
				$operationMessage=$TXT["DBErrorDeleteFile"].$image;exit;
			}
			if(!unlink("$image")) {
				$operationMessage=$TXT["DBErrorDeleteFile"].$image;exit;
			}
			if(!unlink("$image".".txt")) {
				$operationMessage=$TXT["DBErrorDeleteFile"].$image;exit;
			}
		}
		return 1;
	}
	
	//saveGalleryDescription
	function saveGalleryDescription($group,$shortDesc,$longDesc,$default) {
		global $TXT; 
		global $LANG;
		$file=fopen("./pg.txt", "w");
		$ddate=date('d.M.Y H:i');
		if ($default) $defValue="dd"; else $defValue="d";
		fwrite($file,$defValue."\t".$group."\t".$shortDesc."\t".$longDesc."\t".$_SESSION['USER']."\t".$ddate."\t\r\n");
		fclose($file);
		global $operationMessage;
		$operationMessage=$shortDesc.$TXT["DBOKSaveGallery"];
		return 1;
	}
	
	//increment picture view counter
	function incViewCounter($imageFileName,$count) {
		$maxIndex=1;$res=0;
	    if (file_exists($imageFileName.".txt")) {
			$file=fopen($imageFileName.".txt","r");
			while (!feof($file)) {
				$b[$maxIndex-1] = fgets($file);
				$maxIndex++;
			}
			fclose ($file);
			$file=fopen($imageFileName.".txt","w");
			for ($i=1;$i<$maxIndex;$i++) {
				if ($i==1) {
					$d=explode("\t",$b[0]);
					if (!isset($d[2])) $d[2]=""; 	//Picture title set to empty string 
					if ($d[0]!="#") { 
						fwrite($file,"#\t1\t\t\r\n"); 
						fwrite($file,$b[0]);
					}
					else 
					{	
						if (((isset($_SESSION['ADMIN']))&&($_SESSION['ADMIN'])) || (!$count))	
							fwrite($file,"#\t".$d[1]."\t".$d[2]."\t\r\n");
						else 
							fwrite($file,"#\t".++$d[1]."\t".$d[2]."\t\r\n");
						$res=$d[1];
					}
				}
				else {
					fwrite($file,$b[$i-1]);
				}
			}
			fclose ($file);
		}
		else {
			$file=fopen($imageFileName.".txt","a");
			fwrite($file,"#\t1\t\t\r\n"); 
			fclose($file);
		}
		return $res;
	}
	
	//insert picture title
	function setPictureTitle($imageFileName,$title) {
		$maxIndex=1;
	    if (file_exists($imageFileName.".txt")) {
			$file=fopen($imageFileName.".txt","r");
			while (!feof($file)) {
				$b[$maxIndex-1] = fgets($file);
				$maxIndex++;
			}
			fclose ($file);
			$file=fopen($imageFileName.".txt","w");
			for ($i=1;$i<$maxIndex;$i++) {
				if ($i==1) {
					$d=explode("\t",$b[0]);
					if (!isset($d[2])) $d[2]=""; 	//Picture title set to empty string 
					if ($d[0]!="#") { 
						fwrite($file,"#\t1\t\t\r\n"); 
						fwrite($file,$b[0]);
					}
					else 
					{	
						fwrite($file,"#\t".$d[1]."\t".$title."\t\r\n");
					}
				}
				else {
					fwrite($file,$b[$i-1]);
				}
			}
			fclose ($file);
		}
	}
	
	//insert a comment to the picture
	function insertComment($imageFileName,$Name,$Email,$Comment,$paramQ,$paramC) {
		$file=fopen($imageFileName.".txt","a");
		$ddate=date('d.M.Y H:i');
		fwrite($file,trim($Name)."\t".trim($Comment)."\t".trim($Email)."\t".$_SERVER['REMOTE_ADDR']."\t".$ddate."\t".$paramQ."\t".$paramC."\t\r\n");
		fclose($file);
	}

	//get picture title
	function getPictureTitle($imageFileName) {
	    if (file_exists($imageFileName.".txt")) {
			$file=fopen($imageFileName.".txt","r");
			while (!feof($file)) {
				$b = explode("\t",fgets($file));
				if(($b[0]!="") && ($b[0]="#") && (isset($b[2]))) {
					return $b[2];
				}
			}
			fclose($file);
		}
		return false;
	}
	
	//does the picture have a comment
	function hasComment($imageFileName) {
	    if (file_exists($imageFileName.".txt")) {
			$file=fopen($imageFileName.".txt","r");
			while (!feof($file)) {
				$b = explode("\t",fgets($file));
				if(($b[0]!="")&&($b[0]!="#")&& isset($b[1])) {
					return true;
				}
			}
			fclose($file);
		}
		return false;
	}

	//delete picture comment
	function deleteComment($imageFileName, $line) {
		$maxIndex=1;
		global $TXT; 
		global $LANG;
	    if (file_exists($imageFileName.".txt")) {
			$file=fopen($imageFileName.".txt","r");
			while (!feof($file)) {
				$b[$maxIndex-1] = fgets($file);
				$maxIndex++;
			}
			fclose ($file);
			$file=fopen($imageFileName.".txt","w");
			for ($i=1;$i<$maxIndex;$i++) {
				if ($i!=$line) {
					fwrite($file,$b[$i-1]);
					//echo($b[$i-1]."<br/>");
				}
			}
			fclose ($file);
		}
		global $operationMessage;
		$operationMessage=$TXT["DBOKDelPictComment"];
		return 1;
	}

?>