<?php

use maierlabs\lpfw\Appl;

class ApplTest extends PHPUnit_Framework_TestCase
{
    public function setup() {
        $include = array('appl.class.php');
        $argv=$_SERVER["argv"];
        $p=pathinfo($argv[sizeof($argv)-1]);
        foreach ($include as $item) {
            if (file_exists($p["dirname"].'/'.$item)){
                include_once $p["dirname"].'/'.$item;
            } else if (file_exists($p["dirname"].'/brassai/tools/'.$item)){
                include_once $p["dirname"].'/brassai/tools/'.$item;
            } else if (file_exists($p["dirname"].'/tools/'.$item)){
                include_once $p["dirname"].'/tools/'.$item;
            } else if (file_exists($p["dirname"].'/../'.$item)){
                include_once $p["dirname"].'/../'.$item;
            } else {
                throw (new Exception("Inludefile not found"));
            }
        }
    }

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
        ob_start();
        Appl::addCssStyle(".body { color:white}");
        Appl::includeCss();
        $echo = ob_get_contents();
        ob_end_clean();
        $res = strpos($echo,"<style>\n.body { color:white}</style>");
        $this->assertTrue($res!==false);
    }

}
