<?php
/**
 * Parameter:
 *  pictureid:picture id
 *  personid: person id
 *  size: if empty the original size
 *  padding: padding in % value eg 10 for 10%
 */

//are we in a session?
include_once 'lpfw/sessionManager.php';
if ( !isset($_SESSION['lastReq']) ) {
    http_response_code(401);
    die('Unauthorized');
}
include_once 'dbBL.class.php';
include_once 'dbDaPersonInPicture.class.php';
include_once 'lpfw/ltools.php';


//file Name without extensions
$pictureid = getParam("pictureid");
$personid = getParam("personid");
if ($personid==null && $pictureid==null) {
    http_response_code(401);
    die("Missing parameter personid and pictureid!");
}

$picture = $db->getPictureById(intval($pictureid));
if ($picture==null) {
    http_response_code(401);
    die("Picture id:".$pictureid." not found!");
}
$file_name=$picture["file"];

$person = $db->getPersonByID(intval($personid));
if ($person==null) {
    http_response_code(401);
    die("Person id:".$personid." not found!");
}

$dbPIP = new dbDaPersonInPicture($db);
$pictureTag = $dbPIP->getPersonInPicture(intval($pictureid),intval($personid));
if (null==$pictureTag) {
    http_response_code(401);
    die("Person id:".$personid." in picture id:".$pictureid." not tagged!");
}

//Width
if(isset($_GET['size'])) $new_size = intval($_GET['size']); else $new_size = null;


//Color
$padding =getParam("padding",30);


$new_img = imagecreatefromjpeg($file_name);

//list the width and height and keep the height ratio.

list($width, $height) = getimagesize($file_name);

//calculate the image ratio

$imgratio=$width/$height;



$size = round(floatval($pictureTag["size"])*$width);

$padding = $padding/100*$size;
$size = $size + $padding;

$xpos = round(floatval($pictureTag["xPos"])*$width-$padding/2);
$ypos = round(floatval($pictureTag["yPos"])*$height-$padding/2);
if ($new_size == null)
    $new_size = $size;

$resized_img = imagecreatetruecolor($new_size,$new_size);

//create the image
//imagecopyresized($resized_img, $new_img, 0,0,$xpos, $ypos, $new_size, $new_size, $size, $size);
imagealphablending($resized_img,true);
imagecopyresampled($resized_img,$new_img,0,0,$xpos,$ypos,$new_size,$new_size,$size,$size);
$mask = imagecreatetruecolor($new_size,$new_size);
$transparent = imagecolorallocate($mask,254,254,254);
imagecolortransparent($mask,$transparent);
imagefilledellipse($mask,$new_size/2,$new_size/2,$new_size,$new_size,$transparent);
$red = imagecolorallocate($mask,0,0,0);
imagecopymerge($resized_img,$mask,0,0,0,0,$new_size,$new_size,100);
imagecolortransparent($resized_img,$red);



//finally, return the image
header ("Content-type: image/png");
imagepng($resized_img,null);

imagedestroy ($resized_img);
imagedestroy ($new_img);


?>