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
 * Safety paramateter read 
 */
function getParam($name,$def) {
	if (isset($_POST[$name]))
		return getPostParam($name,$def);
	else
		return getGetParam($name,$def);
}

function getIntParam($name,$def) {
	$ret = getParam($name,"");
	if ($ret!="")
		return (int)$ret;
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

?>
