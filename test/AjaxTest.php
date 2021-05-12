<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../../lpfw/logger.class.php";

class AjaxTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    public function testOpinionPersonText() {
        $this->opinionTester("text","person", false);
    }
    public function testOpinionPersonFriend() {
        $this->opinionTester("friend","person");
    }
    public function testOpinionPersonSport() {
        $this->opinionTester("sport","person",false);
    }
    public function testOpinionEaster() {
        $this->opinionTester("easter","person");
    }
    public function testOpinionPictureText() {
        $this->opinionTester("text","picture", false);
    }
    public function testOpinionPictureFavorite() {
        $this->opinionTester("favorite","picture");
    }
    public function testOpinionPictureNice() {
        $this->opinionTester("nice","picture",false);
    }
    public function testOpinionPictureContent() {
        $this->opinionTester("content","picture",false);
    }
    public function testOpinionMessageText() {
        $this->opinionTester("text","message",false);
    }
    public function testOpinionMessageFavorite() {
        $this->opinionTester("favorite","message");
    }
    public function testOpinionMusicFavorite() {
        $this->opinionTester("favorite","music");
    }

    private function opinionTester($count, $type,$login=true) {
        if (($url=$this->getUrl())===false) return;
        $ret=$this->logoff();
        $this->assertNotNull($ret);
        if ($login) {
            $ret = $this->callTestUrl($url . "ajax/setOpinion?id=9999000&count=" . $count . "&type=" . $type. "&test=test&text=ok", true);
            self::assertSame("login", $ret->content["result"]);
            $this->logon();
        }
        $ret=$this->callTestUrl($url."ajax/setOpinion?id=9999000&count=".$count."&type=".$type. "&test=test&text=ok",true);
        self::assertSame("ok",$ret->content["result"]);
        self::assertSame(1,intval($ret->content["count"]));
        $ret=$this->callTestUrl($url."ajax/setOpinion?id=9999000&count=".$count."&type=".$type. "&test=test&text=ok",true);
        if ($count=="text") {
            self::assertSame("ok",$ret->content["result"]);
            self::assertSame(2,intval($ret->content["count"]));
        } else {
            self::assertSame("exists", $ret->content["result"]);
        }
        $ret=$this->callTestUrl($url."ajax/getOpinions?id=9999000&count=".$count."&type=".$type,true);
        if (sizeof($ret->content)>0) {
            foreach ($ret->content as $option) {
                $ret = $this->callTestUrl($url."ajax/deleteOpinion?id=".$option["id"],true);
            }
        }
        self::assertSame(0,intval($ret->content["count"]),"Test Opinions are not deleted!");
        $this->logoff();
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
        $id = "9898989891";
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

    /**************************[ private ]*************************************************/

    private function logon($session=null) {
        if (($url=$this->getUrl())===false)
            return;
        return $this->callTestUrl($url."ajax/authorization.php?action=phpunit_logon".($session!=null?("&session=".json_encode($session)):""),true);
    }

    private function logoff() {
        if (($url=$this->getUrl())===false)
            return;
        return $this->callTestUrl($url."ajax/authorization.php?action=phpunit_logoff",true);
    }

    private function getUrl() {
        if( !is_array($_SERVER) || !isset($_SERVER["HTTP_REFERER"]))
            return null;
        $url=$_SERVER["HTTP_REFERER"];
        $url = substr($url,0,strlen($url)-strlen("phpunit/"));
        return $url."/brassai/";
    }

}