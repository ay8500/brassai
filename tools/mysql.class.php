<?php
namespace maierlabs\lpfw;

include_once 'logger.class.php';
include_once 'loggerType.class.php';
include_once 'loggerLevel.class.php';

/**
 *@package maierlabs\lpfw
 */
class MySql
{
    private $connection = NULL;
    private $result = NULL;
    private $counter = NULL;

    private $countQuerys = 0;
    private $countChanges = 0;
    private $sqlRequest = array();

    /**
     * Constructor
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $pass
     */
    public function __construct($host, $database, $user = "", $pass = "")
    {
        try {
            $this->connection = mysqli_connect($host, $user, $pass, $database);
        } catch (\Exception $e) {
            Logger::_('Database connection failed', LoggerLevel::error);
        }
    }

    /**
     * "Destructor"
     * @return void
     */
    public function disconnect()
    {
        if (isset($this->connection))
            mysqli_close($this->connection);
        $this->connection = null;
        //Logger::_('Database disconnected', LoggerLevel::info);
    }

    /**
     * Commit Work
     * @return object
     */
    public function commit()
    {
        return mysqli_commit($this->connection);
    }

    /**
     * check db connection
     * @return bool
     */
    public function isDbConnected() {
        return isset($this->connection);
    }


    /**
     * Execute a query get results with $this->fetchRow() and $this->count()
     * @param string $query
     * @return bool
     */
    public function query($query)
    {
        $timer=microtime(true);
        $this->result = mysqli_query($this->connection, $query) or Logger::_("MySQL ERROR 1:" . $query . " MySQL Message:" . mysqli_error($this->connection), LoggerLevel::error);
        $this->counter = NULL;
        $this->countQuerys++;
        array_push($this->sqlRequest, number_format((microtime(true)-$timer) * 1000,2)."ms ".$query);
        return $this->result!==false;
    }

    /**
     * Execute a query that return a single iteger value
     * @param $query
     * @return int
     */
    public function queryInt($query)
    {
        if ($this->query($query)) {
            $r = mysqli_fetch_row($this->result);
            return intval($r[0]);
        }
        return -1;
    }

    /**
     * Execute a query that return a single row
     * @param $query
     * @return array|null
     */
    public function querySignleRow($query)
    {
        if ($this->query($query) && $this->count() == 1)
            return $this->fetchRow();
        return null;
    }

    /**
     * Execute a query that return the first row
     * @param $query
     * @return array
     */
    public function queryFirstRow($query)
    {
        if ($this->query($query) && $this->count() >= 1)
            return $this->fetchRow();
        return null;
    }

    /**
     * fetch a query result row
     * @return array|null
     */
    public function fetchRow()
    {
        if ($this->result !== false)
            return mysqli_fetch_assoc($this->result);
        else
            return null;
    }

    /**
     * get result list as an array
     * @return array
     */
    public function getRowList()
    {
        $ret = array();
        if ($this->result!==false) {
            while ($row = mysqli_fetch_assoc($this->result)) {
                array_push($ret, $row);
            }
        }
        return $ret;
    }


    /**
     * get result list as an array of one column
     * @return array
     */
    public function getOneColumnList($column)
    {
        $ret = array();
        if ($this->result!==false) {
            while ($row = mysqli_fetch_assoc($this->result)) {
                if (isset($row[$column]))
                    array_push($ret, $row[$column]);
            }
        }
        return $ret;
    }


    /**
     * howmanny rows are in the query result
     * @return int|0
     */
    public function count()
    {
        if ($this->counter == NULL && isset($this->result) && gettype($this->result) == "object") {
            $this->counter = mysqli_num_rows($this->result);
            return intval($this->counter);
        }
        return 0;
    }

    /**
     * select count
     * @param string $table
     * @param string $where
     * @return int
     */
    public function tableCount($table, $where = "")
    {
        $this->countQuerys++;
        if ($where == "")
            $sql = "select count(1) from " . $table;
        else
            $sql = "select count(1) from " . $table . " where " . $where;
        return $this->queryInt($sql);
    }

    /**
     * the sum of one field in a table
     * @param string $table
     * @param string $field
     * @param string $where
     * @return int
     */
    public function tableSumField($table, $field, $where = "")
    {
        $this->countQuerys++;
        if ($where == "")
            $sql = "select sum(" . $field . ") from " . $table;
        else
            $sql = "select sum(" . $field . ") from " . $table . " where " . $where;
        return $this->queryInt($sql);
    }

