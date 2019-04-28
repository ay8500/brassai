<?php
include_once __DIR__ . "/../../phpunit/config.class.php";
include_once __DIR__ . "/../appl.class.php";
include_once __DIR__ . "/../logger.class.php";
include_once __DIR__ . "/../userManager.php";
include_once __DIR__ . "/../ltools.php";

use maierlabs\lpfw\Appl;

class ApplTest extends PHPUnit_Framework_TestCase
{
    public function setup() {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    /**
     * @covers \maierlabs\lpfw\Appl::setMember
     * @covers \maierlabs\lpfw\Appl::getMember
     * @covers \maierlabs\lpfw\Appl::getMemberId
     */
    public function testMember()
    {
        Appl::setMember("test",array("id"=>2, "name"=>"test"));

        $this->assertTrue(Appl::getMemberId("test")===2);

        $this->assertFalse(Appl::getMemberId("nix")===2);

        $this->assertTrue(Appl::getMemberId("nix")===null);

        $this->assertTrue(Appl::getMember("test")["name"]==="test");

        $this->assertTrue(Appl::getMember("nix","Foo")==="Foo");
    }

    /**
     * @covers \maierlabs\lpfw\Appl::addCssStyle
     * @covers \maierlabs\lpfw\Appl::includeCss
     * @covers \maierlabs\lpfw\Appl::setMessage
     */
    public function testCssBevorRendering() {
        Appl::addCssStyle(".td{ color:white}");
        Appl::addCss("css.file");
        Appl::setMessage("OK","success");
        ob_start();
        Appl::includeCss();
        $echo = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(strlen($echo)>0);
        $this->assertTrue(Appl::$resultDbOperation==='<div class="alert alert-success">OK</div>');
        $this->assertTrue(strpos($echo,'<link rel="stylesheet" type="text/css" href="css.file?v='.Config::$webAppVersion)!==false);
    }

    /**
     * @covers \maierlabs\lpfw\Appl::addCssStyle
     * @covers \maierlabs\lpfw\Appl::includeCss
     */
     public function testCss() {
        ob_start();
        Appl::addCssStyle(".body { color:white}");
        Appl::includeCss();
        $echo = ob_get_contents();
        ob_end_clean();
        $res = strpos($echo,"\n.body { color:white}</style>");
        $this->assertTrue($res!==false);
    }

    /**
     * @covers \maierlabs\lpfw\Appl::addJs
     * @covers \maierlabs\lpfw\Appl::addJsScript
     * @covers \maierlabs\lpfw\Appl::includeJs
     */
    public function testJsFile() {
        Appl::addJs("js.file");
        Appl::addJs("__DIR__ . \"/../../js/chat.js",true);
        Appl::addJsScript("function getLevi{return 'Levi';}");
        ob_start();
        Appl::includeJs();
        $echo = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(strpos($echo,'<script type="text/javascript" src="js.file?v='.Config::$webAppVersion)!==false);

        $this->assertTrue(strpos($echo,"function getLevi{return 'Levi';}")!==false);

        $this->assertTrue(strpos($echo,"var loggedInUser=")!==false);
    }


    /**
     * @covers \maierlabs\lpfw\Appl::dateTimeAsStr
     * @covers \maierlabs\lpfw\Appl::dateAsStr
     *
     */
    public function testDateTime() {
        $a=Appl::dateTimeAsStr(new DateTime(),"Y");
        $this->assertTrue($a==date("Y"));

        $a=Appl::dateAsStr(new DateTime(),"Y");
        $this->assertTrue($a==date("Y"));

        $a=Appl::dateAsStr("2018-12-31","Y+m+d");
        $this->assertTrue($a=="2018+12+31");

        $_SESSION["timezone"]=120;
        Config::$dateTimeFormat="H";
        $a=Appl::dateTimeAsStr("2018-12-31 13:14:16");
        $this->assertTrue($a==='14');

        Config::$dateFormat="H:i:s";
        $a=Appl::dateAsStr("2018-12-31 13:14:16");
        $this->assertTrue($a==='14:14:16');

    }

    /**
     * @covers \maierlabs\lpfw\Appl::setSiteTitle
     * @covers \maierlabs\lpfw\Appl::setSiteDesctiption
     * @covers \maierlabs\lpfw\Appl::setSiteSubTitle
     */
    public function testApplTitle() {
        Appl::setSiteTitle("A","B","C");
        $this->assertTrue(Appl::$title=="A");
        $this->assertTrue(Appl::$subTitle=="B");
        $this->assertTrue(Appl::$description=="C");
        Appl::setSiteTitle("AA");
        $this->assertTrue(Appl::$title=="AA");
        $this->assertTrue(Appl::$subTitle=="B");
        $this->assertTrue(Appl::$description=="C");
        Appl::setSiteTitle("AAA","BBB");
        $this->assertTrue(Appl::$title=="AAA");
        $this->assertTrue(Appl::$subTitle=="BBB");
        $this->assertTrue(Appl::$description=="C");
        Appl::setSiteSubTitle("S");
        $this->assertTrue(Appl::$subTitle=="S");
        Appl::setSiteDesctiption("D");
        $this->assertTrue(Appl::$description=="D");
    }

    public  function testLTools() {
        $this->assertTrue('<a target="_blank" href="https://www.site.com">Link</a>'==createLink("https://www.site.com",true));
        $this->assertTrue('<a target="_blank" href="http://site.de">http://site.de</a>'==createLink("http://site.de",false));
        $this->assertTrue('Adress: <a href="mailto:mail@site.com">E-Mail</a>'==createLink("Adress: mail@site.com",true));
        $this->assertTrue('Send mail to: <a href="mailto:mail@site.de">mail@site.de</a>'==createLink("Send mail to: mail@site.de",false));

        $this->assertTrue("Anstrom"==getNormalisedChars("Ânström"));
        $this->assertTrue("Kalmar"==getNormalisedChars("Kalmár"));

        $this->assertSame("L.{1,4}v.{1,4}nt.{1,4}",searchSpecialChars("Levente"));
        $this->assertSame("k.{1,4}lm.{1,4}r",searchSpecialChars("kalmár"));
        $this->assertSame("M.{1,4}.{1,4}.{1,4}r",searchSpecialChars("Maier"));
    }

}
