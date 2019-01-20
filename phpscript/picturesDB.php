<?php
session_start();
//User is logged in and have the role of admin
if (!isset($_SESSION['uRole']) || strstr($_SESSION['uRole'],"admin")=="") 
	die("Only for admins");

error_reporting(1);
set_time_limit(0);

include_once __DIR__ . "/../config.class.php";
$db = \Config::getDatabasePropertys();
db_pictures($db->host,$db->user,$db->password,$db->database) ;



// ab hier nichts mehr ändern
function db_pictures($dbhost, $dbuser, $dbpwd, $dbname)
{
	echo("Result of pictures checking source filesystem<br/>");
	
	$allPictures=0;$okPictures=0;$errorPictures=0;
	$conn = mysqli_connect($dbhost, $dbuser, $dbpwd,$dbname) or die(mysqli_error());
	
	$pictures = mysqli_query($conn,"SELECT * FROM picture");
	while ($picture = mysqli_fetch_array($pictures))
	{
		$allPictures++;
		$file= dirname(__DIR__) .DIRECTORY_SEPARATOR. $picture["file"];
		if(file_exists($file)) {
			$okPictures++;
		} else {
			$errorPictures++;
			if(isset($picture["personID"])) $id="Person:".$picture["personID"]." ";
			if(isset($picture["schoolID"])) $id="School:".$picture["schoolID"]." ";
			if(isset($picture["classID"])) $id="Class:".$picture["classID"]." ";
			echo($id.$file."<br/>");
		}
	}
	$pictures = mysqli_query($conn,"SELECT * FROM person");
	while ($picture = mysqli_fetch_array($pictures))
	{
		if($picture["picture"]!=null) {
			$allPictures++;
			$file= dirname(__DIR__) .DIRECTORY_SEPARATOR. "images" .DIRECTORY_SEPARATOR. $picture["picture"];
			if(file_exists($file)) {
				$okPictures++;
			} else {
				$errorPictures++;
				$id="User:".$picture["id"]." ".$picture["lastname"]." ".$picture["firstname"]." ";
				echo($id.$file."<br/>");
			}
		}
	}
	echo("All:".$allPictures."<br/>");
	echo("Ok:".$okPictures."<br/>");
	echo("Error:".$errorPictures."<br/>");
}
?>