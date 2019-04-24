<?php

include_once __DIR__ . "/../config.class.php";
include_once __DIR__ . "/../lpfw/mysqldbauh.class.php";
include_once __DIR__ . "/../lpfw/logger.class.php";
include_once __DIR__ . "/../lpfw/userManager.php";
include_once __DIR__ . "/../dbDAO.class.php";

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
        if ( ($gender = $this->checkFirstName("Levente")) !==false)
            $this->assertSame("m",$gender);
    }

    public function testFemale() {
        if (($gender = $this->checkFirstName("Emese")) !==false)
            $this->assertSame("f",$gender);
    }

    public function testFamilyname() {
        if (($gender = $this->checkFirstName("Kovács")) !==false)
            $this->assertSame("n",$gender);
    }

    public function testNames() {
        $countSetGender=0;
        $this->assertTrue($this->db->query("select id,firstname,lastname from person where gender is null"));
        $persons=$this->db->getRowList();
        foreach ( $persons as $row) {
            $this->firstName = $this->addName($this->firstName,$row["firstname"]);

            $gender=$this->getGenderFirstName($row["firstname"]);
            if ($gender!==false) {
                $data=array(array("field"=>"gender","type"=>"s","value"=>$gender));
                $countSetGender++;
                $this->db->update("person",$data,"id",$row["id"]);
            }
        }
        arsort($this->firstName,SORT_NUMERIC);
        echo("\n".'First name without gender:'.sizeof($this->firstName));
        echo(' Set gender:'.$countSetGender."\n");
        foreach ($this->firstName as $name=>$count) {
            echo($count.":".$name."\n");
        }
    }

    private function getNameServerData($url) {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($handle);

        $ret=json_decode($response);

        curl_close($handle);
        return $ret;
    }

    /**
     * check the gender of firstname
     * @param $name
     * @return bool
     */
    private function checkFirstName($name) {
        $url="https://addressok.blue-l.de/ajax/jsonCheckName.php?name=".$name;
        $ret =$this->getNameServerData($url);

        if (isset($ret->countAll) && $ret->countAll>0) {
            $genderM=0;
            $genderF=0;
            $genderN=0;
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

        if ($genderM==0 && $genderF==0 && $genderN==0)
            return false;

        if ($genderM==0 && $genderF==0 && $genderN>0)
            return 'n';

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
