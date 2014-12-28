<?PHP
if (!isset($SiteTitle))
	$SiteTitle="A kolozsv&aacute;ri Brassai S&aacute;muel l&iacute;ceum v&eacute;n diakjai";
$SupportedLang = array("hu"); //First language ist the default language

// Set languge include file
if (!isset($_SESSION['LANG'])) $_SESSION['LANG']=$SupportedLang[0];
// Change language
if (isset($_GET["language"]))  {
	$_SESSION['LANG']=$_GET["language"];
}

    $LangFile = "Lang_".$_SESSION['LANG'].".php";
    if(file_exists($LangFile))
        include $LangFile;
    else
        include "Lang_".$SupportedLang[0].".php";
?>