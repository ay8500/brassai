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
* Extension for Anonymous User entrys and History features
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
    public function getLoggedInUserId() {
        if (!isset($_SESSION["uId"]))
            return null;
        return intval($_SESSION["uId"]);
    }

    /**
     * SQL Statement to select the anonymous changes
     * @param string $table
     * @param  string $newEntrys
     * @return string
     */
    public function getSqlAnonymous($table=null,$newEntrys=false)
    {
        if ($newEntrys) {
            $changeforID= " is ";
        } else {
            $changeforID= " is not ";
        }
        if ($table!=null)
            return $table."changeForID ".$changeforID." null and ".$table."changeUserID is null and ".$table."changeIP='".$_SERVER["REMOTE_ADDR"]."'";
        else
            return "changeForID ".$changeforID." null and changeUserID is null and changeIP='".$_SERVER["REMOTE_ADDR"]."'";
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
     * Returns a signle entry from a table in consideration of the anonymous changes or NULL if no entry found
     * even if the anonymous copy is returned the id will be from the original
     * @param string $table
     * @param int $id
     * @param boolean $forceThisID
     * @return array|null  the entry
     */
    public function getEntryById($table,$id,$forceThisID=false) {
        if ($id==null || $id=='')
            return null;
        //First get the forced entry by the id
        if ($forceThisID==true) {
            $sql="select * from ".$table.' where id='.$id;
            return  $this->querySignleRow($sql);
        }
        //First get the entry modified by the aktual ip and then the original entry, the original entry has allways a smaler id then a copy
        $sql="select * from ".$table.' where id='.$id." or (changeIP='".$_SERVER["REMOTE_ADDR"]."' and changeForID =".$id.") order by id desc";
        if ($this->query($sql)) {
            $ret =  $this->getRowList();
            /* Change the ID to the original ID
            if (sizeof($ret)>1) {
                $ret[0]["id"]=$ret[0]["changeForID"];
            }
            */
            if (sizeof($ret)>0)
                return $ret[0];
        }
        return null;
    }

    /**
     * get a db entry by a field
     * @return array | NULL if no entry or more then one entry found
     */
    public function getEntryByField($table,$fieldName,$fieldValue) {
        $sql="select id from ".$table." where ".$fieldName."='".trim($fieldValue)."'";
        $sql .=" and changeForID is null";
        $this->query($sql);
        if ($this->count()==1) {
            $entry = $this->fetchRow();
            return $this->getEntryById($table, $entry["id"]);
        } else {
            return null;
        }
    }

    /**
     * get a db entry by a field
     * @param string $table
     * @param string $where
     * @return array | NULL is no entry found
     */
    public function getEntry($table,$where) {
        $sql="select id from ".$table." where ".$where;
        $sql .=" and changeForID is null";
        $this->query($sql);
        if ($this->count()>0) {
            $entry = $this->fetchRow();
            return $this->getEntryById($table, $entry["id"]);
        } else {
            return null;
        }
    }


    /**
     * Insert or update a table entry, also create a history entry on update
     * If user is anonymous create a new entry as a change
     * @return integer if negativ an error occurs
     */
    public function saveEntry($table,$entry) {
        //Build the change data array
        $data = array();
        foreach ($entry as $fieldName=>$fieldValue) {
            if ($fieldName!="id" && $fieldName!="changeForID") {
                $data =$this->insertFieldInArray($data,$fieldName, $fieldValue);
            }
        }
        $data = $this->insertUserDateIP($data);
        //Update
        if (isset($entry["id"]) && $entry["id"]>=0) {
            //User is loggen on
            if ($this->userIsLoggedOn()) {
                $this->createHistoryEntry($table,$entry["id"]);
                if ($this->update($table,$data,"id",$entry["id"])) {
                    return $entry["id"];
                } else
                    return -5;
                //Anonymous user
            } else {
                $dbentry=$this->getEntryById($table, $entry["id"]);
                if ($dbentry!==null) {
                    if (isset($dbentry["changeUserID"])) {
                        //Insert an anonymous copy
                        $data = $this->changeFieldInArray($data, "changeForID", $entry["id"]);
                        if ($this->insert($table, $data))
                            return $this->getInsertedId();
                    } else {
                        //Update the anonymous entry
                        if ($this->update($table, $data, "id", $entry["id"]))
                            return $dbentry["id"];
                    }
                } else
                    return -3;
            }
        }
        //Insert
        else {
            if ($this->insert($table,$data))
                return $this->getInsertedId();
        }
        return -1;
    }

    /**
     * Get an array of elements, or an empty array if no elements found.
     * Anonymous changes from the user IP will be considered
     * even if the anonymous copys are returned the ids will be from the original entrys if the parameter $originalId =true
     * @param $table
     * @param bool $originalId
     * @param string $where
     * @param int $limit
     * @param int $offset
     * @param string $orderby
     * @param string $field
     * @return array
     */
    public function getElementList($table,$originalId=false,$where=null, $limit=null, $offset=null, $orderby=null, $field="*", $join=null) {
        $ret = array();
        $jtable = null;
        //normal entrys
        $sql="select ".$field;
        if ($join==null)
            $sql .= " ,id ";
        $sql .= " from ".$table;
        if ($join!=null) {
            $sql .= " join " . $join;
            $jtable = $table.'.';
        }
        $sql .=" where ((".$jtable."changeForID is null and ".$jtable."changeUserID is not null)";
        //and anonymous new entrys
        $sql.=" or (".$this->getSqlAnonymous($jtable,true).") )";
        if ($where!=null)		$sql.=" and ( ".$where." )";
        if ($orderby!=null)		$sql.=" order by ".$orderby;
        if ($limit!=null)		$sql.=" limit ".$limit;
        if ($offset!=null)		$sql.=" offset ".$offset;
        $this->query($sql);
        if ($this->count()>0) {
            $ret = ($this->getRowList());
            //removeOriginalIfAnonymousExists
        }
        //anonymous entrys
        $sql="select ".$field.",".$jtable."changeForID ";
        if ($join==null)
            $sql .= " ,id ";
        $sql .= " from ".$table;
        if ($join!=null) {
            $sql .= " join " . $join;
            $jtable = $table.'.';
        }
        $sql .=' where '.$this->getSqlAnonymous($jtable);
        if ($where!=null)		$sql.="  and ( ".$where." )";
        if ($orderby!=null)		$sql.=" order by ".$orderby;
        if ($limit!=null)		$sql.=" limit ".$limit;
        if ($offset!=null)		$sql.=" offset ".$offset;
        $this->query($sql);
        if ($this->count()>0) {
            //Change the entrys with the anonymous entrys
            $anyonymous=$this->getRowList();
            foreach ($ret as $i=>$r) {
                $found=array_search($r["id"],array_column($anyonymous,"changeForID"));
                if ($found!==false) {
                    $ret[$i]=$anyonymous[$found];
                    //the original id
                    if ($originalId)
                        $ret[$i]["id"]=$r["id"];
                }
            }
        }
        return $ret;
    }

    /**
     * Get an array of ids, or an empty array if no ids found
     * Anonymous changes from the user IP will be considered
     */
    public function getIdList($table, $where=null, $limit=null, $offset=null, $orderby=null) {
        return $this->getElementList($table,false,$where,$limit,$offset,$orderby,"id");
    }

    /**
     * get history info used to find out how many history entrys exists
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
     * read history entrys from db
     * @param string $table
     * @param  int $id
     * @return array
     */
    public function getHistory($table,$id=null) {
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
            return false;
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
            //$json=json_decode_utf8($hist["jsonData"]);
            $json=json_decode($hist["jsonData"],true);
            $changeDate=$json["changeDate"];
            $ret = $this->update($hist["table"],[["field"=>"changeDate","type"=>"s","value"=>$changeDate]],"id",$hist["entryID"]);
            if ($ret!==false)
                return $this->delete("history", "id", $id);
        }
        return false;
    }


}