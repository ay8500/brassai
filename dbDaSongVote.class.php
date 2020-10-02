<?php

/**
 * Class dbDaSong
 * save,update and delete data in the song and interpret tables
 */

class dbDaSongVote
{
    /**
     * @var dbDAO
     */
    private $dbDAO;

    /**
     * @var \maierlabs\lpfw\MySql
     */
    private $dataBase;

    /**
     * dbDaSongData constructor.
     * @param dbDAO $dbDAO
     */
    public function __construct(dbDAO $dbDAO)
    {
        $this->dbDAO = $dbDAO;
        $this->dataBase = $this->dbDAO->dataBase;
    }


    public function saveInterpret($entry) {
        return $this->dbDAO->dataBase->saveEntry("interpret", $entry);
    }

    public function saveSong($entry) {
        return $this->dbDAO->dataBase->saveEntry("song", $entry);
    }

    public function updateSong($id,$value,$field) {
        $this->dbDAO->dataBase->createHistoryEntry("song",$id);
        return $this->dataBase->update("song", [["field"=>$field,"type"=>"s","value"=>$value]],"id",$id);
    }

    public function updateSongFields($id,$video,$name) {
        $this->dbDAO->dataBase->createHistoryEntry("song",$id);
        $data=array();
        $data=$this->dataBase->insertFieldInArray($data, "video", $video);
        $data=$this->dataBase->insertFieldInArray($data, "name", $name);
        return $this->dataBase->update("song", $data,"id",$id);
    }


    public function getSongById($id) {
        $song = $this->dbDAO->dataBase->getEntryById("song", $id);
        $interpret = $this->dbDAO->dataBase->getEntryById("interpret", $song["interpretID"]);
        $song["interpretName"]=$interpret["name"];
        return $song;
    }


    public function getInterpretById($id) {
        return $this->dbDAO->dataBase->getEntryById("interpret", $id);
    }

    public function getSongList($interpretId=0) {
        if ($interpretId>0) {
            return $this->dbDAO->dataBase->getElementList("song",false,"interpretID=".$interpretId);
        } else {
            return $this->dbDAO->dataBase->getElementList("song",false);
        }
    }

    public function getInterpretList() {
        return $this->dbDAO->dataBase->getElementList("interpret",false,null,null,null,"name asc");
    }

    /*
     * get Voterslist by class id
     */
    public function getVotersListByClassId($classId) {
        $sql  ="select  person.id, person.lastname, person.firstname, person.picture, count(1) as count ";
        $sql .= "from opinion join person on person.id=opinion.changeUserID ";
        $sql .= " where opinion.table='music' and opinion.opinion='favorite' ";
        if (null!=$classId) {
            $sql .=" and person.classID=".$classId;
        }
        $sql .=" group by person.id ";
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    /*
     * get Voterslist by scool id
     */
    public function getVotersListBySchoolId($schoolId) {
        $sql  ="select count(*) as count, person.id, person.lastname, person.firstname, person.picture ";
        $sql .= "from opinion join person on person.id=opinion.changeUserID ";
        $sql .="join class on class.id=person.classID ";
        $sql .="where class.schoolID=".$schoolId." and opinion.table='music' and opinion.opinion='favorite' group by person.id ";
        $sql .="order by count desc";
        $this->dataBase->query($sql);
        $ret = $this->dataBase->getRowList();
        return $ret;
    }

    public function getVotersListForMusicId($musicId) {
        $sql  ="select  person.id as personid, person.lastname, person.firstname, person.picture  ";
        $sql .= "from opinion join person on person.id=opinion.changeUserID ";
        $sql .="where opinion.entryID=".$musicId." and opinion.table='music' and opinion.opinion='favorite' order by person.lastname";
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }


    /**
     * read songvotelist
     * @return array(count,voted,songID,songLink,songVideo,songName,interpretName,id)
     */
    public function readTopList($classId,$personId) {
        $sql  ="select count(song.id) as count, instr(GROUP_CONCAT(person.id),'".$personId."') as voted, song.*, ";
        $sql .="interpret.name as interpretName ";
        $sql .="from opinion join person on person.id=opinion.changeUserID ";
        $sql .="join song on song.id=opinion.entryID ";
        $sql .="join interpret on interpret.id=song.interpretID ";
        $sql .=" where opinion.table='music' and opinion.opinion='favorite'";
        if (intval($classId!=0))
            $sql .=" and person.classID=".$classId;
        $sql .=" group by song.id order by count desc";
        $this->dataBase->query($sql);
        return  $this->dataBase->getRowList();
    }


    public function getVote($personId,$meetAfterYear) {
        $sql="select * from vote where personID =".$personId." and meetAfterYear=".$meetAfterYear;
        $this->dataBase->query($sql);
        if ($this->dataBase->count()>0) {
            return $this->dataBase->fetchRow();
        } else {
            $ret = array();
            $ret["eventDay"]="";
            $ret["isSchool"]="";
            $ret["isCemetery"]="";
            $ret["isDinner"]="";
            $ret["isExcursion"]="";
            $ret["place"]="";

            return $ret;
        }
    }

    public function saveVote($entry) {
        return $this->dbDAO->dataBase->saveEntry("vote", $entry);
    }

    public function getAllVotes()
    {
        return $this->dataBase->queryArray("select * from songvote");
    }

}