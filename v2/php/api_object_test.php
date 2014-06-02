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
									\"twitter\",
									\"instagram\",
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
	
		PC::debug("Provided Email: " . $emailFromPayload);
		
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
       	$cust->setSchemaJSONPublic($this->schemaLabel_customer);
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


    // Method used by the Fetch App to check a dog into daycare
    // Usage Example:
    // URL/Endpoint: api.php?method=daycareCheckInDog
    // Request Body: {"tag":"3", "dog_name":"Ruby", "dog_id": "4", "customer_last_name":"Morgan", "status":"In", "trainer":"Trainer", "location":"Kettner Arts", "sublocation":"Dog Bowl", "time_check_in":"2014-05-29 15:29:00", "time_check_out":""}
    public function daycareCheckInDog() {
    
    	$this->connectDB();
        $visit = new CreateRecord($this);
        
        $visit->setRequestBody($this->RequestBody);
       	//$visit->setSchemaJSONPublic($this->schemaLabel_daycare); // Before we go any further we should fix this
       	$visit->setSchemaJSONPublic($this->descSchema("daycare", 1));   // test
       	//echo " LABEL: " . $this->schemaLabel_daycare . " ";
       	//echo " FROM DSTEST: " . $this->dsTest("daycare") . " ";

//LABEL: 		{"table": "daycare", "fields": ["tag", "dog_name", "dog_id", "customer_last_name", "status", "trainer", "location", "sublocation", "time_check_in", "time_check_out"]}
//FROM DSTEST:  {"table": "daycare", "fields": ["tag", "dog_name", "dog_id", "customer_last_name", "status", "trainer", "location", "sublocation", "time_check_in", "time_check_out"]}
//FROM DSTEST: {"table":"daycare","fields":["tag","dog_name","dog_id","customer_last_name","status","trainer","location","sublocation","time_check_in","time_check_out"]}
       	$visit->setDataJSONPublic($this->getBody());
       	$visit->dBInsertPublic($this->Request);
       	echo $visit->createdID;
       	return $visit->createdID;
       	
       	$this->disconnectDB(); 
       	
    } // End function daycareCheckInDog


    public function dsTest($table) {
    	PC::debug(" DSTEST: " . $table . " ");
    	$ds = new DescSchema($this);
    	$ds->setTable($table);
    	//$ds->getDesc();
    	return $ds->getDescSilent();

    	//$dsc->getExample();	
    }



} // End Class
 
?>