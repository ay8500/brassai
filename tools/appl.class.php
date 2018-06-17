<?php
/**
 * The best framework for php made by MaierLabs (c) 2018
 * @author Maier Levente
 */
class Appl {
	public static $title = "";
	public static $subTitle = "";
	public static $description = "";
	public static $resultDbOperation = "";
	
	private static $renderingStarted=false;
	
	private static $js = array();
	private static $css = array();
	private static $cssStyle ="";
	private static $jsScript = "";
	
	private static $webAppVersion = '2018-06-17';
	
	public static function setSiteTitle($title,$subtitle=null,$description=null) {
		self::$title=$title;
		if (null!=$description) self::$description=$description;
		if( null!=$subtitle) self::$subTitle=$subtitle;
	}

	public static function setSiteSubTitle($subtitle) {
		self::$subTitle=$subtitle;
	}
	
	public static function setSiteDesctiption($description) {
		self::$description=$description;
	}
	
	/**
	 * add a css file
	 */
	public static function addCss($cssFile) {
		array_push(self::$css, $cssFile);
	}
	
	/**
	 * add css style
	 */
	public static function addCssStyle($style) {
		if (!self::$renderingStarted) {
			self::$cssStyle .="\n".$style;
		} else {
			echo("<style>\n".$style."</style>\n");
		}
	}
	
	/**
	 * add a javascript file
	 */
	public static function addJs($javascriptFile,$phpInterpreter=false) {
		$j = new stdClass();
		$j->file=$javascriptFile;
		$j->interpret=json_encode($phpInterpreter);
		array_push(self::$js, $j);
	}

	/**
	 * add javascript
	 */
	public static function addJsScript($script) {
		self::$jsScript .="\n".$script;
	}

	/**
	 * include the css in html
	 */
	public static function includeCss() {
		foreach (self::$css as $cssfile) {
			echo('<link rel="stylesheet" type="text/css" href="'.$cssfile.'?v='.self::$webAppVersion.'"></link>'."\n");
		}
		if (self::$cssStyle!='')
			echo("<style>".self::$cssStyle."</style>\n");
		self::$renderingStarted=true;
	}
	

	/**
	 * include the javascript in html
	 */
	public static function includeJs() {
		foreach (self::$js as $j) {
			if ($j->interpret==='true') {
				echo  '<script type="text/javascript">'."\n"."// Interpreted js:".$j->file."\n";
				include $j->file;
				echo  "\n".'</script>'."\n";
			} else {
				echo('<script type="text/javascript" src="'.$j->file.'?v='.self::$webAppVersion.'"></script>'."\n");
			}
		}
		echo('<script type="text/javascript">'."\n// Included js".self::$jsScript.'</script>');
	}
	
	/**
	 * set message after loading the page type: info, success, danger, warning
	 */
	public static function setMessage($text,$type) {
		if (self::$renderingStarted==false) {
			self::$resultDbOperation.='<div class="alert alert-'.$type.'">'.$text.'</div>';
		} else {
			self::addJsScript("showDbMessage('".$text."','".$type."');");
		}
	}
}
