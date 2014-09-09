<?php

/*
Project Description:

This file is the extension class of the main API object.

It should include the application-specific methods, not the core CRUD functions.


Author: Christopher Morgan
Contact: christopher.t.morgan -at- gmail
Copyright 2013
*/

/*

API Object Test is an experiment to move all but the core methods out of the original class definition.
This would allow for greater flexability and customization of the API.

You wouldn't need to touch 'api_object.php' whenever a new method was introduced.

*/

class APIObjectTest extends APIObject{

	// Function returns the Customer ID, as an integer, given an actual email address
	// Usage Example:
	// URL/Endpoint: api.php?method=getCustomerIdByEmail&payload=christopher.t.morgan@gmail.com
	// Response Body: 1 and
	// Returns: 1
	// Function returns 0 if the email address provided does not match a Customer record or
	// if no email address was provided.
	// Alternative Usage:
	// You may call this function from within another function of the  APIObject class using
	// self::getCustomerIdByEmail("email@domain.com", echo);
	// Where "echo" is boolean.  If echo == true, the ReponseBody will contain the Owner Id
	// If echo is false, it is returned silently
	// TODO: Protect the incoming Payload with a Regex function
	public function getCustomerIdByEmail($emailFromPayload, $echo) {
	
		//PC::debug("Provided Email: " . $emailFromPayload);
		
		// Check to see if the payload is null.  If it is we can return 0 without hitting the DB
		if ($emailFromPayload)
		{
			$this->connectDB();
    		$cc = new GetRecord($this);
			$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"customer\",\"fields\": [\"id\"], \"where\": {\"email\":\"" . $emailFromPayload . "\"}}"));
			$this->disconnectDB();

