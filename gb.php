<?PHP 
	$SiteTitle="A kolozsvári Brassai Sámuel líceum vén diákok vendégkönyve";
	include("homemenu.php"); 
	
	// Not Work :( include 'gb/gbinclude.php';

	echo('<table style="width:100%;height:100%;"><tr style="width:100%;height:100%"><td style="width:100%;height:100%;text-align:center;">'."\r\n");
    echo('<iframe name="inhalt" src="gb/gb.php" width=100% height=100% marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=yes></iframe>'."\r\n");
	echo('</td></tr></table>');

	include_once 'homefooter.php';
?>
