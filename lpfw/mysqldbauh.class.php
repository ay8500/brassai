<?php

namespace maierlabs\lpfw;

/*
*  |Framework fields | id | changeUserID | changeDate | changeIP | changeForID |
*  |normal entry     | *  | *            | *          | *        | NULL        |
*  |anonymous change | *  | NULL         | *          | *        | *           |
*  |anonymous new    | *  | NULL         | *          | *        | NULL        |

select all 			where changeUserID is not null
select anonymous	where changeUserID is null and IP=*

In the save functions use id=-1 to insert a new entry
*/

include_once "mysql.class.php";

/**
* Database framework for php made by MaierLabs (c) 2018
* Extension for Anonymous User Entrys and history features
* @package maierlabs\lpfw
*/
class MySqlDbAUH extends MySql
{

    function userIsLoggedOn() {
        return ( isset($_SESSION['uId']) && intval($_SESSION['uId'])>-1 );
    }

    /**
     * get the user id form logged in user
     * @return integer or NULL if no user logged on
     */
    function getLoggedInUserId() {
        if (!isset($_SESSION["uId"]))
            return null;
        return intval($_SESSION["uId"]);
    }

    /**
     * insert in update or insert data the date in field changeDate and the remote ip in field changeIP
     * @param array $data
     * @return array
     */
    public function insertUserDateIP($data) {
        $data =$this->changeFieldInArray($data,"changeIP", $_SERVER["REMOTE_ADDR"]);
        $data =$this->changeFieldInArray($data,"changeDate", date("Y-m-d H:i:s"));
        if ($this->userIsLoggedOn()) {
            $data =$this->changeFieldInArray($data,"changeUserID", $this->getLoggedInUserId());
        } else {
            $data =$this->setFieldInArrayToNull($data,"changeUserID");
        }
        return $data;
    }

    /**
     * update one entry
     * @param string $table
     * @param array $entry array of (field->value)
     * @return boolean
     */
    public function updateEntry($table,$entry) {
        if ($table==null || $entry==null || sizeof($entry)==0)
            return false;
        //Build the change data array
        $data = array();
        foreach ($entry as $fieldName=>$fieldValue) {
            if ($fieldName!="id" && $fieldName!="changeForID") {
                $data =$this->insertFieldInArray($data,$fieldName, $fieldValue);
            }
        }
        return $this->update($table,$data,"id",$entry["id"]);
    }


    /**
     * get history info
     * @param string $table
     * @param  int $id
     * @return array

     */
    public function getHistoryInfo($table,$id) {
        $sql="select id from history where `table`='".$table."' and entryID=".$id;
        $this->query($sql);
        if ($this->count()>0) {
            return $this->getRowList();
        } else {
            return array();
        }
    }

    /**
     * @param string $table
     * @param  int $id
     * @return array
     * get history
     */
    public function getHistory($table,$id) {
        if(null!=$table && null!=$id) {
            $sql="select * from history where `table`='".$table."' and entryID=".$id." order by id desc ";
        } elseif(null==$table && null!=$id) {
            $sql="select * from history where `changeUserID`='".$id."' order by entryID desc, id desc limit 1000";
        } elseif(null!=$table) {
            $sql="select * from history where `table`='".$table."' order by entryID desc, id desc limit 300";
        } else {
            $sql="select * from history  order by `table` asc, entryID desc, id desc limit 50";
        }

        $this->query($sql);
        if ($this->count()>0) {
            return $this->getRowList();
        } else {
            return array();
        }
    }

    /**
     * Create a history entry in the history table
     * @param string $table
     * @param string $id
     * @param boolean $delete
     * @return boolean
     */
    public function createHistoryEntry($table,$id,$delete=false) {
        $entry=$this->querySignleRow("select * from ".$table." where id=".$id);
        if ($entry==null)
            return -16;
        $data = array();
        $data=$this->insertFieldInArray($data, "entryID", $id);
        $data=$this->insertFieldInArray($data, "table", $table);
        $data=$this->insertFieldInArray($data, "jsonData", json_encode((object)$entry,JSON_HEX_TAG+JSON_HEX_AMP+JSON_HEX_APOS+JSON_HEX_QUOT));
        $data = $this->insertUserDateIP($data);
        $data =$this->insertFieldInArray($data,"deleted", $delete?1:0 );
        return $this->insert("history", $data);
    }

    /**
     * Delete history entry
     * @param int $id
     * @return bool
     */
    public function deleteHistoryEntry($id)
    {
        $hist = $this->querySignleRow("select * from history where id=".$id);
        if ($hist!=null) {
            $json=json_decode_utf8($hist["jsonData"]);
            $changeDate=$json["changeDate"];
            $ret = $this->update($hist["table"],[["field"=>"changeDate","type"=>"s","value"=>$changeDate]],"id",$hist["entryID"]);
            if ($ret!==false)
                return $this->delete("history", "id", $id);
        }
        return false;
    }


}