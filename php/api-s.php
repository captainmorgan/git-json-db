<?php

//require_once('PhpConsole.php');
//PhpConsole::start(true, true, dirname(__FILE__));
require_once('../../php-console-master/src/PhpConsole/__autoload.php');
PhpConsole\Connector::getInstance();
PhpConsole\Helper::register();


require_once('api_object.php'); // including service class to work with database
require_once('api_secure.php');

session_start();

// This object should contain
// 1. what we know about the request (parameters, payload, etc)
// 2. methods on setting and getting the above
$object = new APIObject($_REQUEST);

// Grab the Response Body from the request
// Response Body only exists for POST requests, not GET requests
if(getenv('REQUEST_METHOD') == 'POST') { 
    $request_body = file_get_contents("php://input");
    $object->setRequestBody($request_body);
    //echo " and " . $request_body;
    
    // If the body is empty, put the payload in the body, assuming we have a payload
    if (!$request_body) {
    	if (isset($_GET['payload'])) {
    		$object->setRequestBody($_GET['payload']);
    	}
    }
}


if (isset($_REQUEST['apikey']))
{
	echo "API Key included" . $_REQUEST['apikey'] . " ";
	//echo $_REQUEST['apikey']);
	setUserSession($_REQUEST['apikey']);
	$token = new APISecure();
	//$token->test();
	$token->generateToken($_SERVER);
}

if (isset($_REQUEST['method'])) {

	// Grab the requested api method
	switch ($_REQUEST['method']) {
	    case 'test':
 	   		//echo "test";
    	    $object->test();
        	break;
    	case 'echoPayload':
    		if (isset($_GET['payload'])) {
        		$object->echoPayload($_GET['payload']);
        	}
        	break;
        case 'getDog':
        	$object->getDog();
        	break;
        case 'getAllDogBreeds':
    	// Test - This works!
        //DBConfig::write('db.password', 'signature');
        	$object->getAllDogBreeds();
        	break;
        case 'getVets':
        	$object->getVets();
        	break; 
        case 'getRecord':
        	$object->getRecord();
        	break;
        case 'getRecordCount':
        	$object->getRecordCount();
        	break;        	
        case 'getRecordInnerJoin':
        	$object->getRecordInnerJoin();
        	break;          	
        case 'getQuestions':
        	$object->getQuestions(($_GET['payload']));
        	//$object->getQuestions();
        	break;        	
   		case 'getBody':
        	echo $object->getBody();
        	break;
   		case 'echoSchema':
       		$object->echoSchema();
        	break;
    	case 'getSchema':
        	echo $object->getSchema();
        	break;                            
    	case 'createCustomer':
    	// Call the createCustomer method
    	// It creates a new record in the customer table
    	// Returns the ID of that customer
    	// We store that ID in the session variable
        	setUserSession($object->createCustomer());
        	break;      
    	case 'createRecord':
        	//$object->createRecordCaller();
        	$object->createRecord();
        	break;   
        case 'updateRecord':
        	$object->updateRecordCaller();
        	break;	        	  
        case 'updateAllRecord':
        	$object->updateAllRecord();
        	break;
        default:
        	echo "No such method";
	}
}


	function setUserSession($u)
	{
		// The User Session is a cookie that gets stored by the user's browser
		// It includes data for this session and is what "logs in" the user
    	session_regenerate_id (); //this is a security measure
    	$_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'] . "Ele the Dog" . $_SERVER['REMOTE_ADDR']);
		PC::debug("Session fingerprint:".$_SESSION['fingerprint']);	
    	$_SESSION['cid'] = $u;
		PC::debug("Session cid:".$_SESSION['cid']);	    	
    	$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    	$_SESSION['time'] =  time();
	}  

?>