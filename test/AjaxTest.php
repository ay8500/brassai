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
        $ret=(array)$this->callTestUrl($url."ajax/getRandomPerson",true);
        $this->assertNotNull($ret);
        $this->assertTrue(isset($ret["content"]) && isset($ret["content"]["id"]) && isset($ret["content"]["name"]) && isset($ret["content"]["image"]) );
    }

    public function testCandleLighter() {
        $id = "9898989898";
        $uId = "7878787878";
        $url=$this->getUrl();
        if($url==null)
            return;
        $ret=(array)$this->callTestUrl($url."ajax/getCandleLighters?id=".$id,false);
        $this->assertNotNull($ret);
        $this->assertTrue(isset($ret["content"]) );
        //$ret=$this->callAjaxUrl($url."ajax/setCandleLighter?id=".$id);
        //$this->assertNotNull($ret);
        //$this->assertTrue(isset($ret["id"])  );
        //$this->assertSame(intval($id),intval($ret["id"]));
        //$ret=$this->callAjaxUrl($url."ajax/getCandleLighters?id=".$id,false);
        //echo("$ret");
        //$this->assertSame(intval($uId),intval($ret["uId"]));
    }

    private function getUrl() {
        if( !is_array($_SERVER) || !isset($_SERVER["HTTP_REFERER"]))
            return null;
        $url=$_SERVER["HTTP_REFERER"];
        $url = substr($url,0,strlen($url)-strlen("phpunit/"));
        return $url."/brassai/";
    }

}