<?php
/**
 * Data access layer for the classmate database
 */

/* |Framework fields | id | changeUserID | changeDate | changeIP | changeForID |
*  |normal entry     | *  | *            | *          | *        | NULL        |  
*  |anonymous change | *  | NULL         | *          | *        | *           |  
*  |anonymous new    | *  | NULL         | *          | *        | NULL        |  

select all 			where changeUserID is not null
select anonymous	where changeUserID is null and IP=*

In the save functions use id=-1 to insert a new entry 

*/
include_once 'tools/mysql.class.php';
include_once 'tools/logger.class.php';

/**
 * data change types 
 */
class changeType
{
	const login = 0;
	const message = 1;
	const personupload = 2;
	const personchange = 3;
	/**
	 * @var Changes für class pictures
	 */
	const classchange = 4;
	const classupload = 5;
	const deletepicture= 6;
	/**
	 * 
	 * @var create a new user
	 */
	const newuser= 7;
}

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

//************************ School *******************************************
	
	public function getSchoolById($id,$forceThisID=false) {
		return $this->getEntryById("school", $id,$forceThisID);
	}
	
	public function getSchoolByText($text) {
		return $this->getEntryByField("school", "text",$text);
	}
	
	public function getSchoolIdByText($text) {
		$ret=$this->getEntryByField("school", "text",$text);
		return $ret["id"];
	}
	
	
