<?php
/**
 * Safety get paramateter read 
 */
function getGetParam($name,$def) {
	return htmlentities(isset($_GET[$name]) ? $_GET[$name] : $def,ENT_QUOTES);
		
}

/**
 * Safety post paramateter read 
 */
function getPostParam($name,$def) {
	return htmlentities(isset($_POST[$name]) ? $_POST[$name] : $def,ENT_QUOTES);
		
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

function httpHeader($booleanResult) {
	if ($booleanResult) {
		header("HTTP/1.0 200 OK");
		//header("Status: 200 OK");
	}
	else { 
		header("HTTP/1.0 400 Bad Request");
		//header("Status: 400 Bad Request");
	}
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

function getConstantName($className,$value)
{
	$class = new ReflectionClass($className);
	$constants = array_flip($class->getConstants());

	return $constants[$value];
}

?>