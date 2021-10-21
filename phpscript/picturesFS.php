<?php
/*****************************************************************************
 * this script check if the picture files are referenced in the database
 * not referenced pictures can be deleted using the parameter action=delete
 *****************************************************************************/
include_once __DIR__ . "/../config.class.php";
include_once Config::$lpfw.'sessionManager.php';
//User is logged in and have the role of admin
if (!isset($_SESSION['uRole']) || strstr($_SESSION['uRole'],"admin")=="") 
	die("Only for admins");

error_reporting(1);
set_time_limit(0);

$db = \Config::getDatabasePropertys();
db_pictures($db->host,$db->user,$db->password,$db->database) ;

function db_pictures($dbhost, $dbuser, $dbpwd, $dbname)
{
	echo("Result of pictures checking source filesystem<br/>");
	
	$statistic=new stdClass();
	$statistic->allPictures=0;$statistic->okPictures=0;$statistic->errorPictures=0;$statistic->package=0;$statistic->original=0;
	$conn = mysqli_connect($dbhost, $dbuser, $dbpwd,$dbname) or die(mysqli_error());
	
	$packageSize=1000;$packageArray = array();
	$it = new RecursiveDirectoryIterator(dirname(__DIR__) .DIRECTORY_SEPARATOR. "images",FilesystemIterator::SKIP_DOTS);
	foreach(new RecursiveIteratorIterator($it) as $file) {
		if (dirname($file)!= dirname(__DIR__) .DIRECTORY_SEPARATOR. "images") {
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
    echo("Original:".$statistic->original."<br/>");
	echo("Ok:".$statistic->okPictures."<br/>");
	echo("Error:".$statistic->errorPictures."<br/>");
}

/*
 * check file references from the $packageArray in the database
 */
function checkFilesInPackage($conn,$packageArray,$statistic) {
	$statistic->package=$statistic->package+1;
	$statistic->allPictures=$statistic->allPictures+sizeof($packageArray);
    $toCheckFilesArray=array();

	//check references to the pictures in the database table "picture"
	$whereIn="";
	foreach($packageArray as $id=>$file) {
		if ($id>0)
			$whereIn.=",";
		$fn=str_replace('\\','/',substr($file,strlen(dirname(__DIR__))+1));
		$whereIn.="'".$fn."'";
		$toCheckFilesArray[]=$fn;
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

    //check references to the pictures in the database table "school"
    $whereIn="'dummyComma'";
    foreach($toCheckFilesArray as $id=>$file) {
        $whereIn.=",'".basename($file)."'";
    }
    $sql="SELECT id, logo FROM school where logo in (".$whereIn.")";
    $pictures = mysqli_query($conn,$sql);
    while ($picture = mysqli_fetch_array($pictures))
    {
        $file = "images/school".$picture["id"]."/".$picture["logo"];
        if (($key = array_search($file, $toCheckFilesArray)) !== false) {
            unset($toCheckFilesArray[$key]);
            $statistic->okPictures=$statistic->okPictures+1;
        }
    }

	//check references to the pictures in pictures in the database table "person"
	$whereIn="'dummyComma'";
	foreach($toCheckFilesArray as $id=>$file) {
        $file = substr($file,strlen("/images"));
		$whereIn.=",'".str_replace("_o.",".",$file)."'";
	}
	$sql="SELECT picture FROM person where picture in (".$whereIn.")";
	$pictures = mysqli_query($conn,$sql);
	while ($picture = mysqli_fetch_array($pictures))
	{
        $file = "images/".$picture["picture"];
		if (($key = array_search($file, $toCheckFilesArray)) !== false) {
			unset($toCheckFilesArray[$key]);
			$statistic->okPictures=$statistic->okPictures+1;
		}
        $originalFile = str_replace(".", "_o.", $file);
        if (($key = array_search($originalFile, $toCheckFilesArray)) !== false) {
            unset($toCheckFilesArray[$key]);
            $statistic->original=$statistic->original+1;

        }
	}
	
	//The list of not referenced pictures
	foreach ($toCheckFilesArray as $notFound) {
		if (strpos($notFound,"images")!==false)
			$notFoundImg = $notFound;
		else
			$notFoundImg = "images/".$notFound;
		echo('Not in the DB:'.$notFound.' <a href="'.$notFoundImg.'" target="not_found"><img style="height:50px" src="'.$notFoundImg.'"/></a>');
        echo(date ("Y.m.d H:i:s.",filemtime(dirname(__DIR__) . "/images/" .$notFound)));
		$fa= explode("-",basename($notFound),2);
		if (sizeof($fa)==2 && substr($fa[0],0,1)=="d") {
			$sql="select * from person where id =".substr($fa[0], 1);
			$ps = mysqli_query($conn,$sql);
			while ($person= mysqli_fetch_array($ps)) {
				echo (' Org:'.$person["picture"].' <img style="height:50px" src="images/'.$person["picture"].'"/>');
			}
		}
		echo("<br/>");
		if (isset($_GET["action"]) && $_GET["action"]=="delete") {
			unlink(dirname(__DIR__) .DIRECTORY_SEPARATOR. $notFoundImg);
		}
		
	}
	$statistic->errorPictures=$statistic->errorPictures+sizeof($toCheckFilesArray);
	
	return $statistic;
}
?>