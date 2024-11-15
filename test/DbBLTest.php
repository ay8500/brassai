<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../dbBL.class.php";
include_once __DIR__ . "/../../lpfw/logger.class.php";

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
        $dbPropertys = \Config::getDatabasePropertys();
        $dataBase = new \maierlabs\lpfw\MySqlDbAUH($dbPropertys->host,$dbPropertys->database,$dbPropertys->user,$dbPropertys->password);
        $this->db = new dbBL($dataBase);
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
        $_SERVER["REMOTE_ADDR"]="192.168.255.255";
    }

    public function testBusinessLayer()
    {
        $this->assertNotNull($this->db);
    }


    public function testClassSchool()
    {
        unsetActClass();
        $this->assertNull(getActClass());
        unsetActSchool();

        //$this->assertNull($this->db->handleClassSchoolChange(null,99992));
        //$this->assertTrue(getActSchoolId()===99992);

        //$this->assertNotNull($this->db->handleClassSchoolChange(74,1908998));
        //$this->assertTrue(getActSchoolId()===1);
        //$this->assertTrue(getActClassId()===74);


    }

}
