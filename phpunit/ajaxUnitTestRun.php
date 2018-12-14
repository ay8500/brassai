<?php
session_start();
$timer=microtime(true);
/**
 * Run a single test as ajax
 */
include 'config.class.php';
include 'phpunit.class.php';
include_once 'PHPUnit_Framework_TestCase.php';

header('Content-Type: application/json');

$pu = new \phpunit\phpunit();

$file=$pu->getGetParam("file");
$dir=$pu->getGetParam("dir");
$testNr=$pu->getGetParam("testNr");

if (null==$testNr || null==$file || null==$dir ) {
    die('Invalid parameter list!');
}

include $dir.$file;

$testClassName=substr($file,0,strpos(strtolower($file),".php"));
$testMethodList = $pu->getTestClassMethods($testClassName);
$testSetupMethod= $pu->getTestClassSetupMethod($testClassName);
$testTearDownMethod= $pu->getTestClassTearDownMethod(($testClassName));

$result=array();
if ($testNr<sizeof($testMethodList)-1)
    $result["filestatus"]="running";
else
    $result["filestatus"] = "done";
$result["tests"]=$testMethodList;
$result["testNr"]=intval($testNr);
$result["testName"]=$testMethodList[$testNr];

if ($testNr==0) {
    $theTestClass  = new $testClassName();
    $_SESSION["class"]= serialize($theTestClass);
} else {
    $theTestClass = unserialize($_SESSION["class"]);
}

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ob_start();

if ($testSetupMethod!=null) {
    try {
        $theTestClass->$testSetupMethod();
    } catch (\Exception $e){
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
    catch (\Error $e){
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
    catch (\Throwable $e) {
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
}

if (isset($testMethodList[$testNr])) {
    try {
        $functionName=$testMethodList[$testNr];
        $theTestClass->$functionName();
    } catch (\Exception $e){
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
    catch (\Error $e){
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
    catch (\Throwable $e) {
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
}

if ($testTearDownMethod!=null) {
    try {
        $theTestClass->$testTearDownMethod();
    } catch (\Exception $e){
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
    catch (\Error $e){
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
    catch (\Throwable $e) {
        exceptionOccured($theTestClass,$result,$e,$timer);
    }
}


$result["echo"]=ob_get_clean();
$res = $theTestClass->assertGetUnitTestResult();
$result["test"]=$res->testResult;
$result["assertOk"]=$res->assertOk;
$result["assertError"]=$res->assertError;
$result["time"]=number_format((microtime(true)-$timer) * 1000,2);



echo json_encode($result);

function exceptionOccured($theTestClass,$result,$e,$timer) {
    $res = $theTestClass->assertGetUnitTestResult();
    $result["assertOk"]=$res->assertOk;
    $result["assertError"]=$res->assertError;
    $result["errorMessage"]=$e->getMessage();
    $result["test"]=false;
    $result["filestatus"]="error";
    $result["echo"]=ob_get_clean();
    $result["time"]=number_format((microtime(true)-$timer) * 1000,2);
    echo json_encode($result);
    die();
}
