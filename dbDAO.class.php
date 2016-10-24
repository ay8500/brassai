<?php
include_once 'mysql.class.php';
include_once 'logger.class.php';

class dbDAO {
	private $dataBase = NULL;


	public function __construct(){
		//Connect to the DB
		if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0) {
			$this->dataBase = new MySqlDb('db652851844.db.1and1.com','db652851844','dbo652851844','levi1967');
		} else { 
			$this->dataBase = new MySqlDb('localhost',"db652851844",'root','root');
		}
	}

	public function disconnect() {
		$this->dataBase->disconnect();
	}

//************************ Class ******************************************* 	

	public function getClassById($id) {
		return $this->getEntryById("class", $id);
	}
	
	public function getClassByText($text) {
		return $this->getEntryByField("class", "text",$text);
	}
	
	public function saveClass($class ) {
		$where="name='".$this->dataBase->replaceSpecialChars($class["name"])."' and graduationYear=".$class["graduationYear"];
		return $this->saveEntry("class", $class,$where);
	}
	
	public function getClassList() {
		return   $this->getElementList("class");
	}
	
	/**
	 * Use in combination with getQueryRow if you want to make a loop over all personen 
	 * @return NULL
	 */
	public function queryPersons() { 	
		$sql="select * from person where changeForID is null";
		$this->dataBase->query($sql);
		return $this->dataBase->count();
	}
	
	/**
	 * Use in combination with queryPersons if you want to make a loop over all personen 
	 * @return NULL
	 */
	public function getQueryRow () {
		return $this->dataBase->fetchRow();
	}
	
//************************** Person *******************************************	
	
	public function savePerson($person,$whereSecondPrimaryKey=null) {
		return $this->saveEntry("person", $person,$whereSecondPrimaryKey);
	}
	
	public function savePersonField($personId,$fieldName,$fieldValue) {
		if ($fieldName==null || $fieldName=="")
			return -1;
		$person=$this->getPersonByID($personId);
		if ($person!=null) {
			$person[$fieldName]=$fieldValue;
			$this->savePerson($person);
		}
	}

	public function getPersonByUser($username) {
		return $this->getEntryByField("person", "user", $username);
	}

	public function getPersonByEmail($email) {
		if ($email==null || trim($email)=="")
			return null;
		$person = $this->getEntryByField("person", "email", $email);
		if ($person==null)
			$person = $this->getEntryByField("person", "email", "~".$email);
		return $person;
	}
	
	public function getPersonByFacobookId($fbid) {
		if ($fbid==null || trim($fbid)=="")
			return null;
		$sql="select * from person where facebookid ='".trim($fbid)."' and changeForID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return  $this->dataBase->fetchRow();
		} else
			return null;
	}
	
	public function getPersonByID($personid) {
		return $this->getEntryById("person", $personid);
	}
	
	/**
 	* Search for person lastname and firstname 
 	* @param unknown $name
 	*/
	public function searchForPerson($name) {
		$ret = array();
		$name=trim($name);
		if( strlen($name)>1) {
			
			$sql="select person.*, class.graduationYear as scoolYear, class.name as scoolClass from person join  class on class.id=person.classID where ";  
			$sql .=" (classID != 0 or isTeacher = 1)";
			$sql .=" and person.changeForID is null";
			$this->dataBase->query($sql);
			while ($person=$this->dataBase->fetchRow()) {
				if (stristr(html_entity_decode($person["lastname"]), $name)!="" ||
					stristr(html_entity_decode($person["firstname"]), $name)!="" ||
					(isset($person["birthname"]) && stristr(html_entity_decode($person["birthname"]), $name)!="")) {
					array_push($ret, $person);
				}
			}
			
			//Reorganise the list with the self change entrys
			$sql="select person.*, class.graduationYear as scoolYear, class.name as scoolClass from person join  class on class.id=person.classID where ";
			$sql .=" (classID != 0 or isTeacher = 1)";
			$sql .=" and person.changeForID is not null";
			$sql .=" and person.changeIP ='".$_SERVER["SERVER_ADDR"]."'";
			$this->dataBase->query($sql);
			while ($person=$this->dataBase->fetchRow()) {
				if (stristr(html_entity_decode($person["lastname"]), $name)!="" ||
					stristr(html_entity_decode($person["firstname"]), $name)!="" ||
					(isset($person["birthname"]) && stristr(html_entity_decode($person["birthname"]), $name)!="")) {
					
					$arrayIdx = array_search($person["changeForID"], array_column($ret,"id"));
					unset($ret[$arrayIdx]);	
					$person["id"]=$person["changeForID"];
					array_push($ret, $person);
				}
			}
			usort($ret, "compareAlphabetical");
		}
		return $ret;
	}
	
	public function getPersonListByClassId($classId) {
		$ret = $this->getElementList("person","classID=".$classId);
		usort($ret, "compareAlphabetical");
		return $ret;
	}	
	
	public function getPersonListToBeChecked() {
		$sql="select c.*, o.id as changeForIDjoin from person as c left join person as o on c.changeForID=o.id   where c.changeForID is not null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$ret= $this->dataBase->getRowList();
			usort($ret, "compareAlphabetical");
			return $ret;
		} else 
			return array();
	}	
	
	public function deletePersonEntry( $id) {
		$this->dataBase->delete("person", "id", $id);
	}
	
	public function acceptChangeForPerson($id) {
		$p=$this->dataBase->querySignleRow("select * from person where id=".$id);
		if (sizeof($p)>0) {
			$p["id"]=$p["changeForID"];
			unset($p["changeForID"]);
			$this->dataBase->delete("person", "id", $id);
			$this->updateEntry("person", $p);
		}
	}
	
	
	public function getCountOfPersons($classId,$guests) {
		$ret = array();
		$sql="select 1 from person where ";
		$sql .=" classID = ".$classId;
		if ($guests)
			$sql .=" and role like '%guest%'";
		else
			$sql .=" and not(role like '%guest%')";
		$sql .=" and changeForID is null ";
		if ($classId==0)
			$sql .=" and isTeacher = 1";
		$this->dataBase->query($sql);
		return $this->dataBase->count();
	}
	