    /**
     * the sum of a field multiplicated with an other field
     * @param string$table
     * @param string $field
     * @param string $multField
     * @param string $where
     * @return int
     */
    public function tableSumMultField($table, $field, $multField, $where = "")
    {
        $this->countQuerys++;
        if ($where == "")
            $sql = "select sum(" . $field . " * " . $multField . " ) from " . $table;
        else
            $sql = "select sum(" . $field . " * " . $multField . " )  from " . $table . " where " . $where;
        return $this->queryInt($sql);
    }


    /** Insert and update need a $data array for the values
     * $data[0]["field"]='Fieldname';
     * $data[0]["type"]='s';            //Types s=string, d=datatime, n=number
     * $data[0]["value"]='value';
     * @param string $table
     * @param array $data
     * @return boolean
     * */
    public function insert($table, $data)
    {
        $sql = "insert into `" . $table . "` (";
        foreach ($data as $i => $d) {
            if ($i != 0) $sql .= ",";
            $sql .= "`" . $d["field"] . "`";
        }
        $sql .= ") values (";
        foreach ($data as $i => $d) {
            if ($i != 0) $sql .= ",";

            if (isset($d["value"])) {
                if ($d["type"] != "n") $sql .= "'";
                if ($d["type"] != "n") {
                    $sql .= $this->replaceSpecialChars($d["value"]);
                } else {
                    if ($d["value"]==='') {
                        $sql .= 'null';
                    } else {
                        $sql .= $d["value"];
                    }
                }
                if ($d["type"] != "n") $sql .= "'";
            } else {
                $sql .= "null";
            }
        }
        $sql .= ")";
        $this->countChanges++;
        array_push($this->sqlRequest, $sql);
        $this->result = mysqli_query($this->connection, $sql) or Logger::_("MySQL ERROR:" . $sql . " MySQL Message:" . mysqli_error($this->connection), LoggerLevel::error);;
        return $this->result !==false;
    }

    /**
     * Error message from last request
     * @return  \mysqli_sql_exception
     */
    public function getErrorMessage()
    {
        return mysqli_error($this->connection);
    }

    /**
     * last autogenerated id
     * @return int|string
     */
    public function getInsertedId()
    {
        return mysqli_insert_id($this->connection);
    }

    /**
     * Update data in a table
     * Example for data [["field"=>"facebookid","type"=>"s","value"=>$facebookId]]
     * @param string $table
     * @param array $data
     * @param string $whereField
     * @param string $whereValue
     * @return boolean
     */
    public function update($table, $data, $whereField = "", $whereValue = "")
    {
        if ($whereField != "") {
            $where = $whereField . "='" . $whereValue . "'";
        } else {
            $where = null;
        }
        return $this->updateWhere($table, $data, $where);
    }

    /**
     * Update data in a table
     * Example for data [["field"=>"facebookid","type"=>"s","value"=>$facebookId]]
     * @param string $table
     * @param array $data
     * @param string $where where condition example: firstName='Levi'
     * @return boolean
     */
    public function updateWhere($table, $data, $where = null)
    {
        $sql = "update " . $table . " set ";
        $notFirstElement = false;
        foreach ($data as $d) {
            if ($notFirstElement) $sql .= ",";
            $notFirstElement = true;
            $sql .= "`" . $d["field"] . "`=";
            if (isset($d["value"])) {
                if ($d["type"] != "n") $sql .= "'";
                if ($d["type"] != "n") {
                    $sql .= $this->replaceSpecialChars($d["value"]);
                } else {
                    if ($d["value"]=='') {
                        $sql .= 'null';
                    } else {
                        $sql .= $d["value"];
                    }
                }
                if ($d["type"] != "n") $sql .= "'";
            } else {
                $sql .= "null";
            }
        }
        if ($where != null) {
            $sql .= " where " . $where . " ";
        }
        $this->countChanges++;
        array_push($this->sqlRequest, $sql);
        $this->result = mysqli_query($this->connection, $sql) or Logger::_("MySQL ERROR:" . $sql . " MySQL Message:" . mysqli_error($this->connection), LoggerLevel::error);
        return $this->result!==false;
    }

