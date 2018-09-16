<?php	
	
	/**
	 * get the user id form logged in user
	 * @return integer or NULL if no user logged on
	 */
	function getLoggedInUserId() {
		if (!isset($_SESSION["uId"]))
			return null;
		return intval($_SESSION["uId"]);
	}
	
	/**
	 * get the logged in user class id 
	 * @return integer or -1 if no user logged on
	 */
	function getLoggedInUserClassId() {
		global $db;
		$loggedInUser=$db->getPersonByID(getLoggedInUserId());
		if ($loggedInUser!=null)
			return intval($loggedInUser["classID"]);
		else 
			return -1;
	}
	
	/**
	 * get logged in name including first and lastname
	 * @return string
	 */
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
	 * @return integer or null
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
	 * Set aktual person class id
	 * @param unknown $classId
	 */
	function setAktSchool($schoolId) {
		$_SESSION['aktSchool']=$schoolId;
	}
	
	function unsetAktSchool() {
		unset($_SESSION['aktSchool']);
	}
	
	
	/**
	* The aktual person class id
	* @return integer or -1
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
	 * The aktual school id
	 * @return number|NULL
	 */
	function getAktSchool() {
		global $db;
		return $db->getSchoolById(getAktSchoolId());
	}
	
	/**
	 * The aktual school staf class id
	 * @return number|NULL
	 */
	function isAktClassStaf() {
		global $db;
		return $db->getStafClassIdBySchoolId(getAktSchoolId())==getAktClassId();
	}
	
	/**
	 * The aktual person school id
	 * @return number|-1
	 */
	function getAktSchoolId() {
		if (isset($_SESSION['aktSchool']) && null!=$_SESSION['aktSchool'] && intval($_SESSION['aktSchool'])>0)
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
		$usr =$db->getPersonByFacobookId($facebookId);
		if (null != $usr) {
			setUserInSession(
				$usr["role"],
				$usr["user"],
				$usr["id"]);
			if (!userIsAdmin() && userIsLoggedOn())
				saveLogInInfo("Facebook",$usr['id'],$usr['user'],$facebookId,"true");
			return true;
		}		
		return false;
	}
	
	/**
	 * save user informations in the session
	 * @param User role $admin
	 * @param User name $user
	 * @param User id $uid
	 */
	function setUserInSession($role, $user, $uid )
	{
		global $db;
		$_SESSION['uRole']=$role;
		$_SESSION['uName']=$user;
		$_SESSION['uId']=$uid;
		$db->savePersonLastLogin($_SESSION['uId']);
	}
	
	/**
	 * Logout user
	 */
	function logoutUser() {
		unset($_SESSION['uRole']);
		unset($_SESSION['uName']);
		unset($_SESSION['uId']);
		unset($_SESSION['FacebookId'] );
		unset($_SESSION['FacebookName']);
		unset($_SESSION['FacebookEmail']);
		unset($_SESSION["FacebookFirstName"]);
		unset($_SESSION["FacebookLastName"]);
		unsetAktClass();
	}
	
	/**
	 * a user is logged on
	 */
	function userIsLoggedOn() {
		return ( isset($_SESSION['uId']) && intval($_SESSION['uId'])>-1 );
			
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
 * @param string $action SaveData,SavePassw,SaveGeo, NewPassword
 * @param string $uid
 * @param string $cuser
 * @param string $cpassw
 * @param string $result
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
			return $count<30;
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
	
?>