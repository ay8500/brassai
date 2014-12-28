<?php
Header ("Content-type: image/png"); 
include_once("sessionManager.php");
if (!isset($_SESSION['scoolYear'])) exit; 
if(isset($_GET['file'])) $file_name=$_GET['file']; else exit;
if(isset($_GET['width'])) $ThumbWidth =$_GET['width']; else $ThumbWidth = 200;
$Thumb = false; if ((isset($_GET['thumb'])) && ($_GET['thumb']=="true")) $Thumb = true;
$color ="ffffff"; if (isset($_GET['color'])) $color=($_GET['color']); 

 
$limitedext = array(".gif",".jpg",".png",".jpeg",".bmp");		

//check the file's extension
$ext = strrchr($file_name,'.');
$ext = strtolower($ext);

//uh-oh! the file extension is not allowed!
if (!in_array($ext,$limitedext)) {
	exit();
}

if($ext== ".jpeg" || $ext == ".jpg"){
	$new_img = imagecreatefromjpeg($file_name);
}elseif($ext == ".png" ){
	$new_img = imagecreatefrompng($file_name);
}elseif($ext == ".gif"){
	$new_img = imagecreatefromgif($file_name);
}

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

//the resizing is going on here!
imagecopyresized($resized_img, $new_img, $xpos, $ypos, 0, 0, $newwidth, $newheight, $width, $height);

//finally, save the image

ImageJpeg ($resized_img,null,80);

ImageDestroy ($resized_img);
ImageDestroy ($new_img);


?>

