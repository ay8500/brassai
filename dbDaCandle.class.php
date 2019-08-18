<?php
/**
 * Class dbDaCandle
 * save,update and delete data in the candle table
 */

class dbDaCandle
{
    /**
     * @var dbDAO
     */
    private $dbDAO;

    /**
     * dbDaOpinion constructor.
     * @param dbDAO $dbDAO
     */
    public function __construct(dbDAO $dbDAO)
    {
        $this->dbDAO = $dbDAO;
    }

    public function getLightedCandleList($id=null) {
        $sql = 'select personID from candle join person on person.id=candle.personID ';
        $sql .=" where deceasedYear is not null ";
        $sql .=" and  lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
        if($id!=null) {
            $sql .=' and userID='.$id;
        }
        $this->dbDAO->dataBase->query($sql);
        if ($this->dbDAO->dataBase->count()>0) {
            $candles= $this->dbDAO->dataBase->getRowList();

            $sql='id in (';
            foreach ($candles as $idx=>$candle) {
                if ($idx!=0) $sql .=",";
                $sql .=$candle["personID"];
            }
            $sql.=')';
            return $this->dbDAO->getSortedPersonList($sql);
        }
        return array();
    }

    /**
     * Count of candles by person id always +1 from the system :)
     * if Id = null all candles + 1 candle for each deceased person from system
     * @param integer $id
     * @return integer
     */
    public function getCandlesByPersonId($id=null) {
        if (null!=$id) {
            $sql='select count(*) from candle where personId='.$id." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
            return $this->dbDAO->dataBase->queryInt($sql)+1;
        } else {
            $sql='select count(*) from person where deceasedYear is not null';
            $ret = $this->dbDAO->dataBase->queryInt($sql);

            $sql="select count(*) from candle where lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
            return $ret + $this->dbDAO->dataBase->queryInt($sql);
        }
    }

    public function getCandleDetailByPersonId($id) {
        $sql='select * from candle where personID='.$id." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."' order by id desc";
        $this->dbDAO->dataBase->query($sql);
        if ($this->dbDAO->dataBase->count()>0)
            return $this->dbDAO->dataBase->getRowList();

        return array();
    }

    public function getCandleDetailByUserId($id) {
        $sql='select * from candle where userID='.$id." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
        $this->dbDAO->dataBase->query($sql);
        if ($this->dbDAO->dataBase->count()>0)
            return $this->dbDAO->dataBase->getRowList();

        return array();
    }

    public function checkLightning($id, $userId=null) {
        if ($userId!=null) {
            $sql='select count(*) from candle where personId='.$id." and userID=".$userId." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
            $ret = $this->dbDAO->dataBase->queryInt($sql);
            return ($ret==0);
        }
        $sql='select count(*) from candle where personId='.$id." and ip='".$_SERVER["REMOTE_ADDR"]."' and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
        $ret = $this->dbDAO->dataBase->queryInt($sql);
        return ($ret==0);
    }

    public function setCandleLighter($id, $userId=null) {
        $data=array();
        if (userIsLoggedOn()) {
            $data=$this->dbDAO->dataBase->insertFieldInArray($data, "userID", $userId);
        }
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "ip", $_SERVER["REMOTE_ADDR"]);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "lightedDate", date("Y-m-d H:i:s"));
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "personID", $id);
        $this->dbDAO->dataBase->insert("candle", $data);
    }

}