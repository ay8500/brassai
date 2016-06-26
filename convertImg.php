<?php
include_once 'sessionManager.php';
//are we in a session?
if ( !isset($_SESSION['lastReq']) ) {
	http_response_code(401);
	echo("Access not allowed!");
	exit;
}
include_once 'data.php';
include_once 'ltools.php';


//file Name without extensions
$file_name = getParam("file", "");
if ($file_name=="") {
	echo("Missing parameter file!");
	exit; 
}

//filename userId-pictureId
$data = explode("-",$file_name);
if (sizeof($data)!=2) {
	echo("Wrong parameter file!");
	exit;
}
$databaseName = getAktDatabaseName();
if ($data[0]=="all")
	$databaseName = $databaseName . 'group';
$file_name="images/".$databaseName."/p".$file_name.".jpg";


//Width
if(isset($_GET['width'])) $ThumbWidth =$_GET['width']; else $ThumbWidth = 200;

//Thumb
$Thumb = false; if ((isset($_GET['thumb'])) && ($_GET['thumb']=="true")) $Thumb = true;

//Color
$color ="ffffff"; if (isset($_GET['color'])) $color=($_GET['color']); 

 
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
$picture = loadPictureAttributes($databaseName,$data[0],$data[1]);

if ($picture["deleted"]!="true" || userIsAdmin()) {
	//resizing the image
	imagecopyresized($resized_img, $new_img, $xpos, $ypos, 0, 0, $newwidth, $newheight, $width, $height);
	if ($picture["visibleforall"]!="true" && !userIsLoggedOn()  ) {
		imagefilter ( $resized_img , IMG_FILTER_PIXELATE, 6,true);
		imagefilter ( $resized_img , IMG_FILTER_GAUSSIAN_BLUR);
	}
}

//finally, return the image
Header ("Content-type: image/png");
ImageJpeg ($resized_img,null,80);

ImageDestroy ($resized_img);
ImageDestroy ($new_img);


?>

