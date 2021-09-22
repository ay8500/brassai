<?php
/**
 * Class dbDaOpinion
 * save,update and delete data in the opinion table
 */

include_once "dbDaCandle.class.php";

class dbDaOpinion
{
    /**
     * @var dbDAO
     */
    private $dbDAO;

    /**
     * @var dbDaCandle
     */
    private $dbCandle;

    /**
     * dbDaOpinion constructor.
     * @param dbDAO $dbDAO
     */
    public function __construct(dbDAO $dbDAO)
    {
        $this->dbDAO = $dbDAO;
        $this->dbCandle = new dbDaCandle($this->dbDAO);
    }

    /**
     * Save opinion
     * @param $id
     * @param $table
     * @param $type
     * @param $text
     * @return int the counter or -1 on error
     */
    public function setOpinion($id, $uid, $table, $type, $text = null)
    {
        $data = array();
        $db = $this->dbDAO->dataBase;
        if ($text != null)
            $data = $db->insertFieldInArray($data, 'text', $text);
        $data = $db->insertFieldInArray($data, 'schoolID', getActSchoolId());
        $data = $db->insertFieldInArray($data, 'entryID', $id);
        $data = $db->insertFieldInArray($data, 'table', $table);
        $data = $db->insertFieldInArray($data, 'opinion', $type);
        $data = $db->insertFieldInArray($data, 'changeDate', date("Y-m-d H:i:s"));
        $data = $db->insertFieldInArray($data, 'changeIP', $_SERVER["REMOTE_ADDR"]);
        if ($uid != null)
            $data = $db->insertFieldInArray($data, 'changeUserID', $uid);
        if ($db->insert('opinion', $data) !== false) {
            $this->dbDAO->updateRecentChangesList();
            return sizeof($this->getOpinions($id, $table, $type));
            //return $db->queryInt("select count(1) from opinion where `table`='" . $table . "' and opinion ='" . $type . "' and entryID=" . $id);
        } else {
            return -1;
        }
    }

    /**
     * deltete an opinion by id
     * @param $id
     * @return stdClass information about the deleted opinion
     */
    public function deleteOpinion($id)
    {
        $ret = new stdClass();
        $opinion = $this->dbDAO->dataBase->querySignleRow("select * from opinion where id=" . $id);
        if ($opinion != null &&
            (
                isUserAdmin() ||
                (isUserLoggedOn() && $opinion["changeUserID"] == getLoggedInUserId()) ||
                (!isUserLoggedOn() && $opinion["changeIP"]) == $_SERVER["REMOTE_ADDR"]
            )
        ) {
            $ret->table = $opinion["table"];
            $ret->type = $opinion["opinion"];
            $ret->id = $opinion["entryID"];
            $this->dbDAO->dataBase->delete("opinion", "id", $id);
            $ret->count = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='" . $ret->table . "' and opinion ='" . $ret->type . "' and entryID=" . $ret->id);
        } else {
            $ret->count = -1;
        }
        return $ret;
    }

    public function sendEasterEgg($id) {
        $ret = new stdClass();
        $opinion = $this->dbDAO->dataBase->querySignleRow("select * from opinion where id=" . $id);
        $exist = $this->existOpinion($opinion["changeUserID"],$opinion["entryID"],"person","easteregg","changeDate >'".date("Y")."-01-01 00:00:00'");
        if ($opinion != null && !$exist &&
            ( isUserAdmin() ||  (isUserLoggedOn() && $opinion["entryID"] == getLoggedInUserId()) )

        ) {
            $ret->table = $opinion["table"];
            $ret->type = $opinion["opinion"];
            $ret->id = $opinion["entryID"];
            $ret->ok = $this->setOpinion($opinion["changeUserID"],$opinion["entryID"],"person","easteregg")>0;
        } else {
            $ret->ok = false;
        }
        return $ret;

    }

    /**
     * get only one opinion from logge in user or teh ip address
     * @param $id
     * @param $table
     * @param $type
     * @return array|null
     */
    public function getOpinion($id, $table, $type, $where=null)
    {
        $where = $where==null?"":" and ".$where;
        if (isUserLoggedOn()) {
            $this->dbDAO->dataBase->query("select * from opinion where `table`='" . $table . "' and opinion='" . $type . "' and entryID=" . $id .$where. " and changeUserID=" . getLoggedInUserId());
            return $this->dbDAO->dataBase->getRowList();
        }
        $this->dbDAO->dataBase->query("select * from opinion where `table`='" . $table . "' and opinion='" . $type .$where. "' and entryID=" . $id . " and changeIP='" . $_SERVER["REMOTE_ADDR"] . "'");
        return $this->dbDAO->dataBase->getRowList();
    }

