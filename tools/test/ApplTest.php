<?php

include_once "../appl.class.php";
use maierlabs\lpfw\Appl;

class ApplTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers Appl::setMember
     * @covers Appl::getMemeber
     * @covers Appl::getMemberId
     */
    public function testMember()
    {
        Appl::setMember("test",array("id"=>2, "name"=>"test"));

        $this->assertTrue(Appl::getMemeberId("test")===2);

        $this->assertFalse(Appl::getMemeberId("nix")===2);

        $this->assertTrue(Appl::getMemeberId("nix")===null);

        $this->assertTrue(Appl::getMemeber("test")["name"]==="test");
    }

    public function testCssFile() {
        Appl::addCss("css.file");
        ob_start();
        Appl::includeCss();
        $echo = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(strpos($echo,'<link rel="stylesheet" type="text/css" href="css.file?v=')!==false);
    }


    public function testCss() {
        Appl::addCssStyle(".body { color:white}");
        ob_start();
        Appl::includeCss();
        $echo = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(strpos($echo,"<style>\n.body { color:white}</style>")!==false);
    }

}
