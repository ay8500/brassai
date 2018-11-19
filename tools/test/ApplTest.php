<?php
include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../appl.class.php";
include_once __DIR__ . "/../logger.class.php";

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
    }

    /**
     * @covers \maierlabs\lpfw\Appl::addCss
     * @covers \maierlabs\lpfw\Appl::addCssStyle
     * @covers \maierlabs\lpfw\Appl::includeCss
     */
    public function testCssFile() {
        Appl::addCss("css.file");
        Appl::addCssStyle(".levi {data:ok;}");
        ob_start();
        Appl::includeCss();
        $echo = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(strpos($echo,'<link rel="stylesheet" type="text/css" href="css.file?v=')!==false);
        $this->assertTrue(strpos($echo,'.levi')!==false);
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
        $res = strpos($echo,"<style>\n.body { color:white}</style>");
        $this->assertTrue($res!==false);
    }

}
