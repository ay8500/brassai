<?php

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../../dbDAO.class.php";
include_once __DIR__ . "/../logger.class.php";

class DbBLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var dbDAO
     */
    private $db;

    /**
     * @var \maierlabs\lpfw\MySqlDbAUH
     */
    private $dataBase;

    public function setUp() {
        //Connect to the DB
        $dbPropertys = \Config::getDatabasePropertys();
        $this->dataBase = new \maierlabs\lpfw\MySqlDbAUH($dbPropertys->host,$dbPropertys->database,$dbPropertys->user,$dbPropertys->password);

        $this->db=new dbDAO($this->dataBase);
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
        $_SERVER["REMOTE_ADDR"]="::1";
    }

    public function testArray2ValueSearch()
    {
        $this->assertNotNull($this->db);

        $testArray = array(
            array("a" => "10", "b" => "20"),
            array("a" => "10", "b" => "21"),
            array("a" => "10", "b" => "21"),
            array("a" => "11", "b" => "20"),
            array("a" => "11", "b" => "21")
        );
        $this->assertTrue($this->db->array3ValueSearch($testArray,"a","11","a","11","b","20")==3);
    }

    public function testInsertToArrayRecentChangesList() {
        $accList = array(
            array("id"=>1,"changeDate"=>"2018-01-01","type"=>"person","action"=>"change","changeUserID"=>11),
            array("id"=>2,"changeDate"=>"2018-01-02","type"=>"picture","action"=>"change","changeUserID"=>11),
            array("id"=>3,"changeDate"=>"2018-01-03","type"=>"person","action"=>"opinion","changeUserID"=>11),
            array("id"=>4,"changeDate"=>"2018-01-04","type"=>"person","action"=>"change","changeUserID"=>11),
            array("id"=>5,"changeDate"=>"2018-01-05","type"=>"class","action"=>"change","changeUserID"=>11),
        );
        //entry exists
        $accList=$this->db->insertToArrayRecentChangesList($accList,2,"2018-10-10","picture","change",12);
        $this->assertTrue(sizeof($accList)==5);
        $this->assertTrue($accList[0]["id"]==2);
        $this->assertTrue($accList[0]["changeUserID"]==12);
        //new entry
        $accList=$this->db->insertToArrayRecentChangesList($accList,6,"2018-10-10","picture","change",null);
        $this->assertTrue(sizeof($accList)==5);
        $this->assertTrue($accList[0]["id"]==6);
        $this->assertTrue($accList[0]["changeUserID"]==null);
    }

    public function testUpdateRecentChangesList() {
        $accList=$this->db->getRecentChangesListByDate(date_create(), 48);
        $this->db->updateAcceleratorEntry($accList,1);
        //$this->db->updateRecentChangesList();
        $accList = $this->db->getAcceleratorData(1);
        $this->assertTrue(sizeof($accList)>0);
    }

    public function testDeleteFromRecentChangesList()
    {
        $accList = $this->db->getAcceleratorData(1);
        $this->assertFalse($this->db->deleteFromRecentChangesList(1,"notexistingtype"));
        $accList2 = $this->db->getAcceleratorData(1);
        $this->assertTrue(sizeof($accList)==sizeof($accList2));

        $this->assertTrue($this->db->deleteFromRecentChangesList($accList[3]["id"],$accList[3]["type"]));
        $accList2 = $this->db->getAcceleratorData(1);
        $this->assertTrue(sizeof($accList)==sizeof($accList2));
        $this->db->updateAcceleratorEntry($accList,1);
        $accList3 = $this->db->getAcceleratorData(1);
        $this->assertTrue($accList==$accList3);
    }
}
