<?php

namespace maierlabs\lpfw;

include_once __DIR__ . "/../config.class.php";

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
	public static $translator = null;
	
	private static $renderingStarted=false;
	
	private static $js = array();
	private static $css = array();
	private static $cssStyle ="";
	private static $jsScript = "";
	
	private static $members = array();

    /**
     * Set translated title, subtitle and desription also used in the metatags.
     * @param string $title
     * @param string $subtitle
     * @param string $description
     * @return void
     */
	public static function setSiteTitle($title,$subtitle=null,$description=null) {
		self::$title=self::__($title);
		if (null!=$description) self::$description=self::__($description);
		if( null!=$subtitle) self::$subTitle=self::__($subtitle);
	}

    /**
     * Set translated subtitle also used in the metatags.
     * @param string $subtitle
     * @return void
     */
	public static function setSiteSubTitle($subtitle) {
		self::$subTitle=self::__($subtitle);
	}

    /**
     * Set translated description also used in the metatags.
     * @param string $description
     * @return void
     */
	public static function setSiteDesctiption($description) {
		self::$description=self::__($description);
	}

    /**
     * datetime as string
     * @param string|datetime $dateTime
     * @return string
     */
	public static function dateTimeAsStr($dateTime,$format=null) {
	    if (!is_object($dateTime)) {
	        $dateTime = new \DateTime($dateTime);
        }
        if (isset($_SESSION["timezone"])) {
            $dateTime->modify(($_SESSION["timezone"]-\Config::$timeZoneOffsetMinutes).'minute');
        }
        if($format==null) {
            return $dateTime->format(\Config::$dateTimeFormat);
        } else {
            return $dateTime->format($format);
        }
    }

    public static function dateAsStr($date,$format=null) {
        if($format==null) {
            return self::dateTimeAsStr($date,\Config::$dateFormat);
        } else {
            return self::dateTimeAsStr($date,$format);
        }
    }


    /**
	 * add a css file
     * @param string $cssFile
     * @return void
	 */
	public static function addCss($cssFile,$phpInterpreter=false,$verion=true) {
	    if (self::$renderingStarted) {
            echo  '<style>'."\n"."/* iserted and interpreted css:".$cssFile."*/\n";
            include $cssFile;
            echo  "\n".'</style>'."\n";
        } else {
            $css = new \stdClass();
            $css->file = $cssFile;
            $css->interpret = json_encode($phpInterpreter);
            $css->version = $verion;
            array_push(self::$css, $css);
        }
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
            if ($cssfile->interpret==='true') {
                echo  '<style>'."\n"."/* Interpreted css:*/".$cssfile->file."\n";
                include $cssfile->file;
                echo  "\n".'</style>'."\n";
            } else {
                if ($cssfile->version) {
                    echo('<link rel="stylesheet" type="text/css" href="'.$cssfile->file.'?v='.\Config::$webAppVersion.'"></link>'."\n");
                } else {
                    echo('<link rel="stylesheet" type="text/css" href="'.$cssfile->file.'"></link>'."\n");
                }
            }
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
					echo('<script type="text/javascript" src="'.$j->file.'?v='.\Config::$webAppVersion.'"></script>'."\n");
				} else {
					echo('<script type="text/javascript" src="'.$j->file.'"></script>'."\n");
				}
			}
		}
		if (self::$jsScript!='')
		    echo('<script type="text/javascript">'."\n// Included js".self::$jsScript.'</script>');
	}
	
	/**
	 * set translated message after loading the page type: info, success, danger, warning
     * @param string $text
     * @param string $type
     * @return void
	 */
	public static function setMessage($text,$type) {
		if (self::$renderingStarted==false) {
			self::$resultDbOperation.='<div class="alert alert-'.$type.'">'.self::__($text).'</div>';
		} else {
			self::addJsScript("showDbMessage('".self::__($text)."','".$type."');");
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
    public static function getMember($name, $default=null) {
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
    public static function getMemberId(string $name)
    {
        $m =self::getMember($name);
        return ($m!=null && isset($m["id"]))?$m["id"]:null;
    }

    /**
     * translate text in the actual language by using the function stored in self::$translator
     * @param $text
     * @return mixed
     */
    public static function __($text) {
        if (self::$translator==null) {
            return $text;
        } else {
            return call_user_func(self::$translator,$text);
        }
    }

    public static function _($text) {
        echo(self::__($text));
    }
}
