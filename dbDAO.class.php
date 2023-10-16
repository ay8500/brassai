<?php
/**
 * Data access layer for the classmate database
 * Tables: picture,person, class, school, message, request
*/

use maierlabs\lpfw\MySqlDbAUH ;
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'mysqldbauh.class.php';


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

//************************ School *******************************************

    /**
     * @param int $id
     * @param bool $forceThisID
     * @return array | null
     */
    public function getSchoolById($id, $forceThisID=false) {
		return $this->dataBase->getEntryById("school", $id,$forceThisID);
	}


	
//************************ Class ******************************************* 	

	/**
	 * this class id is for staf only
     * @param  integer  $classId
	 * @return boolean
	 */
	public function isClassIdForStaf($classId) {
		$ret = $this->dataBase->getEntryById("class", $classId);
		return $ret["graduationYear"]==0;
	}
	
	/**
	 * get class by id
	 * @param integer $id
	 * @param boolean $forceThisID
	 * @return array or null
	 */
	public function getClassById($id,$forceThisID=false) {
		return $this->dataBase->getEntryById("class", $id,$forceThisID);
	}


	public function getTeachersIdByClassId($id) {
        $class=$this->getClassById($id);
        $teachers = explode(',',$class["teachers"]);
        if (isset($class["headTeacherID"]))
            $teachers[]=$class["headTeacherID"];
        if (isset($class["secondHeadTeacherID"]))
            $teachers[]=$class["secondHeadTeacherID"];
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
        $sql="select * from class where headTeacherID =".$id. " or secondHeadTeacherID = ".$id ;
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    /**
     * Get teacher period for a school
     * Example in the field function in school1  {"1":"1975-1976"}, school1 and 3: {"1":"1975-1976","3":"1989-2021"}
     * @param $person
     * @param $schoolId
     */
    public function getTeacherPeriod($person,$schoolId) {
        $json = json_decode($person["employer"],true);
        if ($json==null)
            return "";
        if (isset($json[$schoolId]))
            return $json[$schoolId];
        return "";
    }

    public function getClassByText($text) {
		$sql="select * from class where text='".trim($text)."'";
		$sql .=" and changeForID is null";
        if (getActSchoolId()!=null)
		    $sql .=" and schoolId =".getActSchoolId();
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			$entry = $this->dataBase->fetchRow();
			return $this->dataBase->getEntryById('class', $entry["id"]);
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
        if ($schoolId==null)
            return null;
		$ret=$this->dataBase->getEntry("class", "schoolID=".$schoolId." and graduationYear=0");
        if ($ret == null) {
            $entry = array();
            $entry["schoolID"]=$schoolId;
            $entry["graduationYear"]=0;
            return $this->dataBase->saveEntry("class",$entry);
        }
		return $ret;
	}
	
	
	/**
	 * save a class
	 * @param array $class
	 * @return number
	 */
	public function saveClass($class ) {
		return $this->dataBase->saveEntry("class", $class);
	}

    /**
     * save school
     * @param array $school
     * @return number
     */
    public function saveSchool($school ) {
        return $this->dataBase->saveEntry("school", $school);
    }

    /**
     * Get the list of classes for a schoool
     * @param int $schoolID
     * @return array
     */
	public function getClassList($schoolID,$originalId=false,$isEveningClass=null,$isTwentyfirstcentury=null,$realClass=true) {
	    if (intval($schoolID)<=0)
            return array();
        $sql="schoolID=".$schoolID." and graduationYear!=0 ";
        if($realClass) {
            $sql .= " and graduationYear>1800 ";
        }
        if ($isEveningClass!==null) {
            if ($isEveningClass) {
                $sql .= " and eveningClass = 1";
            } else {
                $sql .= " and eveningClass = 0";
            }
        }
        if ($isTwentyfirstcentury!==null) {
            if ($isTwentyfirstcentury)
                $sql .= " and graduationYear > 2000";
            else
                $sql .= " and graduationYear <= 2000";
        }
		return   $this->dataBase->getElementList("class",$originalId, $sql,null,null,"text asc");
	}

    /**
     * Get the list of schoools
     * @return array
     */
    public function getSchoolList($originalId=false) {
        return $this->dataBase->getElementList("school",$originalId, " 1 = 1",null,null,"name asc");
    }

    public function getSchoolPersonCount($schoolId, $teacher=false) {

        if ($teacher)
            $sql = "SELECT COUNT(*) FROM person where schoolIdsAsTeacher like '%(".$schoolId.")%'";
        else
            $sql = "SELECT COUNT(*) FROM person JOIN class on class.id = person.classID where class.schoolID=".$schoolId;
        return $this->dataBase->queryInt($sql);
    }
	
	/**
	 * Delete a class
	 * @param int $id
     * @return boolean
	 */
	public function deleteClass($id) {
		$this->dataBase->createHistoryEntry("class",$id,true);
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
        if (isset($person["birthyear"]) && ($person["birthyear"]=='' || intval($person["birthyear"])<1800)) {
            $person["birthyear"]=null;
        }
        unset($person["schoolID"]);
	    $ret =$this->dataBase->saveEntry("person", $person);
		return $ret;
	}
	
	public function savePersonFacebookId($id,$facebookId) {
		$this->dataBase->createHistoryEntry("person",$id);
		$this->dataBase->update("person", [["field"=>"facebookid","type"=>"s","value"=>$facebookId]],"id",$id);	
	}

	public function savePersonGeolocation($id,$lat,$lng) {
		if (!isUserAdmin()) {
			$this->dataBase->createHistoryEntry("person",$id);
		}
		$this->dataBase->update("person", [["field"=>"geolat","type"=>"s","value"=>$lat],["field"=>"geolng","type"=>"s","value"=>$lng]],"id",$id);
	}
	
	/**
	 * save changes on only one field 
	 * @return integer -1 on error or person id if operation is succeded
	 */
	public function savePersonField($personId,$fieldName,$fieldValue=null, $noHistory=false) {
	    if ($noHistory) {
	        $ret = $this->dataBase->update("person", [["field"=>$fieldName,"type"=>"s","value"=>$fieldValue]],"id",$personId);
	        return $ret?$personId:-1;
        }
		$person=$this->getPersonByID($personId);
		if ($person!=null) {
		    $person[$fieldName]=$fieldValue;
			return $this->savePerson($person);
		}
		return -1;
	}
	
	/**
	 * Get persons,pictures from a school class
	 * @param int $classId
	 * @return stdClass
	 */
	public function getClassStatistics($classId,$countPictures=true) {
		$ret = new stdClass();
        if ($classId==null || $classId<0 ) {
            $ret->personCount=0;
            $ret->classPictures=0;
            $ret->guestCount=0;
            $ret->personWithPicture=0;
            $ret->personPictures=0;
        } else {
            $ret->personCount = $this->dataBase->queryInt("select count(*) as c from person where classID=" . $classId . " and changeForID is null ");
            $ret->classPictures = $this->dataBase->queryInt("select count(*) as c from picture where isDeleted=0 and classID =" . $classId . " and changeForID is null ");
            $ret->guestCount = $this->dataBase->queryInt("select count(*) as c from person where classID=" . $classId . " and changeForID is null and role like '%guest%'");
            if ($countPictures) {
                $ret->personWithPicture = $this->dataBase->queryInt("select count(id) from person where classID=" . $classId . " and changeForID is null and picture is not null and picture<>''");
                $ret->personPictures = $this->dataBase->queryInt("select count(id) from picture where personID in (select id from person where classID=" . $classId . " and changeForID is null ) and changeForID is null ");
            }
            $t = $this->dataBase->querySignleRow("select person.id,title, firstname, lastname,picture from person left join class on class.headTeacherID=person.id where class.id=" . $classId);
            if ($t != null) {
                $ret->teacher = (object)$t;
            }
        }
		return $ret;
	}

	/**
	 * get a person by unsername
	 * @return NULL is no person found 
	 */
	public function getPersonByUser($username) {
		return $this->dataBase->getEntryByField("person", "user", $username);
	}

	/**
	 * get a person by email
	 * @param string $email
	 * @return array person or NULL
	 */
	public function getPersonByEmail($email) {
		if ($email==null || trim($email)=="")
			return null;
		$person = $this->dataBase->getEntryByField("person", "email", $email);
		//the protected email
		if ($person==null)
			$person = $this->dataBase->getEntryByField("person", "email", "~".$email);
		return $this->getPersonByID(getRealId($person));
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
	    if ($personid==null || intval($personid)<=0)
            return null;
		//$person = $this->dataBase->getEntryById("person", $personid,$forceThisID, 'person.*, schoolID', 'class on class.id = person.classID');
        $person = $this->dataBase->getEntryById("person", $personid,$forceThisID);
        if (null==$person)
            return null;
        if ($person["classID"]!=0)
            $person["schoolID"]=($this->getClassById($person["classID"]))["schoolID"];
        else
            $person["schoolID"]=substr($person["schoolIdsAsTeacher"],1,strpos($person["schoolIdsAsTeacher"],')')-1);
	    return $person;
	}

    public function getPersonByID4DataCheck($id) {
        $d = $this->getPersonByID($id);
        $class = $this->getClassById($d["classID"]);
        $school = $this->getSchoolById($d["schoolID"]);
        $ret["Iskola"]=$school["name"];
        $ret["Osztály"]=$class["text"];
        $ret = array_merge($ret,$d);
        return $ret;
    }


    function getPersonWithInfo($personId) {
        $sql = "SELECT ";
        $sql .= "person.*, ";
        $sql .= "class.name as className, class.graduationYear, class.schoolID, ";
        $sql .= "changer.id as changerID, changer.firstname as changerFirstName, changer.lastname as changerLastName, ";
        $sql .= "count( distinct history.id) as countHistory, ";
        $sql .= "count( distinct picture.id) as countPictures, ";
        $sql .= "count( distinct personinpicture.pictureID) as countTagedPictures, ";
        $sql .= "count( distinct opinionText.id) as opinionText, ";
        $sql .= "count( distinct opinionEaster.id) as opinionEaster ";
        $sql .= "from person ";
        $sql .= "join class on class.id = person.classID ";
        $sql .= "join person as changer on changer.id = person.changeUserID ";
        $sql .= "inner join picture on person.id = picture.personID  ";
        $sql .= "inner join personinpicture on personinpicture.personID = person.id ";
        $sql .= "inner join history on history.entryID=person.id and history.table='person' ";
        $sql .= "inner join opinion as opinionEaster on opinionEaster.entryID = person.id and opinionEaster.table='person' and opinionEaster.opinion like 'easter%' ";
        $sql .= "inner join opinion as opinionText on opinionText.entryID = person.id and opinionText.table='person' and opinionText.opinion = 'text' ";
        $sql .= "where id=".$personId." or (changeIP='".$_SERVER["REMOTE_ADDR"]."' and changeForID =".$personId.") order by id desc";
        return $this->dataBase->queryFirstRow($sql);
    }


    /**
 	* Search for person lastname and firstname 
 	* @param string $name
     *@return array
 	*/
	public function searchForPerson($name) {
        $rteacherRegexp = '(tanár|tanár\\súr|tanárnő|Tanár|Tanár\\súr|Tanárnő|tanar|tanar\\sur|tanarno|Tanar|Tanar\\sur|Tanarno)';
        $teacher =(preg_match($rteacherRegexp, $name) === 1) ;
        $name = preg_replace($rteacherRegexp,"",$name);
        $ret = array();
		$nameItems=explode(' ', trim($name));
		foreach ($nameItems as $nameWord) {
				$ret=$this->arrayMergeByFieldId($ret,$this->searchForPersonOneString($nameWord,$teacher));
		}
		usort($ret, "compareAlphabetical");
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
	 * search for persons using one word name
	 * @param string $name
     * @return array
	 */
	private function searchForPersonOneString($name, $teacher=false) {
		$ret = array();
        $name=trim($name);
        if( intval($name)>1930 && intval($name)<2100 ) {
            $where =  "graduationYear=".intval($name);
        } elseif( strlen($name)>0 ) {

            $where ="(";
            $name = searchSpecialCharsAsRegex($name);
            $where .=" person.lastname rlike '".$name."' ";
            $where .="  or person.firstname rlike '".$name."' ";
            $where .="  or person.birthname rlike '".$name."' ";
            $where .=" )";

		} else {
                $where = " class.id=".getActClassId();
        }
        $sql  ="select person.*, class.schoolID as schoolID, class.graduationYear as schoolYear, class.eveningClass, class.name as schoolClass, school.Logo as schoolLogo  from person";
        $sql .=" left join  class on class.id=person.classID";
        $sql .=" left join  school on school.id=class.schoolID";
        $sql .=" where (graduationYear != 0 or role not like '%guest%' )";		//No administator users
        if ($teacher) {
            $sql .=" and schoolIdsAsTeacher is not null";
        }
        $sql .=" and ( person.changeForID is null and ".$where;
        $sql .=" and person.id not in ( select changeForID from person where  ".$this->dataBase->getSqlAnonymous("person.")." ) ";
        $sql.=") or (".$this->dataBase->getSqlAnonymous("person.")."and ".$where.") limit 150";

        $persons = $this->dataBase->queryArray($sql);
        foreach ($persons as $person) {
            if (isset($person["changeForID"]))
                $person["id"]=$person["changeForID"];
            if (array_search($person["id"],array_column($ret,"id"))===false) {
                if ($person["schoolID"]==NULL && $person["schoolIdsAsTeacher"]) {
                    $person["schoolID"] = substr($person["schoolIdsAsTeacher"], 1, strpos($person["schoolIdsAsTeacher"], ")") - 1);
                    $person["schoolLogo"] = $this->getSchoolById($person["schoolID"])["logo"];
                }
                array_push($ret, $person);
            }
        }
        usort($ret, "compareAlphabetical");
		return $ret;
	}
	
	/**
	 * get the list of classmates
	 * @param integer|array class id or class array
	 * @param boolean guest default false
	 * @param boolean withoutFacebookId default false
	 * @param boolean all default false all entrys
     * @return array
	 */
	public function getPersonListByClassId($class,$guest=false,$withoutFacebookId=false,$all=false, $notDied=false) {
        if (!is_array($class)) {
            $class = $this->getClassById($class);
        }
        $where ="classID=".$class["id"];
        if (!$all) {
            if($guest)
                $where.=" and role like '%guest%'";
            else
                $where.=" and  (role not like '%guest%' or role is null)";
            if ($withoutFacebookId) {
                $where.=" and (facebookid is null or length(facebookid)<5) ";
            }
        }
        if ($notDied==true) {
            $where.=" and deceasedYear is null";
        }
        $ret = $this->dataBase->getElementList("person",false,$where);
        foreach ($ret as $idx=>$r) {
            $ret[$idx]["schoolID"]=$class["schoolID"];
        }
        usort($ret, "compareAlphabetical");
        return $ret;
	}

    /**
     * get the list of teachers
     * @param integer|array school id or school array
     * @return array
     */
    public function getTeacherListBySchoolId($school) {
        if (!is_array($school)) {
            $school = $this->getSchoolById($school,true);
        }
        $where ="schoolIdsAsTeacher like '%(".$school["id"].")%'";
        $ret = $this->dataBase->getElementList("person",true,$where);
        foreach ($ret as $idx=>$r) {
            $ret[$idx]["schoolID"]=$school["id"];
        }
        usort($ret, "compareAlphabetical");
        return $ret;
    }

     /**
	 * get the person list using the actual school as filter
	 */
	public function getPersonList($where=null,$limit=null,$ofset=null,$order=null,$field="*",$join=null) {
        if (getActSchoolId()!=null) {
            $where .= " and class.schoolID =".getActSchoolId();
            $join = "class on class.id = person.classID";
            $field .= ' ,schoolID';
        }
		$ret = $this->dataBase->getElementList("person",false,$where,$limit,$ofset,$order,$field,$join);
		return $ret;
	}

	/**
	 * get sorted person list using the actual school as filter
	 */
	public function getSortedPersonList($where=null, $limit=null, $offset=null, $schoolID=null) {
        if ($schoolID!=null) {
            $where .= " and class.schoolID = " . $schoolID;
            $ret = $this->dataBase->getElementList("person", false, $where, $limit, $offset, null, 'person.*, schoolID', "class on class.id = person.classID");
        } else {
            $ret = $this->dataBase->getElementList("person", false, $where, $limit, $offset, null, 'person.*, schoolID', "class on class.id = person.classID");
        }
		usort($ret, "compareAlphabeticalByLastFirstName");
		return $ret;
	}

    /**
     * get sorted teacher list using the actual school as filter
     */
    public function getSortedTeacherList($where=null, $limit=null, $offset=null, $schoolID=null) {
        if ($schoolID!=null) {
            $where .= " and schoolIdsAsTeacher like '%(" . $schoolID. ")%'";
            $ret = $this->dataBase->getElementList("person", false, $where, $limit, $offset, null, 'person.*, '.$schoolID.' as schoolID ' );
        } else {
            $ret = $this->dataBase->getElementList("person", false, $where, $limit, $offset, null, 'person.*, schoolIdsAsTeacher as schoolID ');
        }
        usort($ret, "compareAlphabeticalTeacher");
        return $ret;
    }


    /**********************************[ Article ]*****************************/

    /**
     * get article by id
     * @param integer $id
     * @param boolean $forceThisID
     * @return array or null
     */
    public function getArticleById($id,$forceThisID=false) {
        return $this->dataBase->getEntryById("article", $id,$forceThisID);
    }


    /**
	 * get entry count
     * @param string $table
	 * @param string $where
	 * @return integer
	 */
	public function getTableCount($table,$where=null,$distinct=null,$join=null) {
        if ($distinct!=null)
            $sql =" COUNT(DISTINCT ".$distinct.") ";
        else
            $sql =" COUNT(1) ";
		//normal entrys
		$sql  ="SELECT ".$sql." FROM ".$table;
        if ($join!=null)
            $sql .=" JOIN ".$join;
        $sql .=" WHERE ( (".$table.".changeForID is null and ".$table.".changeUserID is not null) ";
		//anonymous new entrys from the aktual ip
		$sql.=" or (".$table.".changeForID is null and ".$table.".changeIP='".$_SERVER["REMOTE_ADDR"]."' and ".$table.".changeUserID is null)  )";
		if ($where!=null)
			$sql.=" AND ".$where;
		return $this->dataBase->queryInt($sql);
	}
	

	/**
	 * List of temporary changes made by anonymous users
     * @param string $table
     * @return array
	 */
	public function getListToBeChecked($table) {
        if ($table!="personInPicture") {
            $sql = "select c.*, o.id as changeForIDjoin from " . $table . " as c ";
            $sql .= "left join " . $table . " as o on c.changeForID=o.id  ";
        } else {
            $sql = "select c.* from " . $table . " as c ";
        }
        //c.changeUserID is null
		$sql.="where  c.changeForID is not null or c.changeUserID is null ";
		$sql.="order by c.changeDate asc";
		return $this->dataBase->queryArray($sql);
	}

    /**
     * Count of temporary changes made by anonymous users
     * @param string $table
     * @return int
     */
    public function getCountToBeChecked($table) {
        $sql ="select count(1) from ".$table." as c ";
        if ($table!="personInPicture")
          $sql.="left join ".$table." as o on c.changeForID=o.id  ";
        $sql.="where c.changeForID is not null or c.changeUserID is null ";
        $sql.="order by c.changeDate asc";
        return $this->dataBase->queryInt($sql);
    }


    /**
	 * Delete person from database and the picture 
	 * @return boolean
	 */
	public function deletePersonEntry( $id ) {
		$this->dataBase->createHistoryEntry("person",$id,true);
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

	public function getCountOfPersons($classId,$guests) {
		if(null!=$classId) {
			$sql =" classID = ".$classId;
			if ($guests)
				$sql .=" and role like '%guest%'";
			else
				$sql .=" and (role not like '%guest%' or role is null)";
			if ($this->isClassIdForStaf($classId))
				$sql .=" and schoolIdsAsTeacher  is not null";
			return sizeof($this->dataBase->getIdList("person",$sql));
		}
		return 0;
	}
	
	public function getPersonIdListWithPicture() {
		$where="picture is not null and picture != ''";
		return $this->dataBase->getIdList("person",$where);
	}
	
//******************** Picture DAO *******************************************
	
	/**
	 * Save picture
	 * @param array $picture
	 * @return integer, negativ if an error occurs
	 */
	public function savePicture($picture) {
		$newEntry=$picture["id"]==-1;
		$id = $this->dataBase->saveEntry("picture", $picture);
		if ($newEntry && $id>=0) {
			$this->dataBase->update("picture", [["field"=>"orderValue","type"=>"n","value"=>$id],["field"=>"isDeleted","type"=>"i","value"=>0]],"id",$id);
		}
		return $id;
	}

    /**
     * remove isDeleted flag from picture
     * @param $id
     * @return bool
     */
	public function notUnlinkPicture($id) {
        return 	$this->dataBase->update("picture", [["field"=>"isDeleted","type"=>"i","value"=>0]],"id",$id);
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
		return $this->dataBase->getElementList("picture",false,$sql,$limit,$offset,$orderby);
	}


    /**
     * Get list of pictures by where
     * @param string where
     * @return array of pictures
     */
    public function getListOfPicturesWhere($sql="",$orderby=null,$limit=null,$offset=null) {
        return $this->dataBase->getElementList("picture",false, $sql,$limit,$offset,$orderby);
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
		$sql = " where `".$type."`=".$typeId. " and albumName is not null and albumName!='' and isDeleted<>1";
        if ($type=="schoolID") {
            $sql .= " and personID is null and classID is null ";
        }
        $sql .=" group by albumName";
		$sql="select count(albumName) as count,albumName, albumName as albumText from picture".$sql;
		$this->dataBase->query($sql);
		return array_merge($startList,$this->dataBase->getRowList());
	}

    public function getListOfPictureTags($startList=array()) {
        $sql  = "select count(tag) as count,tag from picture";
        $sql .= " where  tag !='' and tag is not null group by tag";
        $this->dataBase->query($sql);
        $ret = array();
        foreach ($this->dataBase->getRowList() as $tag) {
            if (strpos($tag["tag"],",")===false) {
                array_push($ret,$tag);
            }
        }
        return array_merge($startList,$ret);
    }

    public function getMainAlbumCount($type,$typeId,$text) {
        $sql ="select count(*) as count, '".$text."' as albumText, '' as albumName from picture";
        $sql .= " where ".$type."=".$typeId. " and isDeleted=0 and (albumName is null or albumName='')";
        $sql .= " and (tag not like 'sport%' or tag is null) and changeForID is null";
        if ($type=="schoolID") {
            $sql .=" and personID is null and classID is null";
        }
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    /**
     * Get the count of pictures with a specified tag an in the actual school
     * @param $tag
     * @return bool|int
     */
    public function getPictureTagCount($tag) {
        $sql  = "select count(*)  from picture";
        $sql .= " where isDeleted=0 and (albumName='' or `albumName` is null) and tag like '%".$tag."%' and changeForID is null";
        if (getActSchoolId()!=null) {
            $sql .= " and schoolId =".getActSchoolId();
        }
        return $this->dataBase->queryInt($sql);
    }

    public function getPersonMarksCount($personId=null) {
        $sql="SELECT count(*) FROM `personInPicture` ";
        if ($personId!=null)
            $sql .= " WHERE `personID` =".$personId;
        return $this->dataBase->queryInt($sql);
    }

    public function getPersonMarks($personId=null) {
        $sql="SELECT pictureID,personID FROM `personInPicture` ";
        if ($personId!=null)
            $sql .= " WHERE `personID` =".$personId;
        return $this->dataBase->queryArray($sql);
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
			return   $this->dataBase->getElementList("picture",false,"isDeleted=0");
		} else {
			return   $this->dataBase->getElementList("picture",false,"isDeleted=0 and ".$where);
		}
	}
	
	/**
	 * List of recent not deleted pictures
	 */
	public function getRecentPictureList($limit) {
		return   $this->dataBase->getElementList("picture",false,"isDeleted=0",$limit,null,"uploadDate desc");
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
		$this->dataBase->createHistoryEntry("picture",$id,true);
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
		    $name = searchSpecialCharsAsRegex($name);
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


    /**
     * get recent changes in the database
     * @param string $dateFrom
     * @param int $limit
     * @param string $filter all, teacher, person, picture, opinion, class, family, candle, tag
     * @param string $ip
     * @param int $userid
     * @return array
     */
    public function getRecentChangesListByDate($dateFrom, $limit,$filter='all',$ip=null, $userid=null) {
        $rows=array();
        $sqlIpUser="";$sqlCandleIpUser="";
        if ($ip!=null) {
            $sqlIpUser .=" and person.changeIP='".$ip."' ";
            $sqlCandleIpUser .=" and ip='".$ip."' ";
        }
        if ($userid!=null) {
            $sqlIpUser .=" and person.changeUserID='".$userid."' ";
            $sqlCandleIpUser .=" and userID='".$userid."' ";
        }
        $sqlSchool = getActSchoolId()==null?"":(" and `class`.schoolID=".getActSchoolId(). " ");
        if (in_array($filter,array("all"))) {
            $sql = " (select person.id, person.changeDate, 'person' as type, 'change' as action, person.changeUserID from person join class on class.id = person.classID";
            $sql .= " where person.changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlSchool;
            $sql .= $sqlIpUser." and ( (person.changeUserID is not null and person.changeForID is null) or person.changeIP='" . $_SERVER["REMOTE_ADDR"] . "') order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("teacher"))) {
            $sql = " (select person.id, person.changeDate, 'person' as type, 'change' as action, person.changeUserID from person join class on class.id = person.classID";
            $sql .= " where person.changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlSchool;
            $sql .= $sqlIpUser." and ( (person.changeUserID is not null and person.changeForID is null) or person.changeIP='" . $_SERVER["REMOTE_ADDR"] . "') and schoolIdsAsTeacher  is not null order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("person"))) {
            $sql = " (select person.id, person.changeDate, 'person' as type, 'change' as action, person.changeUserID from person join class on class.id = person.classID";
            $sql .= " where person.changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlSchool;
            $sql .= $sqlIpUser." and ( (person.changeUserID is not null and person.changeForID is null) or person.changeIP='" . $_SERVER["REMOTE_ADDR"] . "') and schoolIdsAsTeacher is null order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","picture"))) {
            $sql = " (select id, changeDate, 'picture' as type, 'change' as action, changeUserID from picture where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'";
            $sql .= str_replace("person.","",$sqlIpUser)." and ( (changeUserID is not null and changeForID is null) or changeIP='" . $_SERVER["REMOTE_ADDR"] . "') ";
            $sql .= getActSchoolId()==null?"":(" and schoolID=".getActSchoolId());
            $sql .=" and (isDeleted=0) order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","class"))) {
            $sql = " (select id, changeDate, 'class' as type, 'change' as action, changeUserID from class where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'";
            $sql .= str_replace("person.","",$sqlIpUser)." and ( (changeUserID is not null and changeForID is null) or changeIP='" . $_SERVER["REMOTE_ADDR"] . "')";
            $sql .= getActSchoolId()==null?"":(" and schoolID=".getActSchoolId());
            $sql .= " and graduationYear>1800 ";
            $sql .= " order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","opinion"))) {
            $sql = " (select entryID as id, changeDate, `table` as type, 'opinion' as action, changeUserID from opinion where ";
            $sql .= getActSchoolId()==null?"":(" schoolID=".getActSchoolId(). " and ");
            $sql .= " changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".str_replace("person.","",$sqlIpUser)." and `table`!='message' and `opinion` not like 'easter%'  order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","easter"))) {
            $sql  = " (select entryID as id, opinion.changeDate, 'person' as type, 'easter' as action, opinion.changeUserID from opinion";
            $sql .= " join person on person.id=opinion.entryID join class on class.id = person.classID ";
            $sql .= " where opinion.changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".str_replace("person.","opinion.",$sqlIpUser).$sqlSchool." and  `opinion` like 'easter%' and  opinion.changeDate > '".date("Y")."-01-01' order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","family"))) {
            $sql  = " (select id1 as id, family.changeDate, 'person' as type, 'family' as action, family.changeUserID from family";
            $sql .= " join person on person.id =family.id1 join class on class.id=person.classID";
            $sql .= " where family.changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".str_replace("family.","",$sqlIpUser).$sqlSchool." order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","candle"))) {
            $sql = " (select personID as id, lightedDate as changeDate, 'person' as type, 'candle' as action, userID as changeUserID from candle ";
            $sql .= "join person on person.id=candle.personID join class on class.id=person.classID ";
            $sql .= "where showAsAnonymous=0 and lightedDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".$sqlCandleIpUser.$sqlSchool." order by lightedDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","tag"))) {
            $sql  = " (select pictureID as id, personInPicture.changeDate, 'picture' as type, 'marked' as action, personInPicture.changeUserID from personInPicture join picture on picture.id = personInPicture.pictureID";
            $sql .= " where personInPicture.changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".str_replace("person.","personInPicture.",$sqlIpUser);
            $sql .= getActSchoolId()==null?"":(" and picture.schoolID=".getActSchoolId());
            $sql .= " order by personInPicture.changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","message"))) {
            $sql  = " (select message.id, message.changeDate, 'message' as type, 'message' as action, message.changeUserID from message join person on person.id=message.changeUserID join class on class.id = person.classID where ";
            $sql .= " message.changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "'".str_replace("person.","message.",$sqlIpUser).$sqlSchool." and endDate is null and privacy='world' order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
            $sql = " (select id, NOW() as changeDate, 'message' as type, 'message' as action, changeUserID from message where true ".str_replace("person.","",$sqlIpUser)." and endDate>NOW() and privacy='world' order by endDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        if (in_array($filter,array("all","game"))) {
            $sql = " (select game.id as id, dateEnd as changeDate, 'game' as type, 'played' as action, userId as changeUserID from game";
            $sql .= " join person on person.id=game.userId join class on class.id=person.classID ";
            $sql .=  " where dateEnd<='" . $dateFrom->format("Y-m-d H:i:s") . "' ".(getActSchoolId()==null?"":(" and schoolID=".getActSchoolId()));
            $sql .= $sqlCandleIpUser. " order by dateEnd desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        /*
        if (in_array($filter,array("all","article"))) {
            $sql = " (select id, changeDate, 'article' as type, 'change' as action, changeUserID from article where changeDate<='" . $dateFrom->format("Y-m-d H:i:s") . "' ";
            $sql .= getActSchoolId()==null?"":(" and schoolID=".getActSchoolId());
            $sql .= str_replace("person.","",$sqlIpUser)." and ( (changeUserID is not null and changeForID is null) or changeIP='" . $_SERVER["REMOTE_ADDR"] . "') order by changeDate desc limit " . $limit . ") ";
            $this->dataBase->query($sql);
            $rows = array_merge($rows, $this->dataBase->getRowList());
        }
        */
        //Order list by change date using the spaceship operator
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

    /**
     * @deprecated
     */
    public function updateRecentChangesList()
    {
        return;
    }

//********************* Message ******************************************
	/**
	 * get chat messages
	 */
	public function getMessages($limit=null,$offset=null) {
		return $this->dataBase->getElementList("message",false,"classID is null",$limit,$offset,"changeDate desc");
	}

	/**
	 * get class messages
	 */
	public function getClassMessages($classId,$limit=null,$offset=null) {
		return $this->dataBase->getElementList("message",false,"classID =".$classId,$limit,$offset,"changeDate desc");
	}
	
	/**
	Returns a signle entry or NULL if no entry found
	 */
	public function getMessage($id) {
		return $this->dataBase->getEntryById("message", $id);
	}

	public function setMessageAsDeleted($id) {
		$entry=array();
		$entry["id"]=$id;
		$entry["endDate"]=date("Y-m-j H:i:s");
		$this->dataBase->createHistoryEntry("message",$id);
		return $this->dataBase->updateEntry("message", $entry);
	}
	
	public function saveMessage($entry) {
		return $this->dataBase->saveEntry("message",$entry);
	}
	
	public function saveNewMessage($entry) {
		$entry["id"]=-1;
		return $this->dataBase->saveEntry("message", $entry);
	}
	
	/**
	 * save messager comment
	 * @param int $id
	 * @param string $comment
	 * @return boolean
	 */
	public function saveMessageComment($id,$comment) {
		$this->dataBase->createHistoryEntry("message",$id);
		return $this->dataBase->update("message", [["field"=>"comment","type"=>"s","value"=>$comment]],"id",$id);
	}
	
	public function saveMessagePersonID($id,$uid) {
		$this->dataBase->createHistoryEntry("message",$id);
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
		$this->dataBase->createHistoryEntry("message",$id,true);
		return $this->dataBase->delete("message", "id", $id);
	}
	
	/**
	 * Accept the anonymous message entrys 
	 * @return boolean
	 */
	public function acceptChangeForMessage($id) {
		$p=$this->dataBase->querySignleRow("select * from message where id=".$id);
		if ($p!=null) {
		    $p["changeUserID"]=-1;
		    $this->dataBase->createHistoryEntry("message",$id);
		    return $this->dataBase->updateEntry("message", $p);
		}
		return false;
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
		return $this->dataBase->deleteWhere("request", $where)!==false;
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
		if (!isUserLoggedOn() || $forceCount) {
			$data=array();
			$data=$this->dataBase->insertFieldInArray($data, "ip", $_SERVER["REMOTE_ADDR"]);
			$data=$this->dataBase->insertFieldInArray($data, "date", date("Y-m-d H:i:s"));
			$data=$this->dataBase->insertFieldInArray($data, "typeID", $type);
			$this->dataBase->insert("request", $data);
		}
	}
	
}