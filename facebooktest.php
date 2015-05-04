<?php
session_start();
$_SESSION['FacebookId'] = "a965038823537045";
$_SESSION['FacebookName'] = "Levi Levi";
$_SESSION['FacebookEmail'] =  "MailLevi";
session_write_close();

//print_r($_SESSION);

header("Location: start.php");
?>