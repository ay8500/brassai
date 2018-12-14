<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 08.12.2018
 * Time: 01:16
 */

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../../dbDAO.class.php";
include_once __DIR__ . "/../logger.class.php";
include_once __DIR__ . "/../../dbDaFamily.class.php";


class FamilyRelativeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var dbDAO
     */
    private $db;

    public function setUp() {
        $this->db=new dbDaFamily(new dbDAO(null));
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    public function testBrothers()
    {
        $this->assertNotNull($this->db);
        $a = array(array("a" => "1", "b" => "11"), array("a" => "2", "b" => "41"), array("a" => "3", "b" => "31"), array("a" => "4", "b" => "11"));
        $this->assertTrue(sizeof($a) == 4);
        $a = $this->db->unique_multidim_array($a,"b");
        $this->assertTrue(sizeof($a) == 3);
    }
}