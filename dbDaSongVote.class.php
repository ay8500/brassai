<?php

/**
 * Class dbDaSong
 * save,update and delete data in the song ang songvote tables
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
     * dbDaOpinion constructor.
     * @param dbDAO $dbDAO
     */
    public function __construct(dbDAO $dbDAO)
    {
        $this->dbDAO = $dbDAO;
        $this->dataBase = $this->dbDAO->dataBase;
    }

    public function deleteVote($voteId) {
        $this->dataBase->createHistoryEntry("songvote",$voteId,true);
        return $this->dataBase->delete("songvote", "id",$voteId);
    }

    public function saveSongVote($entry) {
        return $this->dbDAO->dataBase->saveEntry("songvote",$entry);
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
        return $this->dbDAO->dataBase->getEntryById("song", $id);
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
        $sql .="from songvote join person on person.id=songvote.personID";
        if (null!=$classId) {
            $sql .=" where person.classID=".$classId;
        }
        $sql .=" group by personID";
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    /*
     * get Voterslist by scool id
     */
    public function getVotersListBySchoolId($schoolId) {
        $sql  ="select  person.id, person.lastname, person.firstname, person.picture, count(1) as count ";
        $sql .="from songvote join person on person.id=songvote.personID ";
        $sql .="join class on class.id=person.classID ";
        $sql .="where class.schoolID=".$schoolId." group by personID";
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    public function getVotersListForMusicId($musicId) {
        $sql  ="select  person.id as personid, person.lastname, person.firstname, person.picture  ";
        $sql .="from songvote join person on person.id=songvote.personID ";
        $sql .="where songvote.songID=".$musicId." order by person.lastname";
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }


    /**
     * read songvotelist
     */
    public function readTopList($classId,$personId) {
        $sql  ="select count(1) as count, instr(GROUP_CONCAT(person.id),'".$personId."') as voted, song.id as songID, song.link as songLink, song.video as songVideo, song.name as songName, interpret.name as interpretName, songvote.id as id  ";
        $sql .="from songvote join person on person.id=songvote.personID ";
        $sql .="join song on song.id=songvote.songID ";
        $sql .="join interpret on interpret.id=song.interpretID ";
        if (intval($classId!=0))
            $sql .="where person.classID=".$classId;
        $sql .=" group by song.id order by count desc";
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
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

}