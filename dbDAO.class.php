<?php
/**
 * Data access layer for the classmate database
 * Tables: picture,person, class, school, message, request
*/

use maierlabs\lpfw\MySqlDbAUH ;

include_once 'lpfw/mysqldbauh.class.php';
include_once 'lpfw/logger.class.php';
include_once 'lpfw/ltools.php';
include_once 'config.class.php';


class dbDAO {
    /**
     * @var MySqlDbAUH|null
     */
	public $dataBase = NULL;

    /**
     * dbDAO constructor.
     */
    public function __construct($dataBase){
        $this->dataBase=$dataBase;
    }

    /**
     * Disconnect from database
     */
    public function disconnect() {
		$this->dataBase->disconnect();
	}

    /**
     * SQL Statement to select the anonymous changes
     * @param string $table
     * @return string
     */
	private function getSqlAnonymous($table=null)
    {
        if ($table!=null)
            return $table."changeForID is not null and ".$table."changeUserID is null and ".$table."changeIP='".$_SERVER["REMOTE_ADDR"]."'";
        else
            return "changeForID is not null and changeUserID is null and changeIP='".$_SERVER["REMOTE_ADDR"]."'";
    }


//************************ School *******************************************

    /**
     * @param int $id
     * @param bool $forceThisID
     * @return array
     */
    public function getSchoolById($id, $forceThisID=false) {
		return $this->getEntryById("school", $id,$forceThisID);
	}

    /**
     * @param string $text
     * @return array
     */
    public function getSchoolByText($text) {
		return $this->getEntryByField("school", "text",$text);
	}

    /**
     * @param string $text
     * @return mixed
     */
    public function getSchoolIdByText($text) {
		$ret=$this->getEntryByField("school", "text",$text);
		return $ret["id"];
	}
	
	
//************************ Class ******************************************* 	

	/**
	 * this class id is for staf only
     * @param  integer  $classId
	 * @return boolean
	 */
	public function isClassIdForStaf($classId) {
		$ret = $this->getEntryById("class", $classId);
		return $ret["graduationYear"]==0;
	}
	
	/**
	 * get class by id
	 * @param integer $id
	 * @param boolean $forceThisID
	 * @return array or null
	 */
	public function getClassById($id,$forceThisID=false) {
		return $this->getEntryById("class", $id,$forceThisID);
	}


	public function getTeachersIdByClassId($id) {
        $class=$this->getClassById($id);
        $teachers = explode(',',$class["teachers"]);
        if (isset($class["headTeacherID"]))
            $teachers[]=$class["headTeacherID"];
        return $teachers;
    }

