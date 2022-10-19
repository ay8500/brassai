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

    /**
     * Returns the purchases to one person
     * @param $id
     * @return array of objects
     */
    public function getDecorationPurcasesByPersonId($id) {
        $purchases= array();
        //TODO get Data from Data
        if ($id==851) {
            $purchase = new stdClass();
            $purchase->ip = "192.168.1.1";
            $purchase->date = new DateTime("2022-09-23 13:44:00");
            $purchase->text = "Angyalaid vezessenek tovább az utadon, legyen lelkednek örök béke és nyugalom.";
            $purchase->person = $this->dbDAO->getPersonByID(658);
            $purchases[] = $purchase;
        } else if ($id==9942) { // Kalmár József
            $purchase = new stdClass();
            $purchase->ip="192.168.1.1";
            $purchase->date = new DateTime("2022-03-23");
            $purchase->text = "Emléked örökre élni fog szívünkben";
            $purchase->person = $this->dbDAO->getPersonByID(889);
            $purchases[] = $purchase;
            $purchase = new stdClass();
            $purchase->ip="192.168.1.1";
            $purchase->date = new DateTime("2021-04-02");
            $purchase->text = "Emlékedet megőrizzük";
            $purchase->person = $this->dbDAO->getPersonByID(658);
            $purchases[] = $purchase;
            $purchase = new stdClass();
            $purchase->ip="192.168.1.1";
            $purchase->date = new DateTime("2020-09-06");
            $purchase->text = "Ha rám gondoltok, mosolyogjatok, emlékem így áldás lesz rajtatok";
            //$purchase->person = $this->dbDAO->getPersonByID(658);
            $purchases[] = $purchase;
        } else if ($id==7899)  {
            $purchase = new stdClass();
            $purchase->ip="192.168.1.1";
            $purchase->date = new DateTime("2020-01-23");
            $purchase->text = "Elcsitult a szív, mely értünk dobogott, számunkra Te sosem leszel halott, örökké élni fogsz, akár a csillagok";
            $purchase->person = $this->dbDAO->getPersonByID(889);
            $purchases[] = $purchase;
            $purchase = new stdClass();
            $purchase->ip="192.168.1.1";
            $purchase->date = new DateTime("2021-03-23");
            $purchase->text = "Amíg éltél szerettünk, amíg élünk nem feledünk";
            $purchase->person = $this->dbDAO->getPersonByID(889);
            $purchases[] = $purchase;
            $purchase = new stdClass();
            $purchase->ip="192.168.1.1";
            $purchase->date = new DateTime("2020-04-02");
            $purchase->text = "Angyalaid vezessenek tovább az utadon, legyen lelkednek örök béke és nyugalom.";
            $purchase->person = $this->dbDAO->getPersonByID(658);
            $purchases[] = $purchase;
        }
        return $purchases;
    }

    public function getDecorationsByPersonId($id=null) {
        $decesedYear = $this->dbDAO->dataBase->queryInt("select deceasedYear from person where id=".$id);
        $decoration = new stdClass();
        $decoration->extended = false;
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
        //TODO get Data from Database
        if ($id==9942) { //Kalmár József
            $decoration->extended = true;
            $decoration->rosesDown->count = 7;
            $decoration->flowerRightBottom->count = 1;
        } else if ($id==7899)  { //Nemes Mária
            $decoration->extended = true;
            $decoration->flowerRightTop->count=1;
            $decoration->flowerRightBottom->count=1;
            $decoration->rosesDown->count=7;
            $decoration->rosesUp->count=4;
            $decoration->flowerLeft->count=3;
        } else if ($id==662)  {  //Kovács László
            $decoration->rosesUp->count=2;
        } else if ($id==851)  {  //Pazmany Zsuzsa
            $decoration->flowerRightTop->count=1;
            $decoration->rosesUp->count=1;
        } else if ($decesedYear == intval(Date("Y"))) {
            $decoration->flowerRightTop->count=1;
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