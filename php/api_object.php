<?php

/*
Project Description:
This is an API framework to interact with a database using REST web services.
You can use this API to perform standard CRUD (Create, Retreive, Update and Delete functions.

This API will also interact with the database using various users, each having been granted
lease priviledge for the requested function.  


Author: Christopher Morgan
Contact: christopher.t.morgan -at- gmail
Copyright 2013
*/


// Include Major Child Classes
require_once('create_record.php');
require_once('update_record.php');
require_once('update_all_record.php');
require_once('get_record.php');
require_once('get_record_count.php');
require_once('get_record_inner_join.php');
require_once('build_questions.php');
// Include Helper Classes
require_once('dbcore.class.php');


class APIObject {
    
    // Class properties
    public $Request;		// Contents of $_REQUEST, which we pass in during instantiation
    public $RequestBody;	// Contents of the POST Request Body    
    public $payload;		// Just a string of data we pass need arbitrarily named "payload"
    public $method;			// A string naming the method we want to call
    public $schema;			// A string that we match against a known, defined JSON Schema
    public $pkey;			// Testing this for use of a primay key (secondary payload)
    
    public $ResponseBody;
    
    protected $conn;		// PDO Database Connection Handler

	// Our Schema Library follows
	// These schemas are important since when INSERTing, the data and schema must match
	// But we don't want to expose the schema of each table for security reasons.
	
	protected $schemaLabel_customertest = "{\"table\":\"customertest\",\"fields\":[\"first_name\",\"last_name\",\"phone\",\"dob\"]}";

    protected $schemaLabel_customer = "{
						\"table\":\"customer\",
						\"fields\":
									[
									\"salutation\",
   									\"first_name\",
    								\"last_name\",
									\"phone\",
									\"email\",
									\"marketing_email\",
									\"address_street\",
									\"address2\",
									\"city\",
									\"state\",
									\"zip\",
									\"country\",
									\"company\",
									\"work_phone\",
									\"work_street1\",
									\"work_street2\",
									\"work_city\",
									\"work_state\",
									\"work_zip\",
									\"work_country\",
									\"email_pref\",
									\"referral\",
									\"dob\"
									]
									}";
									
	protected $schemaLabel_dogtest = "{\"table\":\"dogtest\", \"fields\":[\"dog_name\",\"dog_notes\",\"owner_id\"]}";
	
	/* protected $schemaLabel_dog = "{\"table\":\"dog\", \"fields\":[\"dog_name\", \"breed\", \"dog_notes\",\"owner_id\"]}"; */
    
    protected $schemaLabel_dog = "{\"table\":\"dog\", \"fields\":[\"dog_name\", \"breed\", \"behaviors\", \"allergies\", \"dog_birthday\", \"dog_dhp\", \"dog_rabies\", \"dog_bordetella\", \"dog_giardia\", \"vet\", \"dog_notes\", \"gender\", \"furcolor\", \"pawwidth\", \"neckgirth\", \"waistgirth\", \"height\", \"license\", \"microchip\", \"owner_id\"]}";
    
    protected $schemaLabel_delegate = "{\"table\":\"delegate\", \"fields\":[\"first_name\",\"last_name\",\"phone\",\"email\",\"owner_id\"]}";
    
    protected $schemaLabel_question = "{\"table\": \"question\", \"fields\": [\"text\", \"type\", \"options\", \"default\", \"guidelines\"]}";

    protected $schemaLabel_simpletest = "{\"table\": \"simpletest\", \"fields\": [\"id\", \"one\", \"two\"]}";
    
    protected $schemaLabel_daycare = "{\"table\": \"daycare\", \"fields\": [\"tag\", \"dog_name\", \"dog_id\", \"customer_last_name\", \"status\", \"trainer\", \"location\", \"sublocation\", \"time_check_in\", \"time_check_out\"]}";    
    
    //protected $schemaLabel_simpletest = "{\"table\": \"simpletest\",\"set\": {\"one\": \"jupiter\"}, \"where\": {\"id\": \"1\"}}";


    // Constructor -> runs by default when class is instantiated
    // We are passing in the PHP global $_REQUEST variable and setting the params from that
    function APIObject($R) {
    
    	$this->Request = $R;
    
     	if (isset($this->Request['payload'])) {
			$this->payload = $R['payload'];
			//echo $this->payload;
		}
		if (isset($this->Request['method'])) {
			$this->method = $R['method'];
			//echo $this->method;
		}
     	if (isset($R['schema'])) {
			$this->schema = $R['schema'];
			//echo $this->schema;
		}
     	if (isset($R['pkey'])) {
			$this->pkey = $R['pkey'];
			//echo $this->schema;
		}  		
    }

    // my method
    public function test() {
    /*	echo "Instantiate Sub-Class\r\n";
        $record = new CreateRecord($this->Request);
        $record->setRequestBody($this->RequestBody);
        echo "From the sub-class: ";
        print_r($record->RequestBody);
        echo "...fin \n";
        $record->reportWhatIKnow();
        */
    }
 
 	// PDO Class version
 	private function connectDB () {
 	
 	//DBConfig::write('db.password', 'signature');
 	
 		try {
 		
 			$db = DBCore::getInstance();
 			$db->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$db->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->conn->exec('SET NAMES utf8');
			$this->conn = $db->conn;
			//debug("Successfully connected to database.");
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
   			//debug("Unsuccessful in connecting to database...");
   			exit();
		} 			
 	
 	}
 
 
 /*
 //******************************   
    private function connectDB () {
    
    	$mysql_host 	= 	'localhost';
		$mysql_user 	= 	'signature';
		$mysql_pass 	= 	'signature';
		$mysql_db 		= 	'jquery';

		try {
    		$this->conn = new PDO("mysql:host=$mysql_host;dbname=$mysql_db", $mysql_user, $mysql_pass);
    		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		// Make sure we are talking to the database in UTF-8
   			$this->conn->exec('SET NAMES utf8');
			//debug("Successfully connected to database.");
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
   			//debug("Unsuccessful in connecting to database...");
   			exit();
		}
    }
 */   
    private function disconnectDB () {
    
    	try {
    		$this->conn = null;
    	} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
   			//debug("Unsuccessful in disconnecting to database...");
   			exit();
		}
    }