    /**
     * Select the classes where the teacher was active
     * @param $id
     * @return array
     */
	public function getClassListByTeacherID($id) {
	    $sql="select * from class where id in  (select classID from person where teachers like '%".$id."%')";
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    /**
     * Select the classes where the teacher was the head teacher
     * @param $id
     * @return array
     */
    public function getClassListByHeadTeacherID($id) {
        $sql="select * from class where headTeacherID =".$id;
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    public function getClassByText($text) {
		$sql="select * from class where text='".trim($text)."'";
		$sql .=" and changeForID is null";
		$sql .=" and schoolId =".getAktSchoolId();
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			$entry = $this->dataBase->fetchRow();
			return $this->getEntryById('class', $entry["id"]);
		} else {
			return null;
		}
	}
	
	public function getClassIdByText($text) {
		$ret=$this->getClassByText($text);
		if (null==$ret)
			return null;
		return $ret["id"];
	}

	public function getStafClassIdBySchoolId($schoolId) {
		$ret=$this->getStafClassBySchoolId($schoolId);
		if (null==$ret)
			return null;
		return $ret["id"];
	}

	public function getStafClassBySchoolId($schoolId) {
		$ret=$this->getEntry("class", "schoolID=".$schoolId." and graduationYear=0");
		return $ret;
	}
	
	
	/**
	 * save a class
	 * @param array $class
	 * @return number
	 */
	public function saveClass($class ) {
		$this->createHistoryEntry("class",$class["id"]);
		return $this->saveEntry("class", $class);
	}

    /**
     * Get the list of classes for a schoool
     * @param int $schoolID
     * @return array
     */
	public function getClassList($schoolID=1,$originalId=false) {
		return   $this->getElementList("class",$originalId, "schoolID=".$schoolID." and graduationYear <> 0",null,null,"text asc");
	}
	
	/**
	 * Delete a class
	 * @param int $id
     * @return boolean
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
	 * @param string $name
     * @return array
	 */
	private function searchForClassOneString($name) {
		$ret = array();
		$name=trim($name);
		if( strlen($name)>1) {
			$sql="select * from class where";
			$sql .=" name like '%".$name."%' ";
			$sql .=" or graduationYear like '%".$name."%' ";
			$sql .=" or text like '%".$name."%' ";
			$sql .=" limit 50";
				
			$this->dataBase->query($sql);
			while ($class=$this->dataBase->fetchRow()) {
					array_push($ret, $class);
			}
			asort($ret);
		}
		return $ret;
	}
	
	
//************************** Person *******************************************
	/**
	 * Insert or update a person
	 * If user is anonymous create a new entry as a change   
	 * @return integer if negativ an error occurs
	 */
	public function savePerson($person) {
	    if (isset($person["deceasedYear"]) && $person["deceasedYear"]=='') {
            $person["deceasedYear"]=null;
        }
	    $ret =$this->saveEntry("person", $person);
	    $this->updateRecentChangesList();
		return $ret;
	}
	
	public function savePersonFacebookId($id,$facebookId) {
		$this->createHistoryEntry("person",$id);
		$this->dataBase->update("person", [["field"=>"facebookid","type"=>"s","value"=>$facebookId]],"id",$id);	
	}

	public function savePersonGeolocation($id,$lat,$lng) {
		if (!userIsAdmin()) {
			$this->createHistoryEntry("person",$id);
		}
		$this->dataBase->update("person", [["field"=>"geolat","type"=>"s","value"=>$lat],["field"=>"geolng","type"=>"s","value"=>$lng]],"id",$id);
	}
	
	/**
	 * save changes on only one field 
	 * @return integer -1 on error or person id if operation is succeded
	 */
	public function savePersonField($personId,$fieldName,$fieldValue=null) {
		$person=$this->getPersonByID($personId);
		if ($person!=null) {
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
	public function getClassStatistics($classId,$countPictures=true) {
		$ret = new stdClass();
		$ret->personCount=$this->dataBase->queryInt("select count(id) from person where classID=".$classId." and changeForID is null");
        $ret->classPictures=$this->dataBase->queryInt("select count(id) from picture where classID =".$classId." and changeForID is null ");
        $ret->guestCount=$this->dataBase->queryInt("select count(id) from person where classID=".$classId." and changeForID is null and role like '%guest%'");
		if($countPictures) {
			$ret->personWithPicture=$this->dataBase->queryInt("select count(id) from person where classID=".$classId." and changeForID is null and picture is not null and picture<>''");
			$ret->personPictures=$this->dataBase->queryInt("select count(id) from picture where personID in (select id from person where classID=".$classId." and changeForID is null ) and changeForID is null ");
		}
		$t = $this->dataBase->querySignleRow("select firstname, lastname,picture from person left join class on class.headTeacherID=person.id where class.id=".$classId);
		if ($t!=null) {
		    $ret->teacher=(object)$t;
		}
		return $ret;
	}

	/**
	 * get a person by unsername
	 * @return NULL is no person found 
	 */
	public function getPersonByUser($username) {
		return $this->getEntryByField("person", "user", $username);
	}

	/**
	 * get a person by email
	 * @param string $email
	 * @return array person or NULL
	 */
	public function getPersonByEmail($email) {
		if ($email==null || trim($email)=="")
			return null;
		$person = $this->getEntryByField("person", "email", $email);
		//the protected email
		if ($person==null)
			$person = $this->getEntryByField("person", "email", "~".$email);
		return $this->getPersonByID(getRealId($person));
	}

	/**
	 * get a person by lastname and firstname eg. Müller Peter
	 * @param string lastname firstname
	 * @return array person or NULL
	 */
	public function getPersonByLastnameFirstname($name) {
		if ($name==null || trim($name)=="")
			return null;
		$n=explode(' ', trim($name));
		if (sizeof($n)==2) {
			$person = $this->getEntryByField("person", "lastname", $n[0]);
			if ($person!=null) {
				if ($person["firstname"]==$n[1])
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
	
	/**
	 * get a person by Facebook ID
	 * @param string facebookid
	 * @return array person or NULL
	 */
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
	    if ($personid!=null && intval($personid)>=0)
		    return $this->getEntryById("person", $personid,$forceThisID);
	    return null;
	}
	
	/**
 	* Search for person lastname and firstname 
 	* @param string $name
     *@return array
 	*/
	public function searchForPerson($name) {
		$ret = array();
		$nameItems=explode(' ', trim($name));
		foreach ($nameItems as $nameWord) {
				$ret=$this->personMerge($ret,$this->searchForPersonOneString($nameWord));
		}
		usort($ret, "compareAlphabetical");
		return $ret;
	}
	
	/**
	 * Merge two person lists and return a list of persons that existst in both of the input array
	 * If one of the input arrays are empty the return the other one
	 * @param array $array1
	 * @param array $array2
     * @return array
	 */
	private function personMerge($array1,$array2) {
		$ret=array();
		if (sizeof($array1)==0)
			return  $array2;
		if (sizeof($array2)==0)
			return  $array1;
		foreach ($array1 as $person1) {
			foreach ($array2 as $person2) {
				if ($person1["id"]==$person2["id"]) {
                    if (array_search($person1["id"],array_column($ret,"id"))===false) {
                        array_push($ret, $person1);
                    }
				}
			}
		}
		return $ret;
	}
	
	/**
	 * search for persons using one word name
	 * @param string $name
     * @return array
	 */
	private function searchForPersonOneString($name) {
		$ret = array();
        $name=trim($name);
        if( intval($name)>1930 && intval($name)<2100 ) {
            $where =  "graduationYear=".$name;
        } elseif( strlen($name)>0 ) {

            $where ="(";
            $name = searchSpecialChars($name);
            $where .=" person.lastname rlike '".$name."' ";
            $where .="  or person.firstname rlike '".$name."' ";
            $where .="  or person.birthname rlike '".$name."' ";
            $where .=" )";

		} else {
                $where = " class.id=".getAktClassId();
        }
        $sql  ="select person.*, class.graduationYear as scoolYear, class.eveningClass, class.name as scoolClass from person";
        $sql .=" left join  class on class.id=person.classID";
        $sql .=" where (graduationYear != 0 or isTeacher = 1)";		//No administator users
        $sql .=" and ( person.changeForID is null and ".$where;
        $sql .=" and person.id not in ( select changeForID from person where  ".$this->getSqlAnonymous("person.")." ) ";
        $sql.=") or (".$this->getSqlAnonymous("person.")."and ".$where.") limit 150";

        $this->dataBase->query($sql);
        while ($person=$this->dataBase->fetchRow()) {
            if (isset($person["changeForID"]))
                $person["id"]=$person["changeForID"];
            if (array_search($person["id"],array_column($ret,"id"))===false)
                array_push($ret, $person);
        }
        usort($ret, "compareAlphabetical");
		return $ret;
	}
	
	/**
	 * get the list of classmates
	 * @param integer classId 
	 * @param boolean guest default false
	 * @param boolean withoutFacebookId default false
	 * @param boolean all default false all entrys
     * @return array
	 */
	public function getPersonListByClassId($classId,$guest=false,$withoutFacebookId=false,$all=false, $notDied=false) {
		if ($classId>=0) {
			$where ="classID=".$classId;
			if (!$all) {
				if($guest)
					$where.=" and role like '%guest%'";
				else
					$where.=" and role not like '%guest%'";
				if ($withoutFacebookId) {
					$where.=" and (facebookid is null or length(facebookid)<5) ";
				}
			}
			if ($notDied==true) {
                $where.=" and deceasedYear is null";
            }
			$ret = $this->getElementList("person",false,$where);
			usort($ret, "compareAlphabetical");
			return $ret;
		}
		return array();
	}


    /**
     * If you need to strip as many national characters from UTF-8 as possible and keep the rest of input unchanged
     * (i.e. convert whatever can be converted to ASCII and leave the rest)
     * @param string $s
     * @return string
     */
    private function clearUTF($s)
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $r = '';
        $s1 = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        for ($i = 0; $i < strlen($s1); $i++)
        {
            $ch1 = $s1[$i];
            $ch2 = mb_substr($s, $i, 1);

            if ($ch1!="'" && $ch1!='"')
                $r .= $ch1=='?'?$ch2:$ch1;
        }
        return $r;
    }

    /**
     * If you need to strip as many national characters from UTF-8 as possible and keep the rest of input unchanged
     * (i.e. convert whatever can be converted to ASCII and leave the rest)
     * @param string $s
     * @return string
     */
    private function wildcardUTF($s)
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $r = '';
        $s1 = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        for ($i = 0; $i < strlen($s1); $i++)
        {
            $ch1 = $s1[$i];
            $ch2 = mb_substr($s, $i, 1);

            if ($ch1!="'" && $ch1!='"')
                $r .= $ch1=='?'?$ch2:$ch1;
            else
                $r .= '_';
        }
        return $r;
    }

    /**
	 * get the person list!
	 */
	public function getPersonList($where=null,$limit=null,$ofset=null,$order=null,$field="*",$join=null) {
		$ret = $this->getElementList("person",false,$where,$limit,$ofset,$order,$field,$join);
		return $ret;
	}

	/**
	 * get sorted person list!
	 */
	public function getSortedPersonList($where=null,$limit=null,$ofset=null) {
		$ret = $this->getElementList("person",false,$where,$limit,$ofset);
		usort($ret, "compareAlphabeticalTeacher");
		return $ret;
	}
	
	/**
	 * get entry count
     * @param string $table
	 * @param string $where
	 * @return integer
	 */
	public function getTableCount($table,$where=null) {
		//normal entrys
		$sql="select count(1) from ".$table." where ( (changeForID is null and changeUserID is not null) ";
		//anonymous new entrys from the aktual ip
		$sql.=" or (changeForID is null and changeIP='".$_SERVER["REMOTE_ADDR"]."' and changeUserID is null)  )";
		if ($where!=null)
			$sql.=" and ".$where;
		return $this->dataBase->queryInt($sql);
	}
	

	/**
	 * List of temporary changes made by anonymous users
     * @param string $table
     * @return array
	 */
	public function getListToBeChecked($table) {
		$sql ="select c.*, o.id as changeForIDjoin from ".$table." as c ";
		$sql.="left join ".$table." as o on c.changeForID=o.id  ";
		$sql.="where c.changeUserID is null ";
		$sql.="order by c.changeDate asc";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			return $this->dataBase->getRowList();
		} else 
			return array();
	}

    /**
     * Count of temporary changes made by anonymous users
     * @param string $table
     * @return int
     */
    public function getCountToBeChecked($table) {
        $sql ="select count(1) from ".$table." as c ";
        $sql.="left join ".$table." as o on c.changeForID=o.id  ";
        $sql.="where c.changeUserID is null ";
        $sql.="order by c.changeDate asc";
        return $this->dataBase->queryInt($sql);
    }


    /**
	 * Delete person from database and the picture 
	 * @return boolean
	 */
	public function deletePersonEntry( $id ) {
		$this->createHistoryEntry("person",$id,true);
		$person=$this->getPersonByID($id,true);
        if(isset($person["changeForID"]))
            $pOriginal=$this->getPersonByID($person["changeForID"]);
        else
            $pOriginal=null;

        $delete =    (null==$pOriginal && isset($person["picture"]))
                  || (null!=$pOriginal && isset($pOriginal["picture"]) && isset($person["picture"]) && $pOriginal["picture"]!=$person["picture"])
                  || (null!=$pOriginal && !isset($pOriginal["picture"]) && isset($person["picture"]) );
        if ($delete) {
	    	$fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/images/";
			$ret1 =unlink($fileFolder.$person["picture"]);
        } else {
			$ret1=true;
        }
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
		if ($p!=null) {
			//Accept a change
			if (isset($p["changeForID"])) {
				$objID=$p["changeForID"];
				//make history entry 
				$this->createHistoryEntry($table,$objID);
				//Delete the temporary entry
				if ($this->dataBase->delete($table, "id", $id)) {
					//Update the entry
					$p["id"]=$objID; 			//the object entry
					$p["changeUserID"]=-1;  	//anonym user
					unset($p["changeForID"]);   //remove flag changes for id
					return $this->updateEntry($table, $p)>=0;
				} else { 
					return false;
				}
			} else
			//Accept a new entry
			{
				$this->createHistoryEntry($table,$p["id"]);
				$p["changeUserID"]=-1;
				return $this->updateEntry($table, $p)>=0;
			}
		} else {
			return false;			
		}
	}
	
	
	public function getCountOfPersons($classId,$guests) {
		if(null!=$classId) {
			$sql =" classID = ".$classId;
			if ($guests)
				$sql .=" and role like '%guest%'";
			else
				$sql .=" and not(role like '%guest%')";
			if ($this->isClassIdForStaf($classId))
				$sql .=" and isTeacher = 1";
			return sizeof($this->getIdList("person",$sql));
		}
		return 0;
	}
	
	public function getPersonIdListWithPicture() {
		$where="picture is not null and picture != ''";
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
		if ($newEntry && $id>=0) {
			$this->dataBase->update("picture", [["field"=>"orderValue","type"=>"n","value"=>$id],["field"=>"isDeleted","type"=>"i","value"=>0]],"id",$id);
		}
        $this->updateRecentChangesList();
		return $id;
	}
	
	/**
	 * Get list of pictures
	 * @param integer $id if null get all pictures of a type
	 * @param string $type the type of picture personID, classID, schoolID
	 * @return array of pictures  
	 */
	 public function getListOfPictures($id,$type,$album=null,$orderby=null,$limit=null,$offset=null) {
		$sql="";
		if($id!=null)
			$sql.=$type."=".$id;
		else
			$sql.=$type." is not null ";
		if (null!=$album) {
			$sql.=" and albumName='".$album."'"; 
		} else {
			$sql.=" and (albumName is null or albumName='')";
		}
		return $this->getElementList("picture",false,$sql,$limit,$offset,$orderby);
	}


    /**
     * Get list of pictures by where
     * @param string where
     * @return array of pictures
     */
    public function getListOfPicturesWhere($where="",$orderby=null,$limit=null,$offset=null) {
        $sql="";
        $sql.="isDeleted=0 ";
        if ($where!="")
            $sql.=" and ".$where;
        return $this->getElementList("picture",false, $sql,$limit,$offset,$orderby);
    }

    /**
     * Get list of pictures
     * @param integer $id if null get all pictures of a type
     * @param string $type the type of picture personID, classID, schoolID
     * @return int
     */
    public function getNrOfPictures($id,$type,$album=null) {
        $sql="";
        if($id!=null)
            $sql.=$type."=".$id;
        else
            $sql.=$type." is not null ";
        $sql .= " and changeForID is null";
        if (null!=$album) {
            $sql.=" and albumName='".$album."'";
        }
        return $this->getTableCount("picture",$sql);
    }

    /**
     * Get the number of pictures that belongs to the person and the tagged pictures
     * @param int $id
     * @return int
     */
    public function getNrOfPersonPictures($id) {
        return $this->getNrOfPictures($id,'personID')+$this->dataBase->queryInt("select count(1) from personInPicture where personID=".$id);
    }

    /*
	 * get the list of picture albums 
	 */
	public function getListOfAlbum($type,$typeId,$startList=array()) {
		$sql = " where ".$type."=".$typeId. " and albumName != ''";
		$sql="select distinct(albumName) as albumName, albumName as albumText from picture".$sql;
		$this->dataBase->query($sql);
		return array_merge($startList,$this->dataBase->getRowList());
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

	public function changePictureAlbumName($id,$albumName) {
		$data=array();
		$data=$this->dataBase->insertFieldInArray($data, "albumName", $albumName);
		return $this->dataBase->update("picture", $data, "id", $id);
	}
	
	/**
	 * change picture album name
	 * @param string $type 'classID' or 'schoolID' or 'personID'
	 * @param integer $typeId the ID
	 * @param string $oldAlbumName
	 * @param string $newAlbumName
     * @return boolean
	 */
	public function changeAlbumName($type, $typeId, $oldAlbumName,$newAlbumName) {
		$data=array();
		$data=$this->dataBase->insertFieldInArray($data, "albumName", $newAlbumName);
		$where = $type."='".$typeId."' and albumName='".$oldAlbumName."'";
		return $this->dataBase->updateWhere("picture", $data, $where);
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

    /**
     * get class group picture
     * @param $id
     * @return array|nullg
     */
	public function getGroupPictureIdByClassId($id) {
		$sql="select id from picture where title like '%Tabló%' and classID=".$id;
		return $this->dataBase->queryInt($sql);
	}
	
	/**
	 * List of all not deleted pictures
	 */
	public function getPictureList($where=null) {
		if (null==$where) {
			return   $this->getElementList("picture",false,"isDeleted=0");
		} else {
			return   $this->getElementList("picture",false,"isDeleted=0 and ".$where);
		}
	}
	
	/**
	 * List of recent not deleted pictures
	 */
	public function getRecentPictureList($limit) {
		return   $this->getElementList("picture",false,"isDeleted=0",$limit,null,"uploadDate desc");
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
     * the picture will be not deleted if the entry is annonymous entry an the original entry has the same picture
	 * @return boolean
	 */
	public function deletePictureEntry( $id ) {
		$p=$this->getPictureById($id);
		if(isset($p["changeForID"]))
            $pOriginal=$this->getPictureById($p["changeForID"]);
		else
		    $pOriginal=null;

		$delete = null==$pOriginal || ( null!=$pOriginal && $pOriginal["file"]!=$p["file"]);
		if ($delete) {
		    $fileFolder=dirname($_SERVER["SCRIPT_FILENAME"])."/";
		    $file=$p["file"];
		    $ret1 =unlink($fileFolder.$file);
		} else {
		    $ret1 = true;
        }
		$this->createHistoryEntry("picture",$id,true);
		$ret2= $this->dataBase->delete("picture", "id", $id);
		return $ret1 && $ret2;
	}
	
	public function searchForPicture($name) {
		$retintersect = array();
        $retmerge = array();
		$nameItems=explode(' ', trim($name));
		foreach ($nameItems as $idx=>$nameWord) {
			if (strlen(trim($nameWord))>2) {
			    $search=$this->searchForPictureOneString($nameWord);
			    if ($idx==0)
                    $retintersect = $search;
			    else
			        $retintersect=$this->array_array_intersect($retintersect,$search);
                $retmerge = array_merge($retmerge,$search);
			}
		}
		if (sizeof($retintersect)>0)
		    return $retintersect;
		else
		    return $retmerge;
	}

	public function array_array_intersect($a1,$a2) {
	    $ret=array();
	    foreach ($a2 as $a) {
	        $keys = array_column($a1,"id");
	        $key= array_search($a["id"],$keys);
	        if ($key!==false) {
	            array_push($ret,$a);
            }
        }
	    return $ret;
    }
	/**
	 * Search for class by year name
	 * @param string $name
     * @return array
	 */
	private function searchForPictureOneString($name) {
		$ret = array();
		$name=trim($name);
		if( strlen($name)>1) {
		    $name = searchSpecialChars($name);
			$sql="select p.* from picture as p";
			//$sql .=" left join  person as cp on c.changeUserID=cp.id where";
            $sql .=" where p.title rlike '".$name."' ";
            $sql .="  or p.comment rlike '".$name."' ";
			$sql .=" limit 50";
			$this->dataBase->query($sql);
			while ($class=$this->dataBase->fetchRow()) {
				array_push($ret, $class);
			}
			asort($ret);
		}
		return $ret;
	}

//********************************[ Accelerator ]*****************************************************************

    public function getRecentChangesListByDate($dateFrom, $limit,$filter='all',$ip=null, $userid=null) {
        $rows=array();
        $sqlIpUser="";$sqlCandleIpUser="";
        if ($ip!=null) {
            $sqlIpUser .=" and changeIP='".$ip."' ";
            $sqlCandleIpUser .=" and ip='".$ip."' ";
        }
        if ($userid!=null) {
            $sqlIpUser .=" and changeUserID='".$userid."' ";
            $sqlCandleIpUser .=" and userID='".$userid."' ";
        }
        if (in_array($filter,array("all"))) {
            $sql = " (select id, changeDate, 'person' as type, 'change' as action, changeUserID from person where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'";
            $sql .= $sqlIpUser." and ( changeForID is null or changeIP='" . $_SERVER["REMOTE_ADDR"] . "') order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("teacher"))) {
            $sql = " (select id, changeDate, 'person' as type, 'change' as action, changeUserID from person where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'";
            $sql .= $sqlIpUser." and ( changeForID is null or changeIP='" . $_SERVER["REMOTE_ADDR"] . "') and isTeacher=1 order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("person"))) {
            $sql = " (select id, changeDate, 'person' as type, 'change' as action, changeUserID from person where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'";
            $sql .= $sqlIpUser." and ( changeForID is null or changeIP='" . $_SERVER["REMOTE_ADDR"] . "') and isTeacher=0 order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","picture"))) {
            $sql = " (select id, changeDate, 'picture' as type, 'change' as action, changeUserID from picture where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'";
            $sql .= $sqlIpUser." and ( changeForID is null or changeIP='" . $_SERVER["REMOTE_ADDR"] . "') and (isDeleted=0) order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","opinion"))) {
            $sql = " (select entryID as id, changeDate, `table` as type, 'opinion' as action, changeUserID from opinion where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlIpUser." order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","class"))) {
            $sql = " (select id, changeDate, 'class' as type, 'change' as action, changeUserID from class where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlIpUser." order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","family"))) {
            $sql = " (select id1 as id, changeDate, 'person' as type, 'family' as action, changeUserID from family where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlIpUser." order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","candle"))) {
            $sql = " (select personID as id, lightedDate as changeDate, 'person' as type, 'candle' as action, userID as changeUserID from candle where lightedDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlCandleIpUser." order by lightedDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","tag"))) {
            $sql = " (select pictureID as id, changeDate, 'picture' as type, 'marked' as action, changeUserID from personInPicture where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlIpUser." order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        //Order list by change date
        usort($rows,function($a,$b) {
            return ($b["changeDate"]<=>$a["changeDate"]);
        });
        //Remove duplicate entrys and keep the newest
        $keylist = array();
        $ret = array();
        foreach ($rows as $row) {
            $key=$row["type"].$row["action"].$row["id"];
            if (!in_array($key, $keylist)) {
                array_push($keylist, $key);
                array_push($ret,$row);
                if (sizeof($ret)==$limit)
                    break;
            }
        }
        return $ret;
	}

    public function updateRecentChangesList()
    {
        return;
    }



//********************* Message ******************************************

	/**
	 * get chat messages
	 */
	public function getMessages($limit=null,$offset=null) {
		return $this->getElementList("message",false,"classID is null",$limit,$offset,"changeDate desc");
	}

	/**
	 * get class messages
	 */
	public function getClassMessages($classId,$limit=null,$offset=null) {
		return $this->getElementList("message",false,"classID =".$classId,$limit,$offset,"changeDate desc");
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
		return $this->saveEntry("message",$entry);
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
		if ($p!=null) {
		    $p["changeUserID"]=-1;
		    $this->createHistoryEntry("message",$id);
		    return $this->updateEntry("message", $p);
		}
		return -1;
	}

//********************* Request ******************************************

	/**
	 * reset requests for an ip an type
	 * @param integer $type
	 * @param string $ip
     * @return boolean
	 */
	public function deleteRequest($type,$ip) {
		$where="typeID=".$type." and ip='".$ip."'";
		return $this->dataBase->deleteWhere("request", $where);
	}
	
	/**
	 * get the amount of requests
	 * @param integer $type
	 * @param integer $hours
     * @return integer
	 */
	public function getCountOfRequest( $type,$hours=0) {
		$sql="SELECT count(1) FROM request";
		$sql .=" where typeID=".$type;
		$sql .=" and ip='".$_SERVER["REMOTE_ADDR"]."'";
		if ($hours>0) {
			$sql .=" and date>'".date("Y-m-d H:i:s",strtotime("-".$hours." hours"))."'";
		}
		$r =$this->dataBase->queryInt($sql);
		return $r;
	}
	
	/**
	 * List of requests grouped by IP and type
	 */
	public function getListOfRequest($hours=0) {
		$sql="SELECT count(1) as count,typeID,ip,date FROM request";
		if ($hours>0) {
			$newDate = new DateTime();
            try {
                $newDate = $newDate->sub(new DateInterval('PT' . $hours . 'H'));
            } catch (Exception $e) {
                logger($e->getMessage(),loggerLevel::error);
            }
            $sql .=" where date>'".$newDate->format("Y-m-d H:i:s")."'";
		}
		$sql .=" group by typeID,ip order by ip,typeID"; 
		$this->dataBase->query($sql);
        return $this->dataBase->count() > 0 ? $this->dataBase->getRowList() : array();
	}

	/**
	 * Save request if user is not loggen on
	 * @param  integer  $type changeType
     * @param boolean $forceCount increment request counter even the user is logged on
     * @return void
	 */
	public function saveRequest($type,$forceCount=false) {
		if (!userIsLoggedOn() || $forceCount) {
			$data=array();
			$data=$this->dataBase->insertFieldInArray($data, "ip", $_SERVER["REMOTE_ADDR"]);
			$data=$this->dataBase->insertFieldInArray($data, "date", date("Y-m-d H:i:s"));
			$data=$this->dataBase->insertFieldInArray($data, "typeID", $type);
			$this->dataBase->insert("request", $data);
		}
	}
	
	
//********************* Private ******************************************	

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
        $sql.=" or (".$this->getSqlAnonymous($jtable).") )";
		if ($where!=null)		$sql.=" and ( ".$where." )";
		if ($orderby!=null)		$sql.=" order by ".$orderby;
		if ($limit!=null)		$sql.=" limit ".$limit;
		if ($offset!=null)		$sql.=" offset ".$offset;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
            $ret = $this->dataBase->getRowList();
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
        $this->dataBase->query($sql);
        if ($this->dataBase->count()>0) {
            //Change the entrys with the anonymous entrys
            $anyonymous=$this->dataBase->getRowList();
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
	private function getIdList($table, $where=null, $limit=null, $offset=null, $orderby=null) {
		return $this->getElementList($table,false,$where,$limit,$offset,$orderby,"id");
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
			return  $this->dataBase->querySignleRow($sql);
		}
		//First get the entry modified by the aktual ip and then the original entry, the original entry has allways a smaler id then a copy
		$sql="select * from ".$table.' where id='.$id." or (changeIP='".$_SERVER["REMOTE_ADDR"]."' and changeForID =".$id.") order by id desc";
		if ($this->dataBase->query($sql)) {
			$ret =  $this->dataBase->getRowList();
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
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			$entry = $this->dataBase->fetchRow();
			return $this->getEntryById($table, $entry["id"]);
		} else {
			return null;
		}
	}

	/**
	 * get a db entry by a field
	 * @return array | NULL is no entry found
	 */
	public function getEntry($table,$where) {
		$sql="select id from ".$table." where ".$where;
		$sql .=" and changeForID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
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
	public function saveEntry($table,$entry) {
		//Build the change data array
		$data = array();
		foreach ($entry as $fieldName=>$fieldValue) {
			if ($fieldName!="id" && $fieldName!="changeForID") {
				$data =$this->dataBase->insertFieldInArray($data,$fieldName, $fieldValue);
			}
		}
		$data = $this->dataBase->insertUserDateIP($data);
		//Update
		if ($entry["id"]>=0) {
			//User is loggen on
			if (userIsLoggedOn()) {
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
	
	
    /**
     * get history info
     * @param string $table
     * @param  int $id
     * @return array
     */
    public function getHistoryInfo($table,$id) {
        return $this->dataBase->getHistoryInfo($table,$id);
    }

    /**
     * get history
     * @param string $table
     * @param  int $id
     * @return array
     */
    public function getHistory($table,$id) {
        return $this->dataBase->getHistory($table,$id);
    }

    /**
     * delete history entry and restore the change date from source entry
     * @param int $id
     * @return bool
     */
    public function deleteHistoryEntry($id) {
        return $this->dataBase->deleteHistoryEntry($id);
    }

    /**
     * Create a history entry in the history table
     * @param string $table
     * @param string $id
     * @param boolean $delete
     * @return boolean
     */
    public function createHistoryEntry($table,$id,$delete=false)  {
        return $this->dataBase->createHistoryEntry($table,$id,$delete);
    }

    /**
     * update one entry returns -1 for anny error
     * @param string $table
     * @param array $entry
     * @return int
     */
    public function updateEntry($table,$entry) {
        return $this->dataBase->updateEntry($table,$entry);
    }


    public function getRequestCounter() {
		return $this->dataBase->getCounter();
	}


  }