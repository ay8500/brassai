<?php

include_once __DIR__ . "/../../config.class.php";
include_once __DIR__ . "/../mysqldbauh.class.php";
include_once __DIR__ . "/../logger.class.php";
include_once __DIR__ . "/../userManager.php";
include_once __DIR__ . "/../../dbDAO.class.php";

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

class nameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \maierlabs\lpfw\MySqlDbAUH
     */
    private $db ;

    /**
     * @var dbDAO
     */
    private $dbDAO;

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
        $this->dbDAO = new dbDAO($this->db);
    }

    public function tearDown()
    {
        $this->db->disconnect();
    }


    public function testMale() {
        $this->assertTrue($this->checkFirstName("Péter")=="m");
    }

    public function testFemale() {
        $this->assertTrue($this->checkFirstName("Rozália")=="f");
    }

    public function testFamilyname() {
        $this->assertFalse($this->checkFirstName("Kovács"));
    }

    public function testNames() {
        $countSetGender=0;
        $this->assertTrue($this->db->query("select id,firstname,lastname from person where gender is null"));
        $persons=$this->db->getRowList();
        foreach ( $persons as $row) {
            $this->firstName = $this->addName($this->firstName,$row["firstname"]);
            $this->lastName = $this->addName($this->lastName,$row["lastname"]);
            /*
            $gender=$this->getGenderFirstName($row["firstname"]);
            if ($gender!==false) {
                $data=array(array("field"=>"gender","type"=>"s","value"=>$gender));
                $countSetGender++;
                $this->db->update("person",$data,"id",$row["id"]);
            }
            */
        }
        arsort($this->firstName,SORT_NUMERIC);
        echo("\n".'FirstName:'.sizeof($this->firstName).'- LastName'.sizeof($this->lastName));
        echo(' Set gender:'.$countSetGender."\n");
        foreach ($this->firstName as $name=>$count) {
            echo($count.":".$name."\n");
        }

    }


    /**
     * check the gender of firstname
     * @param $name
     * @return bool
     */
    private function checkFirstName($name) {
        $url="https://addressok.blue-l.de/ajax/jsonCheckName.php?name=".$name;
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($handle);


        $ret=json_decode($response);

        curl_close($handle);

        $genderM=0;
        $genderF=0;
        $genderN=0;

        if ($ret->countAll>0) {
            foreach ($ret as $r) {
                if (is_object($r)) {
                    if ($r->gender=='m') $genderM++;
                    if ($r->gender=='n') $genderN++;
                    if ($r->gender=='f') $genderF++;
                }
            }
        } else {
            return false;
        }

        if ($genderM==0 && $genderF==0)
            return false;

        return $genderM>$genderF?'m':'f';

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
                return $nameArray;
            }
        }
        return $nameArray;
    }

    /**
     * @param string $name
     */
    private function getGenderFirstName($name) {
        $name = str_replace("-"," ",$name);
        $name = str_replace("."," ",$name);
        $name = str_replace("("," ",$name);
        $name = str_replace(")"," ",$name);
        $names = explode(" ",$name);
        foreach ($names as $n) {
            $n = trim($n);
            if (strlen($n)>2) {
                return $this->checkFirstName($n);
            }
        }
        return false;
    }

}