    /**
     * check if an Opinion exists
     * @return boolean
     */
    public function existOpinion($entryID,$changeUserID, $table, $type, $where=null)
    {
        $where = $where==null?"":" and ".$where;
        $where = "select id from opinion where `table`='" . $table . "' and opinion='" . $type . "' and entryID=" . $entryID .$where. " and changeUserID=".$changeUserID ;
        $ret = $this->dbDAO->dataBase->queryArray($where);
        return sizeof($ret)>0;
    }

    /**
     * get the opinions for a person
     * @param int $id person id
     */
    public function getOpinions($id, $table, $type, $start = 1)
    {
        $this->dbDAO->dataBase->query("select * from opinion where `table`='" . $table . "' and opinion='" . $type . "' and entryID=" . $id . " order by changeDate desc");
        $data = $this->dbDAO->dataBase->getRowList();
        $ret = array();
        for ($i = 0; $i < sizeof($data); $i++) {
            $opinion = new stdClass();
            $opinion->id = $data[$i]["id"];
            $opinion->text = $data[$i]["text"];
            $opinion->person = $data[$i]["changeUserID"];
            $opinion->date = $data[$i]["changeDate"];
            $opinion->ip = $data[$i]["changeIP"];
            $opinion->myopinion = (isUserAdmin() || getLoggedInUserId() == $data[$i]["changeUserID"]) || (!isUserLoggedOn() && $data[$i]["changeIP"] == $_SERVER["REMOTE_ADDR"]);
            $ret[] = $opinion;
        }
        return $ret;
    }

    /**
     * get the opinions for a person
     * @param int $id person id
     * @param string $type
     * @return stdClass
     */
    public function getOpinionCount($id, $type)
    {
        $ret = new stdClass();
        if ($type == 'picture') {
            $ret->opinions = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='picture' and opinion='text' and entryID=" . $id);
            $ret->favorite = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='picture' and opinion='favorite' and entryID=" . $id);
            $ret->content = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='picture' and opinion='content' and entryID=" . $id);
            $ret->nice = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='picture' and opinion='nice' and entryID=" . $id);
        } elseif ($type == 'message') {
            $ret->opinions = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='message' and opinion='text' and entryID=" . $id);
            $ret->favorite = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='message' and opinion='favorite' and entryID=" . $id);
            $ret->content = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='message' and opinion='content' and entryID=" . $id);
            $ret->nice = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='message' and opinion='nice' and entryID=" . $id);
        } elseif ($type == 'music') {
            $ret->opinions = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='music' and opinion='text' and entryID=" . $id);
            $ret->favorite = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='music' and opinion='favorite' and entryID=" . $id);
            $ret->content = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='music' and opinion='content' and entryID=" . $id);
            $ret->nice = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='music' and opinion='nice' and entryID=" . $id);
        } else {
            $ret->opinions = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='person' and opinion='text' and entryID=" . $id);
            $ret->friends = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='person' and opinion='friend' and entryID=" . $id);
            $ret->sport = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='person' and opinion='sport' and entryID=" . $id);
            /* easter */
            $ret->easter = $this->dbDAO->dataBase->queryInt("select count(1) from opinion where `table`='person' and opinion like 'easter%' and entryID=" . $id/*. " and changeDate >'".date("Y")."-01-01 00:00:00'"*/);
            $ret->candles = $this->dbCandle->getCandlesByPersonId($id);
        }
        return $ret;
    }

    public function getOpinionPersonCount($table, $type, $year=null)
    {
        $ret = new stdClass();
        $year = $year==null?"":" AND changeDate > '".$year."-01-01 00:00:00' ";
        $ret->user = $this->dbDAO->dataBase->queryArray("select count(changeUserID), changeUserID from opinion where `table`='" . $table . "' and opinion='" . $type . "'" . $year . " GROUP BY changeUserID");
        $ret->opinion = $this->dbDAO->dataBase->queryArray("select count(entryID), entryID from opinion where `table`='" . $table . "' and opinion='" . $type . "'" . $year . " GROUP BY entryID");
        return $ret;
    }
}