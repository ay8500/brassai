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

    public function getLightedCandleList($id=null, $limit=12) {
        $sql = 'select distinct personID from candle join person on person.id=candle.personID ';
        $sql .=" where deceasedYear is not null ";
        $sql .=" and  ((candle.userId is null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."')";
        $sql .=" or    (candle.userId is not null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-6 month"))."') )";
        if($id!=null) {
            $sql .=' and userID='.$id;
        }
        $sql .= ' ORDER BY candle.lightedDate DESC LIMIT '.$limit;
        $candles= $this->dbDAO->dataBase->queryArray($sql);
        $ret = array();
        foreach ($candles as $candle) {
            $ret[] = $this->dbDAO->getPersonByID($candle["personID"]);
        }
        return $ret;
    }

    /**
     * Count of candles by person id always +1 from the system :)
     * if Id = null all candles + 1 candle for each deceased person from system
     * @param integer $id
     * @return integer
     */
    public function getCandlesByPersonId($id=null) {
        if (null!=$id) {
            $sqlAnonymous='select count(*) from candle where personId='.$id." and userId is null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
            $sqlUsername ='select count(*) from candle where personId='.$id." and userId is not null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-6 month"))."'";
            $candles=$this->dbDAO->dataBase->queryInt($sqlAnonymous) + $this->dbDAO->dataBase->queryInt($sqlUsername);
            $sql='select count(*) from person where deceasedYear is not null and id='.$id;
            return $candles + $this->dbDAO->dataBase->queryInt($sql);
        } else {
            $sql='select count(*) from person where deceasedYear is not null';
            $ret = $this->dbDAO->dataBase->queryInt($sql);

            $sqlAnonymous="select count(*) from candle where userId is null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
            $sqlUsername ="select count(*) from candle where userId is not null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-6 month"))."'";
            return $ret + $this->dbDAO->dataBase->queryInt($sqlAnonymous) + $this->dbDAO->dataBase->queryInt($sqlUsername);
        }
    }

    public function getCandleDetailByPersonId($id) {
        $sql ='select * from candle where personID='.$id." and ";
        $sql .="((userId is     null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."') or ";
        $sql .=" (userId is not null and lightedDate >'".date('Y-m-d H:i:s',strtotime("-6 month"))."')  ) order by id desc";
        $this->dbDAO->dataBase->query($sql);
        if ($this->dbDAO->dataBase->count()>0)
            return $this->dbDAO->dataBase->getRowList();

        return array();
    }

    public function getCandleDetailByUserId($id) {
        $sql='select * from candle where userID='.$id." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-6 month"))."'";
        $this->dbDAO->dataBase->query($sql);
        if ($this->dbDAO->dataBase->count()>0)
            return $this->dbDAO->dataBase->getRowList();

        return array();
    }

    /**
     * returns how many days will light the candle
     * @param $id
     * @param null $userId
     * @return bool|DateTime|false
     */
    public function checkLightning($id, $userId=null) {
        if ($userId!=null) {
            $sql='select lightedDate from candle where personId='.$id." and userID=".$userId." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-6 month"))."'";
            $ret = $this->dbDAO->dataBase->queryArray($sql);
            if (sizeof($ret)==0)
                return false;
            $d = DateTime::createFromFormat ( "Y-m-d H:i:s", $ret[0]["lightedDate"] );
            $d->modify("+6 month");
            return date_diff($d,new DateTime("now"))->format("%a");

        }
        $sql='select lightedDate from candle where personId='.$id." and ip='".$_SERVER["REMOTE_ADDR"]."' and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
        $ret = $this->dbDAO->dataBase->queryArray($sql);
        if (sizeof($ret)==0)
            return false;
        $d = DateTime::createFromFormat ( "Y-m-d H:i:s", $ret[0]["lightedDate"] );
        $d->modify("+2 month");
        return date_diff($d,new DateTime("now"))->format("%a");
    }

    /**
     * light the candle
     * @param $id
     * @param null $userId
     * @param int $asAnonymous
     * @return bool
     */
    public function setCandleLighter($id, $userId=null,$asAnonymous=0) {
        $data=array();
        if (userIsLoggedOn()) {
            $data=$this->dbDAO->dataBase->insertFieldInArray($data, "userID", $userId);
        }
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "ip", $_SERVER["REMOTE_ADDR"]);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "lightedDate", date("Y-m-d H:i:s"));
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "personID", $id);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "showAsAnonymous", $asAnonymous);
        return $this->dbDAO->dataBase->insert("candle", $data)!==false;
    }

}