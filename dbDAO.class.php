<?php
/**
 * Data access layer for the classmate database
*/

use maierlabs\lpfw\MySqlDbAUH ;

include_once 'tools/mysqldbauh.class.php';
include_once 'tools/logger.class.php';
include_once 'tools/ltools.php';
include_once 'config.class.php';

/**
 * data change types 
 */
class changeType
{
	const login = 0;
	const message = 1;
	const personupload = 2;
	const personchange = 3;
	const classchange = 4;      //changes für class pictures
	const classupload = 5;      //changes für class pictures
	const deletepicture = 6;    //changes für class pictures
	const newuser= 7;           //create a new user
    const newPassword = 8;
}

class dbDAO {
    /**
     * @var MySqlDbAUH|null
     */
	private $dataBase = NULL;

    /**
     * dbDAO constructor.
     */
    public function __construct(){
		//Connect to the DB
        $db = \Config::getDatabasePropertys();
		$this->dataBase = new MySqlDbAUH($db->host,$db->database,$db->user,$db->password);
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
            return $table.".changeForID is not null and ".$table.".changeUserID is null and ".$table.".changeIP='".$_SERVER["REMOTE_ADDR"]."'";
        else
            return "changeForID is not null and changeUserID is null and changeIP='".$_SERVER["REMOTE_ADDR"]."'";
    }

