<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../../lpfw/logger.class.php";

class AjaxTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    public function testGetRandomPerson() {
        $url=$this->getUrl();
        if($url==null)
            return;
        $ret=$this->callAjaxUrl($url."ajax/getRandomPerson");
        $this->assertNotNull($ret);
        $this->assertTrue(isset($ret["id"]) && isset($ret["name"]) && isset($ret["image"]) );
    }

    public function testCandleLighter() {
        $id = "9898989898";
        $uId = "7878787878";
        $url=$this->getUrl();
        if($url==null)
            return;
        $ret=$this->callAjaxUrl($url."ajax/getCandleLighters?id=".$id,false);
        $this->assertNotNull($ret);
        $this->assertTrue($ret=="" );
        //$ret=$this->callAjaxUrl($url."ajax/setCandleLighter?id=".$id);
        //$this->assertNotNull($ret);
        //$this->assertTrue(isset($ret["id"])  );
        //$this->assertSame(intval($id),intval($ret["id"]));
        //$ret=$this->callAjaxUrl($url."ajax/getCandleLighters?id=".$id,false);
        //echo("$ret");
        //$this->assertSame(intval($uId),intval($ret["uId"]));
    }

    private function callAjaxUrl($url,$json=true){
        if ($url==null || strlen($url)==0)
            return null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
        //curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
        $resp = curl_exec($ch);
        curl_close($ch);
        if ($resp===false)
            return null;
        if ($json)
            return json_decode($resp,true);
        return $resp;
    }

    private function getUrl() {
        if( !is_array($_SERVER) || !isset($_SERVER["HTTP_REFERER"]))
            return null;
        $url=$_SERVER["HTTP_REFERER"];
        $url = substr($url,0,strlen($url)-strlen("phpunit/"));
        return $url."/brassai/";
    }

}