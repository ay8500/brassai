<?php
session_start();
// added in v4.0.0
require_once 'autoload.php';
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;

// init app with app id and secret
FacebookSession::setDefaultApplication( '1606012466308740','4845414b223ba81a17eba1c4af7e6a95' );
// login helper with redirect_uri
    $helper = new FacebookRedirectLoginHelper('http://brassai.blue-l.de/fb/fblogin.php');
	//$helper = new FacebookRedirectLoginHelper('http://192.168.201.40/brassai/fb/fblogin.php');
try {
  $session = $helper->getSessionFromRedirect();
} catch( FacebookRequestException $ex ) {
   echo("Facebook returns an error");
} catch( Exception $ex ) {
  echo("validation fails or other local issues");
}
// see if we have a session
if ( isset( $session ) ) {
  // graph api request for user data
  $request = new FacebookRequest( $session, 'GET', '/me' );
  $response = $request->execute();
  // get response
  $graphObject = $response->getGraphObject();
		$ara = $graphObject->asArray();
     	$fbid = $graphObject->getProperty('id');              // To Get Facebook ID
 	    $fbfullname = $graphObject->getProperty('name'); // To Get Facebook full name
	    $femail = $graphObject->getProperty('email');    // To Get Facebook email ID
	    $_SESSION['FacebookId'] = $fbid;           
        $_SESSION['FacebookName'] = $fbfullname;
	    $_SESSION['FacebookEmail'] =  $femail;
	 /*
	echo("Username:".$fbfullname = $graphObject->getProperty('name') );
	echo("<br/><br/>");
	print_r($_SERVER["HTTP_REFERER"]);
	echo("<br/><br/>");
	echo $graphObject->getProperty('country');
	echo("<br/><br/>");
	$info = $session->getSessionInfo();
	echo $info->getExpiresAt()->format('Y-m-d H:i:s');
	echo("<br/><br/>");
	print_r($ara);
	*/
	
  header("Location: ../start.php");
} else {
  $loginUrl = $helper->getLoginUrl();
  //echo("not logged on");
 header("Location: ".$loginUrl);
}

?>