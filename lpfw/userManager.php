<?php
/**
 * @version 2019.05.10
 */
include_once __DIR__ . "/../config.class.php";
include_once 'iDbDaUser.class.php';

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
	 * get logged in name including first and lastname
     * @param \maierlabs\lpfw\iDbDaUser $db;
	 * @return string
	 */
	function getLoggedInUserName($db) {
        if (null==getLoggedInUserId())
            return "";
		$loggedInUser=$db->getUserById(getLoggedInUserId());
		if ($loggedInUser!=null) {
			return $db->getPersonName($loggedInUser);
		} 
		else
			return "";
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
			return intval($_SESSION["aktUId"]);
		else 
			return null;
	}


	/**
	 * are the logged in user and aktual viewed user the same?
	 * @return boolean
	 */
	function isAktUserTheLoggedInUser() {
		return (getAktUserId()==getLoggedInUserId() );
	}

    /**
     * is the person a guest
     * @return boolean*/
    function isPersonGuest($person) {
        return (isset($person["role"]) && strstr($person["role"],"guest")!="");
    }

    /**
     * is the person a admin
     * @return boolean*/
    function isPersonAdmin($person) {
        return (isset($person["role"]) && strstr($person["role"],"admin")!="");
    }

    /**
     * is the person a editor
     * @return boolean
     */
    function isPersonEditor($person) {
        return (isset($person["role"]) && strstr($person["role"],"editor")!="");
    }



     /**
	 * Check login data an set role username an id in the session array
     * @param \maierlabs\lpfw\iDbDaUser $db
     * @param string $user
     * @param string $passw
      *@param boolean $ismd5 is the password md5 encoded
     * @return boolean
	 */
	function checkUserLogin($db,$user,$passw) {
		if (\Config::$secret_key==null) {
		    $dpassw = md5($passw);
		} else {
		    $dpassw= encrypt_decrypt("encrypt",$passw);
		}

		$usr = $db->getUserByUsename($user);
		if (null != $usr && $usr["passw"]==$dpassw) {
			setUserInSession($db, $usr["role"], $usr["user"],	$usr["id"]);
			return true;
		} else {
			$usr =$db->getUserByEmail($user);
			if (null != $usr && $usr["passw"]==$dpassw) {
				setUserInSession($db, $usr["role"], $usr["user"], $usr["id"]);
				return true;
			}
		}
		return false;
	}

	/**
	 * Check facebook login data in each client
     * @param  \maierlabs\lpfw\iDbDaUser $db
	 */
	function checkFacebookUserLogin($db,$facebookId) {
		$usr =$db->getUserByFacebookId($facebookId);
		if (null != $usr) {
			setUserInSession($db, $usr["role"], $usr["user"], $usr["id"]);
			if (!userIsAdmin() && userIsLoggedOn())
                \maierlabs\lpfw\Logger::_("Facebook\t".getLoggedInUserId()."\t".$facebookId);

            return true;
		}		
		return false;
	}
	
	/**
	 * save user informations in the session
     * @param  \maierlabs\lpfw\iDbDaUser $db
	 * @param User role $admin
	 * @param User name $user
	 * @param User id $uid
	 */
	function setUserInSession($db,$role, $user, $uid )
	{
		$_SESSION['uRole']=$role;
		$_SESSION['uName']=$user;
		$_SESSION['uId']=$uid;
		$db->setUserLastLogin($_SESSION['uId']);
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
     * @param  \maierlabs\lpfw\iDbDaUser $db
	 */
	function checkUserNameExists($db,$id,$userName) {
		$usr = $db->getUserByUsename($userName);
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
     * @param  \maierlabs\lpfw\iDbDaUser $db
	 */
	function checkUserEmailExists($db,$id,$email) {
		$usr = $db->getUserByEmail($email);
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
     * @param  \maierlabs\lpfw\iDbDaUser $db
     *
	 * return value 
	 * >0 -> Password set the result value is the person id 
	 * -1 -> Email not found, 
	 * -2 -> Passw to short, 
	 * -3 -> Sequrity violation 
	 */
	function resetUserPasswort($db,$email, $newPassw) {
		if (strlen($newPassw)>4) {
			if ($db->checkRequesterIp(changeType::newPassword)) {
				$usr = $db->getUserByEmail($email);
                if (null != $usr) {
						$db->setUserPassword($usr["id"],encrypt_decrypt("encrypt",$newPassw));
						$db->setRequest(changeType::newPassword);
						$ret = $usr["id"];
                        \maierlabs\lpfw\Logger::_("NewPassword\t".getLoggedInUserId());
                    return $ret;
				}
				return -1; //email not found
			}
			return -3; //to manny changes
		}
		return -2; //password to short
	}
	
	
	/**
	 * Create a random password
	 */
	function createPassword($length=10) {
		$chars = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i <= $length) {
		    mt_srand();
			$password .= $chars{mt_rand(0,strlen($chars)-1)};
			$i++;
		}
		return $password;
	}

	/**
	 * Krypt or encrypt a string
	 * @param string $action "encrypt" or "decrypt"
	 * @param string $string
	 * @return string
	 */
	function encrypt_decrypt($action, $string) {
		$output = false;
	
		$encrypt_method = "AES-256-CBC";

		// hash
		$key = hash('sha256', \Config::$secret_key);
	
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', \Config::$secret_iv), 0, 16);
	
		if( $action == 'encrypt' ) {
            if (strlen($string)!=32 && strlen($string)!=60 && strlen($string)!=88 ) {
			    $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			    return base64_encode($output);
            } else {
                return $string;
            }
		}
		else if( $action == 'decrypt' ){
		    if (strlen($string)==32 || strlen($string)==60 || strlen($string)==88 ) {
			    return openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		    } else {
		        return $string;
            }
		}
	
		return $string;
	}
	
?>