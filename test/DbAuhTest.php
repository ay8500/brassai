<?php

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../lpfw/mysqldbauh.class.php";
include_once __DIR__ . "/../lpfw/logger.class.php";

/**
 * Class DBConnection
 */
class DbAuhTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }


    private function createData()
    {
        $p = \Config::getDatabasePropertys();
        $db = new MySqlDbAUH($p->host, $p->database, $p->user, $p->password);

        ($db->isDbConnected());
        ($db->resetCounter());

        //Create test table an drop in some entrys
        // table:  phpunit
        // fields: id, contval, changeDate, changeForID, changeUserID, changeIP
        $db->query("drop table phpunit");
        ($db->query("create table phpunit (id int auto_increment primary key,
                              contval varchar(1024) null, 
                              changeDate timestamp default CURRENT_TIMESTAMP not null, 
                              changeUserID int, 
                              changeIP varchar(64), changeForID int  )"));
        return $db;
    }

    private function deleteData($db)
    {
        $db->query("drop table phpunit");
        $db->disconnect();
    }


    public function testInsertUserDateIP(): void
    {
        $p = \Config::getDatabasePropertys();
        $db = new MySqlDbAUH($p->host, $p->database, $p->user, $p->password);
        //user not logged in
        if (isset($_SESSION))
            unset($_SESSION['uId']);
        $this->assertSame(null, $db->getLoggedInUserId());

        //Empty array user is not logged on
        $_SERVER["REMOTE_ADDR"] = "122.121.33.41";
        $data = array();
        $data = $db->insertUserDateIP($data);
        $this->assertSame(2, sizeof($data));
        $this->assertSame("changeIP", $data[0]["field"]);
        $this->assertSame("s", $data[0]["type"]);
        $this->assertSame("122.121.33.41", $data[0]["value"]);

        //Not epty array user is logged on
        $_SESSION['uId'] = "346";
        $data = $db->insertUserDateIP($data);
        $this->assertSame(3, sizeof($data));
        $this->assertSame("changeUserID", $data[2]["field"]);
        $this->assertSame("n", $data[2]["type"]);
        $this->assertSame(346, $data[2]["value"]);

        $db->disconnect();
    }

    /**
     * @covers \maierlabs\lpfw\MySqlDbAUH
     *
     */
    public function testUpdateEntry(): void
    {
        $db = $this->createData();
        $this->assertNotNull($db);

        $this->assertFalse($db->updateEntry("phpunit", array()));
        $this->assertFalse($db->updateEntry(null, array("id" => 2)));

        $_SERVER["REMOTE_ADDR"] = "122.121.33.41";
        $_SESSION['uId'] = "346";
        $data = array();
        $data = $db->insertUserDateIP($data);
        $data = $db->insertFieldInArray($data, "contval", "543");
        $data = $db->insertFieldInArray($data, "changeForID", '123');
        $this->assertTrue($db->insert("phpunit", $data));
        $id = $db->getInsertedId();
        $ret = $db->queryFirstRow("select * from phpunit where id=" . $id);
        self::assertNotNull($ret);
        self::assertSame(543, intval($ret["contval"]));
        $this->assertTrue($db->updateEntry("phpunit", array("contval" => "4562", "id" => $id)));
        $ret = $db->queryFirstRow("select * from phpunit where id=" . $id);
        self::assertNotNull($ret);
        self::assertSame(4562, intval($ret["contval"]));

        $this->deleteData($db);
        self::assertFalse($db->isDbConnected());
    }

    public function testHistory(): void
    {
        $db = $this->createData();
        $this->assertNotNull($db);

        $historyInfo = $db->getHistoryInfo("test",9999999);
        $this->assertSame(0,sizeof($historyInfo));

        $history = $db->getHistory("test",9999999);
        $this->assertSame(0,sizeof($history));


        $this->deleteData($db);
        self::assertFalse($db->isDbConnected());
    }

}
