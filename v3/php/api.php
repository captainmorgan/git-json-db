<?php

require_once('./PhpConsole/__autoload.php');
PhpConsole\Connector::getInstance();
PhpConsole\Helper::register();
//PC::debug("Starting API");

require_once('api_object.php'); // including service class to work with database
require_once('api_object_test.php'); // including service class to work with database
require_once('api_object_daycare.php'); // including service class to work with database


session_start();

// This object should contain
// 1. what we know about the request (parameters, payload, etc)
// 2. methods on setting and getting the above
$object = new APIObject($_REQUEST);
$objecttest = new APIObjectTest($_REQUEST);
$daycareobj = new Daycare($_REQUEST);

// Grab the Response Body from the request
// Response Body only exists for POST requests, not GET requests
if(getenv('REQUEST_METHOD') == 'POST') { 
    $request_body = file_get_contents("php://input");
    $object->setRequestBody($request_body);
    $objecttest->setRequestBody($request_body);
    $daycareobj->setRequestBody($request_body);
    //echo " and " . $request_body;
    
    // If the body is empty, put the payload in the body, assuming we have a payload
    if (!$request_body) {
    	if (isset($_GET['payload'])) {
    		//$object->setRequestBody($_GET['payload']);
    		$objecttest->setRequestBody($_GET['payload']);
    		$daycareobj->setRequestBody($_GET['payload']); // Test.  This may not work because you can only use GET once per var
    	}
    }
}

if (isset($_REQUEST['method'])) {

	//PC::debug("Method is " . $_REQUEST['method']);
	// Grab the requested api method
	switch ($_REQUEST['method']) {
    	case 'echoPayload':
    		if (isset($_GET['payload'])) {
        		$object->echoPayload($_GET['payload']);
        	}
        	break;
        case 'getDog':
        	$objecttest->getDog();
        	break;
        case 'getAllDogBreeds':
        	$objecttest->getAllDogBreeds();
        	break;
        case 'getVets':
        	$objecttest->getVets();
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
        	setUserSession($objecttest->createCustomer());
        	//$objecttest->createCustomer();
        	break;
    	case 'createRecord':
        	//$object->createRecordCaller();
        	$object->createRecord();
        	break;   
        case 'updateRecord':
        	//$object->updateRecordCaller();
        	$object->updateRecord();
        	break;

        //case 'updateRecord2':
        //	$object->updateRecord2();
        //	break;	        	
        	        	  
        case 'updateAllRecord':
        	$object->updateAllRecord();
        	break;
        	
        case 'getDelegatesForDog':
        	$objecttest->getDelegatesForDog();
        	break;
        case 'addDog':
        	$objecttest->addDog();
        	break;
        case 'addDelegate':
        	$objecttest->addDelegate();
        	break;
        case 'daycareCheckInDog':
        	//$objecttest->daycareCheckInDog();
        	$daycareobj->daycareCheckInDog();
        	break;
        case 'getCustomerById':
        	$objecttest->getCustomerById();
        	break;
        case 'getCustomerIdByEmail':
        	$objecttest->getCustomerIdByEmail($_GET['payload'], 1);
        	break;
        // Invoke the silent option, used only as a test
        case 'getCustomerIdByEmailSilent':
        	$objecttest->getCustomerIdByEmail($_GET['payload'], 0);
        	PC::debug("Returned from extension");
        	break;
        case 'descSchema':
        	$objecttest->descSchema($_GET['payload'], 0);
        	break;
        case 'getSchemaExample':
        	$objecttest->getSchemaExample($_GET['payload']);
        	break;
       case 'addDogWithEmail':
        	$objecttest->addDogWithEmail();
        	break;
        case 'addDelegateWithEmail':
        	$objecttest->addDelegateWithEmail();
        	break;
        case 'daycareIsDogAlreadyCheckedIn':
        	$daycareobj->daycareIsDogAlreadyCheckedIn();
        	break;
        case 'daycareCheckOutDog':
        	$daycareobj->daycareCheckOutDog($_GET['payload']);
        	break;
        case 'daycareDogAction':
        	$daycareobj->daycareDogAction();
        	break;
        case 'actionTimeOut':
        	$daycareobj->actionTimeOut();
        	break;
        case 'actionDogBowl':
        	$daycareobj->actionDogBowl();
        	break;
        case 'actionWalk':
        	$daycareobj->actionWalk();
        	break;
        case 'getData':
        	$daycareobj->getData();
        	break;
        case 'isDogAlreadyCheckedIn':
        	$daycareobj->isDogAlreadyCheckedIn();
        	break;
        case 'getDogDataByID':
        	$daycareobj->getDogDataByID();
        	break;
        case 'updateDogRecord':
        	$daycareobj->updateDogRecord();
        	break;
        case 'getActiveTags':
        	$daycareobj->getActiveTags();
        	break;
        case 'getDogCount':
        	$daycareobj->getDogCount($_GET['payload']);
        	break;	
        case 'getActiveTrainers':
        	$daycareobj->getActiveTrainers($_GET['payload']);
        	break;
        case 'getDogsByEmail':
        	$daycareobj->getDogsByEmail($_GET['payload']);
        	break;
        case 'getDogsByOther':
        	$daycareobj->getDogsByOther($_GET['payload']);
        	break;
        case 'getDogsOnWalk':
        	$daycareobj->getDogsOnWalk();
        	break;
        case 'getCustomerVisitCount':
        	$daycareobj->getCustomerVisitCount($_GET['payload']);
        	break;
        case 'callGetPassword':
        	$object->callGetPassword();
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
		//PC::debug("Session fingerprint:".$_SESSION['fingerprint']);	
    	$_SESSION['cid'] = $u;
		//PC::debug("Session cid:".$_SESSION['cid']);	    	
    	$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    	$_SESSION['time'] =  time();
	}  

?>