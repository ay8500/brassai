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
$helper = new FacebookRedirectLoginHelper('http://'.$_SERVER["SERVER_NAME"].dirname($_SERVER["SCRIPT_NAME"]).'/fblogin.php');
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
	//graph api request for user data
	$request = new FacebookRequest( $session, 'GET', '/me',array ("locale"=>"hu_HU") );
 	$response = $request->execute();
 	// get response
	$graphObject = $response->getGraphObject();

    $_SESSION['FacebookId'] 		= $graphObject->getProperty('id');           
	$_SESSION['FacebookName'] 		= $graphObject->getProperty('name');
	$_SESSION['FacebookFirstName'] 	= $graphObject->getProperty('first_name');
	$_SESSION['FacebookLastName'] 	= $graphObject->getProperty('last_name');
	$_SESSION['FacebookEmail'] 		= $graphObject->getProperty('email');
    $_SESSION['FacebookLink'] 		= $graphObject->getProperty('link');
	
    //friends
    //$request = new FacebookRequest( $session, 'GET', '/965038823537045/friends',array ("locale"=>"hu_HU") );
    //$response = $request->execute();
    //$graphObject = $response->getGraphObject();
    
	/*
	$ara = $graphObject->asArray();
	echo("Username:".$graphObject->getProperty('name') );
	echo("<br/><br/>");
	echo("Firstname:".$ara["first_name"]." Lastname:".$ara["last_name"]);
	echo("<br/><br/>");
	echo("Mail:".$graphObject->getProperty('email') );
	echo("<br/><br/>");
	echo $graphObject->getProperty('country');
	echo("<br/><br/>");
	$info = $session->getSessionInfo();
	echo $info->getExpiresAt()->format('Y-m-d H:i:s');
	echo("<br/><br/>");
	print_r($ara);
	*/
	
 	header("Location: ../start.php?action=facebooklogin");
} else {
 	$loginUrl = $helper->getLoginUrl(array('req_perms' => 'email'));
 	//$loginUrl = $helper->getLoginUrl(array('req_perms' => 'email,public_profile,user_friends'));
 	//echo("not logged on");
	header("Location: ".$loginUrl);
}

?>