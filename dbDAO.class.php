<?php
/* |Framework fields | id | changeUserID | changeDate | changeIP | changeForID |
*  |normal entry     | *  | *            | *          | *        | NULL        |  
*  |anonymous change | *  | NULL         | *          | *        | *           |  
*  |anonymous new    | *  | NULL         | *          | *        | NULL        |  

select all 				where changeUserID is not null
select where anonymous	where changeUserID is null and IP=* 

*/
include_once 'tools/mysql.class.php';
include_once 'tools/logger.class.php';

class dbDAO {
	private $dataBase = NULL;


	public function __construct(){
		//Connect to the DB
		if (strpos($_SERVER["SERVER_NAME"],"lue-l.de")>0 || strpos($_SERVER["SERVER_NAME"],".online.de")>0) {
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
	/**
	 * Insert or update a person
	 * If the person has a sekond primary key exists then force an update
	 * If user is anonymous create a new entry as a change   
	 * @return integer if negativ an error occurs
	 */
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

	/**
	 * get a person by unsername
	 * @return NULL is no person found 
	 */
	public function getPersonByUser($username,$nullChangeForID=true) {
		return $this->getEntryByField("person", "user", $username,$nullChangeForID);
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
	
	/**
	 * Returns a signle person in consideration of the anonymous changes or NULL if no entry found
	 */
	public function getPersonByID($personid,$forceThisID=false) {
		return $this->getEntryById("person", $personid,$forceThisID);
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
			$sql .=" and (person.changeUserID is not null or person.changeIP ='".$_SERVER["REMOTE_ADDR"]."')";
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
			$sql .=" and person.changeIP ='".$_SERVER["REMOTE_ADDR"]."'";
			$this->dataBase->query($sql);
			while ($person=$this->dataBase->fetchRow()) {
				if (stristr(html_entity_decode($person["lastname"]), $name)!="" ||
					stristr(html_entity_decode($person["firstname"]), $name)!="" ||
					(isset($person["birthname"]) && stristr(html_entity_decode($person["birthname"]), $name)!="")) {
					
					$arrayIdx = array_search($person["changeForID"], array_column($ret,"id"));
					unset($ret[$arrayIdx]);	
					array_push($ret, $person);
				}
			}
			usort($ret, "compareAlphabetical");
		}
		return $ret;
	}
	
	/**
	 * gel the list of classmates 
	 */
	public function getPersonListByClassId($classId) {
		$where ="classID=".$classId;
		$where.=" and (person.changeUserID is not null or person.changeIP ='".$_SERVER["REMOTE_ADDR"]."')";
		$ret = $this->getElementList("person",$where);
		usort($ret, "compareAlphabetical");
		return $ret;
	}	
	
	public function getPersonListToBeChecked() {
		$sql="select c.*, o.id as changeForIDjoin from person as c left join person as o on c.changeForID=o.id   where c.changeUserID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$ret= $this->dataBase->getRowList();
			usort($ret, "compareAlphabetical");
			return $ret;
		} else 
			return array();
	}	
	
	/**
	 * Delete person from database 
	 * @return boolean
	 */
	public function deletePersonEntry( $id) {
		return $this->dataBase->delete("person", "id", $id);
	}
	
	/**
	 * Accept the anonymous changes for a person
	 * @return boolean
	 */
	public function acceptChangeForPerson($id) {
		$p=$this->dataBase->querySignleRow("select * from person where id=".$id);
		if (sizeof($p)>0) {
			if (isset($p["changeForID"])) {
				$p["id"]=$p["changeForID"];
				unset($p["changeForID"]);
				$p["changeUserID"]=getAktUserId();
				if ($this->dataBase->delete("person", "id", $id))
					return $this->updateEntry("person", $p)>=0;
				else 
					return false;
			} else {
				$p["changeUserID"]=getAktUserId();
				return $this->updateEntry("person", $p)>=0;
			}
		} else 
			return false;
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
		$sql .=" and (person.changeUserID is not null or person.changeIP ='".$_SERVER["REMOTE_ADDR"]."')";
		if ($classId==0)
			$sql .=" and isTeacher = 1";
		$this->dataBase->query($sql);
		return $this->dataBase->count();
	}
	
	public function getPersonIdListWithPicture() {
		$where="picture is not null and picture not like '%avatar%'";
		$where .=" and (changeUserID is not null or changeIP ='".$_SERVER["REMOTE_ADDR"]."')";
		
		return $this->getIdList("person",$where);
	}
	
//******************** Picture DAO *******************************************
	
	/**
	 * Save picture
	 * @param array $picture
	 * @param string $whereSecondPrimaryKey
	 * @return integer, negativ if an error occurs
	 */
	public function savePicture($picture,$whereSecondPrimaryKey=null) {
		return $this->saveEntry("picture", $picture,$whereSecondPrimaryKey);
	}
	
	public function savePictureField($id,$personId,$classId,$schoolId,$file,$isVisibleForAll,$title,$comment,$uploadDate,$isDeleted=0) 
	{
		if ($personId==null)
			return -1;
		$picture = array();
		$picture["id"]=$id;
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

	
//******************** Song  DAO *******************************************

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

//********************* Message ******************************************

	public function getMessages($count) {
		return $this->getElementList("message",null,$count,"changeDate desc");
	}
	
	/**
	Returns a signle entry or NULL if no entry found
	 */
	public function getMessage($id) {
		return $this->getEntryById("message", $id);		
	}

	public function setMessageAsDeleted($id) {
		$entry=array();
		$entry["id"]=$id;
		$entry["isDeleted"]=1;
		return $this->updateEntry("message", $entry);
	}
	
	public function saveMessage($entry) {
		return $this->saveEntry("message", $entry, "text ='".$this->dataBase->replaceSpecialChars($entry["text"])."'");
	}
	
	public function saveNewMessage($entry) {
		$entry["id"]=-1;
		return $this->saveEntry("message", $entry);
	}
	
	public function getMessageListToBeChecked()
	{
		$sql ="select message.*, person.lastname, person.firstname, person.birthname from message";
		$sql.=" left join person on message.changeUserID=person.id";
		$sql.=" where message.changeUserID is null or isDeleted=1";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$ret= $this->dataBase->getRowList();
			return $ret;
		} else
			return array();
	}
	
	/**
	 * Delete message from the db
	 * @return boolean
	 */
	public function deleteMessageEntry( $id) {
		return $this->dataBase->delete("message", "id", $id);
	}
	
	/**
	 * Accept the anonymous message entrys 
	 * @return integer, negativ value in case of on error 
	 */
	public function acceptChangeForMessage($id) {
		$p=$this->dataBase->querySignleRow("select * from message where id=".$id);
		if (sizeof($p)>0) {
			$p["id"]=-1;
			unset($p["changeForID"]);
			if ($this->dataBase->delete("message", "id", $id)) {
				$newid = $this->saveEntry("message",$p,"text='".$p["text"]."'");
				if ($newid>=0) {
					$update = array();
					$update["id"]=$newid;
					$update["changeUserID"]=-1;
					return $this->updateEntry("message", $update);
				} else
					return $newid;
			} else
				return  -12;
		} else
			return -11;
	}
	
	
//********************* Private ******************************************	
	
	/**
	 * get a array of elements, or an empty array if no elements found
	 */
	private function getElementList($table,$where=null,$limit=null,$orderby=null) {
		$rows=$this->getIdList($table,$where,$limit,$orderby);
		$ret=array();
		foreach ($rows as $row) {
			array_push($ret, $this->getEntryById($table, $row["id"]));
		}
		return $ret;
	}
	
	/**
	 * get a array of ids, or an empty array if no ids found
	 */
	private function getIdList($table,$where=null,$limit=null,$orderby=null) {
		$sql="select id from ".$table." where ( changeForID is null ";
		$sql.=" or (changeForID =-1 and changeIP='".$_SERVER["REMOTE_ADDR"]."') )";
		if ($where!=null)
			$sql.=" and ".$where;
		if ($orderby!=null)
			$sql.=" order by ".$orderby;
		if ($limit!=null)
			$sql.=" limit ".$limit;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			return $this->dataBase->getRowList();
		} else {
			return array();
		}
	}
	
	
	/**
	 * Returns a signle entry from a table in consideration of the anonymous changes or NULL if no entry found
	 * even if the copy is returned the id will be from the original
	 * @return the entry of null if not found
	 */
	private function getEntryById($table,$id,$forceThisID=false) {
		//First get the foced entry by the id
		if ($forceThisID==true) {
			$sql="select * from ".$table.' where id='.$id;
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				return  $this->dataBase->fetchRow();
			} else 
				return null;
		}
		//First get the original entry by the id
		$sql="select * from ".$table.' where id='.$id." and changeForID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			$entry = $this->dataBase->fetchRow();
			//Check if a changed version for the ip is available
			$sql="select * from ".$table." where changeIP='".$_SERVER["REMOTE_ADDR"]."' and changeForID =".$id;
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				return  $this->dataBase->fetchRow();
			}
			return $entry;
		//Try to get the copy
		} else { 
			$sql="select * from ".$table.' where id='.$id." and changeForID is not null and changeIP='".$_SERVER["REMOTE_ADDR"]."'";
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				return $this->dataBase->fetchRow();
			} else 
				return null;
		}
	}
	
	/**
	 * get a db entry by a field
	 * @return NULL is no entry found 
	 */
	private function getEntryByField($table,$fieldName,$fieldValue) {
		$sql="select * from ".$table." where ".$fieldName."='".trim($fieldValue)."'";
		$sql .=" and changeForID is null";
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
	 * @return integer if negativ an error occurs
	 */
	private function saveEntry($table,$entry,$whereSecondPrimaryKey=null) {
		//Build the change data array
		$data = array();
		foreach ($entry as $fieldName=>$fieldValue) {
			if ($fieldName!="id" && $fieldName!="changeForID") {
				$data =$this->dataBase->insertFieldInArray($data,$fieldName, $fieldValue);
			}
		}
		$data =$this->dataBase->changeFieldInArray($data,"changeIP", $_SERVER["REMOTE_ADDR"]);
		$data =$this->dataBase->changeFieldInArray($data,"changeDate", date("Y-m-d H:i:s"));
		if (getLoggedInUserId()>=0) {
			$data =$this->dataBase->changeFieldInArray($data,"changeUserID", getLoggedInUserId());
		} else {
			$data =$this->dataBase->setFieldInArrayToNull($data,"changeUserID");
		}
		
		//Update
		if ($entry["id"]>=0) {
			//User is loggen on
			if (getLoggedInUserId()>=0) {
				//Update the entry
				if ($this->dataBase->update($table,$data,"id",$entry["id"]))							
					return $entry["id"];
				else 
					return -5;
			//Anonymous user
			} else {
				$dbentry=$this->getEntryById($table, $entry["id"]);
				if(isset($dbentry["changeUserID"])) {
					//Insert an anonymous copy 
					$data =$this->dataBase->changeFieldInArray($data,"changeForID", $entry["id"]);
					if ($this->dataBase->insert($table,$data))
						return $this->dataBase->getInsertedId();
					else 
						return -4;
				} else {
					//Update the anonymous entry
					if ($this->dataBase->update($table,$data,"id",$entry["id"]))							
						return $dbentry["id"];
					else 
						return -7;
				}
			}
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
				}
			}
			//Insert
			if ($this->dataBase->insert($table,$data))
				return $this->dataBase->getInsertedId();
			else 
				return -1;
		}
	
	}
	
	/**
	 * update one entry returns -1 for anny error
	 */
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