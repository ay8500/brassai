<?php
/**
 * Parameter:
 * 	color: 
 * 		Background color of the empty space if parameter thumb=true
 * 		Default value: ffffff 
 *  thumb: 
 *  	If true a sqare picture is generated the empty space if filled with the color parameter
 * 		Default value: false
 *  
 *  file:picture filename
 *  id: picture id
 *  
 *  quality: jpeg quality default 75
 *  
 *  width,height default value 200
 */
include_once 'tools/sessionManager.php';
//are we in a session?
if ( !isset($_SESSION['lastReq']) ) {
	http_response_code(401);
	echo("Access not allowed!");
	exit;
}
include_once 'dbBL.class.php';
include_once 'tools/ltools.php';


//file Name without extensions
$file_name = getParam("file", "");
$id = getParam("id", "");
if ($file_name=="" && $id=="") {
	http_response_code(401);
	echo("Missing parameter id or file!");
	exit; 
}

if ($file_name!="") {
	$picture = $db->getPictureByFileName($file_name);
	if ($picture==null) {
		http_response_code(401);
		echo("Picture:'".$file_name."' not found!");
		exit;
	}
} else {
	$picture = $db->getPictureById(intval($id));
	if ($picture==null) {
		http_response_code(401);
		echo("Picture id:".$id." not found!");
		exit;
	}
	$file_name=$picture["file"];
}


//Width
if(isset($_GET['width'])) $ThumbWidth =$_GET['width']; else $ThumbWidth = 200;

//Thumb
$Thumb = false; if ((isset($_GET['thumb'])) && ($_GET['thumb']=="true")) $Thumb = true;

//Color
$color ="ffffff"; if (isset($_GET['color'])) $color=($_GET['color']); 

//Quality
$quality=75; if (isset($_GET['quality'])) $quality=intval($_GET['quality']);
 
/*
//check the file's extension
$limitedext = array(".gif",".jpg",".png",".jpeg",".bmp");		

$ext = strrchr($file_name,'.');
$ext = strtolower($ext);

//the file extension is not allowed!
if (!in_array($ext,$limitedext)) {
	exit();
}
*/


//if($ext== ".jpeg" || $ext == ".jpg"){
	if (!file_exists($file_name)) {
		$file_name="images/avatar.jpg";
	}
	$new_img = imagecreatefromjpeg($file_name);
/*
}elseif($ext == ".png" ){
	$new_img = imagecreatefrompng($file_name);
}elseif($ext == ".gif"){
	$new_img = imagecreatefromgif($file_name);
}
*/
	
//list the width and height and keep the height ratio.

list($width, $height) = getimagesize($file_name);

//calculate the image ratio

$imgratio=$width/$height;

if ($imgratio>1){
	$newwidth = $ThumbWidth;
	$newheight = $ThumbWidth/$imgratio;
}else{
	$newheight = $ThumbWidth;
	$newwidth = $ThumbWidth*$imgratio;
}

//function for resize image.
$ypos = 0;
$xpos = 0;

if ($Thumb) { 
	$resized_img = imagecreatetruecolor($ThumbWidth,$ThumbWidth);
	imagefill($resized_img,0,0, imagecolorallocate($resized_img,hexdec(substr($color, 0,2)),hexdec(substr($color, 2,2)),hexdec(substr($color, 4,2))));
	$ypos = intval(($ThumbWidth-$newheight)/2 );
	$xpos = intval(($ThumbWidth-$newwidth)/2);
}
else
	$resized_img = imagecreatetruecolor($newwidth,$newheight);

//visibility
if (isset($picture["isDeleted"]) && ($picture["isDeleted"]==0 || userIsAdmin())) {
	//resizing the image
	imagecopyresized($resized_img, $new_img, $xpos, $ypos, 0, 0, $newwidth, $newheight, $width, $height);
	if ($picture["isVisibleForAll"]==0 && !userIsLoggedOn()  ) {
		imagefilter ( $resized_img , IMG_FILTER_PIXELATE, 16,true);
		imagefilter ( $resized_img , IMG_FILTER_GAUSSIAN_BLUR);
	}
}
else {
	imagecopyresized($resized_img, $new_img, $xpos, $ypos, 0, 0, $newwidth, $newheight, $width, $height);

}

//finally, return the image
Header ("Content-type: image/png");
ImageJpeg ($resized_img,null,$quality);

ImageDestroy ($resized_img);
ImageDestroy ($new_img);


?>