			// The GetRecord API method just gave a value that looks like this:
			// [{"id":281}]
			// Next, we take the value from the "id" key
			$jsonOfCust = json_decode( $this->ResponseBody, true );
			// If we don't find the value in the DB, return 0
			// We don't have a customer with that email
			if (is_null($jsonOfCust['0']['id']))
			{
				echo "0";
				return 0;
			}
			else
			{
				// This method has a "silent option".  If echo, the second parameter is true, it will echo its response.
				// If the echo option is 0, the method will still *return* its response, but not echo it
				// This is so you may call the method from another method.
				if ($echo)
				{
					echo $jsonOfCust['0']['id'];
				}
				// Silent option
				return $jsonOfCust['0']['id'];
			}
		}
		// The payload was null and no email was provided
		else
		{
			if ($echo)
			{
				echo "0";
			}
			// Silent option
			return 0;
		}

	} // End getCustomerById

    // createCustomer
    public function createCustomer() {
       // We will instantiate a new CreateRecord Object
       // That requires the Schema (param property)
       // and Data (RequestBody)
       // This should return the ID of the new table row (customertest.id)
       // 
       // Should the insert stuff be in the main class?  I don't think so, because we will have UpdateRecord, QueryRecord sub-classes eventually
       // schema, and jsondata don't need to be passed in, the class already knows about them
       // Usage Instructions:
       // URL/EndPoint: api.php?method=createCustomer
       // Request Body: {"salutation":"Mr.","first_name":"Test","last_name":"User","phone":"3434343434","email":"411@test.com","marketing_email":"","address_street":"","address2":"","city":"San Diego","state":"CA","zip":"","country":"United States","twitter":"@","instagram":"@","company":"","work_phone":"","work_street1":"","work_street2":"","work_city":"San Diego","work_state":"","work_zip":"","work_country":"","email_pref":"1","referral":"Something-else","dob":"1980-01-01"}
       // Response: 232, where that number is the newly created customer's ID

       	$this->connectDB();
        $cust = new CreateRecord($this);

        $cust->setRequestBody($this->RequestBody);

		// There is currently an issue with visability.
		// This is a hack courtesy: http://stackoverflow.com/questions/11752327/fatal-error-call-to-private-method-but-method-is-protected
		// But I don't want to create new public methods for each of the core classes...
       	//$cust->setSchemaJSONPublic($this->schemaLabel_customer);   // Old way using schema labels
       	$cust->setSchemaJSONPublic($this->descSchema("customer", 0));
       	$cust->setDataJSONPublic($this->getBody());
       	$cust->dBInsertPublic($this->Request);

       	echo $cust->createdID;
       	return $cust->createdID;

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
		else {
			echo "Error.  Not a dog";
		}
    } // End getDog


    public function dsTest($table) {
    	//PC::debug(" DSTEST: " . $table . " ");
    	$ds = new DescSchema($this);
    	$ds->setTable($table);
    	//$ds->getDesc();
    	return $ds->getDescSilent();

    	//$dsc->getExample();	
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
	} // End getAllDogBreeds

	// Method to return the Veterinarians that are in the database
	// Veterinarians are stored in the 'veterination' table.
	// This method is primary called by 'select2' jQuery plugin, which is used on 'form.html' to register a dog
	public function getVets() {
    	$this->connectDB();
		$cc = new GetRecord($this);
		$cc->dBQuery(trim("{\"table\": \"veterinarian\",\"fields\": [\"id\", \"text\"], \"where\": {\"text\": \"%".$this->payload."%\"}}"));
		// Outputs as a single JSON string {}, not an array [{}]
		echo "{\"term\":\"".$this->payload."\",\"results\":". $cc->record ."}";		
	} // End function getVets


	// Function to return a Customer record, using the ID as the payload
	// Primary created as a test; this is not being used in the application
	// Usage Example:
	// URL/Endpoint: api.php?method=getCustomerById&payload=1
	// Response Body: [{"email":"","first_name":"Christopher"}]
	public function getCustomerById() {	
		$this->connectDB();
    	$cc = new GetRecord($this);
		$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"customer\",\"fields\": [\"email\", \"first_name\"], \"where\": {\"id\":\"" . $this->payload . "\"}}"));
		echo $this->ResponseBody;    	
		$this->disconnectDB();
	} // End getCustomerById


	// Public function to return the list of Delegates for a particular Dog
	// Delegates are technically children elements of a Customer record
	// This function uses the GetRecordInnerJoin method to join on the 'owner_id' field common to both the Dog and Delegate tables
	// Thanks to Mike Zelnik (jmzelnik -at- gmail.com) for cracking the necessary SQL syntax
	// Usage Example:
	// URL/Endpoint: api.php?method=getDelegatesForDog&payload=133
	//		where '133' is the ID of the dog
	// Response Body: [{"id":25,"first_name":"David","last_name":"Morgan"}]
	// Usage Example when there are multiple Delegates
	// URL/Endpoint: api.php?method=getDelegatesForDog&payload=3
	// Response Body: [{"id":2,"first_name":"Sumrall","last_name":"Jane"},{"id":3,"first_name":"Jobert","last_name":"Reffords"}]
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


	// Function to add a Dog to the database and link to an Owner, given the Owner's email address
	// This differs from the 'addDog' method since that requires knowing the Customer/Owner's ID
	// Customer/Owner IDs should be unique in the database.  This is validated by the form.
	// Usage instructions:
	// URL/Endpoint: method=addDogWithEmail
	// Request Body: {"dog_name":"LuckySeven8", "breed":"", "behaviors":"", "allergies":"", "dog_birthday":"", "dog_dhp":"", "dog_rabies":"", "dog_bordetella":"", "dog_giardia":"", "vet":"", "dog_notes":"", "gender":"", "furcolor":"", "pawwidth":"", "neckgirth":"", "waistgirth":"", "height":"", "license":"", "microchip":"", "email":"christopher.t.morgan@gmail.com"}
	// Response Body: 167, or ID of newly-created Dog
	// Note: the "email" key-pair must be the last element of the JSON
	public function addDogWithEmail() {

       	$s = $this->getBody();
       	$s = str_replace("email", "owner_id", $s);
       	$sLength = strlen($s);
       	$stringLeft = substr($s, 0, strpos($s, "owner_id") + 11);
       	$stringRight = "\"" . substr($s, -1);
       	$sEmailLength = strlen($s) - (strpos($s, "owner_id") + 13);
       												// email is 11 char in our example	
       	//echo " lenth: " . $sLength;					// 322
       	//echo " pos: " . strpos($s, "owner_id");		// 298
       	//echo " last: " . strrpos($s, "\"}");		// 320
       	
       	$email = mb_substr($s, strpos($s, "owner_id") + 11, $sEmailLength);
       	$stringMiddle = self::getCustomerIdByEmail($email, 0);
       	$combinedString = $stringLeft . $stringMiddle . $stringRight;
       	//echo $combinedString;
       	
       	$this->connectDB();
        $dog = new CreateRecord($this);

        $dog->setRequestBody($this->RequestBody);

       	//$dog->setSchemaJSON($this->schemaLabel_dog);  // This is wrong
       	//$visit->setSchemaJSONPublic($this->descSchema("daycare", 1));
       	$dog->setSchemaJSONPublic($this->descSchema("dog", 0));  // Testing before walking

       	//$dog->setDataJSON($combinedString);
       	$dog->setDataJSONPublic($combinedString);
       	//$visit->dBInsertPublic($this->Request);
		$dog->dBInsertPublic($this->Request);

		
       	echo $dog->createdID;
       	return $dog->createdID;	
       		
	} // End function addDogWithEmail

	// Function to add a Dog to the database and link to an Owner, given the Owner's Customer ID
	// Customer has a many:1 relationship with Dog
	// Example Usage:
	// URL/Endpoint: api.php?addDog
	// Request Body: {"dog_name":"api", "breed":"", "behaviors":"", "allergies":"", "dog_birthday":"", "dog_dhp":"", "dog_rabies":"", "dog_bordetella":"", "dog_giardia":"", "vet":"", "dog_notes":"", "gender":"", "furcolor":"", "pawwidth":"", "neckgirth":"", "waistgirth":"", "height":"", "license":"", "microchip":"", "owner_id":"1"}
	// 		where, "owner_id" is the unique ID of the customer.  A Dog must belong to a Customer.
	// Response Body: ID of newly-created dog
	public function addDog() {
	
	       //echo $this->RequestBody;
	       //$dogData = $this->RequestBody;
	       //echo $dogData;

        $this->connectDB();
        $dog = new CreateRecord($this);

        $dog->setRequestBody($this->RequestBody);
       	//$dog->setSchemaJSONPublic($this->schemaLabel_dog);
       	$dog->setSchemaJSONPublic($this->descSchema("dog", 1));
       	$dog->setDataJSONPublic($this->getBody());
       	//$dog->setDataJSON("{\"dog_name\":\"api\", \"breed\":\"\", \"behaviors\":\"\", \"allergies\":\"\", \"dog_birthday\":\"\", \"dog_dhp\":\"\", \"dog_rabies\":\"\", \"dog_bordetella\":\"\", \"dog_giardia\":\"\", \"vet\":\"\", \"dog_notes\":\"\", \"gender\":\"\", \"furcolor\":\"\", \"pawwidth\":\"\", \"neckgirth\":\"\", \"waistgirth\":\"\", \"height\":\"\", \"license\":\"\", \"microchip\":\"\", \"owner_id\":\"1\"}");
       	$dog->dBInsertPublic($this->Request);
       	echo $dog->createdID;
       	return $dog->createdID;	
	
	} // End function addDog


	// Function to create and add a Delegate.  Delegates are children elements of Customers
	// Usage Instructions:
	// This works by manipulating the text of the JSON Request Body before making the query
	// URL/Endpoint: api.php?method=addDelegate
	// Request Body: {"delegate_first_name":"","delegate_last_name":"","phone":"","delegate_email":"","owner_id_delegate":"233"}
	// 		where, "owner_id_delegate" is the unique ID of the customer
	// Result: Returns ID of newly-created Delegate
	public function addDelegate() {
	    $this->connectDB();
        $del = new CreateRecord($this);
        $del->setRequestBody($this->RequestBody);
       	//$del->setSchemaJSONPublic($this->schemaLabel_delegate);
       	$del->setSchemaJSONPublic($this->descSchema("delegate", 0));
       	$del->setDataJSONPublic($this->getBody());
       	$del->dBInsertPublic($this->Request);
       	echo $del->createdID;
       	return $del->createdID;	
	} // End function addDelegate


	
	// Function to add Delegates and link to Customer with the Customer's email address
	// Differs from 'addDelegate' because that requires the knowledge of the Customer ID
	// Usage instructions: 
	// URL/Endpoint: api.php?method=addDelegateWithEmail
	// Request Body: {"first_name":"Mister","last_name":"Delgato","phone":"5556545","email":"delgato@gmail","owner_email":"1@1.com"}
	//		where, "owner_email" is a valid Customer email
	// Results Body: ID of newly-created Delegate
	public function addDelegateWithEmail() {
       	
       	$s = $this->getBody();
       	$s = str_replace("owner_email", "owner_id", $s);
       	$sLength = strlen($s);
       	$stringLeft = substr($s, 0, strpos($s, "owner_id")+11);
       	$stringRight = "\"" . substr($s, -1);
       	$sEmailLength = strlen($s) - (strpos($s, "owner_id") + 13);
       												// email is 11 char in our example
       												
       	$email = mb_substr($s, strpos($s, "owner_id")+11, $sEmailLength);
       	$stringMiddle = self::getCustomerIdByEmail($email, 0);
       	$combinedString = $stringLeft . $stringMiddle . $stringRight;
       	//echo $combinedString;
       	
       	$this->connectDB();
        $dog = new CreateRecord($this);
        $dog->setRequestBody($this->RequestBody);
       	//$dog->setSchemaJSON($this->schemaLabel_delegate);
       	$dog->setSchemaJSONPublic($this->descSchema("delegate", 1));
       	//$dog->setSchemaJSONPublic($this->descSchema("dog", 1));
       	$dog->setDataJSONPublic($combinedString);
		$dog->dBInsertPublic($this->Request);
		
       	echo $dog->createdID;
       	return $dog->createdID;	
       		
	} // End function addDelegateWithEmail


	public function linkFileToRecord() {
/*
      	$this->connectDB();
        $up = new UpdateAllRecord($this);
        $up->setRequestBody($this->RequestBody);
       	$up->setTableFromSchema($this->descSchema("dog", 0));
       	$up->setFieldsFromSchema($this->descSchema("dog", 0));
       	$up->setDataFromJSON($this->RequestBody);
       	$up->setIDfromPayload($this->payload);
       	$up->setSetClause($this->RequestBody);
       	$this->ResponseBody = $up->dBUpdate(trim($this->getBody()));
       	$this->disconnectDB();		
	*/
	
	} // End function linkFileToRecord


} // End Class
 
?>