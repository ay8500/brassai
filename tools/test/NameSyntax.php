<?php

namespace maierlabs\lpfw;

include_once __DIR__ . "/../../config.class.php";



class NameSyntaxCheck extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MySqlDb
     */
    private $db ;

    /**
     * @var array
     */
    private $firstName = array();
    private $lastName = array();

    public function setup()
    {

        $include = array('mysql.class.php');
        $argv=$_SERVER["argv"];
        $p=pathinfo($argv[sizeof($argv)-1]);
        foreach ($include as $item) {
            if (file_exists($p["dirname"].'/'.$item)){
                include_once $p["dirname"].'/'.$item;
            } else if (file_exists($p["dirname"].'/brassai/tools/'.$item)){
                include_once $p["dirname"].'/brassai/tools/'.$item;
            } else if (file_exists($p["dirname"].'/tools/'.$item)){
                include_once $p["dirname"].'/tools/'.$item;
            } else if (file_exists($p["dirname"].'/../'.$item)){
                include_once $p["dirname"].'/../'.$item;
            } else {
                throw (new Exception("Inludefile not found"));
            }
        }

        $db = \Config::getDatabasePropertys();
        $this->db = new MySqlDb($db->host,$db->database,$db->user,$db->password);

    }

    public function testNames() {
        $this->assertTrue($this->db->query("select firstname,lastname,id from person"));
        while ($row = $this->db->fetchRow()) {
            $this->firstName = $this->addName($this->firstName,$row["firstname"]);
            $this->lastName = $this->addName($this->lastName,$row["lastname"]);
        }
        arsort($this->firstName,SORT_NUMERIC);
        arsort($this->lastName,SORT_NUMERIC);
        echo(sizeof($this->firstName));
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
            if (strlen($n)>2) {
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
