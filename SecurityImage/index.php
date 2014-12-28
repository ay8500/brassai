<?php
	session_start();
	$paramSI=htmlentities(isset($_GET['paramSI']) ? $_GET['paramSI'] : "",ENT_QUOTES);
	if ($paramSI  == $_SESSION['SECURITY_CODE']) {
		$Message="Security code OK.";
	} else {
		$Message="Security code wrong.";
	}

?>
<div class="title"> Security code </div>

<img name="paramSI" alt="" src="SecurityImage.php" />
<form>
<input name="paramSI"  type="text" size="6"/>
</form>
<div class="message"><?PHP echo($Message); ?></div>
