<?php

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../mysqldbauh.class.php";
include_once __DIR__ . "/../logger.class.php";

/**
 * Class DBConnection
 */
class DBConnection extends PHPUnit_Framework_TestCase
{
    /**
     * @var MySqlDbAUH
     */
    private $db ;

    public function setup()
    {
        $p = \Config::getDatabasePropertys();
        $this->db = new MySqlDbAUH($p->host,$p->database,$p->user,$p->password);
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    public function tearDown()
    {
        $this->db->disconnect();
    }

    public function testDB():void
    {
        $this->assertTrue(is_object($this->db));
    }

    /**
     * @covers \maierlabs\lpfw\MySql::MySql
     * @covers \maierlabs\lpfw\MySql::disconnect
     * @covers \maierlabs\lpfw\MySql::query
     * @covers \maierlabs\lpfw\MySql::getCounter
     * @covers \maierlabs\lpfw\MySql::querySignleRow
     */
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
