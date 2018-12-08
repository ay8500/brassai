<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 07.12.2018
 * Time: 10:22
 */
include 'config.class.php';
include 'phpunit.class.php';
include_once 'PHPUnit_Framework_TestCase.php';

header('Content-Type: application/json');

$pu = new \phpunit\phpunit();

$file=$pu->getGetParam("file");
$dir=$pu->getGetParam("dir");
$testNr=$pu->getGetParam("testNr");

if (null==$testNr || null==$file || null==$dir )
    die('Invalid parameter list!');

include $dir.$file;


$testClassName=substr($file,0,strpos(strtolower($file),".php"));
$testMethodList = $pu->getTestClassMethods($testClassName);
$testSetupMethod= $pu->getTestClassSetupMethod($testClassName);
$testTearDownMethod= $pu->getTestClassTearDownMethod(($testClassName));

$result=array();

if ($testNr<sizeof($testMethodList))
    $result["filestatus"]="running";
else
    $result["filestatus"] = "done";




$result["tests"]=$testMethodList;
$result["testNr"]=intval($testNr);

$theTestClass = new $testClassName();

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ob_start();

if ($testSetupMethod!=null) {
    try {
        $theTestClass->$testSetupMethod();
    } catch (\Exception $e){
        exceptionOccured($theTestClass,$result,$e);
    }
    catch (\Error $e){
        exceptionOccured($theTestClass,$result,$e);
    }
    catch (\Throwable $e) {
        exceptionOccured($theTestClass,$result,$e);
    }
}

if (isset($testMethodList[$testNr])) {
    try {
        $functionName=$testMethodList[$testNr];
        $theTestClass->$functionName();
    } catch (\Exception $e){
        exceptionOccured($theTestClass,$result,$e);
    }
    catch (\Error $e){
        exceptionOccured($theTestClass,$result,$e);
    }
    catch (\Throwable $e) {
        exceptionOccured($theTestClass,$result,$e);
    }
}

if ($testTearDownMethod!=null) {
    try {
        $theTestClass->$testTearDownMethod();
    } catch (\Exception $e){
        exceptionOccured($theTestClass,$result,$e);
    }
    catch (\Error $e){
        exceptionOccured($theTestClass,$result,$e);
    }
    catch (\Throwable $e) {
        exceptionOccured($theTestClass,$result,$e);
    }
}


$result["echo"]=ob_get_clean();
$res = $theTestClass->assertGetUnitTestResult();
$result["test"]=$res->testResult;
$result["assertOk"]=$res->assertOk;
$result["assertError"]=$res->assertError;



echo json_encode($result);

function exceptionOccured($theTestClass,$result,$e) {
    $res = $theTestClass->assertGetUnitTestResult();
    $result["assertOk"]=$res->assertOk;
    $result["assertError"]=$res->assertError;
    $result["errorMessage"]=$e->getMessage();
    $result["test"]=false;
    $result["filestatus"]="error";
    $result["echo"]=ob_get_clean();
    echo json_encode($result);
    die();
}
