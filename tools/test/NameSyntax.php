<?php

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../mysqldbauh.class.php";
include_once __DIR__ . "/../logger.class.php";

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

class NameSyntax extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \maierlabs\lpfw\MySqlDbAUH
     */
    private $db ;

    /**
     * @var array
     */
    private $firstName = array();
    private $lastName = array();

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

    public function testNames() {
        $this->assertTrue($this->db->query("select firstname,lastname,id from person"));
        while ($row = $this->db->fetchRow()) {
            $this->firstName = $this->addName($this->firstName,$row["firstname"]);
            $this->lastName = $this->addName($this->lastName,$row["lastname"]);
        }
        arsort($this->firstName,SORT_NUMERIC);
        arsort($this->lastName,SORT_NUMERIC);
        echo(sizeof($this->firstName).'-'.sizeof($this->lastName));
        /*foreach ($this->lastName as $name=>$count) {
            echo($count.":".$name." Check:".$this->checkName($name)."\n");
        }*/

    }


    private function checkName($name) {
        $url="https://addressok.blue-l.de/ajax/jsonCheckName.php?name=".$name;
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($handle);


        $ret=json_decode($response);

        curl_close($handle);

        return $ret->countAll;

    }

    /**
     * @param string $name
     */
    private function addName($nameArray,$name) {
        $name = str_replace("-"," ",$name);
        $name = str_replace("."," ",$name);
        $name = str_replace("("," ",$name);
        $name = str_replace(")"," ",$name);
        $names = explode(" ",$name);
        foreach ($names as $n) {
            $n = trim($n);
            if (strlen($n)>0) {
                if (!array_key_exists($n, $nameArray)) {
                    $nameArray[$n] = 1;
                } else {
                    $nameArray[$n] = $nameArray[$n] + 1;
                }
            }
        }
        return $nameArray;
    }
}
