<?PHP
	include_once("data.php");
	include_once 'sendMail.php';
	
	//Change scool year and class if parameters are there
	if (isset($_GET['scoolYear']))   { setAktScoolYear($_GET['scoolYear']); }
	if (isset($_GET['scoolClass']))  { setAktScoolClass($_GET['scoolClass']); }
	
	//Login if crypted loginkey present and correct
	if (isset($_GET['key']))   {
		$login = explode("-", encrypt_decrypt("decrypt", $_GET['key']));
		if (sizeof($login)==3) {
			setAktScoolClass($login[0]);
			setAktScoolYear($login[1]);
			setAktUserId($login[2]);
			$diak=getPerson($login[2],getAktDatabaseName());
			setUserInSession($diak["admin"], $diak["user"],$login[2] , $login[1], $login[0]);
			if (!userIsAdmin()) {
				saveLogInInfo("Login",$_SESSION['uId'],$diak["user"],"","direct");
				sendHtmlMail(null,
					"<h2>Login</h2>".
					"Uid:".$_SESSION['uId']." User: ".$diak["user"]," Direct-Login");
			}
		}
	}
	
	/**
	 * get the user id form logged in user
	 * @return NULL if no user logged on
	 */
	function getLoggedInUserId() {
		if (!isset($_SESSION["uId"]))
			return null;
		return $_SESSION["uId"];
	}
	
	/**
	 * get user scool year
	 * @return NULL if no user logged on
	 */
	function getUScoolYear() {
		if (isset($_SESSION["uScoolYear"]))
			return $_SESSION["uScoolYear"];
		else 
			return null;
	}
	
	/**
	 * get user scool class
	 * @return NULL if no user logged on
	 */
	function getUScoolClass() {
		if (isset($_SESSION["uScoolClass"]))
			return $_SESSION["uScoolClass"];
		else 
			return null;
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
	 * are the logged in user and aktual viewed user the same?
	 * @return boolean
	 */
	function isAktUserTheLoggedInUser() {
		return (getAktDatabaseName()==getUserDatabaseName() && getAktUserId()==getLoggedInUserId() );
	}

	
	/**
	 * Check login data in each client 
	 */
	function checkUserLogin($user,$passw) {
		$ret = false;
		if (checkRequesterIP()) {
			$ret=false;
			$diak["user"]=$user;
			$diak["passw"]=$passw;
			$usr =getGlobalUser($diak,"compairUserPassw");
			if (null != $usr) {
				setUserInSession(
					$usr["admin"],
					$usr["user"],
					$usr["id"],
					$usr["scoolYear"],
					$usr["scoolClass"]);
				$ret = true;
			}
			else {
				$diak["email"]=$user;
				$usr =getGlobalUser($diak,"compairEmailPassw");
				if (null != $usr) {
					setUserInSession(
						$usr["admin"],
						$usr["user"],
						$usr["id"],
						$usr["scoolYear"],
						$usr["scoolClass"]);
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
		$ret = false;
		if (checkRequesterIP()) {
			$ret=false;
			$diak["facebookid"]=$facebookId;
			$usr =getGlobalUser($diak,"compairFacebookId");
			if (null != $usr) {
				setUserInSession(
					$usr["admin"],
					$usr["user"],
					$usr["id"],
					$usr["scoolYear"],
					$usr["scoolClass"]);
				$ret = true;
				if (!userIsAdmin() && userIsLoggedOn())
					saveLogInInfo("Facebook",$usr['id'],$usr['user'],$facebookId,$ret);
			}		
		}
	}
	
	/**
	 * save user informations in the session
	 * @param User role $admin
	 * @param User name $user
	 * @param User id $uid
	 * @param Scool year $scoolYear
	 * @param Scool class $scoolClass
	 */
	function setUserInSession($admin, $user, $uid, $scoolYear, $scoolClass)
	{
		$_SESSION['uRole']=$admin;
		$_SESSION['uName']=$user;
		$_SESSION['uId']=$uid;
		$_SESSION['uScoolYear']=$scoolYear;
		$_SESSION['uScoolClass']=$scoolClass;
	}
	
	/**
	 * Logout user
	 */
	function logoutUser() {
		$_SESSION['uRole']="";
		$_SESSION['uName']="";
		$_SESSION['uId']=NULL;
		$_SESSION['uScoolYear']="";
		$_SESSION['uScoolClass']="";
		$_SESSION['FacebookId'] = NULL;
		$_SESSION['FacebookName'] = NULL;
		$_SESSION['FacebookEmail'] =  NULL;
	}
	
	/**
	 * a user is logged on
	 */
	function userIsLoggedOn() {
		return ( isset($_SESSION['uId']) && $_SESSION['uId']>-1 );	
			
	}
	
	/**
	 * user is loggen in and is a admin
	 */
	function userIsAdmin() {
		if (isset($_SESSION['uRole'])) 
			return strstr($_SESSION['uRole'],"admin")!="";
		else 
			return false;
	}
	
	/**
	 * user is logged in and is a editor
	 */
	function userIsEditor() {
		if (isset($_SESSION['uRole']) && getAktDatabaseName()==getUserDatabaseName()) 
			return strstr($_SESSION['uRole'],"editor")!="";
		else 
			return false;
	}

	
	/**
	 * user is logged in and is a viewer
	 */
	function userIsViewer() {
		if (isset($_SESSION['uRole'])) 
			return strstr($_SESSION['uRole'],"viewer")!="";
		else 
			return false;
	}

	/**
	 * generate a login key for the aktual user
	 * @return key string
	 */
	function generateAktUserLoginKey() {
		$message= getAKtScoolClass()."-".getAktScoolYear()."-".getAktUserId();
		
		return encrypt_decrypt("encrypt",$message);
	}
	
	/**
	 * generate an login key for the a user in the aktual database
	 * @return Ambigous <boolean, string>
	 */
	function generateUserLoginKey($uid) {
		$message= getAKtScoolClass()."-".getAktScoolYear()."-".$uid;
		
		return encrypt_decrypt("encrypt",$message);
	}

	/**
	 * check if the username is unique in the database
	 */
	function checkUserNameExists($id,$userName) {
		$diak["user"]=$userName;
		$usr =getGlobalUser($diak,"compairUser");
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
		$diak["email"]=$email;
		$usr =getGlobalUser($diak,"compairEmail");
		if (null!=$usr) {
			if ( $usr["id"]==$id)
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
		//Read the Database
		$authData = readUserAuthDB();
		$person = getPersonDummy();
		$ret = -1;
		if (strlen($newPassw)>3) { 
			if (checkRequesterIP()) {
					//check user email
					$diak["email"]=$email;
					$usr =getGlobalUser($diak,"compairEmail");
					if (null != $usr) {
						setAktScoolClass($usr["scoolClass"]);
						setAktScoolYear($usr["scoolYear"]);
						$usr["passw"]=$newPassw;
						$ret = $usr["id"];
						savePerson($usr);
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
		fwrite($file,$_SERVER["REMOTE_ADDR"]."\t".date('d.m.Y H:i')."\t".getUserDatabaseName()."\t".$res."\t".$uid."\t".$action."\t".$cuser."\t".$cpassw."\r\n");
		
	}
	
	
	
	/**
	 * Check the requester IP 
	 * if the IP can't login more then 10 time on a day then return value will set to false 
	 * this is a safety funtion to prevent automatic loging of password crack
	 */
	function checkRequesterIP() {
		//TODO protection against hacking attaks 
		return true;
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
	 * create a log backup file if the file is to big or to old
	 */
	function backupLoginData() {
		//TODO
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