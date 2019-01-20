<?php

include_once __DIR__ . "/../phpunit/config.class.php";
include_once __DIR__ . "/../dbBL.class.php";
include_once __DIR__ . "/../lpfw/logger.class.php";

class DbBLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var dbBL
     */
    private $db;

    /**
     * @var \maierlabs\lpfw\MySqlDbAUH
     */
    private $dataBase;

    public function setUp() {
        global $db;
        $this->db=$db;
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
        $_SERVER["REMOTE_ADDR"]="192.168.255.255";
    }

    public function testBusinssLayer()
    {
        $this->assertNotNull($this->db);
    }


    public function testClassSchool()
    {
        unsetAktClass();
        $this->assertNull(getAktClass());
        unsetAktSchool();

        $this->assertNull($this->db->handleClassSchoolChange(null,99992));
        $this->assertTrue(getAktSchoolId()===99992);

        $this->assertNotNull($this->db->handleClassSchoolChange(74,1908998));
        $this->assertTrue(getAktSchoolId()===1);
        $this->assertTrue(getAktClassId()===74);


    }

}
