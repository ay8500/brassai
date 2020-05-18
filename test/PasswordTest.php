<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../../lpfw/mysqldbauh.class.php";
include_once __DIR__ . "/../../lpfw/userManager.php";
include_once __DIR__ . "/../../lpfw/logger.class.php";

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

/**
 * Class PasswortTest
 */
class PasswordTest extends PHPUnit_Framework_TestCase
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

    public function testDatabaseEncryption()
    {
        $ok = $this->db->queryInt("select count(*),passw from person having length(passw)>=32");
        $all = $this->db->queryInt("select count(*) from person");
        $this->assertSame($all,$ok);
        /*
        while ($row = $this->db->fetchRow()) {
            $p=$row["passw"];
            $dp=encrypt_decrypt("decrypt",$p);
            $this->assertTrue(strlen($p)==32);
            $this->assertTrue(strlen($dp)!=32);
        }
        */
    }

    public function testEncription() {
        $this->assertSame("QXAxeXBKY0NoUTBDd0hpZkYyaXc1QT09",encrypt_decrypt("encrypt","MaierLabs"));
        $this->assertSame("MaierLabs",encrypt_decrypt("decrypt","QXAxeXBKY0NoUTBDd0hpZkYyaXc1QT09"));
        $this->assertSame("QXAxeXBKY0NoUTBDd0hpZkYyaXc1QT09",encrypt_decrypt("encrypt","QXAxeXBKY0NoUTBDd0hpZkYyaXc1QT09"));
    }


}