//******************** Picture DAO *******************************************
	
	public function savePicture($picture,$whereSecondPrimaryKey=null) {
		return $this->saveEntry("picture", $picture,$whereSecondPrimaryKey);
	}
	
	public function savePictureField($id,$personId,$classId,$schoolId,$file,$isVisibleForAll,$title,$comment,$uploadDate,$isDeleted=0) 
	{
		$picture = array();
		$picture["id"]=$id;
		if ($personId!=null)
			$picture["personID"]=$personId;
		if ($classId!=null)
			$picture["classID"]=$classId;
		if ($schoolId!=null)
			$picture["schoolID"]=$schoolId;
		if ($file!=null)
			$picture["file"]=$file;
		if ($isVisibleForAll!=null)
			$picture["isVisibleForAll"]=$isVisibleForAll;
		if ($title!=null)
			$picture["title"]=$title;
		if ($comment!=null)
			$picture["comment"]=$comment;
		if ($isDeleted!=null)
			$picture["isDeleted"]=$isDeleted;
		if ($uploadDate!=null)
			$picture["uploadDate"]=$uploadDate;
		return $this->savePicture($picture);		
	}
	
	public function getListOfPictures($id,$type,$isDeleted,$isVisibleForAll) {
		$sql="select * from picture ";
		if ($type=="person")
			$sql.="where personId=".$id;
		if ($type=="class")
			$sql.="where classId=".$id;
		if ($type=="school")
			$sql.="where schoolId=".$id;
			if ($isDeleted<2) {
			$sql.=" and isDeleted=".$isDeleted;}
		if ($isVisibleForAll<2) {
			$sql.=" and isVisibleForAll=".$isVisibleForAll; }
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			return $this->dataBase->getRowList();
		} else {
			return array();
		}
	}
	
	public function getPictureByFileName($filename) {
		$sql="select * from picture where file='".$filename."'";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else {
			return array();
		}
	}
	
	public function getPictureById($id) {
		$sql="select * from picture where id=".$id;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else {
			return array();
		}
	}
	
	public function getNextPictureId() {
		return $this->dataBase->getNextAutoIncrement("picture");
	}

