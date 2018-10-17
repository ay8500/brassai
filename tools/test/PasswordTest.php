<?php

include_once __DIR__ . "/../../config.class.php";
use \maierlabs\lpfw\MySqlDb as MySqlDb;

/**
 * Class PasswortTest
 */
class PasswordTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MySqlDb
     */
    private $db ;

    public function setup()
    {

        $include = array('mysql.class.php','userManager.php');
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
        $this->assertTrue($this->db->query("select user,passw,firstname,lastname,id from person"));
        /*
        while ($row = $this->db->fetchRow()) {
            $p=$row["passw"];
            $dp=encrypt_decrypt("decrypt",$p);
            $this->assertTrue(strlen($p)==32);
            $this->assertTrue(strlen($dp)!=32);
        }
        */
    }

}
