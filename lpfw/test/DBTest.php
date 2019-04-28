<?php

use \maierlabs\lpfw\MySql as MySql;

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../mysql.class.php";
include_once __DIR__ . "/../logger.class.php";



/**
 * Class Test MySql Database
 * The database connection parameter are in config.class.php
 * A single table will be created and deleted on the db
 */
class DBTest extends PHPUnit_Framework_TestCase
{
    private const NO_SUCH_TABLE= "nosuchtable";
    private const TEST_TABLE= "phpunittest";

    public function setUp()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    public function testDBconnectionFailure() {
        $p = \Config::getDatabasePropertys();
        $db = new MySql($p->host, $p->database, $p->user . 'x', $p->password);
        $this->assertNotNull($db);
        $this->assertFalse($db->isDbConnected());
    }

    public function testDBconnection() {
        $p = \Config::getDatabasePropertys();
        $db = new MySql($p->host, $p->database, $p->user, $p->password);
        $this->assertNotNull($db);
        $this->assertTrue($db->isDbConnected());
        $db->disconnect();
    }

    public function testFieldInArray() {
        $data=array();
        $db = new MySql("","");
        $data = $db->insertFieldInArray($data,"Levi","Jeans");
        $data = $db->insertFieldInArray($data,"BMWid","320");
        $data = $db->insertFieldInArray($data,"changeDate","1988-12-30");
        $this->assertTrue($data[0]["field"]=="Levi" && $data[0]["type"]=="s" && $data[0]["value"]=="Jeans");
        $this->assertTrue($data[2]["field"]=="changeDate" && $data[2]["type"]=="d" && $data[2]["value"]=="1988-12-30");

        $data = $db->changeFieldInArray($data,"Levi","OK");
        $this->assertTrue($data[0]["field"]=="Levi" && $data[0]["type"]=="s" && $data[0]["value"]=="OK");

        $data = $db->changeFieldInArray($data,"VW","OK");
        $this->assertTrue($data[3]["field"]=="VW" && $data[3]["type"]=="s" && $data[3]["value"]=="OK");

        $data = $db->deleteFieldInArray($data,"Levi");
        $this->assertSame(3,sizeof($data));

        $data = $db->deleteFieldInArray($data,"Porsche");
        $this->assertSame(3,sizeof($data));

        $data = $db->setFieldInArrayToNull($data,"VW");
        $this->assertTrue($data[2]["field"]=="VW" && $data[2]["type"]=="s" && !isset($data[2]["value"]) );

        $ret = $db->createFieldArray("s","Levi","OK");
        $this->assertTrue($ret["field"]=="Levi" && $ret["type"]=="s" && $ret["value"]=="OK");

        $ret = $db->createFieldArray("s","Levi",null);
        $this->assertTrue($ret["field"]=="Levi" && $ret["type"]=="s" && $ret["value"]==null);
    }

    private function createData() {
        $p = \Config::getDatabasePropertys();
        $db = new MySql($p->host, $p->database, $p->user, $p->password);

        ($db->isDbConnected());
        ($db->resetCounter());

        //Create test table an drop in some entrys
        // table:  phpunit
        // fields: id, contval, changeDate, changeForID, changeUserID
        ($db->query("drop table ".self::TEST_TABLE));
        $this->assertTrue($db->query("create table ".self::TEST_TABLE." (id int auto_increment primary key,contval varchar(1024) null, changeDate timestamp default CURRENT_TIMESTAMP not null, changeUserID int, changeIP varchar(64), changeForID int  )"));
        $data=array();
        $data = $db->insertFieldInArray($data,"contval","9876543");
        $data = $db->insertFieldInArray($data,"changeUserID",'10');
        $data = $db->insertFieldInArray($data,"changeForID",null);
        $this->assertTrue($db->insert(self::TEST_TABLE,$data));
        $this->assertTrue($db->insert(self::TEST_TABLE,$data));
        $data = $db->changeFieldInArray($data,"changeForID",'');
        $this->assertTrue($db->insert(self::TEST_TABLE,$data));
        return $db;
    }

    private function deleteData($db) {
        $db->query("drop table ".self::TEST_TABLE);
        $db->disconnect();
    }

