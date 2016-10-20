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

	public function getClassById($classid) {
		$sql="select * from class where id =".$classid;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else
			return null;
	}
	
	public function getClassByText($text) {
		$sql="select * from class where text ='".$text."'";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else
			return null;
	}
	
	/**
	 * Save class
	 * @param $id
	 * @param $schoolID
	 * @param $name
	 * @param $graduationYear
	 * @param $text
	 * @param $headTeacherID
	 * @return 0=Ok, -1=Error, 1=Alert:class already exists
	 */
	public function saveClass($id, $ip, $schoolID, $name, $graduationYear, $text=null,$headTeacherID=null ) {
		$data = array();
		$i=0;
		$data[$i]["field"]="schoolID";$data[$i]["type"]="n";$data[$i++]["value"]=$schoolID;
		$data[$i]["field"]="name";$data[$i]["type"]="s";$data[$i++]["value"]=trim($name);
		$data[$i]["field"]="graduationYear";$data[$i]["type"]="n";$data[$i++]["value"]=trim($graduationYear);
		if ($text==null) {
			$data[$i]["field"]="text";$data[$i]["type"]="s";$data[$i++]["value"]=trim($graduationYear)." ".trim($name);
		} else {
			$data[$i]["field"]="text";$data[$i]["type"]="s";$data[$i++]["value"]=trim($text);
		}
		if ($headTeacherID!=null) {
			$data[$i]["field"]="$headTeacherID";$data[$i]["type"]="n";$data[$i++]["value"]=$headTeacherID;
		}
		$data[$i]["field"]="changeDate";$data[$i]["type"]="d";$data[$i++]["value"]=date("Y-m-d H:i:s");
		if ($ip!=null) {
			$data[$i]["field"]="changeIP";$data[$i]["type"]="s";$data[$i++]["value"]=$ip;
		}
		
		if ($id==null) {
			$sql="select * from class where name='".trim($name)."' and graduationYear=".trim($graduationYear);
			$this->dataBase->query($sql);
			if ($this->dataBase->count()>0) {
				$row=$this->dataBase->fetchRow();
				$this->dataBase->update("class",$data,"id",$row["id"]);
				return 1;
			} else {
				$this->dataBase->insert("class",$data);
				return 0;
			}
		} else {
			$sql="select * from class where id=".$id;
			$this->dataBase->query($sql);
			if ($this->dataBase->count()>0) {
				$this->dataBase->update("class",$data,"id",$id);
				return 0;
			} else {
				return -1;
			}
		}
	}
	
	public function getClassList() {
		$sql="select * from class where changeIP is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()>0) {
			return $this->dataBase->getRowList();
		} else {
			return null;
		}
	}
	
	public function queryPersons() { 	
		$sql="select * from person where changesForPersonID is null";
		$this->dataBase->query($sql);
		return $this->dataBase->count();
	}
	
	
	public function getQueryRow () {
		return $this->dataBase->fetchRow();
	}
	
	public function savePerson(	$id,$changesForPersonID,$classID,$userID,$isTeacher,$firstname,$lastname,$picture,$geolat,$geolng,
								$user,$passw,$role,$birthname,$partner,$address,$zipcode,$place,$country,
								$phone,$mobil,$email,$skype,$education,$employer,$function,$children,
								$ip,$facebook,$homepage,$facebookid,$twitter) {
		$data = array();
		$i=0;
		if($changesForPersonID!=null) {
			$data[$i]["field"]="changesForPersonID";$data[$i]["type"]="n";$data[$i++]["value"]=$changesForPersonID;
		}
		$data[$i]["field"]="classID";$data[$i]["type"]="n";$data[$i++]["value"]=$classID;
		$data[$i]["field"]="firstname";$data[$i]["type"]="s";$data[$i++]["value"]=$firstname;
		$data[$i]["field"]="lastname";$data[$i]["type"]="s";$data[$i++]["value"]=$lastname;
		$data[$i]["field"]="picture";$data[$i]["type"]="s";$data[$i++]["value"]=$picture;
		$data[$i]["field"]="geolat";$data[$i]["type"]="s";$data[$i++]["value"]=$geolat;
		$data[$i]["field"]="geolng";$data[$i]["type"]="s";$data[$i++]["value"]=$geolng;
		$data[$i]["field"]="user";$data[$i]["type"]="s";$data[$i++]["value"]=$user;
		$data[$i]["field"]="passw";$data[$i]["type"]="s";$data[$i++]["value"]=$passw;
		$data[$i]["field"]="role";$data[$i]["type"]="s";$data[$i++]["value"]=$role;
		$data[$i]["field"]="birthname";$data[$i]["type"]="s";$data[$i++]["value"]=$birthname;
		$data[$i]["field"]="partner";$data[$i]["type"]="s";$data[$i++]["value"]=$partner;
		$data[$i]["field"]="address";$data[$i]["type"]="s";$data[$i++]["value"]=$address;
		$data[$i]["field"]="zipcode";$data[$i]["type"]="s";$data[$i++]["value"]=$zipcode;
		$data[$i]["field"]="place";$data[$i]["type"]="s";$data[$i++]["value"]=$place;
		$data[$i]["field"]="country";$data[$i]["type"]="s";$data[$i++]["value"]=$country;
		$data[$i]["field"]="phone";$data[$i]["type"]="s";$data[$i++]["value"]=$phone;
		$data[$i]["field"]="mobil";$data[$i]["type"]="s";$data[$i++]["value"]=$mobil;
		$data[$i]["field"]="email";$data[$i]["type"]="s";$data[$i++]["value"]=$email;
		$data[$i]["field"]="skype";$data[$i]["type"]="s";$data[$i++]["value"]=$skype;
		$data[$i]["field"]="education";$data[$i]["type"]="s";$data[$i++]["value"]=$education;
		$data[$i]["field"]="employer";$data[$i]["type"]="s";$data[$i++]["value"]=$employer;
		$data[$i]["field"]="function";$data[$i]["type"]="s";$data[$i++]["value"]=$function;
		$data[$i]["field"]="children";$data[$i]["type"]="s";$data[$i++]["value"]=$children;
		$data[$i]["field"]="facebook";$data[$i]["type"]="s";$data[$i++]["value"]=$facebook;
		$data[$i]["field"]="homepage";$data[$i]["type"]="s";$data[$i++]["value"]=$homepage;
		$data[$i]["field"]="twitter";$data[$i]["type"]="s";$data[$i++]["value"]=$twitter;
		$data[$i]["field"]="facebookid";$data[$i]["type"]="s";$data[$i++]["value"]=$facebookid;
		$data[$i]["field"]="changeIP";$data[$i]["type"]="s";$data[$i++]["value"]=$ip;
		$data[$i]["field"]="changeDate";$data[$i]["type"]="d";$data[$i++]["value"]=date("Y-m-d H:i:s");
		$data[$i]["field"]="changeUserID";$data[$i]["type"]="n";$data[$i++]["value"]=$userID;
		$data[$i]["field"]="isTeacher";$data[$i]["type"]="n";$data[$i++]["value"]=$isTeacher;
		
		if ($id==null) {
			$sql="select * from person where user='".trim($user)."' and changesForPersonID is null";
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				$row=$this->dataBase->fetchRow();
				$this->dataBase->update("person",$data,"id",$row["id"]);
				return 1;
			} else if ($this->dataBase->count()==0) {
				$this->dataBase->insert("person",$data);
				return 0;
			} else if ($this->dataBase->count()>1) {
				return -2;
			}
		} else {
			$sql="select * from person where id=".$id;
			$this->dataBase->query($sql);
			if ($this->dataBase->count()>0) {
				$this->dataBase->update("person",$data,"id",$id);
				return 0;
			} else {
				return -1;
			}
		}
	}
		
	public function savePersonTextData($personId, $userIP, $userID, $type, $privacy, $text) {
		if($personId==null || $text==null)
			return -3;
		
		if ($privacy=="world") $text="~~".$text;
		if ($privacy=="scool") $text="~".$text;

		$data = array();
		$i=0;
		if ($type=="story") {
			$data[$i]["field"]="story";$data[$i]["type"]="s";$data[$i++]["value"]=$text;
		}
		else if ($type=="cv") {
			$data[$i]["field"]="cv";$data[$i]["type"]="s";$data[$i++]["value"]=$text;
		}
		else if ($type=="aboutMe") {
			$data[$i]["field"]="aboutMe";$data[$i]["type"]="s";$data[$i++]["value"]=$text;
		} else
			return -4;
		
		if($userIP!=null) {
			$data[$i]["field"]="changeIP";$data[$i]["type"]="s";$data[$i++]["value"]=$userIP;
		}
		if($userID>=0) {
			$data[$i]["field"]="changeUserID";$data[$i]["type"]="n";$data[$i++]["value"]=$userID;
		}
		$data[$i]["field"]="changeDate";$data[$i]["type"]="d";$data[$i++]["value"]=date("Y-m-d H:i:s");
		
		//This is  an anonymuschange
		if ($userID<0) {
			$sql="select * from person where changesForPersonID=".$personId." and changeIP ='".trim($userIP)."'";
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==0) {
				$d=$this->getPersonByID($personId);
				$db->savePerson(null, $personId, $d["classID"],$userID, $d["isTeacher"], $d["firstname"], $d["lastname"], $d["picture"], $d["geolat"], $d["geolng"], $d["user"], $d["passw"], $d["role"], $d["birthname"], $d["partner"], $d["address"], $d["zipcode"], $d["place"], $d["country"], $d["phone"], $d["mobil"], $d["email"], $d["skype"], $d["education"], $d["employer"], $d["function"], $d["children"], $userIP, $d["facebook"], $d["homepage"], $d["facebookid"], $d["twitter"]);
			}
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				$row=$this->dataBase->fetchRow();
				$this->dataBase->update("person",$data,"id",$row["id"]);
				return 1;
			} else  {
				return -2;
			}
		} else {
			$this->dataBase->update("person",$data,"id",$personId);
			return 0;
		}
			
	}
	
	public function getPersonByUser($username) {
		if ($username==null || trim($username)=="")
			return null;
		$sql="select * from person where user ='".trim($username)."' and changesForPersonID is null";
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			$row = $this->dataBase->fetchRow();
			$personid=intval($row["id"]);
		} else
			return null;
					
			
		$sql="select * from person where changeIP ='".$_SERVER["REMOTE_ADDR"]."' and changesForPersonID=".$personid;
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else {
			return $this->getPersonByID($personid);
		}
	}
		
	public function getPersonByID($personid) {
		if ($personid==null || trim($personid)=="")
			return null;

		$sql="select * from person where changeIP ='".$_SERVER["REMOTE_ADDR"]."' and changesForPersonID=".trim($personid);
		$this->dataBase->query($sql);
		if ($this->dataBase->count()==1) {
			return $this->dataBase->fetchRow();
		} else {
			$sql="select * from person where id =".trim($personid)." and changesForPersonID is null";
			$this->dataBase->query($sql);
			if ($this->dataBase->count()==1) {
				return $this->dataBase->fetchRow();
			} else
				return null;
		}
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
			$this->dataBase->query($sql);
	
			while ($person=$this->dataBase->fetchRow()) {
				if (stristr(html_entity_decode($person["lastname"]), $name)!="" ||
					stristr(html_entity_decode($person["firstname"]), $name)!="" ||
					(isset($person["birthname"]) && stristr(html_entity_decode($person["birthname"]), $name)!="")) {
					array_push($ret, $person);
				}
			}
			usort($ret, "compareAlphabetical");
		}
		return $ret;
	}
	
	public function getPersonListByClassId($classId) {
		$ret = array();
		$sql="select person.*, class.graduationYear as scoolYear, class.name as scoolClass from person left join  class on class.id=person.classID where ";
		$sql .=" classID = ".$classId;
		$sql .=" and changesForPersonID is null ";
		if ($classId==0)
			$sql .=" and isTeacher = 1";
		$this->dataBase->query($sql);
		
		while ($person=$this->dataBase->fetchRow()) {
			if (true) {
				array_push($ret, $person);
			}
		}
		usort($ret, "compareAlphabetical");
		return $ret;
	}	
	
	public function getCountOfPersons($classId,$guests) {
		$ret = array();
		$sql="select 1 from person where ";
		$sql .=" classID = ".$classId;
		if ($guests)
			$sql .=" and role like '%guest%'";
		else
			$sql .=" and not(role like '%guest%')";
		$sql .=" and changesForPersonID is null ";
		if ($classId==0)
			$sql .=" and isTeacher = 1";
		$this->dataBase->query($sql);
		return $this->dataBase->count();
		
	}
		
	
}
?>