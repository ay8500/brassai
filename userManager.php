<?PHP
	$_SESSION['lastReq']=date('d.m.Y H:i');

	include_once("data.php");
	
	function getLoggedInUserId() {
		if (!isset($_SESSION["uId"]))
			return null;
		return $_SESSION["uId"];
	}
	
	function getUScoolYear() {
		if (isset($_SESSION["uScoolYear"]))
			return $_SESSION["uScoolYear"];
		else 
			return null;
	}
	
	function getUScoolClass() {
		if (isset($_SESSION["uScoolClass"]))
			return $_SESSION["uScoolClass"];
		else 
			return null;
	}
	
	
	function setAktUserId($id) {
		$_SESSION["aktUId"]=$id;
	}
	
	function getAktUserId() {
		return $_SESSION["aktUId"];
	}
	
	function isAktUserTheLoggedInUser() {
		return (getAktDatabaseName()==getUserDatabaseName() && getAktUserId()==getLoggedInUserId() );
	}

	
	/**
	 * Check login data in each client 
	 */
	function checkUserLogin($user,$passw) {
		$ret = false;
		//logoutUser();
		if (checkRequesterIP()) {
			$ret=false;
			$data=readUserAuthDB();
			foreach($data as $key =>$usr) {
				if ((strcasecmp($usr["user"],$user)==0) && ($passw==$usr["passw"])) {
					setUserInSession(
						$usr["admin"],
						$usr["user"],
						$usr["id"],
						$usr["scoolYear"],
						$usr["scoolClass"]);
					$ret = true;
				break;
				}
			}
		}
		if (!userIsAdmin()) 
			saveLogInInfo($_SESSION['uName'],$_SESSION['uId'],$user,$passw,$ret);
		return $ret;
	}

	/**
	 * Check facebook login data in each client
	 */
	function checkFacebookUserLogin($facebookId) {
		$ret = false;
		//logoutUser();
		if (checkRequesterIP()) {
			$ret=false;
			$data=readUserAuthDB();
			foreach($data as $key =>$usr) {
				if ($usr["facebookid"]==$facebookId) {
					setUserInSession(
						$usr["admin"],
						$usr["user"],
						$usr["id"],
						$usr["scoolYear"],
						$usr["scoolClass"]);
					$ret = true;
					break;
				}
			}
		}
		if (!userIsAdmin() && userIsLoggedOn())
			saveLogInInfo($_SESSION['uName'],$_SESSION['uId'],"Facebook",$facebookId,$ret);
		return $ret;
	}
	
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
	 * user is loggen in and he is an admin
	 */
	function userIsAdmin() {
		if (isset($_SESSION['uRole'])) 
			return strpos($_SESSION['uRole'],"admin")===0;
		else 
			return false;
	}
	
	/**
	 * user is logged in and he is an editor
	 */
	function userIsEditor() {
		if (isset($_SESSION['uRole']) && getAktDatabaseName()==getUserDatabaseName()) 
			return strpos($_SESSION['uRole'],"editor")===0;
		else 
			return false;
	}

	
	/**
	 * user is logged in and he is an viewer
	 */
	function userIsViewer() {
		if (isset($_SESSION['uRole'])) 
			return strpos($_SESSION['uRole'],"viewer")===0;
		else 
			return false;
	}
	
/**
 * check if the username is unique in the database
 */
function checkUserNameExists($id,$userName) {
	$ret=false;

	$data=readUserAuthDB();

	$actDataBase=getUserDatabaseName();
	foreach ($data as $person) {
		if ((strcasecmp($userName,$person['user'])==0)) {		//same username found
			if ( !(($person["id"]==$id) && ($actDataBase==$person["scoolClass"].$person["scoolYear"])))  { 		//and username is not in the same record
				$ret= true;
				break;
			}
		}
	}
	return $ret;
}

	
	
	/**
	 * Set user password
	 * return value 
	 * >0 -> Password set the result value is the person id 
	 * -1 -> Email not found, 
	 * -2 -> Passw to short, 
	 * -3 -> Sequrity violation 
	 */
	function setUserPasswort($email, $newPassw) {
		//Read the Database
		readDB();
		global $data;
		$ret = -1;
		if (strlen($newPassw)>3) { 
			if (checkRequester()) {
					//check user email
					foreach($data as $key =>$person) {
						if ((strcasecmp($person["email"],$email)==0) ) {
							$person["passw"]=$newPassw;
							$ret = $key;
							savePerson($person);
							break;
						}
					}
			}
			else $ret = -3;
		}
		else $ret =-2;
		saveLogInInfo("NewPassword","",$email,$newPassw,$ret);
		return $ret;
	}
	
	/**
	 * create a new user
	 */
	function createNewUser($myname,$email,$passw,$rights) {
		// todo
		return 0;
	}
	
	/**
	 * Create a random password
	 */
	function createPassword($length) {
		$chars = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i <= $length) {
			$password .= $chars{mt_rand(0,strlen($chars))};
			$i++;
		}
		return $password;
	}

//*********************** Loging *************************************************	
	
	
	/**
	 * Save login information for statistics and sequrity reasons
	 * parameter $user SaveData,SavePassw,SaveGeo, NewPassword, 
	 */
	function saveLogInInfo($user,$uid,$cuser,$cpassw,$result) {
		$file=fopen("login.log","a");
		if ($result) $res="true"; else $res="false";
		fwrite($file,$_SERVER["REMOTE_ADDR"]."\t".date('d.m.Y H:i')."\t".getAktDatabaseName()."\t".$res."\t".$uid."\t".$user."\t".$cuser."\t".$cpassw."\r\n");
		
	}
	
	
	
	/**
	 * Check the requester IP 
	 * if the IP can't login more then 10 time on a day then return value will set to false 
	 * this is a safety funtion to prevent automatic loging of password crack
	 */
	function checkRequesterIP() {
		return true;
	}

	/**
	 * read login log in memory ($logdata[])
	 */
	function readLogingData() {
		backupLoginData();
		$logData = array();
		$logDataField = array("IP","Date","Scool","Result","ID","User","CUser","Passw");
		$file=fopen("login.log","r");
		$i=0;
		while (!feof($file)) {
			$b = explode("\t",fgets($file));
			foreach($logDataField as $idx => $field) {
				if (isset($b[$idx])) $logData[$i][$logDataField[$idx]] = $b[$idx]; else $logData[$i][$logDataField[$idx]] ="";
			}
			$i++;
		}
		return $logData;
	}
	
	/**
	 * create a log backup file if the file is to big or to old
	 */
	function backupLoginData() {
		//todo
	}
	
?>