<?php
//Parameter multipleGalleries is suported use ist only once to set this propery in the session variables
session_start();
include_once("igconfig.php");
$html_text_color 		= "#".sprintf("%02s", dechex($text_color[0])).sprintf("%02s", dechex($text_color[1])).sprintf("%02s", dechex($text_color[2]));
$html_hover_color 		= "#".sprintf("%02s", dechex($hover_color[0])).sprintf("%02s", dechex($hover_color[1])).sprintf("%02s", dechex($hover_color[2]));
$html_background_color 	= "#".sprintf("%02s", dechex($background_color[0])).sprintf("%02s", dechex($background_color[1])).sprintf("%02s", dechex($background_color[2]));
$html_background_color_l= "#".sprintf("%02s", dechex($background_colorl[0])).sprintf("%02s", dechex($background_colorl[1])).sprintf("%02s", dechex($background_colorl[2]));
$html_border_color 		= "#".sprintf("%02s", dechex($border_color[0])).sprintf("%02s", dechex($border_color[1])).sprintf("%02s", dechex($border_color[2]));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="author" content="Levente Maier" />
	<title><?PHP echo($TXT["Title"]);?></title>
	<style type="text/css">
		body	{font-family:	arial, sans-serif;
				text-align:		center;	
				color:			<?php echo "$html_text_color"; ?>;
				background-color:<?php echo "$html_background_color"; ?>;
				padding-top:	0px;		}
		#page	{width:			<?php echo "$width"; ?>px;
				margin:			0px auto;
				padding:		0px;
				text-align:		left;
				border:	solid <?php echo "$html_border_color"; ?> 1px;	}
		#menu	{	margin:		0px auto;
				padding:		5px;
				font-size:		11px;
				text-align:		left;
				border:	solid <?php echo "$html_border_color"; ?> 1px;	
				}
		#menuhr {border:	solid <?php echo "$html_border_color"; ?> 1px;height:1px;}
		#navigation {border-bottom:	solid <?php echo "$html_border_color"; ?> 1px;
				 padding:		5px;		}
		#statusbar {border-bottom:	solid <?php echo "$html_border_color"; ?> 1px; 	color:<?php echo "$html_text_color"; ?>;}
		#img_area {	text-align:	center;
				margin-top:	10px;		
				background-color:	<?php echo "$html_background_color"; ?>;
				}
		#footer	{border-top:	solid <?php echo "$html_border_color"; ?> 1px;
				padding:		10px;
				text-align:	center;		}
		#footer p	{	font-size:		10px;
				color:		<?php echo "$html_text_color"; ?>;
				font-weight:	bold;		}
		#commentArea {text-align:left; font-size:12px;
			border-right-width: 1px; border-right-style: solid; border-right-color: <?php echo "$html_border_color"; ?> ;
			border-top-width: 1px; border-top-style: solid; border-top-color: <?php echo "$html_border_color"; ?> ;
			border-left-width: 1px; border-left-style: solid; border-left-color: <?php echo "$html_border_color"; ?> ;
			border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: <?php echo "$html_border_color"; ?> ;
		}
		#comment {text-align:left; font-size:12px; background-color:<?php echo "$html_background_color_l"; ?>;border:0px;	}
		#commentList {text-align:left; font-size:12px; background-color:<?php echo "$html_background_color"; ?>;border:0px;	}
		#commentHeader {text-align:left; font-size:12px; background-color:<?php echo "$html_background_color_l"; ?>;border:0px;	}
		#metaData	{	font-size:10px;	color:<?php echo "$html_text_color"; ?>;	font-weight:normal;text-align:left;	}
		img {border:0px;	}
		p			{font-size:14px;color:<?php echo "$html_text_color"; ?>;font-weight:normal;	}
		a			{font-size:14px;color:<?php echo "$html_text_color"; ?>;text-decoration:none; }
		a:hover		{font-size:14px;color:<?php echo "$html_hover_color"; ?>;text-decoration:none; }
		#GGroup 	{font-size:14px;color:<?php echo "$html_text_color"; ?>;font-weight:bold;}
		#GLink 		{font-size:12px;color:<?php echo "$html_text_color"; ?>;font-weight:normal;	}
		#GLink:Hover{font-size:12px;color:<?php echo "$html_hover_color"; ?>;font-weight:normal;}
	</style>
</head>
<body>

<?PHP include("igframe.php"); ?>

</body>
</html>
