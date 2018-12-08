<?php

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../mysqldbauh.class.php";
include_once __DIR__ . "/../logger.class.php";

/**
 * Class DBConnection
 */
class DBTest extends PHPUnit_Framework_TestCase
{
    public static $start=false;

    public function setUp()
    {
        if (self::$start==false) {
            echo("Database\n");
            \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
        }
        self::$start=true;
    }

    /**
     *
     */
    public function testDBconnectionFailure() {
        $p = \Config::getDatabasePropertys();
        $db = new MySqlDbAUH($p->host, $p->database, $p->user . 'x', $p->password);
        $this->assertFalse($db->isDbConnected());
    }

    /**
     * @covers \maierlabs\lpfw\MySql
     *
     */
    public function testQuery():void
    {
        $p = \Config::getDatabasePropertys();
        $db = new MySqlDbAUH($p->host, $p->database, $p->user, $p->password);

        self::assertTrue($db->isDbConnected());
        self::assertTrue($db->resetCounter());

        //Create test table an drop in some entrys
        $db->query("drop table phpunit");
        self::assertTrue($db->query("create table phpunit (id int auto_increment primary key,contval varchar(1024) null, changeDate timestamp default CURRENT_TIMESTAMP not null, changeUserID int, changeIP varchar(64), changeForID int  )"));
        $data=array();
        $data = $db->insertFieldInArray($data,"contval","9876543");
        $data = $db->insertFieldInArray($data,"changeUserID",'10');
        $data = $db->insertFieldInArray($data,"changeForID",null);
        self::assertTrue($db->insert("phpunit",$data));
        self::assertTrue($db->insert("phpunit",$data));
        self::assertTrue($db->insert("phpunit",$data));


        $ret = $db->query("select * from levi");
        self::assertFalse($ret);

        $ret = $db->query("select id from phpunit limit 2");
        self::assertTrue($ret);
        self::assertTrue(sizeof($db->getRowList())==2);

        $c = $db->count();
        self::assertTrue(2===$c);

        $ret = $db->queryFirstRow("select id from phpunit limit 3");
        self::assertNotNull($ret);
        $ret = $db->queryFirstRow("select id from phpunit limitare 3");
        self::assertNull($ret);

        $ret = $db->querySignleRow("select id from phpunit limit 1");
        self::assertNotNull($ret);
        $ret = $db->querySignleRow("select id from phpunit limit 3");
        self::assertNull($ret);
        $ret = $db->querySignleRow("select id from phpunit  limitare 3");
        self::assertNull($ret);

        $ret = $db->queryInt("select count(1) from phpunit ");
        self::assertTrue($ret>0);
        $ret = $db->queryInt("select count(1) from phpunit  limitare 5");
        self::assertTrue($ret==-1);

        $db->query("select * from phpunit  wwhere id is not null");
        self::assertNull($db->fetchRow());
        self::assertTrue(sizeof($db->getRowList())==0);
        self::assertTrue($db->count()===0);

        $ret = $db->createFieldArray("s","Levi","OK");
        self::assertTrue($ret["field"]=="Levi" && $ret["type"]=="s" && $ret["value"]=="OK");

        $data=array();
        $data = $db->insertFieldInArray($data,"Levi","Jeans");
        $data = $db->insertFieldInArray($data,"BMWid","320");
        $data = $db->insertFieldInArray($data,"changeDate","1988-12-30");
        self::assertTrue($data[0]["field"]=="Levi" && $data[0]["type"]=="s" && $data[0]["value"]=="Jeans");
        self::assertTrue($data[2]["field"]=="changeDate" && $data[2]["type"]=="d" && $data[2]["value"]=="1988-12-30");

        $data = $db->changeFieldInArray($data,"Levi","OK");
        self::assertTrue($data[0]["field"]=="Levi" && $data[0]["type"]=="s" && $data[0]["value"]=="OK");

        $data = $db->changeFieldInArray($data,"VW","OK");
        self::assertTrue($data[3]["field"]=="VW" && $data[3]["type"]=="s" && $data[3]["value"]=="OK");

        $data = $db->deleteFieldInArray($data,"Levi");
        self::assertTrue(sizeof($data)==3);

        $data = $db->deleteFieldInArray($data,"Porsche");
        self::assertTrue(sizeof($data)==3);

        $data = $db->setFieldInArrayToNull($data,"VW");
        self::assertTrue($data[2]["field"]=="VW" && $data[2]["type"]=="s" && !isset($data[2]["value"]) );

        //Insert
        $data=array();
        $data = $db->insertFieldInArray($data,"contval","Hola");
        $data = $db->insertFieldInArray($data,"changeUserID",'11');
        self::assertTrue($db->insert("phpunit",$data));
        $id = $db->getInsertedId();
        $ret=$db->queryFirstRow("select * from phpunit where changeUserID=11");
        self::assertNotNull($ret);
        self::assertTrue($id==$ret["id"]);
        self::assertFalse($db->insert("phpunit",array($db->createFieldArray("s","error","error"))));
        $ret=$db->getErrorMessage();
        self::assertTrue(strlen($ret)>6);
        self::assertTrue($db->getNextAutoIncrement("phpunit")==5);


        //Delete
        self::assertTrue($db->deleteWhere("phpunit","changeUserID=".$id));
        self::assertNull($db->queryFirstRow("select * from phpunit where changeUserID=".$id));

        self::assertTrue($db->delete("phpunit","changeUserID",1));
        self::assertNull($db->queryFirstRow("select * from phpunit where changeUserID=1"));

        self::assertFalse($db->delete("phpunit","error",1));

        //Table count sum and multiple field
        self::assertTrue($db->commit());
        self::assertTrue($db->tableCount("phpunit")==4);
        self::assertTrue($db->tableCount("phpunit","changeUserID=10")==3);

        self::assertTrue($db->tableSumField("phpunit","id")==10);
        self::assertTrue($db->tableSumField("phpunit","id","changeUserID<>11")==6);

        self::assertTrue($db->tableSumMultField("phpunit","id","changeUserID")==104);
        self::assertTrue($db->tableSumMultField("phpunit","id","changeUserID","changeUserID=10")==60);

        //Update
        $data = array();
        $data = $db->insertFieldInArray($data,"changeUserID",12);
        $data = $db->insertFieldInArray($data,"contval","Levi");
        self::assertTrue($db->update("phpunit",$data,"id","2"));
        self::assertTrue($db->queryInt("select changeUserID from phpunit where id=2")==12);
        self::assertFalse($db->update("phpunit",$data,"error","2"));
        $data = array();
        $data = $db->insertFieldInArray($data,"changeUserID",'');
        self::assertTrue($db->update("phpunit",$data,"id","2"));
        self::assertTrue($db->queryInt("select count(1) from phpunit where changeUserID is null")==1);

        $data = array();
        $data = $db->insertFieldInArray($data,"changeIP","127.0.0.1");
        self::assertTrue($db->update("phpunit",$data));
        self::assertTrue($db->tableCount("phpunit","changeIP='127.0.0.1'")==4);

        $data = $db->changeFieldInArray($data,"changeIP",null);
        self::assertTrue($db->update("phpunit",$data,"id",3));
        self::assertTrue($db->tableCount("phpunit","changeIP is null")==1);

        $data = $db->changeFieldInArray($data,"changeIP",'');
        self::assertTrue($db->update("phpunit",$data,"id",3));
        self::assertTrue($db->tableCount("phpunit","changeIP =''")==1);

        //Counter
        $ret=$db->getCounter();
        self::assertTrue(is_object($ret));
        self::assertTrue($ret->changes==16);
        self::assertTrue($ret->querys==36);


        self::assertTrue($db->query("drop table phpunit"));

        $db->disconnect();
        self::assertFalse($db->isDbConnected());

        self::assertTrue($db->rereplaceSpecialChars("Levi\'s")=="Levi's");
    }


}