//******************** Vote DAO *******************************************
	
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
		return $this->saveEntry("vote", $entry,"personID=".$entry["personID"]." and meetAfterYear=".$entry["meetAfterYear"]);
	}

	
//******************** Song and Message DAO *******************************************

	public function saveMessage($entry) {
		return $this->saveEntry("message", $entry, "text ='".$this->dataBase->replaceSpecialChars($entry["text"])."'");
	}
	
	public function saveSongVote($entry) {
		return $this->saveEntry("songvote", $entry, "personID =".$entry["personID"]." and songID=".$entry["songID"]);
	}
	
	
	public function saveInterpret($entry) {
		return $this->saveEntry("interpret", $entry, "name ='".$this->dataBase->replaceSpecialChars($entry["name"])."'");
	}

	public function saveSong($entry) {
		return $this->saveEntry("song", $entry, "name ='".$this->dataBase->replaceSpecialChars($entry["name"])."'");
	}
	
	public function getSongById($id) {
		return $this->getEntryById("song", $id);
	}
	
	public function getSongByName($name) {
		return $this->getEntryByField("song","name", $name);
	}
	
	public function getInterpretById($id) {
		return $this->getEntryById("interpret", $id);
	}
	
	public function getInterpretByName($name) {
		return $this->getEntryByField("interpret", "name",$name);
	}
	
	public function getSongList() {
		return $this->getElementList("song");
	}

	public function getInterpretList() {
		return $this->getElementList("interpret");
	}
	
