<?PHP
	include_once("data.php");
	include_once 'sendMail.php';
	
	//Change scool year and class if parameters are there
	if (isset($_GET['classid']))   { setAktClass($_GET['classid']); }
	
	//Login if crypted loginkey present and correct
	if (isset($_GET['key'])) {
		directLogin($_GET['key']);
	}
	
	function directLogin($key){
		global $db;
		$personid = encrypt_decrypt("decrypt", $key);
		$person=$db->getPersonByID($personid);
		if (null!=$person) {
			setAktUserId($personid);
			setUserInSession($person["role"], $person["user"],$personid);
			if (!userIsAdmin()) {
				saveLogInInfo("Login",$_SESSION['uId'],$person["user"],"","direct");
				sendHtmlMail(null,
					"<h2>Login</h2>".
					"Uid:".$_SESSION['uId']." User: ".$person["user"]," Direct-Login");
			}
		} else {
			die("A kód nem érvényes!".$login[2]);
		}
	}
	
	/**
	 * get the user id form logged in user
	 * @return NULL if no user logged on
	 */
	function getLoggedInUserId() {
		if (!isset($_SESSION["uId"]))
			return -1;
		return intval($_SESSION["uId"]);
	}
	
	function getLoggedInUserClassId() {
		global $db;
		$loggedInUser=$db->getPersonByID(getLoggedInUserId());
		if ($loggedInUser!=null)
			return intval($loggedInUser["classID"]);
		else 
			return -1;
	}
	
	function getLoggedInUserName() {
		global $db;
		$loggedInUser=$db->getPersonByID(getLoggedInUserId());
		if ($loggedInUser!=null) {
			return getPersonName($loggedInUser);
		} 
		else
			return "Anonim felhasználó";
	}
	
	
	/**
	 * set aktual viewed user id
	 * @param user id
	 */
	function setAktUserId($id) {
		$_SESSION["aktUId"]=$id;
	}
	
	/**
	 * get aktual viewed user id
	 * @return unknown
	 */
	function getAktUserId() {
		if (isset($_SESSION["aktUId"]))
			return $_SESSION["aktUId"];
		else 
			return null;
	}

	/**
	 * Set aktual person class id
	 * @param unknown $classId
	 */
	function setAktClass($classId) {
		$_SESSION['aktClass']=$classId;
	}
	
	function unsetAktClass() {
		unset($_SESSION['aktClass']);
	}
	
	
	/**
	* The aktual person class id
	* @return number|NULL
	*/
	function getAktClassId() {
		if (isset($_SESSION['aktClass'])) {
			return intval($_SESSION['aktClass']);
		} else {
			return -1;
		}
	}
	
	/**
	 * The aktual person class id
	 * @return number|NULL
	 */
	function getAktClass() {
		global $db;
		if (isset($_SESSION['aktClass'])) {
			return $db->getClassById(intval($_SESSION['aktClass']));
		}
		return null;
	}
	
	/**
	 * The aktual person school id
	 * @return number|NULL
	 */
	function getAktSchool() {
		global $db;
		return $db->getSchoolById(getAktSchoolId());
	}

	/**
	 * The aktual person school id
	 * @return number|NULL
	 */
	function getAktSchoolId() {
		if (isset($_SESSION['aktSchool']))
			return intval($_SESSION['aktSchool']);
		else 
			return 1;
	}
	
	
	/**
	 * are the logged in user and aktual viewed user the same?
	 * @return boolean
	 */
	function isAktUserTheLoggedInUser() {
		return (getAktUserId()==getLoggedInUserId() );
	}

	
	/**
	 * Check login data in each client 
	 */
	function checkUserLogin($user,$passw) {
		global $db;
		$ret = false;
		$usr = $db->getPersonByUser($user);
		if (null != $usr && $usr["passw"]==$passw) {
			setUserInSession(
				$usr["role"],
				$usr["user"],
				$usr["id"]);
			$ret = true;
		}
		else {
			$usr =$db->getPersonByEmail($user);
			if (null != $usr && $usr["passw"]==$passw) {
				setUserInSession(
					$usr["role"],
					$usr["user"],
					$usr["id"]);
				$ret = true;
			} else {
				$usr =$db->getPersonByLastnameFirstname($user);
				if (null != $usr && $usr["passw"]==$passw) {
					setUserInSession(
							$usr["role"],
							$usr["user"],
							$usr["id"]);
					$ret = true;
				}				
			}
		}
		if (!userIsAdmin()) {
			if (isset($_SESSION['uId']))
				saveLogInInfo("Login",$_SESSION['uId'],$user,$passw,$ret);
			else 
				saveLogInInfo("Login","",$user,$passw,$ret);
		}
		return $ret;
	}

	/**
	 * Check facebook login data in each client
	 */
	function checkFacebookUserLogin($facebookId) {
		global $db;
		$ret = false;
		$usr =$db->getPersonByFacobookId($facebookId);
		if (null != $usr) {
			setUserInSession(
				$usr["role"],
				$usr["user"],
				$usr["id"]);
			$ret = true;
			if (!userIsAdmin() && userIsLoggedOn())
				saveLogInInfo("Facebook",$usr['id'],$usr['user'],$facebookId,$ret);
		}		
		return $ret;
	}
	
	/**
	 * save user informations in the session
	 * @param User role $admin
	 * @param User name $user
	 * @param User id $uid
	 */
	function setUserInSession($role, $user, $uid )
	{
		$_SESSION['uRole']=$role;
		$_SESSION['uName']=$user;
		$_SESSION['uId']=$uid;
	}
	
	/**
	 * Logout user
	 */
	function logoutUser() {
		$_SESSION['uRole']="";
		$_SESSION['uName']="";
		$_SESSION['uId']=NULL;
		$_SESSION['FacebookId'] = NULL;
		$_SESSION['FacebookName'] = NULL;
		$_SESSION['FacebookEmail'] =  NULL;
		unsetAktClass();
	}
	
	/**
	 * a user is logged on
	 */
	function userIsLoggedOn() {
		return ( isset($_SESSION['uId']) && $_SESSION['uId']>-1 );	
			
	}
	
	/**
	 *User is logged in and have the role of admin
	 */
	function userIsAdmin() {
		if (isset($_SESSION['uRole'])) 
			return strstr($_SESSION['uRole'],"admin")!="";
		else 
			return false;
	}

	/**
	 *User is logged in and have the role of admin
	 */
	function userIsSuperuser() {
		if (isset($_SESSION['uRole']))
			return strstr($_SESSION['uRole'],"superuser")!="";
		else
			return false;
	}
	
	
	/**
	 *User is logged in and have the role of  editor
	 */
	function userIsEditor() {
		global $db;
		//User is editor in his own db
		if (isset($_SESSION['uRole']) && getAktClassId()==getLoggedInUserClassId()) {
			return strstr($_SESSION['uRole'],"editor")!="";
		} else { 
			$p=$db->getPersonByID(getLoggedInUserId());
			//User is teacher and editor then return editor right for all classes where the teacher is head teacher
			if ($p["isTeacher"]==1) { 
				if (strstr($_SESSION['uRole'],"editor")!="") {
					if (isset($p["children"])) {
						$c=explode(",", $p["children"]);
						$ret = false;
						$class = getAktClass();
						if (null!=$class) {
							foreach ($c as $cc) {
								if (substr($cc,0,3)==$class["name"] && substr($cc,3,4)==$class["graduationYear"]) 
									$ret=true;
							}
						}
						return $ret;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	
	/**
	 *User is logged in and have the role as viewer
	 */
	function userIsViewer() {
		if (isset($_SESSION['uRole'])) 
			return strstr($_SESSION['uRole'],"viewer")!="";
		else 
			return false;
	}

	/**
	 * Generate a login key for the aktual user
	 * @return key string
	 */
	function generateAktUserLoginKey() {
		return generateUserLoginKey(getAktUserId());
	}
	
	/**
	 * generate an login key for the a user 
	 * @return key string
	 */
	function generateUserLoginKey($uid) {
		return encrypt_decrypt("encrypt",$uid);
	}

	/**
	 * check if the username is unique in the database
	 */
	function checkUserNameExists($id,$userName) {
		global $db;
		$usr = $db->getPersonByUser($userName);
		if (null != $usr) {
			if ( $usr["id"]==$id) 
				return false;
			else 
				return true;
		}
		return false;
	}
	
		
	/**
	 * Check if a email address allready exists in the db
	 * the id is the current user id, this will be ignored if not null
	 */
	function checkUserEmailExists($id,$email) {
		global $db;
		$usr = $db->getPersonByEmail($email);
		if (null!=$usr) {
			if ( $usr["id"]==$id )
				return false;
			else
				return true;
		}
		return false;
	}

	/**
	 * Set user password
	 * return value 
	 * >0 -> Password set the result value is the person id 
	 * -1 -> Email not found, 
	 * -2 -> Passw to short, 
	 * -3 -> Sequrity violation 
	 */
	function resetUserPasswort($email, $newPassw) {
		$ret = -1;
		if (strlen($newPassw)>3) { 
			if (checkRequesterIP("newpassword")) {
				global $db;
				$usr = $db->getPersonByEmail($email);
				if (null != $usr) {
						$db->savePersonField($usr["id"],"passw",$newPassw);
						$ret = $usr["id"];
					}
			}
			else $ret = -3;
		}
		else $ret =-2;
		saveLogInInfo("NewPassword",$usr["user"],$email,$newPassw,$ret);
		return $ret;
	}
	
	
	/**
	 * Create a random password
	 */
	function createPassword($length) {
		$chars = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i <= $length) {
			$password .= $chars{mt_rand(0,strlen($chars)-1)};
			$i++;
		}
		return $password;
	}

//*********************** Loging *************************************************	
	
	
	/**
	 * Save login information for statistics and sequrity reasons
	 * parameter $user SaveData,SavePassw,SaveGeo, NewPassword, 
	 */
	function saveLogInInfo($action,$uid,$cuser,$cpassw,$result) {
		$file=fopen("login.log","a");
		if ($result) $res="true"; else $res="false";
		fwrite($file,$_SERVER["REMOTE_ADDR"]."\t".date('d.m.Y H:i')."\t".getAktClassName()."\t".$res."\t".$uid."\t".$action."\t".$cuser."\t".$cpassw."\r\n");
		
	}
	
	
	
	/**
	 * Check the requester IP 
	 * if the IP can't login more then a defined time on a day then return value will set to false 
	 * this is a safety funtion to prevent automatic loging of password crack or to mutch anonymous changes
	 */
	function checkRequesterIP($action) {
		global $db;
		if ($action==changeType::login) {
			$count = $db->getCountOfRequest($action,24);
			return $count<20;
		} elseif ($action==changeType::personchange) {
			$count = $db->getCountOfRequest($action,24);
			return $count<60;
		} elseif ($action==changeType::message) {
			$count = $db->getCountOfRequest($action,24);
			return $count<1;
		} elseif ($action==changeType::personupload) {
			$count = $db->getCountOfRequest($action,24);
			return $count<5;
		} 
	}

	/**
	 * read login log  
	 */
	function readLogingData($action,$year) {
		backupLoginData();
		$logData = array();
		$logDataField = array("IP","Date","Scool","Result","ID","Action","CUser","Passw");
		$file=fopen("login.log","r");
		$i=0;
		$type= explode(",",$action);
		while (!feof($file)) {
			$b = explode("\t",fgets($file));
			if (sizeof($b)>=7) {
				if (strpos($b[1],$year)>1 && ($type[0]==$b[5] || (isset($type[1]) && $type[1]==$b[5]) || (isset($type[2]) && $type[2]==$b[5]))) {
					foreach($logDataField as $idx => $field) {
						if (isset($b[$idx])) 
							$logData[$i][$logDataField[$idx]] = $b[$idx]; 
						else 
							$logData[$i][$logDataField[$idx]] ="";
					}
				$i++;
				}
			}
		}
		return $logData;
	}
	
	/**
	 * Krypt or encrypt a string
	 * @param unknown $action
	 * @param unknown $string
	 * @return string
	 */
	function encrypt_decrypt($action, $string) {
		$output = false;
	
		$encrypt_method = "AES-256-CBC";
		$secret_key = 'iskola';
		$secret_iv = 'brassai';
	
		// hash
		$key = hash('sha256', $secret_key);
	
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
	
		if( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		}
		else if( $action == 'decrypt' ){
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
	
		return $output;
	}
	
	function backupLoginData() {
		//TODO
		;

	}

?>