    /**
     * @covers \maierlabs\lpfw\MySql
     *
     */
    public function testQuery():void
    {
        $db = $this->createData();

        //Query mit error
        $ret = $db->query("select * from ".self::NO_SUCH_TABLE);
        $this->assertFalse($ret);

        //Query and count
        $ret = $db->query("select id from ".self::TEST_TABLE." limit 2");
        $this->assertTrue($ret);
        $this->assertSame(2,sizeof($db->getRowList()));
        $c = $db->count();
        $this->assertSame(2,$c);

        //Query first row return none ore one row even the query has more rows
        $ret = $db->queryFirstRow("select id from ".self::TEST_TABLE." limit 3");
        $this->assertNotNull($ret);
        $this->assertSame(1,sizeof($ret));
        $ret = $db->queryFirstRow("select id from ".self::NO_SUCH_TABLE);
        $this->assertNull($ret);

        //Query single row return null if more then one rows are in the result
        $ret = $db->querySignleRow("select id from ".self::TEST_TABLE." limit 1");
        $this->assertNotNull($ret);
        $ret = $db->querySignleRow("select id from ".self::TEST_TABLE." limit 3");
        $this->assertNull($ret);
        $ret = $db->querySignleRow("select id from ".self::NO_SUCH_TABLE);
        $this->assertNull($ret);

        //Query an iteger walue will return false on error
        $ret = $db->queryInt("select count(1) from ".self::TEST_TABLE);
        $this->assertTrue($ret>0);
        $ret = $db->queryInt("select count(1) from ".self::NO_SUCH_TABLE);
        $this->assertFalse($ret);

        $db->query("select * from ".self::TEST_TABLE."  wwhere id is not null");
        $this->assertNull($db->fetchRow());
        $this->assertTrue(sizeof($db->getRowList())==0);
        $this->assertTrue($db->count()===0);

        //Insert
        $data=array();
        $data = $db->insertFieldInArray($data,"contval","Hola");
        $data = $db->insertFieldInArray($data,"changeUserID",'11');
        $this->assertTrue($db->insert(self::TEST_TABLE,$data));
        $id = $db->getInsertedId();
        $ret=$db->queryFirstRow("select * from ".self::TEST_TABLE." where changeUserID=11");
        $this->assertNotNull($ret);
        $this->assertSame($id,intval($ret["id"]));
        $this->assertFalse($db->insert(self::TEST_TABLE,array($db->createFieldArray("s","error","error"))));
        $ret=$db->getErrorMessage();
        $this->assertTrue(strlen($ret)>6);
        $this->assertSame(5,$db->getNextAutoIncrement(self::TEST_TABLE));

        //Insert Null value
        $data=array();
        $data = $db->insertFieldInArray($data,"contval",null);
        $data = $db->insertFieldInArray($data,"changeUserID",'121');
        $this->assertTrue($db->insert(self::TEST_TABLE,$data));
        $ret=$db->queryFirstRow("select * from ".self::TEST_TABLE." where changeUserID=121");
        $this->assertNotNull($ret);
        $this->assertSame(null,$ret["contval"]);


        //Delete
        $this->assertTrue($db->deleteWhere(self::TEST_TABLE,"changeUserID=".$id));
        $this->assertNull($db->queryFirstRow("select * from ".self::TEST_TABLE." where changeUserID=".$id));

        $this->assertTrue($db->delete(self::TEST_TABLE,"changeUserID",1));
        $this->assertNull($db->queryFirstRow("select * from ".self::TEST_TABLE." where changeUserID=1"));

        $this->assertFalse($db->delete(self::TEST_TABLE,"error",1));

        //Table count sum and multiple field
        $this->assertTrue($db->commit());
        $this->assertSame(5,$db->tableCount(self::TEST_TABLE));
        $this->assertSame(3,$db->tableCount(self::TEST_TABLE,"changeUserID=10"));

        $this->assertSame(15,$db->tableSumField(self::TEST_TABLE,"id"));
        $this->assertSame(11,$db->tableSumField(self::TEST_TABLE,"id","changeUserID<>11"));

        $this->assertSame(709,$db->tableSumMultField(self::TEST_TABLE,"id","changeUserID"));
        $this->assertSame(60,$db->tableSumMultField(self::TEST_TABLE,"id","changeUserID","changeUserID=10"));

        //Update
        $data = array();
        $data = $db->insertFieldInArray($data,"changeUserID",12);
        $data = $db->insertFieldInArray($data,"contval","Levi");
        $this->assertTrue($db->update(self::TEST_TABLE,$data,"id","2"));
        $this->assertSame(12,$db->queryInt("select changeUserID from ".self::TEST_TABLE." where id=2"));
        $this->assertFalse($db->update(self::TEST_TABLE,$data,"error","2"));
        $data = array();
        $data = $db->insertFieldInArray($data,"changeUserID",null);
        $this->assertTrue($db->update(self::TEST_TABLE,$data,"id","2"));
        $this->assertSame(1,$db->queryInt("select count(1) from ".self::TEST_TABLE." where changeUserID is null"));

        $data = array();
        $data = $db->insertFieldInArray($data,"changeIP","127.0.0.1");
        $data = $db->insertFieldInArray($data,"changeUserID",'');
        $this->assertTrue($db->update(self::TEST_TABLE,$data));
        $this->assertSame(5,$db->tableCount(self::TEST_TABLE,"changeIP='127.0.0.1'"));

        $data = $db->changeFieldInArray($data,"changeIP",null);
        $this->assertTrue($db->update(self::TEST_TABLE,$data,"id",3));
        $this->assertSame(1,$db->tableCount(self::TEST_TABLE,"changeIP is null"));

        $data = $db->changeFieldInArray($data,"changeIP",'');
        $this->assertTrue($db->update(self::TEST_TABLE,$data,"id",3));
        $this->assertSame(1,$db->tableCount(self::TEST_TABLE,"changeIP =''"));

        //Get one column list
        $ret = $db->query("select * from ".self::TEST_TABLE);
        $this->assertTrue($ret);
        $data = $db->getOneColumnList("id");
        $this->assertSame(1,intval($data[0]));

        //Counter
        $ret=$db->getCounter();
        $this->assertTrue(is_object($ret));
        $this->assertSame(15,$ret->changes);
        $this->assertSame(38,$ret->querys);

        $this->deleteData($db);
        $this->assertFalse($db->isDbConnected());

    }

    public function testStringOperations() {
        $db=new MySql("","");
        $this->assertTrue($db->rereplaceSpecialChars("Levi\'s")=="Levi's");
        $this->deleteData($db);
    }

}
