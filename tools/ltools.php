<?php
/**********************************************
 * Levi PHP Tools for easy parameter reading  *
 **********************************************/

/**
 * Safety get paramateter read 
 */
function getGetParam($name,$def=null) {
	if (isset($_GET[$name]))
		return html_entity_decode(htmlentities($_GET[$name],ENT_QUOTES),ENT_NOQUOTES);
	return  $def;
		
}

/**
 * Safety post paramateter read 
 */
function getPostParam($name,$def=null) {
	return html_entity_decode(htmlentities(isset($_POST[$name]) ? $_POST[$name] : $def,ENT_QUOTES),ENT_NOQUOTES);
		
}

/**
 * Check the value of parameter with the name "action"
 **/
function isActionParam($action) {
	return getParam("action")===$action;
}

/**
 * Safety paramateter read default value can be specified othewise is NULL 
 */
function getParam($name,$def=null) {
	if (isset($_POST[$name]))
		return getPostParam($name,$def);
	else
		return getGetParam($name,$def);
}

/**
 * Read an integer paramateter.Default value can be specified othewise is 0 
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
 * @return true if localhost
 */
function isLocalhost() {
	$whitelist = array('127.0.0.1','::1');
	return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
}

/**
 * Returns the readable name of a enum constant value
 * @param unknown $className
 * @param unknown $value
 */
function getConstantName($className,$value)
{
	$class = new ReflectionClass($className);
	$constants = array_flip($class->getConstants());
	return $constants[$value];
}
?>
