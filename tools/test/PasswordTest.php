<?php

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


        $this->db = new MySqlDb("localhost","db652851844","root","root");

    }


    public function testDB():void
    {
        $this->assertTrue($this->db->query("select user,passw,firstname,lastname,id from person"));
        while ($row = $this->db->fetchRow()) {
            $p=$row["passw"];
            /*
            $ep=encrypt_decrypt("encrypt",$p);
            //Change
            if (strlen($p)!=32) {
                $this->db->update("person", array(["field"=>"passw","type"=>"s","value"=>$ep]),"id",$row["id"]);
            }
            */
            $dp=encrypt_decrypt("decrypt",$p);
            if ($row["user"]=="admin") {
                $this->assertTrue($dp=="levi1967-kvar");
            }
            if ($row["user"]=="levi") {
                $this->assertTrue($dp=="levi67");
            }
        }
    }

}
