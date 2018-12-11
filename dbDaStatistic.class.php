<?php
/**
 * Class dbDaStatistic
 * create person statistics
 */

class dbDaStatistic
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

    /**
     * @param int $id
     * @return array
     */
    public function getPersonActivities($id) {
        $ret = array();
        $db = $this->dbDAO->dataBase;
        $sql="select count(1) as count from history where changeUserID=".$id." and `table`='person' ";
        $ret["personChange"]=$db->queryInt($sql);

        $sql="select count(1) as count from person where changeUserID=".$id;
        $ret["newPerson"]=$db->queryInt($sql);

        $sql="select count(1) as count from picture where changeUserID=".$id;
        $ret["newPicture"]=$db->queryInt($sql);

        $sql="select count(1) as count from candle where userID=".$id;
        $ret["lightedCandles"]=$db->queryInt($sql);

        $sql="select count(1) as count from songvote where personID=".$id;
        $ret["songVotes"]=$db->queryInt($sql);

        $sql="select count(1) as count from song where changeUserID=".$id;
        $ret["songs"]=$db->queryInt($sql);

        $sql="select count(1) as count from interpret where changeUserID=".$id;
        $ret["interprets"]=$db->queryInt($sql);

        $sql="select count(1) as count from opinion where changeUserID=".$id;
        $ret["opinions"]=$db->queryInt($sql);

        $person = $this->dbDAO->getPersonByID($id);
        $r=0;
        if (isset($person["userLastLogin"])) {
            $diff=date_diff(new DateTime($person["userLastLogin"]),new DateTime(),true);
            if ($diff->days<1000)
                $r=1000- ($diff->days);
        }
        $ret["lastLoginPoints"]=$r;

        return  $ret;

    }

    /**
     * get activity counter group by date
     * @param object $dateTime
     * @return array
     */
    public function getActivityCalendar($dateTime) {
        $ret=array();

        $sql ="select changeDate, count(*) as count from history where changeDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(changeDate) order by changeDate";
        $ret = $this->selectDataForActivityCalendar($ret,$sql);
        $sql ="select changeDate, count(*) as count from person where changeDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(changeDate) order by changeDate";
        $ret = $this->selectDataForActivityCalendar($ret,$sql);
        $sql ="select changeDate, count(*) as count from image where changeDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(changeDate) order by changeDate";
        $ret = $this->selectDataForActivityCalendar($ret,$sql);
        $sql ="select changeDate, count(*) as count from opinion where changeDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(changeDate) order by changeDate";
        $ret = $this->selectDataForActivityCalendar($ret,$sql);
        $sql ="select lightedDate as changeDate, count(*) as count from candle where lightedDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(lightedDate) order by lightedDate";
        $ret = $this->selectDataForActivityCalendar($ret,$sql);
        return $ret;
    }

    /**
     * select activity data from date till now an merge them with the given array
     * @param $activity already selected activity counter
     * @param $sql
     */
    private function selectDataForActivityCalendar($activity,$sql) {
        $ret = $activity;
        $db=$this->dbDAO->dataBase;
        $db->query($sql);
        if ($db->count()>0) {
            $r= $db->getRowList();
            foreach ($r as $v) {
                if (isset($ret[$v["changeDate"]]))
                    $ret[$v["changeDate"]]+=$v["count"];
                else
                    $ret[$v["changeDate"]]=intval($v["count"]);
            }
        }
        return $ret;
    }

    /**
     * get the list of the best persons regarding to the activities
     * @param int $count
     * @return array
     */
    public function getPersonChangeBest($count=12) {
        $sql="select count(1) as count, changeUserID as uid from history where changeUserID>=0 and `table`='person' group by changeUserID order by count desc limit ".$count;
        $ret = $this->mergeBestArrays(array(),$sql,1);

        $sql="select count(1) as count, changeUserID as uid from person where changeUserID>=0 group by  changeUserID order by count desc limit  ".$count;
        $ret = $this->mergeBestArrays($ret,$sql,3);

        $sql="select count(1) as count, changeUserID as uid from picture where changeUserID !=0 group by  changeUserID order by count desc limit  ".$count;
        $ret = $this->mergeBestArrays($ret,$sql,5);

        $sql="select userLastLogin, id as uid from person where userLastLogin is not null and changeForID is null order by userLastLogin desc limit  ".$count;
        $this->dbDAO->dataBase->query($sql);
        $r4=array();
        if ($this->dbDAO->dataBase->count()>0) {
            $r = $this->dbDAO->dataBase->getRowList();
            foreach ($r as $s) {
                $diff=date_diff(new DateTime($s["userLastLogin"]),new DateTime(),true);
                if ($diff->days<1000)
                    $r4[$s["uid"]]=1000- ($diff->days);
            }
        }
        $ret = $this->mergeBestArray($ret,$r4,1);

        $sql="select count(1) as count, userID as uid from candle where userID is not null group by  userID order by count desc limit  ".$count;
        $ret = $this->mergeBestArrays($ret,$sql,2);

        $sql="select count(1) as count, personID as uid from songvote where personID !=0 group by  personID order by count desc limit  ".$count;
        $ret = $this->mergeBestArrays($ret,$sql,7);

        $sql="select count(1) as count, changeUserID as uid from song where changeUserID !=0 group by  changeUserID order by count desc limit  ".$count;
        $ret = $this->mergeBestArrays($ret,$sql,7);

        $sql="select count(1) as count, changeUserID as uid from interpret where changeUserID !=0 group by  changeUserID order by count desc limit  ".$count;
        $ret = $this->mergeBestArrays($ret,$sql,7);

        $sql="select count(1) as count, changeUserID as uid from opinion where changeUserID !=0 group by  changeUserID order by count desc limit  ".$count;
        $ret = $this->mergeBestArrays($ret,$sql,1);

        $rets=array();
        for($i=0;$i<$count;$i++) {
            $value = 0;
            foreach ($ret as $uid => $counts) {
                if ($counts > $value) {
                    $value = $counts;
                    $vuid = $uid;
                }
            }
            if (isset($vuid)) {
                $rets[$vuid] = $value;
                unset($ret[$vuid]);
            }
        }
        return $rets;
    }

    /**
     * @param int $id
     * @return int
     */
    private function getOldestHistoryUserId($id) {
        $sql="select changeUserID from history where entyID =".$id." and changeUserID>0 order by changeDate limit 1";
        return $this->dbDAO->dataBase->queryInt($sql);
    }

    /**
     * Add matematicaly the values multiplied with $factor from array2 into corresponding keys from array1
     * @param $a1 array 1
     * @param $a2 array 2
     * @param int $factor
     * @return array
     */
    private function mergeBestArray($a1,$a2,$factor=1) {
        foreach ($a2 as $idx=>$a) {
            if(isset($a1[$idx]))
                $a1[$idx]+=$a*$factor;
            else
                $a1[$idx]=$a*$factor;
        }
        return $a1;
    }

    /**
     * Add matematicaly the values multiplied with $factor from database request into corresponding keys from array
     * @param $inputArray
     * @param $sql
     * @param int $factor
     * @return array
     */
    private function mergeBestArrays($inputArray,$sql,$factor=1) {
        $this->dbDAO->dataBase->query($sql);
        $rr=array();
        if ($this->dbDAO->dataBase->count()>0) {
            $r = $this->dbDAO->dataBase->getRowList();
            foreach ($r as $s) {
                $rr[$s["uid"]]=$s["count"];
            }
        }
        return $this->mergeBestArray($inputArray,$rr,$factor);
    }


}