    /**
     *  delete from a table with where field and value
     * @param string $table
     * @param string $whereField
     * @param  string $whereValue
     * @return boolean
     **/
    public function delete($table, $whereField, $whereValue)
    {
        $this->countChanges++;
        $where = $whereField . "=" . $whereValue;
        return $this->deleteWhere($table, $where);
    }

    /**
     *  delete from a table with where clausel
     * @param string $table
     * @param string $where
     * @return boolean
     **/
    public function deleteWhere($table, $where)
    {
        $this->countChanges++;
        $sql = "delete from " . $table . " where " . $where;
        if ($this->result = mysqli_query($this->connection, $sql)) {
            return true;
        } else {
            Logger::_("MySQL ERROR:" . $sql . " MySQL Message:" . mysqli_error($this->connection), LoggerLevel::error);
            return false;
        }
    }

    /**
     * get the next auto incremented value
     * @param string $table
     * @return int
     */
    public function getNextAutoIncrement($table)
    {
        $sql = "SELECT Auto_increment FROM information_schema.tables WHERE table_name='" . $table . "'";
        return $this->queryInt($sql);
    }


    /**
     * @param string $s
     * @return string
     */
    public function replaceSpecialChars($s)
    {
        return str_replace("'", "\'", $s);
    }

    /**
     * @param string $s
     * @return string
     */
    public function rereplaceSpecialChars($s)
    {
        return str_replace("\'", "'", $s);
    }

    /**
     * create an array for data update or insert
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @return array
     */
    public function createFieldArray($type, $name, $value)
    {
        $ret = array();
        $ret["field"] = $name;
        $ret["type"] = $type;
        $ret["value"] = $value;
        return $ret;
    }

    /**
     * @param array $array
     * @param string $fieldName
     * @param mixed $fieldValue
     * @return array
     */
    public function insertFieldInArray($array, $fieldName, $fieldValue)
    {
        $type = null;
        //Ends with id, year oder start with is => integer
        if (preg_match('/id\\z/i', $fieldName) || preg_match('/Year\\z/', $fieldName) || preg_match('/\\Ais/', $fieldName)) {
            $type = "n";
        } //Ends with date => date
        else if (preg_match('/Date\\z/i', $fieldName)) {
            $type = "d";
        } //its a string
        else /*if ($fieldValue!=null) */ {
            $type = "s";
            $fieldValue = $this->replaceSpecialChars($fieldValue);
        }
        if ($type != null) {
            $ret = array();
            $ret["field"] = $fieldName;
            $ret["type"] = $type;
            $ret["value"] = $fieldValue;
            array_push($array, $ret);
        }
        return $array;
    }

    /**
     * @param array $fieldArray
     * @param string $fieldName
     * @param mixed $fieldValue
     * @return array
     */
    public function changeFieldInArray($fieldArray, $fieldName, $fieldValue)
    {
        $arrayIdx = array_search($fieldName, array_column($fieldArray, "field"));
        if ($arrayIdx !== false) {
            $fieldArray[$arrayIdx]["value"] = $fieldValue;
            return $fieldArray;
        } else {
            return $this->insertFieldInArray($fieldArray, $fieldName, $fieldValue);
        }
    }

    /**
     * @param array $fieldArray
     * @param string $fieldName
     * @return array
     */
    public function deleteFieldInArray($fieldArray, $fieldName)
    {
        $arrayIdx = array_search($fieldName, array_column($fieldArray, "field"));
        if ($arrayIdx !== false) {
            array_splice($fieldArray, $arrayIdx, 1);
        }
        return $fieldArray;
    }


    /**
     * @param array $fieldArray
     * @param string $fieldName
     * @return array
     */
    public function setFieldInArrayToNull($fieldArray, $fieldName)
    {
        $arrayIdx = array_search($fieldName, array_column($fieldArray, "field"));
        if ($arrayIdx !== false) {
            unset($fieldArray[$arrayIdx]["value"]);
        }
        return $fieldArray;
    }

    /**
     * @return void
     */
    public function resetCounter()
    {
        $this->countChanges = 0;
        $this->countQuerys = 0;
        return true;
    }

    /**
     * @return \stdClass
     */
    public function getCounter()
    {
        $ret = new \stdClass();
        $ret->changes = $this->countChanges;
        $ret->querys = $this->countQuerys;
        $ret->sql = $this->sqlRequest;
        return $ret;
    }
}