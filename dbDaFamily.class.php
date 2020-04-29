<?php
/**
 * Class dbDaFamily
 * business logic, save,update and delete data in the family table
 */


class dbDaFamily
{
    /**
     * @var dbDAO
     */
    private $dbDAO;

    /**
     * @var \maierlabs\lpfw\MySqlDbAUH
     */
    private $dataBase;

    /**
     * dbDaOpinion constructor.
     * @param dbDAO $dbDAO
     */
    public function __construct(dbDAO $dbDAO)
    {
        $this->dbDAO = $dbDAO;
        $this->dataBase = $this->dbDAO->dataBase;
    }

    /**
     * Get the count of relatives by person id
     * @param $id
     * @return int
     */
    public function getPersonRelativesCountById($id) {
        $ret = $this->getPersonRelativesRecursiveCount($id,array(array("id2"=>$id)),"",1);
        // Do not count the person ist self
        return sizeof( $ret)-1;
    }

    /**
     * @param int $id
     * @param array $idArray
     * @param string $code
     * @param int $direction
     * @param int $deap
     * @return array
     */
    private function getPersonRelativesRecursiveCount($id,$idArray,$code,$direction,$deap=0) {
        $sql = "select id,id1,id2, code, gender from family where id1=".$id;
        $this->dataBase->query($sql);
        $res=$this->dataBase->getRowList();
        foreach ($res as $e) {
            $foundId= array_search($e["id2"],array_column($idArray,"id2"));
            if ($foundId===false ) {
                $e["deap"]=$deap;
                $e["direction"]=$direction;
                $e["coderec"]=$code.$e["code"];
                array_push($idArray,$e);
                if ($deap<6) {
                    $idArray  = $this->getPersonRelativesRecursiveCount($e["id2"],$idArray,$e["coderec"], $direction*(-1), $deap + 1);
                }
            }
        }
        $idArray = $this->unique_multidim_array($idArray,"id2");
        $sql = "select id,id2 as id1,id1 as id2, code, gender from family where id2=".$id;
        $this->dataBase->query($sql);
        $res=$this->dataBase->getRowList();
        foreach ($res as $e) {
            $foundId= array_search($e["id2"],array_column($idArray,"id2"));
            if ($foundId===false ) {
                $e["deap"]=$deap;
                $e["direction"]=$direction*(-1);
                $e["coderec"]=$code.$e["code"];
                array_push($idArray,$e);
                if ($deap<6) {
                    $idArray  = $this->getPersonRelativesRecursiveCount($e["id2"],$idArray,$e["coderec"],$direction*(-1), $deap + 1);
                }
            }
        }
        return $this->unique_multidim_array($idArray,"id2");
    }


    private function getPersonRelativesRecursive($id,$code="",$direction,$deap,$idList) {
        $return = array();
        $sql = "select id,id1,id2, code, gender from family where id1=".$id;
        $this->dataBase->query($sql);
        $res=$this->dataBase->getRowList();
        foreach ($res as $e) {
            if (!in_array($e["id2"],$idList)) {
                $e["deap"]=$deap;
                $e["direction"]=1;
                $e["coderec"]=$this->cleanUpRelativeCode($code.$e["code"]);
                array_push($idList,$e["id2"]);
                if ($deap<7) {
                    $e["relatives"]=$this->getPersonRelativesRecursive($e["id2"],$e["coderec"], $direction*(-1), $deap + 1,$idList);
                }
                array_push($return,$e);
            }
        }

        $sql = "select id,id2 as id1,id1 as id2, code, gender from family where id2=".$id;
        $this->dataBase->query($sql);
        $res=$this->dataBase->getRowList();
        foreach ($res as $e) {
            if (!in_array($e["id2"],$idList)) {
                $e["deap"] = $deap;
                $e["direction"] = -1;
                $e["coderec"] = $this->cleanUpRelativeCode($code.$this->reverseRelativeCode($e["code"]));
                array_push($idList, $e["id2"]);
                if ($deap <7) {
                    $e["relatives"] = $this->getPersonRelativesRecursive($e["id2"], $e["coderec"], $direction * (-1), $deap + 1, $idList);
                }
                array_push($return, $e);
            }
        }
        return $return;
    }


    /**
     * Delete duplicate entrys in a multidimensional array by a key
     * @param $array
     * @param $key
     * @return array
     */
    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    /**
     * Get the family
     * @param $id
     * @return array
     */
    public function getPersonRelativesById($id) {
        $recursiveList =$this->getPersonRelativesRecursive($id,"",1,0,array($id));
        $ret =$this->reorganiseRecursiveRelativeList($recursiveList);
        return $ret;
    }

    /**
     * Modify the recursive relative list to one dimensional array
     * @param $recusiveList
     * @return array
     */
    private function reorganiseRecursiveRelativeList($recusiveList) {
        $ret = array();
        foreach ($recusiveList as $r) {
            if ($r["direction"]==1) {
            } else {
                $r["gender"]=null;
            }
            array_push($ret, $r);
            if (isset($r["relatives"]))
                $ret = array_merge($ret, $this->reorganiseRecursiveRelativeList($r["relatives"]));
        }
        return $ret;
    }


    /*
     * Parent <=> Child
     * p <=> c, l<=>l, s<=>s
     */
    private function reverseRelativeCode($code) {
        $ret="";
        for($i=0;$i<strlen($code);$i++) {
            if ($code[$i]=="p") {
                $r="c";
            } elseif ($code[$i]=="c") {
                $r="p";
            } else {
                $r=$code[$i];
            }
            $ret = $r.$ret;
        }
        return $ret;
    }

    /**
     * Save relative to a person
     * @param int $id person ID
     * @param int $relativeId
     * @param string $code
     * @param string $relativeGender "f" or "m"
     * @return bool
     */
    public function saveRelatives($id, $relativeId, $code, $relativeGender) {
        $data = array();
        $data = $this->dataBase->insertFieldInArray($data,"id1",$id);
        $data = $this->dataBase->insertFieldInArray($data,"id2",$relativeId);
        $data = $this->dataBase->insertFieldInArray($data,"gender",$relativeGender);
        $data = $this->dataBase->insertFieldInArray($data,"code",$code);
        $data = $this->dataBase->insertUserDateIP($data);
        return $this->dataBase->insert("family",$data)!==false;
    }

    /**
     * Delete relative by relative id
     * @param int $id
     * @return bool
     */
    public function deleteRelatives($id) {
        return $this->dataBase->delete("family","id",$id);
    }

    /**
     * cleanup and reduce the relative code
     * @param $code
     * @return mixed
     */
    public function cleanUpRelativeCode($code) {
        if($code=="pc") return "s";
        //if($code=="ps") return "s";Not work!
        $ret=$code;
        $ret = str_replace("cs","c",$ret); //childres silbing = children
        $ret = str_replace("cp","",$ret); //childres parents
        $ret = str_replace("sp","p",$ret); //silbling parents are the parents
        $ret = str_replace("sss","s",$ret); //silbling silbling = silbling
        $ret = str_replace("ss","s",$ret); //silbling silbling = silbling
        $ret = preg_replace("/pl\z/","p",$ret,1); // my parent life partner is also my parent
        $ret = preg_replace("/lc\z/","c",$ret,1); // my lifepartners child is my also my child

        return $ret;
    }

}