//************************ Class ******************************************* 	

	public function getClassById($id,$forceThisID=false) {
		return $this->getEntryById("class", $id,$forceThisID);
	}
	
	public function getClassByText($text) {
		return $this->getEntryByField("class", "text",$text);
	}
	
	public function getClassIdByText($text) {
		$ret=$this->getEntryByField("class", "text",$text);
		return $ret["id"];
	}
	
	/**
	 * save a class
	 * @param unknown $class
	 * @return number
	 */
	public function saveClass($class ) {
		$this->createHistoryEntry("class",$class["id"]);
		return $this->saveEntry("class", $class);
	}
	
	/**
	 * Get the list of classes for a schoool
	 */
	public function getClassList($schoolID=1) {
		return   $this->getElementList("class","schoolID=".$schoolID,null,"text asc");
	}
	
	/**
	 * Delete a class
	 * @param unknown $id
	 */
	public function deleteClass($id) {
		$this->createHistoryEntry("class",$id,true);
		return  $this->dataBase->delete("class", "id", $id);
	}
	
	public function searchForClass($name) {
		$ret = array();
		$nameItems=explode(' ', trim($name));
		foreach ($nameItems as $nameWord) {
			if (strlen(trim($nameWord))>2) {
				$ret=array_merge($ret,$this->searchForClassOneString($nameWord));
			}
		}
		return $ret;
	}
	
	/**
	 * Search for class by year name 
	 * @param unknown $name
	 */
	private function searchForClassOneString($name) {
		$ret = array();
		$name=trim($name);
		if( strlen($name)>1) {
			$sql="select c.*, p.firstname as tfname, p.lastname as tlname, p.birthname as tbname,p.picture as picture,";
			$sql .=" cp.id as cid, cp.firstname as cfname, cp.lastname as clname, cp.birthname as cbname, cp.role as role";
			$sql .=" from class as c"; 
			$sql .=" join  person as p on c.headTeacherID=p.id";
			$sql .=" join  person as cp on c.changeUserID=cp.id where";
			$sql .=" c.name like '%".$name."%' ";
			$sql .=" or c.graduationYear like '%".$name."%' ";
			$sql .=" or c.text like '%".$name."%' ";
			$this->dataBase->query($sql);
			while ($class=$this->dataBase->fetchRow()) {
					array_push($ret, $class);
			}
			foreach ($ret as $i=>$r) {
				$picture=$this->getGroupPictureByClassID($r["id"]);
				if(isset($picture["id"])) {
					$ret[$i]["classPictureID"]=$picture["id"];
				} else {
					$ret[$i]["classPictureID"]=false;
				}
				
			}
			asort($ret);
		}
		return $ret;
	}
	
	
	/**
	 * List of temporary classes
	 */
	public function getClassListToBeChecked() {
		$sql ="select c.*, o.id as changeForIDjoin from class as c ";
		$sql.="left join class as o on c.changeForID=o.id  ";
		$sql.="where c.changeUserID is null ";
		$sql.="order by c.changeDate asc";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$ret= $this->dataBase->getRowList();
			return $ret;
		} else
			return array();
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
	 * If user is anonymous create a new entry as a change   
	 * @return integer if negativ an error occurs
	 */
	public function savePerson($person) {
		return $this->saveEntry("person", $person);
	}
	
	public function savePersonFacebookId($id,$facebookId) {
		$this->createHistoryEntry("person",$id);
		$this->dataBase->update("person", [["field"=>"facebookid","type"=>"s","value"=>$facebookId]],"id",$id);	
	}
	
	/**
	 * save changes on only one field 
	 * @return positiv integer if the save operation is succeded 
	 */
	public function savePersonField($personId,$fieldName,$fieldValue=null) {
		$person=$this->getPersonByID($personId);
		if ($person!=null) {
			if ($fieldName==null )
				unset($person[$fieldName]);
			else
				$person[$fieldName]=$fieldValue;
			return $this->savePerson($person);
		}
		return -1;
	}
	
	/**
	 * Get persons,pictures from a class
	 * @param int $classId
	 * @return stdClass
	 */
	public function getClassStatistics($classId) {
		$ret = new stdClass();
		$ret->personCount=$this->dataBase->queryInt("select count(id) from person where classID=".$classId." and changeForID is null");
		$ret->personWithPicture=$this->dataBase->queryInt("select count(id) from person where classID=".$classId." and changeForID is null and picture != 'avatar.jpg'");
		$ret->personPictures=$this->dataBase->queryInt("select count(id) from picture where personID in (select id from person where classID=".$classId." and changeForID is null ) and changeForID is null ");
		$ret->classPictures=$this->dataBase->queryInt("select count(id) from picture where classID =".$classId." and changeForID is null ");
		return $ret;
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

	public function getPersonByLastnameFirstname($name) {
		if ($name==null || trim($name)=="")
			return null;
		$n=explode(' ', trim($name));
		if (sizeof($n)==2) {
			$person = $this->getEntryByField("person", "lastname", $n[0]);
			if ($person!=null) {
				if ($person->firstname==$n[1])
					return $person;
			}
		} else {
			$person = $this->getEntryByField("person", "lastname", $n[0]);
			if ($person!=null) {
				return $person;
			}
		}
		return null;
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
		$nameItems=explode(' ', trim($name));
		foreach ($nameItems as $nameWord) {
			if (strlen(trim($nameWord))>2) {
				$ret=$this->personMerge($ret,$this->searchForPersonOneString($nameWord));
			}
		}
		usort($ret, "compareAlphabeticalPicture");
		return $ret;
	}
	
	private function personMerge($array1,$array2) {
		$ret=array();
		if (sizeof($array1)==0)
			return  $array2;
		if (sizeof($array2)==0)
			return  $array1;
		foreach ($array1 as $person1) {
			foreach ($array2 as $person2) {
				if ($person1["id"]==$person2["id"]) {
					array_push($ret,$person1);
				}
			}
		}
		return $ret;
	}
	
	private function searchForPersonOneString($name) {
		$ret = array();
		$name=trim($name);
		if( strlen($name)>1) {
			
			$sql="select person.*, class.graduationYear as scoolYear, class.name as scoolClass from person left join  class on class.id=person.classID where ";  
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
	 * get the list of classmates
	 * @param integer classId 
	 * @param boolean guest default false 
	 * @param boolean withoutFacebookId default false
	 */
	public function getPersonListByClassId($classId,$guest=false,$withoutFacebookId=false) {
		if (null!=$classId) {
			$where ="classID=".$classId;
			$where.=" and (person.changeUserID is not null or person.changeIP ='".$_SERVER["REMOTE_ADDR"]."')";
			if($guest)
				$where.=" and role like '%guest%'";
			else
				$where.=" and role not like '%guest%'";
			if ($withoutFacebookId) {
				$where.=" and (facebookid is null or length(facebookid)<5) ";
			}
			$ret = $this->getElementList("person",$where);
			usort($ret, "compareAlphabetical");
			return $ret;
		}
		return array();
	}	

	/**
	 * get the person list!
	 */
	public function getPersonList() {
		$where=" (person.changeUserID is not null or person.changeIP ='".$_SERVER["REMOTE_ADDR"]."')";
		$ret = $this->getElementList("person",$where);
		usort($ret, "compareAlphabetical");
		return $ret;
	}

	/**
	 * get the recently updated person list
	 */
	public function getRecentChangedPersonList($limit) {
		$where=" (person.changeUserID is not null or person.changeIP ='".$_SERVER["REMOTE_ADDR"]."')";
		$ret = $this->getElementList("person",$where,$limit,"changeDate desc");
		usort($ret, "compareAlphabeticalPicture");
		return $ret;
	}
	
	/**
	 * List of temporary persons
	 */
	public function getPersonListToBeChecked() {
		$sql ="select c.*, o.id as changeForIDjoin from person as c ";
		$sql.="left join person as o on c.changeForID=o.id  ";
		$sql.="where c.changeUserID is null ";
		$sql.="order by c.changeDate asc";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$ret= $this->dataBase->getRowList();
			return $ret;
		} else 
			return array();
	}	
	
	/**
	 * Delete person from database and the picture 
	 * @return boolean
	 */
	public function deletePersonEntry( $id) {
		$this->createHistoryEntry("person",$id,true);
		$person=$this->getPersonByID($id,true);
		$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/";
		$file=$person["picture"];
		if ($file!="avatar.jpg")
			$ret1 =unlink($fileFolder.$file);
		else 
			$ret1=true;
		$ret2= $this->dataBase->delete("person", "id", $id);
		return $ret1 && $ret2;
	}

	
	/**
	 * Accept the anonymous changes for a table entry
	 * @param string $table the table name
	 * @param int $id the id of the entry that contains the changes
	 * @return boolean
	 */
	public function acceptChangeForEntry($table,$id) {
		$p=$this->dataBase->querySignleRow("select * from ".$table." where id=".$id);
		if (sizeof($p)>0) {
			if (isset($p["changeForID"])) {
				$p["id"]=$p["changeForID"]; 
				//make history entry 
				$this->createHistoryEntry($table,$p["id"]);
				unset($p["changeForID"]);
				$p["changeUserID"]=getLoggedInUserId();
				$p["changeIP"]=$_SERVER["REMOTE_ADDR"];
				if ($this->dataBase->delete($table, "id", $id))
					return $this->updateEntry($table, $p)>=0;
				else 
					return false;
			} else {
				$this->createHistoryEntry($table,$p["id"]);
				$p["changeUserID"]=getLoggedInUserId();
				$p["changeIP"]=$_SERVER["REMOTE_ADDR"];
				return $this->updateEntry($table, $p)>=0;
			}
		} else {
			return false;			
		}
	}
	
	
	public function getCountOfPersons($classId,$guests) {
		if(null!=$classId) {
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
		return 0;
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
	 * @return integer, negativ if an error occurs
	 */
	public function savePicture($picture) {
		$newEntry=$picture["id"]==-1;
		$id = $this->saveEntry("picture", $picture);
		if ($newEntry and $id>=0) {
			$this->dataBase->update("picture", [["field"=>"orderValue","type"=>"n","value"=>$id]],"id",$id);
		}
		return $id;
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
	
	/**
	 * Get list of pictures
	 * @param integer $id if null get all pictures of a type
	 * @param string $type the type of picture personID, classID, schoolID
	 * @return array of pictures  
	 */
	 public function getListOfPictures($id,$type,$isDeleted=0,$isVisibleForAll=1) {
		$sql="";
		if($id!=null)
			$sql.=$type."=".$id;
		else
			$sql.=$type." is not null ";
		if ($isDeleted<2) {
			$sql.=" and isDeleted=".$isDeleted;}
		if ($isVisibleForAll<2) {
			$sql.=" and isVisibleForAll=".$isVisibleForAll; }
		return $this->getElementList("picture",$sql,null,"orderValue desc");	
	}
	
	/**
	 * Get list of pictures by where
	 * @param string where 
	 * @return array of pictures  
	 */
	 public function getListOfPicturesWhere($where="") {
		$sql="";
		$sql.="isDeleted=0 ";
		if ($where!="")
			$sql.=" and ".$where; 
		return $this->getElementList("picture",$sql,null,"title asc");	
	}
	
	public function changePictureOrderValues($id1,$id2) {
		$orderValue1=$this->dataBase->queryInt("select orderValue from picture where id=".$id1);
		$orderValue2=$this->dataBase->queryInt("select orderValue from picture where id=".$id2);
		$data=array();
		$data=$this->dataBase->insertFieldInArray($data, "orderValue", $orderValue1);
		$this->dataBase->update("picture", $data, "id", $id2);
		$data=$this->dataBase->changeFieldInArray($data, "orderValue", $orderValue2);
		$this->dataBase->update("picture", $data, "id", $id1);
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

	public function getGroupPictureByClassID($id) {
		$sql="select * from picture where title like '%Tabló%' and classID=".$id;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else {
			return array();
		}
	}
	
	/**
	 * List of all not deleted pictures
	 */
	public function getPictureList() {
		return   $this->getElementList("picture","isDeleted=0");
	}
	
	/**
	 * List of recent not deleted pictures
	 */
	public function getRecentPictureList($limit) {
		return   $this->getElementList("picture","isDeleted=0",$limit,"uploadDate desc");
	}
	
	
	/**
	 * Get a picture by id
	 * @param integer $id
	 * @return array or null if no picture found
	 */
	public function getPictureById($id) {
		$sql="select * from picture where id=".$id;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else {
			return null;
		}
	}
	
	public function getNextPictureId() {
		return $this->dataBase->getNextAutoIncrement("picture");
	}
	
	/**
	 * Delete picture from database and the picture
	 * @return boolean
	 */
	public function deletePictureEntry( $id) {
		$p=$this->getPictureById($id);
		$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/";
		$file=$p["file"];
		$ret1 =unlink($fileFolder.$file);
		$this->createHistoryEntry("picture",$id,true);
		$ret2= $this->dataBase->delete("picture", "id", $id);
		return $ret1 && $ret2;
	}
	
	public function getPictureListToBeChecked() {
		$sql ="select c.*, o.id as changeForIDjoin from picture as c ";
		$sql.="left join picture as o on c.changeForID=o.id  ";
		$sql.="where c.changeUserID is null ";
		$sql.="order by c.changeDate asc";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$ret= $this->dataBase->getRowList();
			return $ret;
		} else
			return array();
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
		return $this->saveEntry("vote", $entry);
	}

	
//******************** Song  DAO *******************************************

	public function deleteVote($voteId) {
		$this->createHistoryEntry("songvote",$voteId,true);
		return $this->dataBase->delete("songvote", "id",$voteId);
	}
	
	public function saveSongVote($entry) {
		return $this->saveEntry("songvote",$entry);
	}
	
	public function saveInterpret($entry) {
		return $this->saveEntry("interpret", $entry);
	}

	public function saveSong($entry) {
		return $this->saveEntry("song", $entry);
	}
	
	public function updateSong($id,$value,$field) {
		$this->createHistoryEntry("song",$id);
		return $this->dataBase->update("song", [["field"=>$field,"type"=>"s","value"=>$value]],"id",$id);
	}
	
	public function updateSongFields($id,$video,$name) {
		$this->createHistoryEntry("song",$id);
		$data=array();
		$data=$this->dataBase->insertFieldInArray($data, "video", $video);
		$data=$this->dataBase->insertFieldInArray($data, "name", $name);
		return $this->dataBase->update("song", $data,"id",$id);
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
	
	public function getSongList($interpretId=0) {
		if ($interpretId>0) {
			return $this->getElementList("song","interpretID=".$interpretId);
		} else {
			return $this->getElementList("song");
		}
	}

	public function getInterpretList() {
		return $this->getElementList("interpret",null,null,"name asc");
	}
	
	/*
	 * get Voterslist by class id
	 */
	public function getVotersListByClassId($classId) {
		$sql  ="select  person.id, person.lastname, person.firstname, person.picture, count(1) as count "; 
		$sql .="from songvote join person on person.id=songvote.personID ";
		$sql .="where person.classID=".$classId." group by personID";
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
		$this->createHistoryEntry("message",$id);
		return $this->updateEntry("message", $entry);
	}
	
	public function saveMessage($entry) {
		return $this->saveEntry("message");
	}
	
	public function saveNewMessage($entry) {
		$entry["id"]=-1;
		return $this->saveEntry("message", $entry);
	}
	
	/**
	 * save messager comment
	 * @param int $id
	 * @param string $comment
	 * @return boolean
	 */
	public function saveMessageComment($id,$comment) {
		$this->createHistoryEntry("message",$id);
		return $this->dataBase->update("message", [["field"=>"comment","type"=>"s","value"=>$comment]],"id",$id);
	}
	
	public function saveMessagePersonID($id,$uid) {
		$this->createHistoryEntry("message",$id);
		return $this->dataBase->update("message", [
					["field"=>"changeUserID","type"=>"n","value"=>$uid],
					["field"=>"name","type"=>"s","value"=>""]
				],"id",$id);
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
		$this->createHistoryEntry("message",$id,true);
		return $this->dataBase->delete("message", "id", $id);
	}
	
	/**
	 * Accept the anonymous message entrys 
	 * @return integer, negativ value in case of on error 
	 */
	public function acceptChangeForMessage($id) {
		$p=$this->dataBase->querySignleRow("select * from message where id=".$id);
		$p["changeUserID"]=-1;
		$this->createHistoryEntry("message",$id);
		return $this->updateEntry("message", $p);
	}

//********************* Request ******************************************

	/**
	 * reset requests for an ip an type
	 * @param integer $type
	 * @param string $ip
	 */
	public function deleteRequest($type,$ip) {
		$where="typeID=".$type." and ip='".$ip."'";
		return $this->dataBase->deleteWhere("request", $where);
	}
	
	/**
	 * get the amount of requests
	 * @param changeType $type
	 * @param integer $hours
	 */
	public function getCountOfRequest($type,$hours=0) {
		$sql="SELECT count(1) FROM request";
		$sql .=" where typeID=".$type;
		$sql .=" and ip='".$_SERVER["REMOTE_ADDR"]."'";
		if ($hours>0) {
			$sql .=" and date>'".date("Y-m-d H:i:s",strtotime("-".$hours." hours"))."'";
		}
		$r =$this->dataBase->queryInt($sql);
		return $r;
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->fetchRow();
			return intval($r["count"]);
		} else {
			return 0;
		}
	}
	
	/**
	 * List of requests grouped by IP and type
	 */
	public function getListOfRequest($hours=0) {
		$sql="SELECT count(1) as count,typeID,ip,date FROM request";
		if ($hours>0) {
			$newDate = new DateTime();
			$newDate =$newDate->sub(new DateInterval('PT'.$hours.'H')); 
			$sql .=" where date>'".$newDate->format("Y-m-d H:i:s")."'";
		}
		$sql .=" group by typeID,ip order by ip,typeID"; 
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			return $this->dataBase->getRowList();
		} else {
			return array();
		}
	}

	/**
	 * Save request
	 * @param  changeType $type
	 */
	public function saveRequest($type) {
		if (!userIsLoggedOn()) {
			$data=array();
			$data=$this->dataBase->insertFieldInArray($data, "ip", $_SERVER["REMOTE_ADDR"]);
			$data=$this->dataBase->insertFieldInArray($data, "date", date("Y-m-d H:i:s"));
			$data=$this->dataBase->insertFieldInArray($data, "typeID", $type);
			$this->dataBase->insert("request", $data);
		}
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
		//normal entrys
		$sql="select id from ".$table." where ( (changeForID is null and changeUserID is not null) ";
		//anonymous new entrys from the aktual ip
		$sql.=" or (changeForID is null and changeIP='".$_SERVER["REMOTE_ADDR"]."' and changeUserID is null)  )";
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
	public function getEntryById($table,$id,$forceThisID=false) {
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
	 * If user is anonymous create a new entry as a change   
	 * @return integer if negativ an error occurs
	 */
	private function saveEntry($table,$entry) {
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
				$this->createHistoryEntry($table,$entry["id"]);
				if ($this->dataBase->update($table,$data,"id",$entry["id"])) {
					return $entry["id"];
				} else 
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
			if ($this->dataBase->insert($table,$data))
				return $this->dataBase->getInsertedId();
			else 
				return -1;
		}
	}
	
	
	public function getPersonChangeBest() {
		$sql="select count(1) as count, changeUserID as uid from history where changeUserID!=0 and `table`='person' group by changeUserID limit 20";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			$r1=array();
			foreach ($r as $s) {
				$r1[$s["uid"]]=$s["count"];
			}
		} 
		$ret = $this->mergeBestArray(array(),$r1,2);

		$sql="select count(1) as count, changeUserID as uid from person where changeUserID !=0 group by  changeUserID limit 20";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			$r2=array();
			foreach ($r as $s) {
				$r2[$s["uid"]]=$s["count"];
			}
		} 
		$ret = $this->mergeBestArray(array(),$r2,2);
		
		$sql="select count(1) as count, changeUserID as uid from picture where changeUserID !=0 group by  changeUserID limit 20";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			$r3=array();
			foreach ($r as $s) {
				$r3[$s["uid"]]=$s["count"];
			}
		} 
		$ret = $this->mergeBestArray($ret,$r3,5);
		$rets=array();
		for($i=0;$i<12;$i++) {
			$value=0;
			foreach ($ret as $uid=>$count) {
				if($count>$value) {
					$value=$count;
					$vuid=$uid;
				}
			}
			$rets[$vuid]=$value;
			unset($ret[$vuid]);
		}
		return $rets;
	}
	
	private function mergeBestArray($a1,$a2,$factor=1) {
		foreach ($a2 as $idx=>$a) {
			if(isset($a1[$idx]))
				$a1[$idx]+=$a*$factor;
			else
				$a1[$idx]=$a*$factor;
		}
		return $a1;
	}
	
	/**
	 * get history info
	 */
	public function getHistoryInfo($table,$id) {
		$sql="select id from history where `table`='".$table."' and entryID=".$id;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			return $this->dataBase->getRowList();
		} else {
			return array();
		}
	}
	
	/**
	 * get history 
	 */
	public function getHistory($table,$id) {
		if(null!=$table && null!=$id) {
			$sql="select * from history where `table`='".$table."' and entryID=".$id." order by id desc ";
		} elseif(null!=$table) {
			$sql="select * from history where `table`='".$table."' order by entryID desc, id desc limit 1000";
		} else {
			$sql="select * from history  order by `table` asc, entryID desc, id desc limit 1000";
		}
			
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			return $this->dataBase->getRowList();
		} else {
			return array();
		}
	}
	
	/**
	 * Create a history entry in the history table
	 * @param unknown $table
	 * @param unknown $id
	 */
	private function createHistoryEntry($table,$id,$delete=false) {
		$entry=$this->dataBase->querySignleRow("select * from ".$table." where id=".$id);
		if (sizeof($entry)==0) 
			return -16;
		$data = array();
		$data=$this->dataBase->insertFieldInArray($data, "entryID", $id);
		$data=$this->dataBase->insertFieldInArray($data, "table", $table);
		$data=$this->dataBase->insertFieldInArray($data, "jsonData", json_encode((object)$entry),JSON_HEX_TAG+JSON_HEX_AMP+JSON_HEX_APOS+JSON_HEX_QUOT);
		$data =$this->dataBase->insertFieldInArray($data,"changeIP", $_SERVER["REMOTE_ADDR"]);
		$data =$this->dataBase->insertFieldInArray($data,"changeDate", date("Y-m-d H:i:s"));
		if (getLoggedInUserId()>=0) {
			$data =$this->dataBase->insertFieldInArray($data,"changeUserID", getLoggedInUserId());
		}  
		$data =$this->dataBase->insertFieldInArray($data,"deleted", $delete?1:0 );
		return $this->dataBase->insert("history", $data);
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