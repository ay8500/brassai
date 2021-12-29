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

    public function getLightedCandleList($id=null, $limit=12, $schoolId=null) {
        $sql = 'select distinct personID from candle join person on person.id=candle.personID ';
        if (null!=$schoolId)
            $sql .=" join class on class.id = person.classID  ";
        $sql .=" where deceasedYear is not null ";
        if (null!=$schoolId)
            $sql .=" and  class.schoolID =".$schoolId;
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

    public function getDecorationsByPersonId($id=null) {
        $decoration = new stdClass();
        $decoration->flowerRightTop = new stdClass();    //max=1
        $decoration->flowerRightBottom = new stdClass(); //max=1
        $decoration->rosesDown =  new stdClass();         //max=7
        $decoration->rosesUp =  new stdClass();           //max=4
        $decoration->flowerLeft =  new stdClass();        //max=3
        $decoration->flowerRightTop->count=0;
        $decoration->flowerRightBottom->count=0;
        $decoration->rosesDown->count=0;
        $decoration->rosesUp->count=0;
        $decoration->flowerLeft->count=0;
        if ($id==9942) {
            $decoration->rosesDown->count = 7;
            $decoration->rosesDown->person = $this->dbDAO->getPersonByID(889);
            $decoration->rosesDown->text = "Emléked örökre élni fog szívünkben";
            $decoration->flowerRightBottom->count = 1;
            $decoration->flowerRightBottom->person = $this->dbDAO->getPersonByID(658);
            $decoration->flowerRightBottom->text = "Emlékedet megőrizzük";
        } else if ($id==7899)  {
            $decoration->flowerRightTop->count=1;
            $decoration->flowerRightTop->text = "Elcsitult a szív, mely értünk dobogott, számunkra Te sosem leszel halott, örökké élni fogsz, akár a csillagok";
            $decoration->flowerRightBottom->count=1;
            $decoration->flowerRightBottom->text = "Amíg éltél szerettünk, amíg élünk nem feledünk";
            $decoration->rosesDown->count=7;
            $decoration->rosesDown->text = "Angyalaid vezessenek tovább az utadon, legyen lelkednek örök béke és nyugalom.";
            $decoration->rosesUp->count=4;
            $decoration->rosesUp->text = "Az Ő szíve pihen, A miénk vérzik, A fájdalmat csak Az élők érzik";
            $decoration->flowerLeft->count=3;
            $decoration->flowerLeft->text = "Ha rám gondoltok, mosolyogjatok, emlékem így áldás lesz rajtatok";
        }
        return $decoration;
    }

    public function getAllCandlesCount() {
        return $this->dbDAO->dataBase->queryInt("select count(*) from candle");
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
        if (isUserLoggedOn()) {
            $data=$this->dbDAO->dataBase->insertFieldInArray($data, "userID", $userId);
        }
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "ip", $_SERVER["REMOTE_ADDR"]);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "lightedDate", date("Y-m-d H:i:s"));
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "personID", $id);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "showAsAnonymous", $asAnonymous);
        return $this->dbDAO->dataBase->insert("candle", $data)!==false;
    }

}