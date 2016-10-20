<?php

class MySqlDb {
  private $connection = NULL;
  private $result = NULL;
  private $counter=NULL;
 
 
  /* Constructor */
  public function __construct($host=NULL, $database=NULL, $user=NULL, $pass=NULL){
	$this->connection = mysqli_connect($host,$user,$pass,$database);
	if (mysqli_connect_errno()) {
		die('Database connection: ' . mysqli_error($this->connection));
	}
	//if (!is_resource($this->connection))	             die('Database connection: ' . mysqli_error($this->connection));
  	//if (!mysqli_select_db($database, $this->connection))	 die('Database connection: ' . mysqli_error());
  }
 
  /* "Destructor" */
  public function disconnect() {
    if (is_resource($this->connection))				
        mysqli_close($this->connection);
  }
 
  /* Execute a query get results with $this->fetchRow() and $this->count() */
  public function query($query) {
  	$this->result=mysqli_query($this->connection,$query)  or die(mysqli_error($this->connection));
  	$this->counter=NULL;
  }
  
  /* Execute a query that return a single iteger value */
  public function queryInt($query) {
  	$this->result=mysqli_query($this->connection,$query)  or die(mysqli_error($this->connection));
	//if(is_resource($this->result)) {
  		return $this->mysqli_result($this->result,0); 
	//}
	return 0;
  }
  
  /* fech a query result row */	 
  public function fetchRow() {
  	return mysqli_fetch_assoc($this->result);
  }
  
  public function getRowList() {
  		$ret=array();
  		while ($row = mysqli_fetch_assoc($this->result)) {
  			array_push($ret, $row);
  		}
  		return $ret;
  }
 
  /* howmanny rows are in the query result */
  public function count() {
  	if($this->counter==NULL && isset($this->result)) {
  		$this->counter=mysqli_num_rows($this->result);
  	}
 	return $this->counter;
  }

  /* select count */
  public function tableCount($table, $where="") {
    	if ($where=="")
    		$sql="select count(1) from ".$table;
    	else
    		$sql="select count(1) from ".$table." where ".$where;
		return $this->queryInt($sql);
  }

  /* the sum of one field in a table */
  public function tableSumField($table,$field, $where="") {
    	if ($where=="")
    		$sql="select sum(".$field.") from ".$table;
    	else
    		$sql="select sum(".$field.") from ".$table." where ".$where;
    	return $this->queryInt($sql);
  }

  /* the sum of a field multiplicated with an other field */
  public function tableSumMultField($table,$field, $multField, $where="") {
    	if ($where=="")
    		$sql="select sum(".$field." * ".$multField." ) from ".$table;
    	else
    		$sql="select sum(".$field." * ".$multField." )  from ".$table." where ".$where;
    	$this->result=mysqli_query($this->connection,$sql) or die(mysqli_error($this->connection));
  		return $this->queryInt($sql);
  }

  
  	/* Insert and update need a $data array for the values 
  	 * $data[0]["field"]='Fieldname';
  	 * $data[0]["type"]='s';            //Types s=string, d=datatime, n=number
  	 * $data[0]["value"]='value';
  	 * */
  
	/* Insert */ 
	public function insert($table, $data) {
	  	$sql  ="insert into `".$table."` (";
	  	for ($i=0;$i<sizeof($data);$i++) {
	  		if ($i!=0) $sql .=",";
	  		$sql .="`".$data[$i]["field"]."`";
	  	}
		$sql .=") values (";
	  	for ($i=0;$i<sizeof($data);$i++) {
	  		if ($i!=0) $sql .=",";
	  		if ($data[$i]["type"]!="n") $sql .="'";
	  		if ($data[$i]["type"]!="n") 
	  			$sql .=$this->replaceSpecialChars($data[$i]["value"]);
	  		else { 
		  		if ($data[$i]["value"]!="")
	  				$sql .=$data[$i]["value"];
				else
					$sql .=0;
	  		}
	  		if ($data[$i]["type"]!="n") $sql .="'";
	  	}
		$sql .=")";
	    if ($this->result=mysqli_query($this->connection,$sql)) {
	   		return true;
	   	}
	   	else {
	   		 echo (mysqli_error($this->connection));
	   		 return false;
	   	}
	}
  
	/* Update */
	public function update($table, $data, $whereField="", $whereValue="") {
	  	$sql="update ".$table." set ";
	  	$notFirstElement=false;
	  	for ($i=0;$i<sizeof($data);$i++) {
	  		if ($notFirstElement) $sql .=",";
	  		$notFirstElement = true;
	  		$sql .="`".$data[$i]["field"]."`=";
	  		if ($data[$i]["type"]!="n") $sql .="'";
	  		if ($data[$i]["type"]!="n") 
	  			$sql .=$this->replaceSpecialChars($data[$i]["value"]);
	  		else { 
		  		if ($data[$i]["value"]!="")
	  				$sql .=$data[$i]["value"];
				else
					$sql .=0;
	  		}
	  		if ($data[$i]["type"]!="n") $sql .="'";
	  	}
	  	if ($whereField!="") {
			$sql.=" where ".$whereField."=".$whereValue;
	  	}
	  	
	   	if ($this->result=mysqli_query($this->connection,$sql)) {
	   		return true;
	   	}
	   	else {
	   		echo (mysqli_error($this->connection));
	   		return false;
	   	}
	}
   	  
   	  public function delete($table, $whereField, $whereValue) {
   	  	$sql="delete from ".$table." where ".$whereField."=".$whereValue;
   	  	if ($this->result=mysqli_query($this->connection,$sql)) {
   	  		return true;
   	  	}
   	  	else {
   	  		echo (mysqli_error());
   	  		return false;
   	  	}
   	  }
   	  
   	  private function mysqli_result($res, $row, $field=0) {
   	  	$res->data_seek($row);
   	  	$datarow = $res->fetch_array();
   	  	return $datarow[$field];
   	  }
   	  
   	  private function replaceSpecialChars($s) {
   	  	return str_replace("'", "\'", $s);
   	  }
   	  
}
?>