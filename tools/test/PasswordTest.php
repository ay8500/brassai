<?php

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../mysqldbauh.class.php";
include_once __DIR__ . "/../userManager.php";
include_once __DIR__ . "/../logger.class.php";

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

/**
 * Class PasswortTest
 */
class PasswordTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MySqlDbAUH
     */
    private $db ;

    public function setup()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);

        $p = \Config::getDatabasePropertys();
        $this->db = new MySqlDbAUH($p->host,$p->database,$p->user,$p->password);
    }

    public function tearDown()
    {
        $this->db->disconnect();
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
