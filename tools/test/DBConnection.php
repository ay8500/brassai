<?php

use \maierlabs\lpfw\MySqlDb as MySqlDb;

include_once __DIR__ . "/../../config.class.php";

/**
 * Class DBConnection
 */
class DBConnection extends PHPUnit_Framework_TestCase
{
    /**
     * @var MySqlDb
     */
    private $db ;

    public function setup()
    {

        $include = array('mysql.class.php');
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

        $db = \Config::getDatabasePropertys();
        $this->db = new MySqlDb($db->host,$db->database,$db->user,$db->password);

    }


    public function testDB():void
    {
        $this->assertTrue(is_object($this->db));
    }

    public function testQuery():void
    {
        $ret = $this->db->query("select * from levi");
        $this->assertFalse($ret);

        $ret = $this->db->query("select id from person limit 1");
        $this->assertTrue($ret);

        $c = $this->db->getCounter();
        $this->assertTrue(2===$c->querys);

        $ret = $this->db->querySignleRow("select id from person limit 1");
        $this->assertTrue($ret!=null);

        $ret = $this->db->querySignleRow("select id from person limit 3");
        $this->assertTrue($ret==null);



    }


}
