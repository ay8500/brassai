<?php
/**
 * Class dbDaFamily
 * business logic, save,update and delete data in the family table
 */


class dbDaFamily
{
    /* Parents, Silbling, Life parnter, Children */
public /* Parents, Silbling, Life parnter, Children */
    $family=array(
    array ("code"=>"x", "text"=>"Távoli rokon","textf"=>"","textm"=>""),
    array ("code"=>"ccc", "text"=>"Dédunokám","textf"=>"","textm"=>""),
    array ("code"=>"ccl", "text"=>"Unokám élettársa", "textf"=>"Unokám felsége","textm"=>"Unokám férje"),
    array ("code"=>"cc", "text"=>"Unokám","textf"=>"","textm"=>""),
    array ("code"=>"cl", "text"=>"Gyerekem élettársa","textf"=>"Menyem","textm"=>"Vöm"),
    array ("code"=>"c", "text"=>"Gyerekem","textf"=>"Lányom","textm"=>"Fiam"),
    array ("code"=>"l", "text"=>"Élettársam","textf"=>"Feleségem","textm"=>"Férjem"),
    array ("code"=>"s", "text"=>"Testvérem","textf"=>"Hugom / Növérem","textm"=>"Fivérem"),
    array ("code"=>"sc", "text"=>"Testvérem gyereke","textf"=>"Unokahugom","textm"=>"Unokaöcsém"),
    array ("code"=>"lsc", "text"=>"Élettársam testvérének gyereke","textf"=>"Unokahugom","textm"=>"Unokaöcsém"),
    array ("code"=>"scc", "text"=>"Testvérem unokája","textf"=>"Dédunokahugom","textm"=>"Dédunokaöcsém"),
    array ("code"=>"p", "text"=>"Szüleim","textf"=>"Anyukám","textm"=>"Apukám"),
    array ("code"=>"ps", "text"=>"Szüleim testvére","textf"=>"Nagynéném","textm"=>"Nagybátyám"),
    array ("code"=>"psl", "text"=>"Szüleim testvérének élettársa","textf"=>"Nagynéném","textm"=>"Nagybátyám"),
    array ("code"=>"psc", "text"=>"Szüleim testvérének gyereke=Unokatestvérem","textf"=>"","textm"=>""),
    array ("code"=>"pscl", "text"=>"Szüleim testvérének gyerekének élettársa","textf"=>"Unokatestvérem felesége","textm"=>"Unokatestvérem férje"),
    array ("code"=>"pscc", "text"=>"Szüleim testvérének unokája","textf"=>"Másodunokahugom","textm"=>"Másodunkaöcsém"),
    array ("code"=>"psccc", "text"=>"Szüleim testvérének dédunokája","textf"=>"","textm"=>""),
    array ("code"=>"pp", "text"=>"Nagyszüleim","textf"=>"Nagyanyám","textm"=>"Nagyapám"),
    array ("code"=>"pps", "text"=>"Nagyszüleim testvére","textf"=>"Nagy-nagyanyám","textm"=>"Nagy-nagyapám"),
    array ("code"=>"ppsc", "text"=>"Nagyszüleim testvérének gyereke","textf"=>"Másodnagynéném","textm"=>"Másodnagybátyám"),
    array ("code"=>"ppscc", "text"=>"Nagyszüleim testvérének unokája=Másodunokatestvér","textf"=>"","textm"=>""),
    array ("code"=>"ppsccc", "text"=>"Nagyszüleim testvérének dédunokája","textf"=>"Másodunokahugom","textm"=>"Másodunokaöcsém"),
    array ("code"=>"ppp", "text"=>"Dédszüleim","textf"=>"Dédanyám","textm"=>"Dédapám"),
    array ("code"=>"pppsc", "text"=>"Dédszüleim testvérének gyereke","textf"=>"Harmaddédnéném","textm"=>"Harmaddédédbátyám"),
    array ("code"=>"pppscc", "text"=>"Dédszüleim testvérének unokája","textf"=>"Harmadnagynéném","textm"=>"Harmadnagybátyám"),
    array ("code"=>"pppsccc", "text"=>"Dédszüleim testvérének dédunokája=Harmadunokatestvér","textf"=>"","textm"=>""),

    array ("code"=>"lp", "text"=>"Élettársam szülei","textf"=>"Anyósom","textm"=>"Apósóm"),
    array ("code"=>"lps", "text"=>"Élettársam szüleinek testvére","textf"=>"Anyósom/aposom higa/növére","textm"=>"Anyosom/apósóm fivére"),
    array ("code"=>"lpls", "text"=>"Távoli rokon","textf"=>"Anyósom/Apósóm Sogornője","textm"=>"Anyósom/Apósóm Sogora"),
    array ("code"=>"lpsc", "text"=>"Élettársam szüleinek testvérének gyereke","textf"=>"Anyósom/aposom unokahuga","textm"=>"Anyosom/apósóm unokaöccse"),
    array ("code"=>"lpscl", "text"=>"Élettársam szüleinek testvérének gyerekének élettársa","textf"=>"Anyósom/aposom unokahugának/unokaöccsének felesége","textm"=>"Anyosom/apósóm unokahugának/unokaöccsének férje"),
    array ("code"=>"lpscl", "text"=>"Élettársam szüleinek testvérének gyerekének élettársa","textf"=>"Anyósom/aposom unokahugának/unokaöccsének felesége","textm"=>"Anyosom/apósóm unokahugának/unokaöccsének férje"),
    array ("code"=>"lpscls", "text"=>"Élettársam szüleinek testvérének gyerekének élettársának gyereke","textf"=>"Anyósom/aposom unokahugának/unokaöccsének élettársának lánya","textm"=>"Anyosom/apósóm unokahugának/unokaöccsének élettársának fia"),

    array ("code"=>"ls", "text"=>"Élettársam testvére","textf"=>"Sógornőm","textm"=>"Sógorom"),
    array ("code"=>"sl", "text"=>"Testvérem élettársa","textf"=>"Sógornőm","textm"=>"Sógorom"),
    array ("code"=>"sls", "text"=>"Testvérem élettársának testvére","textf"=>"Sógornőm/sógorom testvére","textm"=>"Sógornőm/sógorom testvére"),
    array ("code"=>"lsl", "text"=>"Élettársam testvérének élettársa","textf"=>"Sógornőm","textm"=>"Sógorom"),
    array ("code"=>"slsl", "text"=>"Testvérem élettársának testvérének élettérsa","textf"=>"Sógornőm/sógorom sógora","textm"=>"Sógornőm/sógorom sógornője"),
    array ("code"=>"lsls", "text"=>"Élettársam testvérének élettérsának terstvére","textf"=>"Sógornőm/sógorom sógora","textm"=>"Sógornőm/sógorom sógornője"),
    array ("code"=>"slsc", "text"=>"Testvérem élettársának testvérének gyereke","textf"=>"Sógornőm/sógorom unokahuga","textm"=>"Sógornőm/sógorom unokaöccse"),
    array ("code"=>"lssc", "text"=>"Élettársam testvérének testvérének gyereke","textf"=>"Sógornőm/sógorom unokahuga","textm"=>"Sógornőm/sógorom unokaöccse"),

    array ("code"=>"clp", "text"=>"Gyerekem élettársának szülei ","textf"=>"Anyatársam","textm"=>"Apatársam"),
    array ("code"=>"clpls", "text"=>"Távoli rokon","textf"=>"Apatársam/Anyatársam Sógornője","textm"=>"Apatársam/Anyatársam Sógora"),

);


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


    private function getPersonRelativesRecursive($id,$code,$direction,$deap,$idList) {
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

    public function getKinship($code, $gender, $realtiveGender,$deap) {
        $relative= array_search($code,array_column($this->family,"code"));
        if ($realtiveGender=="f" || $gender=="f")
            $textGender =($this->family[$relative]["textf"]!='') ? $this->family[$relative]["textf"] : "";
        elseif ($realtiveGender=="m" || $gender=="m")
            $textGender =($this->family[$relative]["textm"]!='') ? $this->family[$relative]["textm"] : "";
        else {
            $textGender = ($this->family[$relative]["textf"] != '') ? ($this->family[$relative]["textf"] . " / " . $this->family[$relative]["textm"] ) : "";
        }
        return $this->family[$relative]["text"].($textGender!=''?"= ":"").$textGender;
    }

}