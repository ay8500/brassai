<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../lpfw/mysqldbauh.class.php";
include_once __DIR__ . "/../lpfw/userManager.php";
include_once __DIR__ . "/../lpfw/logger.class.php";

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

/**
 * Class PasswortTest
 */
class ZStatisticsTest extends PHPUnit_Framework_TestCase
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

    public function testPerson()
    {
        $all = $this->db->queryInt("select  count(*) from person");
        $this->assertTrue($all>0);
        $todo = $this->db->queryInt("select  count(*) from person where deceasedYear is not null and user is not null");
        echo(" Persons:".$all." todo:".$todo);
        //TODO remove person login data if the person is died
    }

    public function testPicture()
    {
        $all = $this->db->queryInt("select  count(*) from picture");
        $this->assertTrue($all>0);
        echo(" Pictures:".$all);
    }

    public function testHistory()
    {
        $all = $this->db->queryInt("select  count(*) from history");
        $this->assertTrue($all>0);
        echo(" History:".$all);
        //TODO remove entry whithout refereced data
    }

    public function testPersonInPicture()
    {
        $all = $this->db->queryInt("select  count(*) from personInPicture");
        $this->assertTrue($all>0);
        echo(" PersonInPicture:".$all);
        //TODO remove entry whithout refereced data
    }

    public function testOpinion()
    {
        $all = $this->db->queryInt("select  count(*) from opinion");
        $this->assertTrue($all>0);
        echo(" Opinion:".$all);
        //TODO remove entry whithout refereced data
    }

}
