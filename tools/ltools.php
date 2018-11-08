<?php
/*
 * Maierlabs PHP Tools for easy parameter reading  *
 */

/**
 * Safety get paramateter read
 * @param string $name
 * @param string $def
 * @return string
 */
function getGetParam($name,$def=null) {
	if (isset($_GET[$name]))
		return html_entity_decode(htmlentities($_GET[$name],ENT_QUOTES),ENT_NOQUOTES);
	return  $def;
		
}

/**
 * Safety post paramateter read
 * @param string $name
 * @param string $def
 * @return string
 */
function getPostParam($name,$def=null) {
	return html_entity_decode(htmlentities(isset($_POST[$name]) ? $_POST[$name] : $def,ENT_QUOTES),ENT_NOQUOTES);
		
}

/**
 * Check the value of parameter with the name "action"
 * @param string $action
 * @return string
 **/
function isActionParam($action) {
	return getParam("action")===$action;
}

/**
 * Safety paramateter read default value can be specified othewise is NULL
 * @param string $name
 * @param string $def
 * @return string
 */
function getParam($name,$def=null) {
	if (isset($_POST[$name]))
		return getPostParam($name,$def);
	else
		return getGetParam($name,$def);
}

/**
 * Read an integer paramateter.Default value can be specified othewise is 0
 * @param string $name
 * @param int $def
 * @return int
 */
function getIntParam($name,$def=0) {
	$ret = getParam($name);
	if (null!=$ret)
		return intval($ret);
	else
		return $def;
}

/**
 * Is the server the localhost?
 * @return boolean true if localhost
 */
function isLocalhost() {
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $whitelist = array('127.0.0.1', '::1', '192.168.201.40','192.168.201.41');
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }
    return true;
}

/**
 * Returns the readable name of a enum constant value
 * @param string $className
 * @param string $value
 * @return string
 */
function getConstantName($className,$value)
{
	try{
		$class = new ReflectionClass($className);
	} catch (Exception $e) {
		$class = new stdClass();
	}
	$constants = array_flip($class->getConstants());
	if (isset($constants[$value]))
	    return $constants[$value];
	return '';
}

/**
 * Create a link for http address or mailto für e-mail
 * @param $text
 * @return null|string|string[]
 */
function createLink($text,$short=false) {
    if ($short) {
        $text = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]#-/\(\)]~", "<a target=\"_blank\" href=\"\\0\">Link</a>", $text);
        $text = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/', '<a href="mailto:$1">E-Mail</a>', $text);
    } else {
        $text = preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]#-/\(\)]~", "<a target=\"_blank\" href=\"\\0\">\\0</a>", $text);
        $text = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/', '<a href="mailto:$1">$1</a>', $text);
    }
    return $text;
}