<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../../lpfw/htmlParser.class.php";
include_once __DIR__ . "/../../lpfw/sessionManager.php";

use maierlabs\lpfw\htmlParser as Parser;

class WebpageTest extends \PHPUnit_Framework_TestCase
{

    private $url ="/brassai/";

    public function testInexPageLogon()
    {
        $this->logoff();
        $ret = $this->callTestUrl($this->url . "index", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $login = $dom->getElementById("uLogonMenu");
        $this->assertSame("button",$login->tagName);
        $this->logon();
        $ret = $this->callTestUrl($this->url . "index", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $login = $dom->getElementById("uLogoffMenu");
        $this->assertSame("button",$login->tagName);
    }

    public function testClassList()
    {
        $ret = $this->callTestUrl($this->url . "classlist", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divs = $finder->query("//*[contains(@class, 'classdiv')]");
        $this->assertGreaterThan(48,$divs->length);
        $divsa = $finder->query("//*[contains(@class, 'classdiv-a')]");
        $this->assertLessThan($divs->length,$divsa->length);
        $divsb = $finder->query("//*[contains(@class, 'classdiv-b')]");
        $this->assertLessThan($divsa->length,$divsb->length);
        $ret = $this->callTestUrl($this->url . "classlist?tabOpen=dayxxi", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divxs = $finder->query("//*[contains(@class, 'classdiv')]");
        $this->assertGreaterThan(48,$divxs->length);
        $divsxa = $finder->query("//*[contains(@class, 'classdiv-a')]");
        $this->assertLessThan($divxs->length,$divsxa->length);
        $divsxb = $finder->query("//*[contains(@class, 'classdiv-b')]");
        $this->assertLessThan($divsxa->length,$divsxb->length);
    }

    public function testUser1() {
        $ret = $this->callTestUrl($this->url . "U-834", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $firstPicture = $dom->getElementById("firstPicture");
        $this->assertSame("img",$firstPicture->tagName);
        $ret = $this->callTestUrl($this->url . "editDiak?&tabOpen=pictures", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $div = $finder->query("//*[contains(@class, 'pictureframe')]");
        $this->assertGreaterThan(1,$div->length);
    }

    public function testTeacher() {
        $ret = $this->callTestUrl($this->url . "hometable?classid=10", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $div = $finder->query("//*[contains(@class, 'personboxc')]");
        $this->assertGreaterThan(320,$div->length);
    }

    public function testRIP() {
        $ret = $this->callTestUrl($this->url . "rip", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $div = $finder->query("//*[contains(@class, 'rip-element')]");
        $this->assertEquals(24,$div->length);
        for ($i =0 ; $i<$div->length;$i++) {
            $text = $div->item($i)->childNodes->item(1)->nodeValue;
            $this->assertContains("†",$text);
        }
    }

    public function testStartPage()
    {
        $ret = $this->callTestUrl($this->url . "start", false);
        $this->assertNotNull($ret);
        $tabs = Parser::getListItemsBetween($ret->content, 'role="tablist"', '</ul>', true);
        $this->assertCount(11, $tabs);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divs = $finder->query("//*[contains(@class, 'element')]");
        $this->assertSame(48,$divs->length);
        $this->assertContains("function logon() {",$ret->content);
    }

    /**************************[ private ]*************************************************/

    private function logon($session=null) {
        return $this->callTestUrl($this->url."ajax/authorization.php?action=phpunit_logon".($session!=null?("&session=".json_encode($session)):""),true);
    }

    private function logoff() {
        return $this->callTestUrl($this->url."ajax/authorization.php?action=phpunit_logoff",true);
    }

}