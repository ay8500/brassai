<?php

error_reporting(1);
set_time_limit(0);

if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0 || strpos($_SERVER["SERVER_NAME"],".online.de")>0) {
	db_pictures("db652851844.db.1and1.com", 'dbo652851844','levi1967', 'db652851844', __DIR__."/backup.sql") ;
} else {
	db_pictures("localhost", "root", "root", "db652851844", __DIR__."/backup.sql");
}



// ab hier nichts mehr ändern
function db_pictures($dbhost, $dbuser, $dbpwd, $dbname, $dbbackup)
{
	$allPictures=0;$okPictures=0;$errorPictures=0;
	$conn = mysqli_connect($dbhost, $dbuser, $dbpwd,$dbname) or die(mysqli_error());
	
	$pictures = mysqli_query($conn,"SELECT * FROM picture");
	while ($picture = mysqli_fetch_array($pictures))
	{
		$allPictures++;
		$file=dirname(__DIR__)."/".$picture["file"];
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
		if($picture["picture"]!=null && $picture["picture"]!='avatar.jpg') {
			$allPictures++;
			$file=dirname(__DIR__)."/images/".$picture["picture"];
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