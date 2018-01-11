<?php
/**
 * Safety get paramateter read 
 */
function getGetParam($name,$def=null) {
	return html_entity_decode(htmlentities(isset($_GET[$name]) ? $_GET[$name] : $def,ENT_QUOTES),ENT_NOQUOTES);
		
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
	return getParam("action")==$action;
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
 * Set the response header to Ok or Bad Request
 * @param unknown $booleanResult
 */
function httpHeader($booleanResult) {
	if ($booleanResult) {
		header("HTTP/1.0 200 OK");
	}
	else { 
		header("HTTP/1.0 400 Bad Request");
	}
	return $booleanResult;
}

/**
 * Is the server the localhost?
 * @return true if localhost
 */
function localhost() {
	$whitelist = array('127.0.0.1','::1');
	
	if(in_array($_SERVER['REMOTE_ADDR'], $whitelist))
		return true;

	return false;
	
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
