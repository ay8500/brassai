<?php

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../mysqldbauh.class.php";
include_once __DIR__ . "/../logger.class.php";
/**
* Class Test MySqlDbAUH (Anonymous User Entrys) Database
* The database connection parameter are in config.class.php
* A single table will be created and deleted on the db, the history table need to be exists
**/
class DbAuhTest extends PHPUnit_Framework_TestCase
{
    private const NO_SUCH_TABLE= "nosuchtable";
    private const TEST_TABLE= "phpunittest";
    private const SERVER_REMOTE_ADDR = "123.45.67.89";
    private const SERVER_REMOTE_ADDR_1 = "98.76.54.32";
    private const SOME_TEXT = "This is some text Kalmár ";
    private const USER_ID =987123;

    public function setUp()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }


    /**
     * @return MySqlDbAUH
     */
    private function createData()
    {
        $p = \Config::getDatabasePropertys();
        $db = new MySqlDbAUH($p->host, $p->database, $p->user, $p->password);

        $this->assertTrue($db->isDbConnected());
        ($db->resetCounter());

        //Create test table an drop in some entrys
        // table:  phpunit
        // fields: id, contval, changeDate, changeForID, changeUserID, changeIP
        $db->query("drop table ".self::TEST_TABLE);
        ($db->query("create table ".self::TEST_TABLE." (
                              id int auto_increment primary key,
                              contval varchar(1024) null, 
                              changeDate timestamp default CURRENT_TIMESTAMP not null, 
                              changeUserID int, 
                              changeIP varchar(64),
                              changeForID int  )"));

        //Delete test made history entrys
        $this->assertTrue($db->delete("history", "`table`", "'".self::TEST_TABLE."'"));

        $_SERVER["REMOTE_ADDR"] = self::SERVER_REMOTE_ADDR;
        $_SESSION['uId'] = self::USER_ID;
        $data = array();
        $data = $db->insertUserDateIP($data);
        $data = $db->insertFieldInArray($data, "contval", "543");
        $data = $db->insertFieldInArray($data, "changeForID", '123');
        $this->assertTrue($db->insert(self::TEST_TABLE, $data));

        return $db;
    }

    /**
     * @param $db MySqlDbAUH
     * @return void
     */
    private function deleteData($db)
    {
        //Delete test made history entrys
        $db->delete("history", "`table`", "'".self::TEST_TABLE."'");
        $db->query("drop table ".self::TEST_TABLE);
        $db->disconnect();
    }


    public function testInsertUserDateIP(): void
    {
        $db = new MySqlDbAUH("","");
        //user not logged in
        if (isset($_SESSION))
            unset($_SESSION['uId']);
        $this->assertSame(null, $db->getLoggedInUserId());

        //Empty array user is not logged on
        $_SERVER["REMOTE_ADDR"] = self::SERVER_REMOTE_ADDR;
        $data = array();
        $data = $db->insertUserDateIP($data);
        $this->assertSame(2, sizeof($data));
        $this->assertSame("changeIP", $data[0]["field"]);
        $this->assertSame("s", $data[0]["type"]);
        $this->assertSame(self::SERVER_REMOTE_ADDR, $data[0]["value"]);

        //Not epty array user is logged on
        $_SESSION['uId'] = self::USER_ID;
        $data = $db->insertUserDateIP($data);
        $this->assertSame(3, sizeof($data));
        $this->assertSame("changeUserID", $data[2]["field"]);
        $this->assertSame("n", $data[2]["type"]);
        $this->assertSame(self::USER_ID, intval($data[2]["value"]));

    }

    public function testUpdateEntry(): void
    {
        $db = $this->createData();
        $this->assertNotNull($db);

        $this->assertFalse($db->updateEntry(self::TEST_TABLE, array()));
        $this->assertFalse($db->updateEntry(self::NO_SUCH_TABLE, array("id" => 2)));

        $id = $db->getInsertedId();
        $ret = $db->queryFirstRow("select * from ".self::TEST_TABLE." where id=" . $id);
        self::assertNotNull($ret);
        self::assertSame(543, intval($ret["contval"]));
        $this->assertTrue($db->updateEntry(self::TEST_TABLE, array("contval" => self::SOME_TEXT, "id" => $id)));
        $ret = $db->queryFirstRow("select * from ".self::TEST_TABLE." where id=" . $id);
        self::assertNotNull($ret);
        self::assertSame(self::SOME_TEXT, $ret["contval"]);

        $this->deleteData($db);
        self::assertFalse($db->isDbConnected());
    }

    public function testHistory(): void
    {
        $db = $this->createData();
        $this->assertNotNull($db);

        //No such table or id
        $historyInfo = $db->getHistoryInfo(self::NO_SUCH_TABLE,99999999);
        $this->assertSame(0,sizeof($historyInfo));

        $history = $db->getHistory(null,99999999);
        $this->assertSame(0,sizeof($history));

        $this->assertFalse($db->createHistoryEntry(self::NO_SUCH_TABLE,1));

        $this->assertFalse($db->deleteHistoryEntry(99999999));

        $history = $db->getHistory(null,null);
        $this->assertTrue(sizeof($history)>0);

        //Insert History
        $ret = $db->queryFirstRow("select * from ".self::TEST_TABLE);
        $this->assertNotNull($ret);
        $this->assertTrue($db->createHistoryEntry(self::TEST_TABLE,$ret["id"]));

        $historyInfo = $db->getHistoryInfo(self::TEST_TABLE,$ret["id"]);
        $this->assertSame(1,sizeof($historyInfo));

        $this->assertTrue($db->createHistoryEntry(self::TEST_TABLE,$ret["id"]));
        $history = $db->getHistory(self::TEST_TABLE,$ret["id"]);
        $this->assertSame(2,sizeof($history));
        $this->assertSame(self::TEST_TABLE,$history[0]["table"]);
        $this->assertTrue($db->deleteHistoryEntry($history[0]["id"]));
        $history = $db->getHistory(self::TEST_TABLE);
        $this->assertSame(1,sizeof($history));

        $this->deleteData($db);
    }

    private function getEntrysInTheTable($db) {
        return $db->queryInt("select count(*) from ".self::TEST_TABLE);
    }

    public function testSaveEntry() {
        $db = $this->createData();
        $this->assertNotNull($db);
        $_SERVER["REMOTE_ADDR"]=self::SERVER_REMOTE_ADDR;
        $entrysInTheTable =  $this->getEntrysInTheTable($db);

        //Save an entry as logged in user, a new entry will be created
        $_SESSION['uId'] = self::USER_ID;
        $entry=array("id"=>-1,"contval"=>self::SOME_TEXT);
        $this->assertFalse( ($originalId = $db->saveEntry(self::NO_SUCH_TABLE,$entry))>=0);
        $this->assertTrue ( ($originalId = $db->saveEntry(self::TEST_TABLE,$entry))>=0);
        $ret = $db->querySignleRow("select * from ".self::TEST_TABLE." where id=".$originalId);
        $this->assertTrue($ret["id"]==$originalId && isset($ret["changeDate"]));
        $this->assertSame(self::USER_ID,intval($ret["changeUserID"]));
        $this->assertSame(self::SERVER_REMOTE_ADDR,$ret["changeIP"]);
        $this->assertSame(self::SOME_TEXT,$ret["contval"]);
        $this->assertSame(null,$ret["changeForID"]);
        $this->assertSame($entrysInTheTable+1,$this->getEntrysInTheTable($db));

        //Update the entry the entry will be updated
        $entry=array("id"=>$originalId,"contval"=>self::SOME_TEXT." change1 ");
        $this->assertLessThan( 0,$db->saveEntry(self::NO_SUCH_TABLE,$entry));
        $sameId = $db->saveEntry(self::TEST_TABLE,$entry);
        $this->assertTrue( $sameId>=0);
        $this->assertSame($sameId,$originalId);
        $ret = $db->querySignleRow("select * from ".self::TEST_TABLE." where id=".$sameId);
        $this->assertTrue($ret["id"]==$originalId && isset($ret["changeDate"]));
        $this->assertSame(self::USER_ID,intval($ret["changeUserID"]));
        $this->assertSame(self::SOME_TEXT." change1 ",$ret["contval"]);
        $this->assertSame($entrysInTheTable+1,$this->getEntrysInTheTable($db));

        //TODO check history entry

        //Make an anonymous change => a new anonymous entry will be created
        unset($_SESSION['uId']);
        $entry=array("id"=>$originalId,"contval"=>self::SOME_TEXT." anonymous change1 ");
        $this->assertLessThan( 0,$db->saveEntry(self::NO_SUCH_TABLE,$entry));
        $anonymousId = $db->saveEntry(self::TEST_TABLE,$entry);
        $this->assertTrue( $anonymousId>=0);
        $this->assertNotSame($sameId,$anonymousId);
        $ret = $db->querySignleRow("select * from ".self::TEST_TABLE." where id=".$anonymousId);
        $this->assertTrue($ret["id"]==$anonymousId && isset($ret["changeDate"]));
        $this->assertSame(null,$ret["changeUserID"]);
        $this->assertSame(self::SOME_TEXT." anonymous change1 ",$ret["contval"]);
        $this->assertSame($entrysInTheTable+2,$this->getEntrysInTheTable($db));

        //Make second anonymous change => the anonymous entry will be updated
        $entry=array("id"=>$anonymousId,"contval"=>self::SOME_TEXT." anonymous change2 ");
        $newAnonymousId = $db->saveEntry(self::TEST_TABLE,$entry);
        $this->assertTrue( $newAnonymousId>=0);
        $this->assertSame(intval($newAnonymousId),intval($anonymousId));
        $ret = $db->querySignleRow("select * from ".self::TEST_TABLE." where id=".$newAnonymousId);
        $this->assertTrue($ret["id"]===$newAnonymousId && isset($ret["changeDate"]));
        $this->assertSame(null,$ret["changeUserID"]);
        $this->assertSame(self::SOME_TEXT." anonymous change2 ",$ret["contval"]);
        $this->assertSame($entrysInTheTable+2,$this->getEntrysInTheTable($db));

        //Read the entry as logged in user and different_ip => get the original entry
        $_SESSION['uId'] = self::USER_ID;
        $_SERVER["REMOTE_ADDR"]=self::SERVER_REMOTE_ADDR_1;
        $this->assertNotNull($ret = $db->getEntryById(self::TEST_TABLE,$originalId));
        $this->assertSame(intval($ret["id"]),$originalId);
        $this->assertSame(self::USER_ID,intval($ret["changeUserID"]));
        $this->assertSame(self::SOME_TEXT." change1 ",$ret["contval"]);

        $ret = $db->getIdList(self::TEST_TABLE);
        $this->assertCount(1,$ret);
        $this->assertSame(intval($ret[0]["id"]),$originalId);

        //Read the entry as logged in user with the same ip address => get the anonymous entry
        $_SERVER["REMOTE_ADDR"]=self::SERVER_REMOTE_ADDR;
        $this->assertNotNull($ret = $db->getEntryById(self::TEST_TABLE,$originalId));
        $this->assertSame(intval($ret["id"]),$anonymousId);
        $this->assertSame($originalId,intval($ret["changeForID"]));
        $this->assertNull($ret["changeUserID"]);
        $this->assertSame(self::SOME_TEXT." anonymous change2 ",$ret["contval"]);

        $ret = $db->getIdList(self::TEST_TABLE);
        $this->assertCount(1,$ret);
        $this->assertSame(intval($ret[0]["id"]),$anonymousId);

        //Read the entry as logged in user and same ip force the original entry=> get the original entry
        $this->assertNotNull($ret = $db->getEntryById(self::TEST_TABLE,$originalId,true));
        $this->assertSame(intval($ret["id"]),$originalId);
        $this->assertSame(self::USER_ID,intval($ret["changeUserID"]));
        $this->assertSame(self::SOME_TEXT." change1 ",$ret["contval"]);

        $this->assertNotNull($ret = $db->getElementList(self::TEST_TABLE,true,null,null,null,null,"id"));
        $this->assertCount(1,$ret);
        $this->assertSame(intval($ret[0]["id"]),$originalId);


        //Read the entry as anonymous user with the same ip address => get the anonymous entry
        unset($_SESSION['uId']);
        $_SERVER["REMOTE_ADDR"]=self::SERVER_REMOTE_ADDR;
        $this->assertNotNull($ret = $db->getEntryById(self::TEST_TABLE,$originalId));
        $this->assertSame(intval($ret["id"]),$anonymousId);
        $this->assertSame($originalId,intval($ret["changeForID"]));
        $this->assertNull($ret["changeUserID"]);
        $this->assertSame(self::SOME_TEXT." anonymous change2 ",$ret["contval"]);

        $ret = $db->getIdList(self::TEST_TABLE);
        $this->assertCount(1,$ret);
        $this->assertSame(intval($ret[0]["id"]),$anonymousId);


        //Read the entry as anonymous user with different ip address => get the original entry
        $_SERVER["REMOTE_ADDR"]=self::SERVER_REMOTE_ADDR_1;
        $this->assertNotNull($ret = $db->getEntryById(self::TEST_TABLE,$originalId));
        $this->assertSame(intval($ret["id"]),$originalId);
        $this->assertSame(self::USER_ID,intval($ret["changeUserID"]));
        $this->assertSame(self::SOME_TEXT." change1 ",$ret["contval"]);

        $ret = $db->getIdList(self::TEST_TABLE);
        $this->assertCount(1,$ret);
        $this->assertSame(intval($ret[0]["id"]),$originalId);

        //getEntryByField
        $this->assertNull($db->getEntryByField(self::TEST_TABLE,"contval", "" ));
        $this->assertNotNull($db->getEntryByField(self::TEST_TABLE,"contval", self::SOME_TEXT." change1 " ));

        //getEntry
        $this->assertNull($db->getEntry(self::TEST_TABLE,"where contval =''" ));
        $this->assertNotNull($db->getEntry(self::TEST_TABLE,"contval ='".self::SOME_TEXT." change1 "."'"));

        //getEntryById with no id
        $this->assertNull($db->getEntryById(self::TEST_TABLE,null));

        //Not a good join test because the table doesn't exists :(
        $this->assertNotNull($ret = $db->getElementList(self::TEST_TABLE,true,null,null,null,null,"id","no_table."));


        $this->deleteData($db);
    }

    public function testGetSqlAnonymous() {
        $db=new MySqlDbAUH("","");
        $_SERVER["REMOTE_ADDR"]=self::SERVER_REMOTE_ADDR;
        $s = $db->getSqlAnonymous("");
        $l = $db->getSqlAnonymous(self::TEST_TABLE,true);
        $this->assertGreaterThan(strlen($s),strlen($l));
    }

}