    /**
     * SQL Statement to select the new anonymous entrys
     * @param string $table
     * @return string
     */
    private function getSqlAnonymousNew($table=null)
    {
        if ($table!=null)
            return $table.".changeForID is null and ".$table.".changeUserID is null and ".$table.".changeIP='".$_SERVER["REMOTE_ADDR"]."'";
        else
            return "changeForID is null and changeUserID is null and changeIP='".$_SERVER["REMOTE_ADDR"]."'";
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
	public function getClassList($schoolID=1) {
		return   $this->getElementList("class","schoolID=".$schoolID,null,null,"text asc");
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
			$sql="select c.*, p.firstname as tfname, p.lastname as tlname, p.birthname as tbname,p.picture as picture,";
			$sql .=" cp.id as cid, cp.firstname as cfname, cp.lastname as clname, cp.birthname as cbname, cp.role as role";
			$sql .=" from class as c"; 
			$sql .=" left join  person as p on c.headTeacherID=p.id";
			$sql .=" left join  person as cp on c.changeUserID=cp.id where";
			$sql .=" c.name like '%".$name."%' ";
			$sql .=" or c.graduationYear like '%".$name."%' ";
			$sql .=" or c.text like '%".$name."%' ";
			$sql .=" limit 50";
				
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

	public function savePersonGeolocation($id,$lat,$lng) {
		if (!userIsAdmin()) {
			$this->createHistoryEntry("person",$id);
		}
		$this->dataBase->update("person", [["field"=>"geolat","type"=>"s","value"=>$lat],["field"=>"geolng","type"=>"s","value"=>$lng]],"id",$id);
	}
	
	public function savePersonLastLogin($id) {
		$this->dataBase->update("person", [["field"=>"userLastLogin","type"=>"s","value"=>date("Y-m-d H:i:s")]],"id",$id);
	}
	
	/**
	 * save changes on only one field 
	 * @return integer positiv integer if the save operation is succeded
	 */
	public function savePersonField($personId,$fieldName,$fieldValue=null) {
		$person=$this->getPersonByID($personId);
		if ($person!=null) {
			if ($fieldValue==null )
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
	public function getClassStatistics($classId,$countPictures=true) {
		$ret = new stdClass();
		$ret->personCount=$this->dataBase->queryInt("select count(id) from person where classID=".$classId." and changeForID is null");
		if($countPictures) {
			$ret->personWithPicture=$this->dataBase->queryInt("select count(id) from person where classID=".$classId." and changeForID is null and picture is not null");
			$ret->personPictures=$this->dataBase->queryInt("select count(id) from picture where personID in (select id from person where classID=".$classId." and changeForID is null ) and changeForID is null ");
			$ret->classPictures=$this->dataBase->queryInt("select count(id) from picture where classID =".$classId." and changeForID is null ");
		}
		$ret->teacher=(object)$this->dataBase->querySignleRow("select firstname, lastname,picture from person left join class on class.headTeacherID=person.id where class.id=".$classId);
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
			if (strlen(trim($nameWord))>2) {
				$ret=$this->personMerge($ret,$this->searchForPersonOneString($nameWord));
			}
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
					array_push($ret,$person1);
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
		if( strlen($name)>1 && intval($name)==0) {
            $name=$this->clearUTF($name);
			$sql  ="select person.*, class.graduationYear as scoolYear, class.eveningClass, class.name as scoolClass from person";
			$sql .=" left join  class on class.id=person.classID";  
			$sql .=" where (graduationYear != 0 or isTeacher = 1)";		//No administator users
			$sql .=" and ( person.changeForID is null";
            $sql .=" and (soundex(person.lastname) like soundex('".$name."') ";
            $sql .=" or soundex(person.firstname) like soundex('".$name."') ";
            if( strtolower($this->wildcardUTF($name))=="eva")
                $sql .=" or (person.firstname) = 'Éva'";
            $sql .=" or soundex(person.birthname) like soundex('".$name."') )";
            $sql .=" and person.id not in ( select changeForID from person where  ".$this->getSqlAnonymous("person")." ) ";
            $sql.=") or (".$this->getSqlAnonymous("person").") limit 150";
			$this->dataBase->query($sql);

			while ($person=$this->dataBase->fetchRow()) {
                    if (isset($person["changeForID"]))
                        $person["id"]=$person["changeForID"];
					array_push($ret, $person);
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
	 * @param boolean all default false all entrys
     * @return array
	 */
	public function getPersonListByClassId($classId,$guest=false,$withoutFacebookId=false,$all=false) {
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
			$ret = $this->getElementList("person",$where);
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
	public function getPersonList($where=null,$limit=null,$ofset=null) {
		$ret = $this->getElementList("person",$where,$limit,$ofset);
		return $ret;
	}

	/**
	 * get sorted person list!
	 */
	public function getSortedPersonList($where=null,$limit=null,$ofset=null) {
		$ret = $this->getElementList("person",$where,$limit,$ofset);
		usort($ret, "compareAlphabeticalTeacher");
		return $ret;
	}
	
	public function getLightedCandleList($id=null) {
		$sql = 'select personID from candle'." where  lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
		if($id!=null) {
            $sql .=' and userID='.$id;
        }
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$candles= $this->dataBase->getRowList();
		
			$sql='id in (';
			foreach ($candles as $idx=>$candle) {
				if ($idx!=0) $sql .=",";
				$sql .=$candle["personID"];
			}
			$sql.=')';
			return $this->getSortedPersonList($sql);
		}
		return array();
	}
	
	/**
	 * Count of candles by person id always +1 from the system :)
	 * if Id = null all candles + 1 candle for each deceased person from system
	 * @param integer $id
     * @return integer
	 */
	public function getCandlesByPersonId($id=null) {
		if (null!=$id) {
			$sql='select count(*) from candle where personId='.$id." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
			return $this->dataBase->queryInt($sql)+1;
		} else {
			$sql='select count(*) from person where deceasedYear is not null';
			$ret = $this->dataBase->queryInt($sql);
				
			$sql="select count(*) from candle where lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
			return $ret + $this->dataBase->queryInt($sql);
		}
	}

	public function getCandleDetailByPersonId($id) {
		$sql='select * from candle where personID='.$id." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) 
			return $this->dataBase->getRowList();
		
		return array();
	}

    public function getCandleDetailByUserId($id) {
        $sql='select * from candle where userID='.$id." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
        $this->dataBase->query($sql);
        if ($this->dataBase->count()>0)
            return $this->dataBase->getRowList();

        return array();
    }

	public function checkLightning($id, $userId=null) {
		if ($userId!=null) {
			$sql='select count(*) from candle where personId='.$id." and userID=".$userId." and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
			$ret = $this->dataBase->queryInt($sql);
			return ($ret==0);
		}
		$sql='select count(*) from candle where personId='.$id." and ip='".$_SERVER["REMOTE_ADDR"]."' and lightedDate >'".date('Y-m-d H:i:s',strtotime("-2 month"))."'";
		$ret = $this->dataBase->queryInt($sql);
		return ($ret==0);
	}
	
	public function setCandleLighter($id, $userId=null) {
		$data=array();
		if (userIsLoggedOn()) {
			$data=$this->dataBase->insertFieldInArray($data, "userID", $userId);
		}
		$data=$this->dataBase->insertFieldInArray($data, "ip", $_SERVER["REMOTE_ADDR"]);
		$data=$this->dataBase->insertFieldInArray($data, "lightedDate", date("Y-m-d H:i:s"));
		$data=$this->dataBase->insertFieldInArray($data, "personID", $id);
		$this->dataBase->insert("candle", $data);
	}
	
	/**
	 * @deprecated
	 */
	public function dbUtilitySetDeceasedYear() {
		$sql ="update  person set deceasedYear=convert(substring(firstname,instr(firstname,'†')+3) ,signed) where firstname like '%†%'";
		$this->dataBase->query($sql);
		$sql ="update  person set 	firstname=substring(firstname,1,instr(firstname,'†')-1) where firstname like '%†%'";
		$this->dataBase->query($sql);
		$sql ="update  person set 	deceasedYear=null where deceasedYear = -1";
		$this->dataBase->query($sql);
		return "ok";
	}

	/**
	 * get last activity group by date
	 * @param object $dateTime
	 * @return array
	 */
	public function getActivityCalendar($dateTime) {
		$ret=array();
		
		$sql ="select changeDate, count(*) as count from history where changeDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(changeDate) order by changeDate";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r= $this->dataBase->getRowList();
			foreach ($r as $v) { $ret[$v["changeDate"]]=intval($v["count"]);}
		} 
		
		$sql ="select changeDate, count(*) as count from person where changeDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(changeDate) order by changeDate";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r= $this->dataBase->getRowList();
			foreach ($r as $v) {
				if (isset($ret[$v["changeDate"]]))
					$ret[$v["changeDate"]]+=$v["count"];
				else
					$ret[$v["changeDate"]]=intval($v["count"]);
			}
		}

		$sql ="select changeDate, count(*) as count from image where changeDate>'".$dateTime->format("Y-m-d H:i:s")."' group by date(changeDate) order by changeDate";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r= $this->dataBase->getRowList();
			foreach ($r as $v) {
				if (isset($ret[$v["changeDate"]]))
					$ret[$v["changeDate"]]+=$v["count"];
				else
					$ret[$v["changeDate"]]=intval($v["count"]);
			}
		}
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
	 * get the recently updated person list
	 */
	public function getRecentChangedPersonList($limit) {
		$ret = $this->getElementList("person",null,$limit,null,"changeDate desc");
		return $ret;
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
		if (sizeof($p)>0) {
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
	 public function getListOfPictures($id,$type,$isDeleted=0,$isVisibleForAll=1,$album=null) {
		$sql="";
		if($id!=null)
			$sql.=$type."=".$id;
		else
			$sql.=$type." is not null ";
		if ($isDeleted<2) {
			$sql.=" and isDeleted=".$isDeleted;}
		if ($isVisibleForAll<2) {
			$sql.=" and isVisibleForAll=".$isVisibleForAll; }
		if (null!=$album) {
			$sql.=" and albumName='".$album."'"; 
		} else {
			$sql.=" and (albumName is null or albumName='')";
		}
		return $this->getElementList("picture",$sql,null,null,"orderValue desc");	
	}

    /**
     * Get list of pictures
     * @param integer $id if null get all pictures of a type
     * @param string $type the type of picture personID, classID, schoolID
     * @return int
     */
    public function getNrOfPictures($id,$type,$isDeleted=0,$isVisibleForAll=1,$album=null) {
        $sql="";
        if($id!=null)
            $sql.=$type."=".$id;
        else
            $sql.=$type." is not null ";
        if ($isDeleted<2) {
            $sql.=" and isDeleted=".$isDeleted;}
        if ($isVisibleForAll<2) {
            $sql.=" and isVisibleForAll=".$isVisibleForAll; }
        if (null!=$album) {
            $sql.=" and albumName='".$album."'";
        }
        return sizeof($this->getIdList("picture",$sql));
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
		return $this->getElementList("picture",$sql,null,null,"title asc");	
	}
	
	/*
	 * get the list of picture albums 
	 */
	public function getListOfAlbum($type,$typeId,$startList=array()) {
		$sql = " where ".$type."=".$typeId. " and albumName <> ''";
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
	public function getPictureList($where=null) {
		if (null==$where) {
			return   $this->getElementList("picture","isDeleted=0");
		} else {
			return   $this->getElementList("picture","isDeleted=0 and ".$where);
		}
	}
	
	/**
	 * List of recent not deleted pictures
	 */
	public function getRecentPictureList($limit) {
		return   $this->getElementList("picture","isDeleted=0",$limit,null,"uploadDate desc");
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
		$ret = array();
		$nameItems=explode(' ', trim($name));
		foreach ($nameItems as $nameWord) {
			if (strlen(trim($nameWord))>2) {
				$ret=array_merge($ret,$this->searchForPictureOneString($nameWord));
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
			$sql="select p.* from picture as p";
			//$sql .=" left join  person as cp on c.changeUserID=cp.id where";
			$sql .=" where p.title like '%".$name."%' ";
			$sql .=" or p.comment like '%".$name."%' ";
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
     * Union select the ids from the latest changes
     * @param DateTime $dateFrom
     * @param int $limit
     * @return array
     */
	public function getRecentChangeList($dateFrom,$limit=50) {
	    $sql  = " select id, changeDate, 'person' as type from person where changeDate<='".$dateFrom->format("Y-m-d H:i:s")."'";
        $sql .= " and ( changeForID is null or changeIP='".$_SERVER["REMOTE_ADDR"]."') ";
	    $sql .= " union ";
        $sql .= " select id, changeDate, 'picture' as type from picture where changeDate<='".$dateFrom->format("Y-m-d H:i:s")."'";
        $sql .= " and ( changeForID is null or changeIP='".$_SERVER["REMOTE_ADDR"]."') ";
        $sql .= " union ";
        $sql .= " select userID as id, lightedDate as changeDate, 'candle' as type from candle where lightedDate<='".$dateFrom->format("Y-m-d H:i:s")."' and id in ( select max(id) from candle GROUP by userID)";
        $sql .= " order by changeDate desc limit ".$limit;
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
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
		return $this->getElementList("interpret",null,null,null,"name asc");
	}
	
	/*
	 * get Voterslist by class id
	 */
	public function getVotersListByClassId($classId) {
		$sql  ="select  person.id, person.lastname, person.firstname, person.picture, count(1) as count "; 
		$sql .="from songvote join person on person.id=songvote.personID";
		if (null!=$classId) {
			$sql .=" where person.classID=".$classId;
		}
		$sql .=" group by personID";
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

	/**
	 * get chat messages
	 */
	public function getMessages($limit=null,$offset=null) {
		return $this->getElementList("message","classID is null",$limit,$offset,"changeDate desc");
	}

	/**
	 * get class messages
	 */
	public function getClassMessages($classId,$limit=null,$offset=null) {
		return $this->getElementList("message","classID =".$classId,$limit,$offset,"changeDate desc");
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
		$p["changeUserID"]=-1;
		$this->createHistoryEntry("message",$id);
		return $this->updateEntry("message", $p);
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
	 * Save request
	 * @param  integer  $type changeType
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
	 * Get an array of elements, or an empty array if no elements found.
	 * Anonymous changes from the user IP will be considered
     * even if the anonymous copys are returned the ids will be from the original entrys
	 */
	private function getElementList($table, $where=null, $limit=null, $offset=null, $orderby=null, $field="*") {
        $ret = array();
		//normal entrys
		$sql="select ".$field.",id from ".$table." where ((changeForID is null and changeUserID is not null)";
		//and anonymous new entrys
        $sql.=" or (".$this->getSqlAnonymousNew().") )";
		if ($where!=null)		$sql.=" and ( ".$where." )";
		if ($orderby!=null)		$sql.=" order by ".$orderby;
		if ($limit!=null)		$sql.=" limit ".$limit;
		if ($offset!=null)		$sql.=" offset ".$offset;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
            $ret = $this->dataBase->getRowList();
        }
        //anonymous entrys
        $sql="select ".$field.",changeForID from ".$table.' where '.$this->getSqlAnonymous();
        if ($where!=null)		$sql.="  and ( ".$where." )";
        $this->dataBase->query($sql);
        if ($this->dataBase->count()>0) {
            //Change the entrys with the anonymous entrys
            $anyonymous=$this->dataBase->getRowList();
            foreach ($ret as $i=>$r) {
                $found=array_search($r["id"],array_column($anyonymous,"changeForID"));
                if ($found!==false) {
                    $ret[$i]=$anyonymous[$found];
                    //the original id
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
		return $this->getElementList($table,$where,$limit,$offset,$orderby,"id");
	}
	
	
	/**
	 * Returns a signle entry from a table in consideration of the anonymous changes or NULL if no entry found
	 * even if the anonymous copy is returned the id will be from the original
     * @param string $table
     * @param int $id
     * @param boolean $forceThisID
	 * @return array the entry of null if not found
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
			if (sizeof($ret)>1) {
			    $ret[0]["id"]=$ret[0]["changeForID"];
            }
            if (sizeof($ret)>0)
                return $ret[0];
		}
		return null;
	}
	
	/**
	 * get a db entry by a field
	 * @return array  NULL is no entry or more then one entry found
	 */
	private function getEntryByField($table,$fieldName,$fieldValue) {
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
	 * @return NULL is no entry or more then one entry found
	 */
	public function getEntry($table,$where) {
		$sql="select id from ".$table." where ".$where;
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
		if (userIsLoggedOn()) {
			$data =$this->dataBase->changeFieldInArray($data,"changeUserID", getLoggedInUserId());
		} else {
			$data =$this->dataBase->setFieldInArrayToNull($data,"changeUserID");
		}
		
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
	 * get the list of best users including the score
	 */
	public function getNewPersonChangeBest() {
	    $ret = array();
		$sql="select count(1) as count, changeUserID as uid from history where changeUserID!=0 and `table`='person' group by changeUserID";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			$r1=array();
			foreach ($r as $s) {
				$r1[$s["uid"]]=$s["count"];
			}
            $ret = $this->mergeBestArray(array(),$r1,1);unset($r1);
		} 

		
		$sql="select id, changeUserID as uid from person where changeUserID >0 and changeForID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			$r2=array();
			foreach ($r as $s) {
				$uid= $this->getOldestHistoryUserId($s["id"]);
				if ($uid==0) 
					$uid=$s["uid"];
				if (isset($r2[$uid]))
					$r2[$uid]=$r2[$uid]+1;
				else
					$r2[$uid]=1;
			}
            $ret = $this->mergeBestArray(array(),$r2,3);unset($r2);
		}

		$sql="select id, changeUserID as uid from picture where changeUserID >0 and isDeleted=0";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			$r3=array();
			foreach ($r as $s) {
				$uid= $this->getOldestHistoryUserId($s["id"]);
				if ($uid==0) 
					$uid=$s["uid"];
				if (isset($r3[$uid]))
					$r3[$uid]=$r3[$uid]+1;
				else
					$r3[$uid]=1;
			}
            $ret = $this->mergeBestArray($ret,$r3,5);unset($r3);
		}

		$rets=array();
		for($i=0;$i<12;$i++) {
			$value=0;
			foreach ($ret as $uid=>$count) {
				if($count>$value) {
					$value=$count;
					$vuid=$uid;
				}
			}
			if (isset($vuid)) {
			    $rets[$vuid]=$value;
                unset($ret[$vuid]);
            }
		}
		return $rets;
	}

    /**
     * @param int $id
     * @return int
     */
	private function getOldestHistoryUserId($id) {
		$sql="select changeUserID from history where entyID =".$id." and changeUserID>0 order by changeDate limit 1";
		return $this->dataBase->queryInt($sql);
	}

    /**
     * @param int $id
     * @return array
     */
	public function getPersonActivities($id) {
		$ret = array();
		$sql="select count(1) as count from history where changeUserID=".$id." and `table`='person' ";
		$ret["personChange"]=$this->dataBase->queryInt($sql);
		
		$sql="select count(1) as count from person where changeUserID=".$id;
		$ret["newPerson"]=$this->dataBase->queryInt($sql);
		
		$sql="select count(1) as count from picture where changeUserID=".$id;
		$ret["newPicture"]=$this->dataBase->queryInt($sql);
		
		$sql="select count(1) as count from candle where userID=".$id;
		$ret["lightedCandles"]=$this->dataBase->queryInt($sql);

		$sql="select count(1) as count from songvote where personID=".$id;
		$ret["songVotes"]=$this->dataBase->queryInt($sql);
		
		$sql="select count(1) as count from song where changeUserID=".$id;
		$ret["songs"]=$this->dataBase->queryInt($sql);
		
		$sql="select count(1) as count from interpret where changeUserID=".$id;
		$ret["interprets"]=$this->dataBase->queryInt($sql);
		
		$person = $this->getPersonByID($id);
		$r=0;
		if (isset($person["userLastLogin"])) {
			$diff=date_diff(new DateTime($person["userLastLogin"]),new DateTime(),true);
			if ($diff->days<1000)
				$r=1000- ($diff->days);
		}
		$ret["lastLoginPoints"]=$r;
		
		
		
		return  $ret;
		
	}
	
	public function getPersonChangeBest($count=12) {
		$sql="select count(1) as count, changeUserID as uid from history where changeUserID>=0 and `table`='person' group by changeUserID order by count desc limit ".$count;
		$ret = $this->mergeBestArrays(array(),$sql,1);
	
		$sql="select count(1) as count, changeUserID as uid from person where changeUserID>=0 group by  changeUserID order by count desc limit  ".$count;
		$ret = $this->mergeBestArrays($ret,$sql,3);
	
		$sql="select count(1) as count, changeUserID as uid from picture where changeUserID !=0 group by  changeUserID order by count desc limit  ".$count;
		$ret = $this->mergeBestArrays($ret,$sql,5);
		
		$sql="select userLastLogin, id as uid from person where userLastLogin is not null and changeForID is null order by userLastLogin desc limit  ".$count;
		$this->dataBase->query($sql);
		$r4=array();
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			foreach ($r as $s) {
				$diff=date_diff(new DateTime($s["userLastLogin"]),new DateTime(),true);
				if ($diff->days<1000)
					$r4[$s["uid"]]=1000- ($diff->days);
			}
		}
		$ret = $this->mergeBestArray($ret,$r4,1);

		$sql="select count(1) as count, userID as uid from candle where userID is not null group by  userID order by count desc limit  ".$count;
		$ret = $this->mergeBestArrays($ret,$sql,2);
		
		$sql="select count(1) as count, personID as uid from songvote where personID !=0 group by  personID order by count desc limit  ".$count;
		$ret = $this->mergeBestArrays($ret,$sql,7);

		$sql="select count(1) as count, changeUserID as uid from song where changeUserID !=0 group by  changeUserID order by count desc limit  ".$count;
		$ret = $this->mergeBestArrays($ret,$sql,7);

		$sql="select count(1) as count, changeUserID as uid from interpret where changeUserID !=0 group by  changeUserID order by count desc limit  ".$count;
		$ret = $this->mergeBestArrays($ret,$sql,7);
		
		$rets=array();
		for($i=0;$i<$count;$i++) {
            $value = 0;
            foreach ($ret as $uid => $counts) {
                if ($counts > $value) {
                    $value = $counts;
                    $vuid = $uid;
                }
            }
            if (isset($vuid)) {
                $rets[$vuid] = $value;
                unset($ret[$vuid]);
            }
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

	private function mergeBestArrays($inputArray,$sql,$factor=1) {
		$this->dataBase->query($sql);
		$rr=array();
		if ($this->dataBase->count()>0) {
			$r = $this->dataBase->getRowList();
			foreach ($r as $s) {
				$rr[$s["uid"]]=$s["count"];
			}
		}
		return $this->mergeBestArray($inputArray,$rr,$factor);
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

    public function dbUtilityEncryptPasword()
    {
        $ret=0;
        $this->dataBase->query("select user,passw,firstname,lastname,id from person");
        while ($row = $this->dataBase->fetchRow()) {
            $p=$row["passw"];
            //Change
            if (strlen($p)!=32) {
                $ep=encrypt_decrypt("encrypt",$p);
                $this->dataBase->update("person", array(["field"=>"passw","type"=>"s","value"=>$ep]),"id",$row["id"]);
                $ret++;
            }
        }
        return "Elements=".$this->dataBase->count()." Encrypted=".$ret;

    }

    /**
     * Delete history entry
     * @param int $id
     * @return bool
     */
    public function deleteHistoryEntry($id)
    {
        return $this->dataBase->delete("history", "id", $id);
    }

}
