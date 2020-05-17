<?php
include_once '../config.class.php';
include_once Config::$lpfw.'sessionManager.php';

$paramSI=htmlentities(isset($_GET['paramSI']) ? $_GET['paramSI'] : "",ENT_QUOTES);
	if ($paramSI  == $_SESSION['SECURITY_CODE']) {
		$Message="Security code OK.";
	} else {
		$Message="Security code wrong.";
	}

?>
<div class="title"> Security code </div>

<img name="paramSI" alt="" src="SecurityImage" />
<form>
<input name="paramSI"  type="text" size="6"/>
</form>
<div class="message"><?PHP echo($Message); ?></div>
