<?PHP 
$SiteTitle="A kolozsvári Brassai Sámuel véndiák zenedoboz";
$SiteDescription="Kendvenc zenénket itt lejátszhatod";
include("homemenu.php"); 
  if (isset( $_GET['link'])  ) { 
  	$link = $_GET['link'];
  	$nlink = explode("=",$link);
  	if (isset($nlink[1])) 
  		$Video=$nlink[1];
  	else $Video=$nlink[0];
  }
  else $Video="";
?>
<div class="sub_title">Zenedoboz</div>
<div class="container-fluid">
	<div style="text-align: center;">
		<div class="tabEmpty"><a style="margin-bottom: 10px" class="btn btn-default" href="zenetoplista.php">Vissza a toplistához. </a></div>
		<object  class="sub_title" width="660" height="525">
			<param name="movie" value="http://www.youtube.com/v/<?PHP echo($Video);?>&hl=de_DE&fs=1&rel=0&border=1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/<?PHP echo($Video);?>&hl=de_DE&fs=1&rel=0&border=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="660" height="525">
			</embed>
		</object>
		<div class="tabEmpty"><a style="margin-bottom: 10px" class="btn btn-default" href="zenetoplista.php">Vissza a toplistához. </a></div>
	</div>
</div>
 <?PHP  include "homefooter.php" ?>
