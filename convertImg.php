<?php
include_once 'sessionManager.php';
//are we in a session?
if ( !isset($_SESSION['lastReq']) ) {
	http_response_code(401);
	echo("Access not allowed!");
	exit;
}
Header ("Content-type: image/png"); 
include_once 'data.php';
include_once 'ltools.php';


//file Name
$file_name = getParam("file", "");
$data = explode("-",$file_name);
if ($file_name=="") exit; 
$file_name="images/".getAktDatabaseName()."/p".$file_name.".jpg";


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

//visibility
$picture = loadPictureAttributes(getAktDatabaseName(),$data[0],$data[1]);

if (($picture["visibleforall"]!="true" && !userIsLoggedOn()) || ($picture["deleted"]=="true" && !userIsAdmin()) ) {
	
}
else {
	//the resizing is going on here!
	imagecopyresized($resized_img, $new_img, $xpos, $ypos, 0, 0, $newwidth, $newheight, $width, $height);
}

//finally, save the image

ImageJpeg ($resized_img,null,80);

ImageDestroy ($resized_img);
ImageDestroy ($new_img);


?>

