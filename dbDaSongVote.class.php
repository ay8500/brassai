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

    public function updateSongFields($id,$video,$name,$language,$genre,$year) {
        $this->dbDAO->dataBase->createHistoryEntry("song",$id);
        $data=array();
        $data=$this->dataBase->insertFieldInArray($data, "video", $video);
        $data=$this->dataBase->insertFieldInArray($data, "name", $name);
        $data=$this->dataBase->insertFieldInArray($data, "language", $language);
        $data=$this->dataBase->insertFieldInArray($data, "genre", $genre);
        $data=$this->dataBase->insertFieldInArray($data, "year", intval($year));
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
    public function readTopList($classId,$personId,$limit=1000,$language=null,$genre=null,$year=null) {
        $sql  ="select count(song.id) as count, song.*, ";
        $sql .="interpret.name as interpretName ";
        $sql .="from song ";
        $sql .="left join opinion on song.id=opinion.entryID and 'music'=opinion.table and 'favorite'=opinion.opinion ";
        $sql .="join person on person.id=opinion.changeUserID ";
        $sql .="join interpret on interpret.id=song.interpretID ";
        $sql .=" where true ";
        if (intval($classId!=0))
            $sql .=" and person.classID=".$classId;
        if ($language!=null && sizeof($language)>0) {
            $sql .= " and ( song.language in (";
            foreach ($language as $lang=>$value) {
                if ($value)
                    $sql .= "'".$lang."',";
            }
            $sql = trim($sql,",").") or song.language='') ";
        }
        if ($genre!=null && sizeof($genre)>0) {
            $sql .= "  and ( song.genre in (";
            foreach ($genre as $gen=>$value) {
                if ($value!==false)
                    $sql .= "'".$gen."',";
            }
            $sql = trim($sql,",").") or song.genre='') ";
        }
        if ($year!=null && sizeof($year)>0) {
            $sql .= "  and ( false or ";
            foreach ($year as $y=>$value) {
                if ($value!==false)
                    $sql .= "song.year BETWEEN ".(intval($y)>1960?intval($y):1000). " AND ".(9+intval($y)). " OR ";
            }
            $sql = substr($sql,0,-3)." or song.year is null) ";
        }
        $sql .=" group by song.id order by count desc limit ".$limit;
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

    /**
     * Search for Music
     * @param string $text
     *@return array
     */
    public function searchForMusic($text) {
        $ret = array();
        $textItems=explode(' ', trim($text));
        foreach ($textItems as $item) {
            $ret=$this->arrayMergeByFieldId($ret,$this->searchForMusicOneString($item));
        }
        usort($ret, "self::compareAlphabeticalSong");
        return $ret;
    }

    /**
     * Search for class by year name
     * @param string $text
     * @return array
     */
    private function searchForMusicOneString($text) {
        $ret = array();
        $text=trim($text);
        if( strlen($text)>1) {
            $text= searchSpecialCharsAsRegex($text);
            $sql = "select song.*, interpret.name as interpretName from song ";
            $sql .="join interpret on interpret.id=song.interpretID ";
            $sql .="where song.name rlike '".$text."' ";
            $sql .="or interpret.name rlike '".$text."' ";
            $sql .=" limit 50";
            $this->dataBase->query($sql);
            while ($class=$this->dataBase->fetchRow()) {
                array_push($ret, $class);
            }
            asort($ret);
        }
        return $ret;
    }

    /**
     * Merge two array lists by the field id and return a list of elements that existst in both of the input array
     * If one of the input arrays are empty the return the other one
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function arrayMergeByFieldId($array1, $array2) {
        $ret=array();
        if (sizeof($array1)==0)
            return  $array2;
        if (sizeof($array2)==0)
            return  $array1;
        foreach ($array1 as $row1) {
            foreach ($array2 as $row2) {
                if ($row1["id"]==$row2["id"]) {
                    if (array_search($row1["id"],array_column($ret,"id"))===false) {
                        array_push($ret, $row1);
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Compare persons by isTeacher,firstname,lastname,birthname
     * @param person $a
     * @param person $b
     * @return int
     */
    function compareAlphabeticalSong($a,$b) {
        $c = strcmp($a["name"],$b["name"]);
        if ($c!=0) {
            return strcmp($a["interpretName"],$b["interpretName"]);
        }
    }

}