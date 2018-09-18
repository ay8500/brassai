<?php

namespace maierlabs\lpfw;


/**
 * /**
 * The best framework for php made by MaierLabs (c) 2018
 * @author Maier Levente
 * @package maierlabs\lpfw
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
	
	private static $webAppVersion = '2018-08-14';

	private static $members = array();

    /**
     * Set title, subtitle and desription also used in the metatags.
     * @param string $title
     * @param string $subtitle
     * @param string $description
     * @return void
     */
	public static function setSiteTitle($title,$subtitle=null,$description=null) {
		self::$title=$title;
		if (null!=$description) self::$description=$description;
		if( null!=$subtitle) self::$subTitle=$subtitle;
	}

    /**
     * Set subtitle also used in the metatags.
     * @param string $subtitle
     * @return void
     */
	public static function setSiteSubTitle($subtitle) {
		self::$subTitle=$subtitle;
	}

    /**
     * Set description also used in the metatags.
     * @param string $description
     * @return void
     */
	public static function setSiteDesctiption($description) {
		self::$description=$description;
	}

  	/**
	 * add a css file
     * @param string $cssFile
     * @return void
	 */
	public static function addCss($cssFile) {
		array_push(self::$css, $cssFile);
	}
	
	/**
	 * add css style
     * @param string $style
     * @return void
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
     * @param string $javascriptFile
     * @param boolean $phpInterpreter if the javascript file contains php code
     * @param boolean $verion insert a version parameter
     * @return void
	 */
	public static function addJs($javascriptFile,$phpInterpreter=false,$verion=true) {
		$j = new \stdClass();
		$j->file=$javascriptFile;
		$j->interpret=json_encode($phpInterpreter);
		$j->version=$verion;
		array_push(self::$js, $j);
	}

	/**
	 * add javascript
     * @param string $script
     * @return void
	 */
	public static function addJsScript($script) {
		self::$jsScript .="\n".$script;
	}

	/**
	 * include the collected css in html
     * @return void
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
	 * include the collected javascript in html
     * @return void
	 */
	public static function includeJs() {
		foreach (self::$js as $j) {
			if ($j->interpret==='true') {
				echo  '<script type="text/javascript">'."\n"."// Interpreted js:".$j->file."\n";
				include $j->file;
				echo  "\n".'</script>'."\n";
			} else {
				if ($j->version) {
					echo('<script type="text/javascript" src="'.$j->file.'?v='.self::$webAppVersion.'"></script>'."\n");
				} else {
					echo('<script type="text/javascript" src="'.$j->file.'"></script>'."\n");
				}
			}
		}
		echo('<script type="text/javascript">'."\n// Included js".self::$jsScript.'</script>');
	}
	
	/**
	 * set message after loading the page type: info, success, danger, warning
     * @param string $text
     * @param string $type
     * @return void
	 */
	public static function setMessage($text,$type) {
		if (self::$renderingStarted==false) {
			self::$resultDbOperation.='<div class="alert alert-'.$type.'">'.$text.'</div>';
		} else {
			self::addJsScript("showDbMessage('".$text."','".$type."');");
		}
	}

    /**
     * set application member object
     * @param string $name
     * @param object $object
     * @return void
     */
	public static function setMember($name,$object) {
	    self::$members[$name]=$object;
    }

    /**
     * get application member object
     * @param string $name
     * @param object $default
     * @return object
     */
    public static function getMemeber($name,$default=null) {
	    if (isset(self::$members[$name])) {
	        return self::$members[$name];
        } else {
	        if ($default!=null)
	            return $default;
        }
        return null;
    }

    /**
     * get application member id
     * @param string $name
     * @return int|null
     */
    public static function getMemeberId(string $name)
    {
        $m =self::getMemeber($name);
        return ($m!=null && isset($m["id"]))?$m["id"]:null;
    }
}