//********************* Private ******************************************	
	
	private function getElementList($table,$where=null) {
		$ret=array();
		$sql="select id from ".$table." where changeForID is null";
		if ($where!=null)
			$sql.=" and ".$where;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$rows = $this->dataBase->getRowList();
			foreach ($rows as $row) {
				array_push($ret, $this->getEntryById($table, $row["id"]));
			}
		}
		return $ret;
	}
	
	
	/**
	 * Returns a signle entry from a table in consideration of the anonymous changes
	 * @param unknown $table
	 * @param unknown $whereField
	 * @param unknown $whereValue
	 */
	private function getEntryById($table,$id) {
		//First get the entry by the id
		$sql="select * from ".$table.' where id='.$id." and changeForID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			$entry = $this->dataBase->fetchRow();
			//Check if a changed version for the ip is available
			$sql="select * from ".$table." where changeIP='".$_SERVER["SERVER_ADDR"]."' and changeForID =".$id;
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				$row = $this->dataBase->fetchRow();
				$row["idForSave"]=$row["id"];
				$row["id"]=$entry["id"];
				return $row;
			}
			return $entry;
		} else {
			$sql="select * from ".$table.' where id='.$id." and changeIP='".$_SERVER["SERVER_ADDR"]."' and changeForID is not null";
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				$entry = $this->dataBase->fetchRow();
				return $entry;
			} else {
				return null;
			}
		}
	}
	
	private function getEntryByField($table,$fieldName,$fieldValue) {
		$sql="select * from ".$table." where ".$fieldName."='".trim($fieldValue)."' and changeForID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			$entry = $this->dataBase->fetchRow();
			return $this->getEntryById($table, $entry["id"]);
		} else {
			return null;
		}
	}
	
	/**
	 * Insert or update a table entry
	 * If the entry has a sekond primary key exists then force an update
	 * If user is anonymous create a new entry as a change   
	 * @param unknown $table
	 * @param unknown $entry
	 * @param unknown $whereSecondPrimaryKey
	 */
	private function saveEntry($table,$entry,$whereSecondPrimaryKey=null) {
		//Build the change data array
		$data = array();
		foreach ($entry as $fieldName=>$fieldValue) {
			if ($fieldName!="id" && $fieldName!="changeForID") {
				$data =$this->dataBase->insertFieldInArray($data,$fieldName, $fieldValue);
			}
		}
		$data =$this->dataBase->changeFieldInArray($data,"changeIP", $_SERVER["SERVER_ADDR"]);
		$data =$this->dataBase->changeFieldInArray($data,"changeDate", date("Y-m-d H:i:s"));
		if (getLoggedInUserId()>=0) {
			$data =$this->dataBase->changeFieldInArray($data,"changeUserID", getLoggedInUserId());
		} else {
			$data =$this->dataBase->changeFieldInArray($data,"changeUserID", null);
			if ($entry["id"]>=0) 
				$data =$this->dataBase->changeFieldInArray($data,"changeForID", $entry["id"]);
			else 
				$data =$this->dataBase->changeFieldInArray($data,"changeForID", -1);
		}
		
		//Update
		if (!($entry["id"]==-1)) {
			$dbEntry = $this->getEntryById($table, $entry["id"]);
			if ($dbEntry!=null) {
				if (isset($dbEntry["idForSave"]) && $dbEntry["idForSave"]!=null) {
					//Update the copy
					$data =$this->dataBase->deleteFieldInArray($data,"idForSave");
					if ($this->dataBase->update($table,$data,"id",$dbEntry["idForSave"]))
						return $dbEntry["idForSave"];
					else 
						return -6;
				} else {
					if (getLoggedInUserId()>=0  ) {
						//Update the entry
						if ($this->dataBase->update($table,$data,"id",$dbEntry["id"]))							
							return $dbEntry["id"];
						else 
							return -5;
					} else {
						//Insert a copy
						$data = $this->dataBase->changeFieldInArray($data, "changeForID", $entry["id"]);
						if ($this->dataBase->insert($table,$data))
							return 0;
						else 
							return -4;
					}
				}
				return $dbEntry["id"];
			} else 
				return -1;
			
		} 
		//Insert
		else {
			if ($whereSecondPrimaryKey!=null) {
				$sql="select * from ".$table." where ".$whereSecondPrimaryKey." and changeForID is null";
				$this->dataBase->query($sql);
				if ($this->dataBase->count()==1) {
					$row=$this->dataBase->fetchRow();
					//Found a entry to update 
					if ($this->dataBase->update($table,$data,"id",$row["id"]))
						return $row["id"];
					else 
						return -7;
				} if ($this->dataBase->count()>1) {
					return -8;
				} else {
					//Insert
					$this->dataBase->insert($table,$data);
					$sql="select * from ".$table." where ".$whereSecondPrimaryKey." and changeForID is null";
					$this->dataBase->query($sql);
					if ($this->dataBase->count()==1) {
						$row=$this->dataBase->fetchRow();
						return $row["id"];
					} else
						return -9;
				}
			} else {
				if (getLoggedInUserId()>=0) {
					//Insert
					if ($this->dataBase->insert($table,$data))
						return 0;
					else 
						return -1;
				} else {
					//Insert a Copy
					$data = $this->dataBase->changeFieldInArray($data, "changeForID", -1);
					if ($this->dataBase->insert($table,$data))
						return 0;
					else 
						return -4;
				}
			}
		}
	
	}
	
	private function updateEntry($table,$entry) {
		//Build the change data array
		$data = array();
		foreach ($entry as $fieldName=>$fieldValue) {
			if ($fieldName!="id" && $fieldName!="changeForID") {
				$data =$this->dataBase->insertFieldInArray($data,$fieldName, $fieldValue);
			}
		}
		if ($this->dataBase->update($table,$data,"id",$entry["id"]))
			return 0;
		else
			return -1;
	}
}
?>