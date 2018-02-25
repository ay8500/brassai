<?php
session_start();
//User is logged in and have the role of admin
if (!isset($_SESSION['uRole']) || strstr($_SESSION['uRole'],"admin")=="") 
	die("Only for admins");

error_reporting(1);
set_time_limit(0);

if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0 || strpos($_SERVER["SERVER_NAME"],".online.de")>0) {
	db_pictures("db652851844.db.1and1.com", 'dbo652851844','levi1967', 'db652851844', __DIR__."/backup.sql") ;
} else {
	db_pictures("localhost", "root", "root", "db652851844", __DIR__."/backup.sql");
}



function db_pictures($dbhost, $dbuser, $dbpwd, $dbname, $dbbackup)
{
	echo("Result of pictures checking source filesystem<br/>");
	
	$statistic=new stdClass();
	$statistic->allPictures=0;$statistic->okPictures=0;$statistic->errorPictures=0;$statistic->package=0;
	$conn = mysqli_connect($dbhost, $dbuser, $dbpwd,$dbname) or die(mysqli_error());
	
	$packageSize=3000;$packageArray = array();
	$it = new RecursiveDirectoryIterator(dirname(__DIR__)."/images",FilesystemIterator::SKIP_DOTS);
	foreach(new RecursiveIteratorIterator($it) as $file) {
		if (dirname($file)!=dirname(__DIR__)."/images") {
			$packageArray[sizeof($packageArray)]= $file;
			if(sizeof($packageArray)>=$packageSize) {
				$statistic=checkFilesInPackage($conn,$packageArray,$statistic);
				unset($packageArray);
			}
		}
	}
	if (sizeof($packageArray)>0)
		$statistic=checkFilesInPackage($conn,$packageArray,$statistic);
	
	echo("Packages:".$statistic->package."<br/>");
	echo("All:".$statistic->allPictures."<br/>");
	echo("Ok:".$statistic->okPictures."<br/>");
	echo("Error:".$statistic->errorPictures."<br/>");
}

function checkFilesInPackage($conn,$packageArray,$statistic) {
	$statistic->package=$statistic->package+1;
	$statistic->allPictures=$statistic->allPictures+sizeof($packageArray);
	
	//Table picture
	$toCheckFilesArray=array();
	$whereIn="";
	foreach($packageArray as $id=>$file) {
		if ($id>0)
			$whereIn.=",";
		$fn=str_replace('\\','/',substr($file,strlen(dirname(__DIR__))+1));
		$whereIn.="'".$fn."'";
		$toCheckFilesArray[sizeof($toCheckFilesArray)]=$fn;
	}
	$sql="SELECT file FROM picture where file in (".$whereIn.")";
	$pictures = mysqli_query($conn,$sql);
	while ($picture = mysqli_fetch_array($pictures))
	{
		if (($key = array_search($picture["file"], $toCheckFilesArray)) !== false) {
			unset($toCheckFilesArray[$key]);
			$statistic->okPictures=$statistic->okPictures+1;
		} 
	}
	
	//Table person
	$toCheckPersonArray=array();
	$whereIn="";
	foreach($toCheckFilesArray as $id=>$file) {
		if ($id>0)
			$whereIn.=",";
		$fn=substr($file,strlen("/images"));
		$whereIn.="'".$fn."'";
		$toCheckPersonArray[sizeof($toCheckPersonArray)]=$fn;
	}
	$sql="SELECT picture FROM person where picture in (".$whereIn.")";
	$pictures = mysqli_query($conn,$sql);
	while ($picture = mysqli_fetch_array($pictures))
	{
		if (($key = array_search($picture["picture"], $toCheckPersonArray)) !== false) {
			unset($toCheckPersonArray[$key]);
			$statistic->okPictures=$statistic->okPictures+1;
			if (($key = array_search(str_replace(".", "_o.", $picture["picture"]), $toCheckPersonArray)) !== false) {
				unset($toCheckPersonArray[$key]);
				$statistic->okPictures=$statistic->okPictures+1;
			}
		} 
	}
	
	//The list of not referenced pictures
	foreach ($toCheckPersonArray as $notFound) {
		if (strpos($notFound,"images")===false)
			$notFound = "images/".$notFound;
		echo('Not in the DB:'.$notFound.' <a href="'.$notFound.'" target="not_found"><img style="height:50px" src="'.$notFound.'"/></a>');
		$fa= explode("-",basename($notFound),2);
		if (sizeof($fa)==2 && substr($fa[0],0,1)=="d") {
			$sql="select * from person where id =".substr($fa[0], 1);
			$ps = mysqli_query($conn,$sql);
			while ($person= mysqli_fetch_array($ps)) {
				echo (' Org: <img style="height:50px" src="images/'.$person["picture"].'"/>');
			}
		}
		echo("<br/>");
		if (isset($_GET["action"]) && $_GET["action"]=="delete") {
			unlink(dirname(__DIR__)."/".$notFound);
		}
		
	}
	$statistic->errorPictures=$statistic->errorPictures+sizeof($toCheckPersonArray);
	
	return $statistic;
}
?>