//*********************************

    // Set the Request Body variable of the class from the POST Request Body
    public function setRequestBody($s) {
        $this->RequestBody = $s;
        //echo "this is a test ";
        //echo $this->inBody;
        return;
    }

    // Returns the contents of RequestBody variable
    public function getBody() {
        return $this->RequestBody;
    }
    
    
    // Set the schema variable of the class
    public function setSchema($s) {
        $this->schema = $s;
        //echo "this is a test ";
        echo "Schema to lookup: ".$this->schema;
        return;
    }

    // Returns the contents of RequestBody variable
    public function echoSchema() {
        //return $this->schema;
        echo "Here is your schema: \n";
        
		//echo 

    }
        
    
    // Returns a JSON String of the schema, as the API expects
    // This looks at the Schema Library in this class attribute variables
    private function getSchema() {
        switch ($this->schema) {

  		case "customer":
  			return trim($this->schemaLabel_customer);
  			break;      	
    	case "customertest":
        	return trim($this->schemaLabel_customertest);
        	break;
    	case "dogtest":
        	return trim($this->schemaLabel_dogtest);
       	 	break;
    	case "dog":
        	return trim($this->schemaLabel_dog);
       	 	break;
    	case "delegate":
        	return trim($this->schemaLabel_delegate);
       	 	break;      
    	case "daycare":
        	return trim($this->schemaLabel_daycare);
       	 	break; 
       	 case "question":
        	return trim($this->schemaLabel_question);
       	 	break;    	 	       	 	
    	case "test":
        	echo "This is a test";
        	break;
        case "simpletest":
        	return trim($this->schemaLabel_simpletest);
        	break;	
		}
    }    

    
    // This function exists so that the main API can call a protected function
    public function createRecordCaller() {
    	$this->createRecord($this);
    }
 
     // This function exists so that the main API can call a protected function
    public function updateRecordCaller() {
    	$this->updateRecord();
    }
    
    // Create a generic record
    public function createRecord() {
       // We will instantiate a new CreateRecord Object
       // That requires the Schema (param property)
       // and Data (RequestBody)
       // This should return the ID of the new table row
       
       //DBConfig::write('db.password', 'signature');
       $this->connectDB();
       
        //$rec = new CreateRecord($this->Request);
       $rec = new CreateRecord($this); 
    
        $rec->setRequestBody($this->RequestBody);
       	$rec->setSchemaJSON($this->getSchema());
       	$rec->setDataJSON($this->getBody());
       	$rec->dBInsert($this->Request);
       	echo $rec->createdID;
       	
       $this->disconnectDB();
       
        //$cust->reportWhatIKnow();
       
       // TEST Data Format
       // {"first_name":"piper3", "last_name":"chicken", "phone":"998766", "dob":"2013-07-04"}
       // {"dog_name":"Piper", "dog_notes":"chicken", "owner_id":"1"}
    }
    
      
    // This function exists so that the main API can call a protected function
    public function createCustomerCaller() {
    	
    		$this->createCustomer($this);
    }
    
    
    // createCustomer
    public function createCustomer() {
       // We will instantiate a new CreateRecord Object
       // That requires the Schema (param property)
       // and Data (RequestBody)
       // This should return the ID of the new table row (customertest.id)
       // 
       // Should the insert stuff be in the main class?  I don't think so, because we will have UpdateRecord, QueryRecord sub-classes eventually
       // schema, and jsondata don't need to be passed in, the class already knows about them
       
        $this->connectDB();
        $cust = new CreateRecord($this);
       
        //$cust = new CreateRecord($this->Request);
        $cust->setRequestBody($this->RequestBody);
       	$cust->setSchemaJSON($this->schemaLabel_customer); // Since this is create*Customer*, this is hard-linked
       	$cust->setDataJSON($this->getBody());
       	$cust->dBInsert($this->Request);
       	echo $cust->createdID;
       	return $cust->createdID;
    }
 
     // Update a generic record
    protected function updateRecord() {
       // That requires the Schema (param property)
       // and Data (RequestBody)
       // This should return the ID of the new table row
        $up = new UpdateRecord($this->Request);
       	
       	// Test
       	$up->setSchemaJSON($this->getSchema());
       	
       	$this->ResponseBody = $up->dBUpdate(trim($this->getBody()));       
       
       // TEST Data Format
       // {"table": "dog",  "set":{"dog_notes": "Nothing NEW"}, "where": {"dog_name": "ele", "id": "1"}}
    }
    
    
     // The UpdateAll method; a different way to update records
     // Usage:
     //		api.php?method=updateAllRecord&schema=dogtest&pkey=7
     //		where "pkey" is the Primary Key ID of the record and
     //	UpdateAll is relevant because it uses a single JSON in the Request Body, just like
     // the CreateRecord method.
     //		"schema" is the static schema (table and columns) you are updating
    public function updateAllRecord() {
        // That requires the Schema (param property)
        // and ID ('payload' param property)
        // and Data (RequestBody)
        // This should return the ID of the updated table row
		       
        // Connect to the database as a user that has the UPDATE and SELECT priviledges
        DBConfig::write('db.user', 'captain_update');
    	DBConfig::write('db.password', 'captain');
      	$this->connectDB();
       
        $up = new UpdateAllRecord($this);
        $up->setRequestBody($this->RequestBody);
       	$up->setTableFromSchema($this->getSchema());
       	$up->setFieldsFromSchema($this->getSchema());
       	$up->setDataFromJSON($this->RequestBody);
       	//$up->setIDfromPayload($this->payload);
       	$up->setIDfromPayload($this->pkey);
       	$up->setSetClause($this->RequestBody);
       	$this->ResponseBody = $up->dBUpdate(trim($this->getBody()));
       
       	$this->disconnectDB();
    }
    

    
    // Function to query the database and return the results set
    // Function returns an Array of JSON Objects of the result set
    // Example:
    // URL/Endpoint: 'api.php?method=getRecord'
    // Request Body: '{"table": "question", "fields": ["default", "options", "type"], "where":{"active":"1"}}'
    // Output: [{"default":"Hazleton","options":"","type":"1"},{"default":"Libra","options":"","type":"1"}]
    //
    // 
    // This shold be PRIVATE so a user can't query any random table and has to use the helper
    //   functions; getDog(), getCustomer(), getDelegate(), etc
    public function getRecord() {

		// Call this database function as a specific user
		// This user should only have the SELECT priviledge
		// Because if there is a bug in this code, the database is still better protected
    	DBConfig::write('db.user', 'captain_read');
    	DBConfig::write('db.password', 'captain');
    	$this->connectDB();

    	$cc = new GetRecord($this);
		$this->ResponseBody = $cc->dBQuery(trim($this->getBody()));

		echo $this->ResponseBody;
		$this->disconnectDB();
    }
 
 
 	// Function to return a count of the records.  An extension class of GetRecord.
 	// Functions returns an integer of the MySQL COUNT(*) clause
 	// Example:
 	// URL/Endpoint: 'api.php?method=getRecordCount'
    // Request Body: {"table": "question", "fields": ["distinct page"], "where":{"active":"1"}}
    // Output: 3
    // Alternative Request Body: {"table": "question", "fields": ["*"], "where":{"active":"1"}}
    // Output: 7
    // Alternative Request Body: {"table": "question", "fields": ["distinct page"], "where":{"active":"1"}}
    // Output: If 'page' is a SQL keyword, there is an error.  If 'page' is not, output would be 3
 	// Accepts the same inputs as GetRecord INCLUDING a field element!
     public function getRecordCount() {
    	
    	$cc = new GetRecordCount($this->RequestBody);
		$this->ResponseBody = $cc->dBCount(trim($this->getBody()));
		echo $cc->count;
    }   
    
    // Function to return the equivalent of a GetRecord on two tables, using a left inner join and 'on' clause
    // Two tables is a current limitation of the API
    // TODO: This method is buggy and may be vulneratble to SQL Injection
    // Usage Instructions:
    // URL/Endpoint: api.php?method=getRecordInnerJoin
    // Request Body: {"table": "delegate", "join": "dog", "fields": ["first_name", "last_name", "delegate.id"], "where": {"dog.id":"136"}, "on":{"delegate":"owner_id", "dog":"owner_id"}}
    // Result: [{"first_name":"MDelegate","last_name":"ZDelegate","id":27},{"first_name":"MDelegate2","last_name":"ZDelegate2","id":28}]
     public function getRecordInnerJoin() {
    	DBConfig::write('db.user', 'captain_read');
    	DBConfig::write('db.password', 'captain');
    	$this->connectDB();
    	$rec = new GetRecordInnerJoin($this);
		$this->ResponseBody = $rec->dBQuery(trim($this->getBody()));
		echo $this->ResponseBody;
		$this->disconnectDB();
    }   

    
    // Function to return a query of the Dog table.  This is a front-end to the GetRecord method
    // Usage Instructions:
    // URL/Endpoint: api.php?method=getDog
    // Request Body: JSON Object, such as: {"table": "dog","fields": ["dog_name","dog_notes","owner_id"],"where": {"dog_name": "ele", "id": "1"}}
	// The table specificed in the Request Body JSON Object must be "dog", or there is an error.
	// Returns an Array of JSON Objects, such as: [{"dog_name":"ele","dog_notes":"Nothing NEW","owner_id":"3"}]
	// Returns an empty array if there are no results, such as: []
	// Returns an Error Message if the Dog table is not specified, or if the JSON Object is malformed.
    public function getDog() {
    	
    	$dataArr = json_decode(trim($this->getBody()), true);
    	
    	// Check to see if we're dealing with the 'dog' table
    	if ($dataArr['table'] === "dog") {
    		$this->getRecord();
    	}

/*
    		// Test Cases
    		//$test2 = "{\"table\": \"dog\",\"fields\": [\"dog_name\",\"dog_notes\",\"owner_id\"],\"where\": {\"dog_name\": \"ele\", \"id\": \"1\"}}"
    		//$cc->setSchemaJSON(trim("{\"table\": \"dog\",\"fields\": [\"dog_name\",\"dog_notes\",\"owner_id\"]}"));
    		//$test2 = "{\"table\": \"dog\",\"fields\": [\"dog_name\",\"dog_notes\",\"owner_id\"],\"where\": {\"dog_name\": \"ele\"}}";
    		//$cc->setSchemaJSON(trim("{\"table\": \"customer\",\"fields\": [\"id\",\"first_name\",\"last_name\"],\"where\": {\"first_name\": \"Christopher\", \"id\": \"100\"}}"));
    		//test2 = "{\"table\": \"customer\",\"fields\": [\"first_name\",\"dob\",\"last_name\"],\"where\": {\"dob\": \"2013-07-09\"}}"
    		// *** Order 
    		//$cc->setSchemaJSON(trim("{\"table\": \"customer\",\"fields\": [\"first_name\",\"id\",\"last_name\"], \"order\": [\"last_name\", \"first_name\"]}"));
    		//$cc->setSchemaJSON(trim("{\"table\": \"customer\",\"fields\": [\"id\",\"first_name\",\"last_name\"], \"where\": {\"last_name\": \"Morgan\"}, \"order\": [\"first_name\"]}"));
    		// *** Customer Order no Where
    		//$test2 = "{\"table\": \"customer\",\"fields\": [\"id\",\"first_name\",\"last_name\"], \"order\": [\"first_name\"]}";
    		// *** Customer multi Order no Where
    		//$test2 = "{\"table\": \"customer\",\"fields\": [\"id\",\"first_name\",\"last_name\"], \"order\": [\"last_name\", \"first_name\"]}";
    		// *** Valide Range and RangeStart
    		//$cc->setSchemaJSON(trim("{\"table\": \"customer\",\"fields\": [\"first_name\",\"id\",\"last_name\"], \"range\": \"3\", \"range_start\": \"2\"}"));
    		// *** RangeStart, but not Range -> Should ignore it
    		//$cc->setSchemaJSON(trim("{\"table\": \"customer\",\"fields\": [\"first_name\",\"id\",\"last_name\"], \"range_start\": \"2\"}"));
    		// *** Just Range, should assume RangeStart = 0;
    		//$test2 = "{\"table\": \"customer\",\"fields\": [\"first_name\",\"id\",\"last_name\"], \"range\": \"3\"}";
			// *** Customer Where, Order and Range
    		//$cc->setSchemaJSON(trim("{\"table\": \"customer\",\"fields\": [\"id\",\"first_name\",\"last_name\"], \"where\": {\"last_name\": \"Morgan\"}, \"order\": [\"first_name\"], \"range\": \"3\"}"));
    		// *** Not valid JSON
    		//$cc->setSchemaJSON(trim("{\"table\": \"dog\",\"fields\": [\"dog_name\",\"dog_notes\",\"owner_id\""));
		
			//$test2 = "{\"table\": \"dog\",\"fields\": [\"dog_name\",\"dog_notes\",\"owner_id\"],\"where\": {\"dog_name\": \"ele\", \"id\": \"1\"}}";
*/

		else {
			echo "Error.  Not a dog";
		}
    }


	// Function to return a list of dog breeds.  This is a front-end to the GetRecords method.
	// Dog Breeds are stored in the 'breed' table
	// Accepts a search parameter as the 'payload'
	// If payload is not provided, it returns all the breeds.
	// Example: 'api.php?method=getAllDogBreeds' will return everything
	//  or 'api.php?method=getAllDogBreeds&payload=Belgian Malinois' will return what matches the payload string
	// Output is a JSON String in the format:
	//
	// { "term": "belgian",				<-- Payload
	//	"results": [
	//				{"id":"21","text":"Belgian Malinois"},{"id":"22","text":"Belgian Sheepdog"}   <-- Results
	//				]
	// }
	//
	// Request Body is ignored
	//
	public function getAllDogBreeds() {
    	// This would allow method-specific parameters
    	// This is not yet implemented
     	if (isset($this->Request['range'])) {
			$range = $this->Request['range'];
			echo $range;
		}
		
    	$this->connectDB();
		$cc = new GetRecord($this);
		//$cc->dBQuery(trim("{\"table\": \"breed\",\"fields\": [\"id\", \"text\"], \"where\": {\"text\": \"%".$this->payload."%\"}}", true));
									// What does the "true" parameter do?
		$cc->dBQuery(trim("{\"table\": \"breed\",\"fields\": [\"id\", \"text\"], \"where\": {\"text\": \"%".$this->payload."%\"}}"));
		// Outputs as a single JSON string {}, not an array [{}]
		echo "{\"term\":\"".$this->payload."\",\"results\":". $cc->record ."}";
		//return "{\"term\":\"sample\",\"results\":". $cc->record .", \"more\": \"false\"}";
	}

	// Public function to return the list of Delegates for a particular Dog
	// Delegates are technically children elements of a Customer record
	// This function uses the GetRecordInnerJoin method to join on the 'owner_id' field common to both the Dog and Delegate tables
	// Thanks to Mike Zelnik (jmzelnik -at- gmail.com) for cracking the necessary SQL syntax
	// Usage:
	// URL/Endpoint: api.php?method=getDelegatesForDog&payload=133
	//		where '133' is the ID of the dog
	// Results: [{"id":25,"first_name":"David","last_name":"Morgan"}]
	public function getDelegatesForDog() {
    	if ((isset($this->Request['payload'])) && (is_numeric($this->Request['payload']))) {
    		$this->connectDB();
			$cc = new GetRecordInnerJoin($this);
			//echo $this->payload;
			$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"delegate\", \"join\": \"dog\", \"fields\": [\"delegate.id\", \"first_name\", \"last_name\"], \"where\": {\"dog.id\":\"" . $this->payload . "\"}, \"on\":{\"delegate\":\"owner_id\", \"dog\":\"owner_id\"}}"));
			echo $this->ResponseBody;
			$this->disconnectDB();
		}
		else {
			echo "Error.  Must provide a Dog ID in the payload.";
		}
	} // End function getDelegatesForDog

	
	// Method to return the Veterinarians that are in the database
	public function getVets() {
    	$this->connectDB();
		$cc = new GetRecord($this);
		$cc->dBQuery(trim("{\"table\": \"veterinarian\",\"fields\": [\"id\", \"text\"], \"where\": {\"text\": \"%".$this->payload."%\"}}"));
		// Outputs as a single JSON string {}, not an array [{}]
		echo "{\"term\":\"".$this->payload."\",\"results\":". $cc->record ."}";
	}

	
	// Function to build and display a list of Questions.  Uses GetRecord for the query and takes in its output.
	// "JSON-format of a query" -> GetRecord -> JSON Array-format output -> getQuestions -> HTML output
	// Questions are stored in the 'question' table.
	// Usage Example: 'api.php?getQuestions'
	// 
	// Returns: the complete HTML <form>...</form> code
	// *** Should also return the needed <script> code for the form, submission and validation.
	//
	public function getQuestions($page) {

    	$this->connectDB();
		$cc = new GetRecord($this);
		//$cc->dBQuery("{\"table\": \"question\", \"fields\": [\"id\", \"text\", \"default\", \"type\"]}");

		//$sampleOutputfromGetRecord = " [{\"id\":\"1\",\"text\":\"What is your hometown\",\"guidelines\":\"Where are you from, homeslice. Enter it here.\",\"default\":\"Hazleton\",\"options\":\"\",\"type\":\"1\"},{\"id\":\"2\",\"text\":\"Whats your sign\",\"guidelines\":\"What star were you born under you're\",\"default\":\"Libra\",\"options\":\"\",\"type\":\"1\"},{\"id\":\"3\",\"text\":\"Favorite color\",\"guidelines\":\"Here is where you put your favoriate color\",\"default\":\"\",\"options\":\"red, white, blue\",\"type\":\"3\"},{\"id\":\"4\",\"text\":\"Do you like pina colata\",\"guidelines\":\"Or getting caught in the rain\",\"default\":\"\",\"options\":\"\",\"type\":\"2\"},{\"id\":\"37\",\"text\":\"Question 8\",\"guidelines\":\"\",\"default\":\"\",\"options\":\"Web, form , unlimited ,\",\"type\":\"3\"},{\"id\":\"38\",\"text\":\"What is your favorite animal\",\"guidelines\":\"You put your animal here\",\"default\":\"Llama\",\"options\":\"\",\"type\":\"1\"},{\"id\":\"39\",\"text\":\"What is your favorite animal\",\"guidelines\":\"PIck one\",\"default\":\"\",\"options\":\"Dog, cat, llama, alligator, horse\",\"type\":\"3\"},{\"id\":\"40\",\"text\":\"What's my name?\",\"guidelines\":\"English, motherfucker. Do you speak it?\",\"default\":\"\",\"options\":\"Me, My, Mine\",\"type\":\"3\"}]";

		// Outputs as a single JSON string {}, not an array [{}]

	//if payload = 0, ignore the payload.  API should set payload to 0 by default

		// I think this works.  yes it does and it works properly
		$sampleQueryHardCoded = "{\"table\": \"question\", \"fields\": [\"id\", \"text\", \"guidelines\", \"default\", \"options\", \"page\", \"type\"], \"where\":{\"active\":\"1\", \"page\": ".$page."}, \"order\": [\"order\"]}";
/*
[{"id":"1","text":"What is your hometown","guidelines":"Where are you from, homeslice. Enter it here.","default":"Hazleton","options":"","page":"1","type":"1"},{"id":"2","text":"Whats your sign","guidelines":"What star were you born under you're","default":"Libra","options":"","page":"1","type":"1"},{"id":"3","text":"Favorite color","guidelines":"Here is where you put your favoriate color","default":"","options":"red, white, blue","page":"1","type":"3"},{"id":"4","text":"Do you like pina colata","guidelines":"Or getting caught in the rain","default":"","options":"","page":"2","type":"2"},{"id":"37","text":"Question 8","guidelines":"","default":"","options":"Web, form , unlimited ,","page":"2","type":"3"},{"id":"38","text":"What is your favorite animal","guidelines":"You put your animal here","default":"Llama","options":"","page":"2","type":"1"},{"id":"40","text":"What's my name?","guidelines":"English, motherfucker. Do you speak it?","default":"","options":"Me, My, Mine","page":"3","type":"3"}]
*/
		$form = new BuildQuestions("{\"questions\":".$cc->dBQuery($sampleQueryHardCoded) .", \"name\": \"samplename\"}");
		// Test omitting Name:
		//$form = new BuildQuestions("{\"questions\":".$cc->dBQuery($sampleQueryHardCoded)."}");

		//$form->echoInputJSON();
		echo $form->outputHTML;

	}	
	
    
    /*
    getCustomersDogs() {
    	// Function should return all the customers dogs
    	// Should be in a JSON array, I guess
    }
    
    getCustomersDelegates() {
    	// Function should return the customers delegates
    
    }
    
    */
    
    
    // createDog
   // public function createDog(schema, jsondata) {
       // We will instantiate a new CreateRecord Object
       // That requires the Schema (param property)
       // and Data (RequestBody)
       // This also needs the Customer ID (that should be handled by the form and already be in the data
       // 
   // }    
    
   /* 
    // Set Payload
    public function setPayload($s) {
        $this->payload = $s;
        //echo $this->payload;
    }
    */

    // Method echo
    // Simply echos the JSON
    public function echoPayload($s) {
    	if (isset($_GET['payload'])) {
    		echo "Your payload was: " .$s;
    	}
    	else {
    		echo "Payload is empty";
    	}
	}

	// Echos the key:value pairs of an Associative Array, including if one of those values
	// is itself an Associative Array
	// Parses through the converted JSON Object
	public function descArray($arr) {
		foreach ($arr as $key => $value) {
			print($key) . ': '; // key
			print($value); // value
			print("<br />");
			if(is_array($value)) {
				//echo "we got an array on our hands.";
				$this->descArray($value);  // recursion
			}
		}
	}


} // END Class APIObject

?>