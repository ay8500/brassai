<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../../lpfw/htmlParser.class.php";
include_once __DIR__ . "/../../lpfw/sessionManager.php";

use maierlabs\lpfw\htmlParser as Parser;

class WebpageTest extends \PHPUnit_Framework_TestCase
{

    private $url ="/brassai/";

    public function testIndexPageLogon()
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
        $ret = $this->callTestUrl($this->url . "classlist?schoolid=1", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divs = $finder->query("//*[contains(@class, 'classdiv')]");
        $this->assertGreaterThan(48,$divs->length);
        $divsa = $finder->query("//*[contains(@class, 'classdiv-a')]");
        $this->assertLessThan($divs->length,$divsa->length);
        $divsb = $finder->query("//*[contains(@class, 'classdiv-b')]");
        $this->assertLessThan($divsa->length,$divsb->length);
        $ret = $this->callTestUrl($this->url . "classlist?tabOpen=dayxxi&schoolid=1", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divxs = $finder->query("//*[contains(@class, 'classdiv')]");
        $this->assertGreaterThan(48,$divxs->length);
        $divsxa = $finder->query("//*[contains(@class, 'classdiv-a')]");
        $this->assertLessThan($divxs->length,$divsxa->length);
        $divsxb = $finder->query("//*[contains(@class, 'classdiv-b')]");
        $this->assertLessThan($divxs->length,$divsxb->length);
    }

    public function testUser1() {
        $ret = $this->callTestUrl($this->url . "editPerson?uid=834", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $firstPicture = $dom->getElementById("firstPicture");
        $this->assertSame("img",$firstPicture->tagName);
        $ret = $this->callTestUrl($this->url . "editPerson?&tabOpen=pictures", false);
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
        $ret = $this->callTestUrl($this->url . "rip?schoolid=1", false);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $div = $finder->query("//*[contains(@class, 'rip-element')]");
        $this->assertGreaterThan(8,$div->length,"Expected to found at least 8 persons on this page");
        for ($i =0 ; $i<$div->length;$i++) {
            $text = $div->item($i)->childNodes->item(1)->nodeValue;
            $this->assertContains("†",$text,"Element nr:".$i);
        }
    }

    public function testStartPage()
    {
        $this->logoff();
        $ret = $this->callTestUrl($this->url . "start?all=all", false);
        $this->assertNotNull($ret);
        $tabs = Parser::getListItemsBetween($ret->content, 'role="tablist"', '</ul>', true);
        $this->assertCount(10, $tabs);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divs = $finder->query("//*[contains(@class, 'element')]");
        $this->assertSame(48,$divs->length, 'Expected 48 occurence of class="element"');
        $divs = $finder->query("//*[contains(@class, 'changedatetime')]");
        $this->assertSame(48,$divs->length,'Expected 48 occurence of class="changedatetime"');
        $this->assertContains("function logon() {",$ret->content);
        $this->logon();
        $ret = $this->callTestUrl($this->url . "start", false);
        $tabs = Parser::getListItemsBetween($ret->content, 'role="tablist"', '</ul>', true);
        $this->assertCount(11, $tabs);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divs = $finder->query("//*[contains(@class, 'schoolname')]");
        $this->assertSame(0,$divs->length);
    }

    public function testSchoolPage(){
        $this->logoff();
        $ret = $this->callTestUrl($this->url . "school", false);
        $this->assertEquals(200,$ret->http_code);
        $this->assertContains("Iskola nincs kiválasztva.", $ret->content);
        $this->logon();
        //Display
        $ret = $this->callTestUrl($this->url . "school", false);
        $this->assertContains("Brassai Sámuel", $ret->content);
        $this->assertNotNull($ret);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divs = $finder->query("//*[contains(@class, 'element')]");
        //Edit
        $ret = $this->callTestUrl($this->url . "school?action=edit", false);
        $this->assertContains("Brassai Sámuel", $ret->content);
        $this->assertNotNull($ret);
        $dom = new DOMDocument();
        $dom->loadHTML($ret->content);
        $finder = new DOMXPath($dom);
        $divs = $finder->query("//*[contains(@class, 'element')]");
    }

    /**************************[ private ]*************************************************/

    private function logon($session=null) {
        return $this->callTestUrl($this->url."ajax/authorization.php?action=phpunit_logon".($session!=null?("&session=".json_encode($session)):""),true);
    }

    private function logoff() {
        return $this->callTestUrl($this->url."ajax/authorization.php?action=phpunit_logoff");
    }

}