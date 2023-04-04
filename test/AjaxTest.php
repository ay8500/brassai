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
        $url="/brassai/";
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
        if (sizeof($ret->content["list"]>0)) {
            $o1 = $ret->content["list"][0];
            $this->assertTrue(strpos($o1["date"],"today")===false && strpos($o1["date"],"yesterday")===false && strpos($o1["date"],"week")===false && strpos($o1["date"],"month")===false && strpos($o1["date"],"year")===false, "Language ist englisch!");
            foreach ($ret->content["list"] as $option) {
                $ret = $this->callTestUrl($url."ajax/deleteOpinion?id=".$option["id"],true);
            }
        }
        self::assertSame(0,intval($ret->content["count"]),"Test Opinions are not deleted!");
        $this->logoff();
    }

    public function testGetRandomPerson() {
        $url="/brassai/";
        $ret=(array)$this->callTestUrl($url."ajax/getRandomPerson",true);
        $this->assertNotNull($ret);
        $this->assertTrue(isset($ret["content"]) && isset($ret["content"]["id"]) && isset($ret["content"]["name"]) && isset($ret["content"]["image"]) );
    }

    public function testGetGeoPoints() {
        $url="/brassai/";
        $ret=(array)$this->callTestUrl($url."ajax/getGeoPoints?lat2=49.28482762264301&lng1=10.55442810058594&lat1=49.015355983769666&lng2=11.696319580078127",false);
        $this->assertNotNull($ret);
        $this->assertTrue(isset($ret["content"]) && $ret["content"]="49.116316928174:11.1094140625:Maier Levente (Kalmár)|" );
    }

    public function testCandleLighter() {
        $id = "9898989891";
        $uId = "7878787878";
        $url="/brassai/";
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
        $url="/brassai/";
        return $this->callTestUrl($url."ajax/authorization.php?action=phpunit_logon".($session!=null?("&session=".json_encode($session)):""),true);
    }

    private function logoff() {
        $url="/brassai/";
        return $this->callTestUrl($url."ajax/authorization.php?action=phpunit_logoff",true);
    }

}