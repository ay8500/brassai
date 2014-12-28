<?PHP

$width				= 860;
$thumbnails_per_row	= 5;
$thumbnail_indexing	= TRUE;
$thumbnails_per_page= 15;

$thumbnail_filetype	= "jpg";	// png or jpg
$slideshow_filetype	= "jpg";	// png or jpg

if (isset($_GET['multipleGalleries'])) {
	$_SESSION['multipleGalleries'] = $_GET['multipleGalleries'] ;
}
if (isset($_SESSION['multipleGalleries'])) {
	$multipleGalleries = ($_SESSION['multipleGalleries'] == 1);
}
else
	$multipleGalleries		=	false;
	
$link_original_image	= 	false;

// delete all generated images after modifying anything below !!!
// slideshowsettings
$image_margin				= 10;
$image_border				= true;
$image_dropShadow			= true;
$image_offset_x				= 1;
$image_offset_y				= 1;
$image_dropShadow_scale		= 0.99;
$image_dropShadow_offset	= 12;
$image_dropShadow_blurRadius= 10;	// not greater then 46 (because of pixel gaps) and not greater then $dropShadow_offset

// thumbnailsettings
$thumbnail_border			= true;
$thumbnail_dropShadow		= true;
$thumbnail_offset_x			= 0;
$thumbnail_offset_y			= 1;
$thumbnail_dropShadow_scale	= 0.99;
$thumbnail_dropShadow_offset	= 6;
$thumbnail_dropShadow_blurRadius= 6;	// not greater then 46 (because of pixel gaps) and not greater then $dropShadow_offset

$text_color			= array(0,0,0);
$hover_color		= array(255,30, 255);
$background_color	= array(255, 255, 255);
$background_colorl	= array(200, 200, 199);
$border_color		= array(0, 30,20);
$dropShadow_color	= array(0, 0, 10);	// has to be darker then the background color!!!

$SupportedLang = array("hu","en","de"); //First language ist the default language

// Set languge include file
if (!isset($_SESSION['LANG'])) $_SESSION['LANG']=$SupportedLang[0];
// Change language
if (isset($_GET["language"]))  {
	$_SESSION['LANG']=$_GET["language"];
}

    $LangFile = "igLang_".$_SESSION['LANG'].".php";
    if(file_exists($LangFile))
        include $LangFile;
    else
        include "igLang_".$SupportedLang[0].".php";

?>
