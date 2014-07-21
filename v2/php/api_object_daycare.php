<?php

/*
Class Description:
This file is defines the Daycare-specific methods of our API.
The Daycare class extends APIObject, which is the class that defines the core CRUD database functions
This class is a sibling to 'api_object_test' (or whatever we end up naming that).

Author: Christopher Morgan
Contact: christopher.t.morgan -at- gmail
Copyright 2014
*/


class Daycare extends APIObject{

//Check In Data: {"tag":"3", "dog_name":"East", "dog_id": "130", "customer_last_name":"Morgan", "status":"In", "trainer":"Able", "location":"Kettner Arts", "sublocation":"Dog Bowl", "time_check_in":"2014-07-01 16:59:00", "time_check_out":""}
//               {"tag":"3", "dog_name":"Ruby", "dog_id": "4", "customer_last_name":"Morgan", "status":"In", "trainer":"Trainer", "location":"Kettner Arts", "sublocation":"Dog Bowl", "time_check_in":"2014-05-29 15:29:00", "time_check_out":""}


    // Method used by the Fetch App to check a dog into daycare
    // Usage Example:
    // URL/Endpoint: api.php?method=daycareCheckInDog
    // Request Body: {"tag":"3", "dog_name":"Ruby", "dog_id": "4", "customer_last_name":"Morgan", "status":"In", "trainer":"Trainer", "location":"Kettner Arts", "sublocation":"Dog Bowl", "time_check_in":"2014-05-29 15:29:00", "time_check_out":""}
	// Response Body: Integer, the 'id' of the record created in the 'daycare' table.
    public function daycareCheckInDog() {
    
    	$this->connectDB();
        $visit = new CreateRecord($this);
        
        $visit->setRequestBody($this->RequestBody);
       	//$visit->setSchemaJSONPublic($this->schemaLabel_daycare); // Before we go any further we should fix this
       	$visit->setSchemaJSONPublic($this->descSchema("daycare", 1));   // test
       	//echo " LABEL: " . $this->schemaLabel_daycare . " ";

       	$visit->setDataJSONPublic($this->getBody());
       	$visit->dBInsertPublic($this->Request);
       	echo $visit->createdID;
       	return $visit->createdID;
       	
       	$this->disconnectDB(); 
       	
    } // End function daycareCheckInDog

	// Method to determine if a particular Dog ID is currently checked-in to daycare
	// TODO: Call this function internally, so we don't need 2 hits to the webserver with daycareCheckInDog
	// 
	// Usage Example:
	// URL/Endpoint: api.php?method=daycareIsDogAlreadyCheckedIn&payload=32
	//		where payload is the unique ID of the dog
	// Response Body: 1 or 0
	public function daycareIsDogAlreadyCheckedIn() {
    	$this->connectDB();
		$cc = new GetRecordCount($this);
		$this->ResponseBody = $cc->dBCount(trim("{\"table\": \"daycare\", \"fields\": [\"id\"], \"where\":{\"status\":\"in\", \"dog_id\":\"" . $this->payload . "\"}}"));
		
		if ($this->ResponseBody != '0')
		{
			echo "1";
			return true;
		}
		if ($this->ResponseBody == '0')
		{
			echo "0";
			return false;
		}
		$this->disconnectDB();		
	} // End function daycareIsDogAlreadyCheckedIn


	// Method to CheckOut a Dog from daycare, ie send him home with owner
	// This method is simpler than daycareCheckInDog, because we don't need to add alot of values to the record,
	//  we just check out the dog, given the Tag Number.
	// Method to checkOutDog, given the Tag number.  Method updates the record in the 'daycare' table.
	// Changes the "Status" field on any Tags with the Status of In (doesn't update previous uses of that tag id)
	// to "Out" and the "time_check_out" field to the current time.
	//
	// Usage Example: ./php/api.php?method=daycareCheckOutDog&payload=4
	// Response Body: 1 (number of records updated)
	public function daycareCheckOutDog() {

    	if ((isset($this->Request['payload'])) && (is_numeric($this->Request['payload']))) {
    		$this->connectDB();

       		$out = new UpdateRecord($this);
       		//$out->setSchemaJSON($this->schemaLabel_daycare);
       		$out->setSchemaJSONPublic($this->descSchema("daycare", 1)); 

       		$out->dBUpdate("{\"table\": \"daycare\", \"set\":{\"status\": \"Out\", \"time_check_out\": \"" . date("Y-m-d H:i:s") . "\"}, \"where\": {\"Status\": \"In\", \"tag\":\"" . $this->payload . "\"}}");
			$this->disconnectDB();
		}
		else {
			echo "Error.  Must provide a Tag ID in the payload.";
		}

	} // End function daycareCheckOutDog

	//Experimental
	// This function is difficult because multiple fields need to be updated and we can only pass 2
	// We need to pass parameters for the dog id, trainer, and location
	public function daycareDogAction() {
	
	echo " schema: " . $this->schema . " ";
	echo " payload: " . $this->payload . " ";
	echo "pkey: " . $this->pkey . " ";
	
	//{"table": "daycare", "set":{"sublocation": "Time Out", "trainer":"'+getTrainer()+'"}, "where": {"dog_id": "'+dogRecord['id']+'"}}
	
	$this->connectDB();

    $act = new UpdateRecord($this);
    
    // NOTE:  Need to add the status = in qualifyer; otherwise it will update the sublocation of all instances of dog_id in daycare
   	$act->dBUpdate("{\"table\": \"daycare\", \"set\":{\"sublocation\": \"Time Out\"}, \"where\":{\"status\":\"In\",\"dog_id\":\"18\"}}");
    
    $this->disconnectDB();
	
	} // End function daycareDogAction

	// Method to update a dog's sub-location to TimeOut
	// This function should be replaced by the generic daycareDogAction eventually
	public function actionTimeOut() {
		$this->connectDB();
    	$act = new UpdateRecord($this);	
		$act->setSchemaJSONPublic($this->descSchema("daycare", 1));
   		$act->dBUpdate("{\"table\": \"daycare\", \"set\":{\"sublocation\": \"Time Out\"}, \"where\":{\"status\":\"In\",\"dog_id\":\"".$this->payload."\"}}");
    	$this->disconnectDB();
	} // End function actionTimeOut

	// Method to update a dog's sub-location to the Dog Bowl
	// This function should be replaced by the generic daycareDogAction eventually
	public function actionDogBowl() {
		$this->connectDB();
   	 	$act = new UpdateRecord($this);	
		$act->setSchemaJSONPublic($this->descSchema("daycare", 1));
   		$act->dBUpdate("{\"table\": \"daycare\", \"set\":{\"sublocation\": \"Dog Bowl\"}, \"where\":{\"status\":\"In\",\"dog_id\":\"".$this->payload."\"}}");
    	$this->disconnectDB();
	} // End function actionDogBowl

	// Method to update a dog's sub-location to a Walk
	// This function should be replaced by the generic daycareDogAction eventually
	public function actionWalk() {
		$this->connectDB();
    	$act = new UpdateRecord($this);	
		$act->setSchemaJSONPublic($this->descSchema("daycare", 1));
   		$act->dBUpdate("{\"table\": \"daycare\", \"set\":{\"sublocation\": \"Walk\"}, \"where\":{\"status\":\"In\",\"dog_id\":\"".$this->payload."\"}}");
    	$this->disconnectDB();
	} // End function actionWalk

	// Method to pull all Dog Data for Fetch -- the current dogs
	public function getData() {
		$this->connectDB();
    	$data = new GetRecordInnerJoin($this);
    	//$this->ResponseBody = $data->dBQuery(trim($this->getBody()));
    	$this->ResponseBody = $data->dBQuery(trim("{\"table\": \"daycare\", \"join\": \"dog\", \"fields\": [\"dog.id\", \"tag\", \"dog.dog_name\", \"dog.id\", \"customer_last_name\", \"location\", \"sublocation\", \"trainer\", \"time_check_in\", \"updated\", \"dog.breed\", \"dog.dog_notes\", \"dog.behaviors\", \"Status\"], \"where\": {\"Status\":\"In\"}, \"on\":{\"daycare\":\"dog_id\", \"dog\":\"id\"}}"));
    	echo $this->ResponseBody;
		$this->disconnectDB();
	} // End function getData

	// Function to determine if a dog is currently Checked In to daycare
	// Usage Example:
	// api.php?method=isDogAlreadyCheckedIn&payload=184
	// Response Body: 1 or 0, depending if the dog is checked in to daycare
	public function isDogAlreadyCheckedIn () {
		$this->connectDB();
		$cc = new GetRecordCount($this);
		//$this->ResponseBody = $cc->dBCount(trim($this->getBody()));
		//var p = '{"table": "daycare", "fields": ["*"], "where":{"status":"in", "dog_id":"'+dogId+'"}}';
		$this->ResponseBody = $cc->dBCount(trim("{\"table\": \"daycare\", \"fields\": [\"*\"], \"where\":{\"status\":\"in\", \"dog_id\":\"".$this->payload."\"}}"));
		echo $cc->count;
		$this->disconnectDB();
	} // End function isDogAlreadyCheckedIn

	// Function to get a dog's record (a specific number and order of fields) given the Dog ID
	// Note the function name is *ID* not *Id*
	// Usage Exmaple:
	// api.php?method=getDogDataByID&payload=1
	// Response Body: [{"id":1,"dog_name":"ele","breed":"BM","behaviors":"","allergies":"","dog_birthday":"0000-00-00","dog_notes":"Nothing NEW'","gender":"","furcolor":"","pawwidth":"","neckgirth":"","waistgirth":"","height":"","license":"","microchip":""}]
	public function getDogDataByID() {	
	//'{"table": "dog","fields": ["id","dog_name","breed", "behaviors", "allergies", "dog_birthday", "dog_notes", "gender", "furcolor", "pawwidth", "neckgirth", "waistgirth", "height", "dog_notes", "license", "microchip"], "where": {"id": "'+ dogid +'"}}'
		$this->connectDB();
    	$cc = new GetRecord($this);
		$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"dog\",\"fields\": [\"id\",\"dog_name\",\"breed\", \"behaviors\", \"allergies\", \"dog_birthday\", \"dog_notes\", \"gender\", \"furcolor\", \"pawwidth\", \"neckgirth\", \"waistgirth\", \"height\", \"dog_notes\", \"license\", \"microchip\"], \"where\": {\"id\": \"" .$this->payload. "\"}}"));
		echo $this->ResponseBody;    	
		$this->disconnectDB();
	} // End function getDogDataByID

	// Method to update a Dog's record.  Used by the Record Dog Data part of Fetch
	// This updates a dog's record, with a Request Body in the same format as creating a record
	// This requires the schema and pkey parameters sent
	// This is the first extended updateAllRecord
	//  Usage Example:
	// api.php?method=updateDogRecord&payload=18
	// Request Body: {"gender":"Female","furcolor":"Blonde Tan Yellow","pawwidth":"34","neckgirth":"25","waistgirth":"25","height":"26","license":"","microchip":"","dog_notes":"zThese are notes regarding Roopie.  She poops on people."}
	// Response Body: 1 or 0, depending on if the record were updated
	public function updateDogRecord() {
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
	} // End function updateDogRecord

	// Helper function to return the tag numbers that are actively assigned to dogs in daycare
	// Usage Example:
	// api.php?getActiveTags
	// Response Body: [{"tag":1},{"tag":2},{"tag":3},{"tag":4},{"tag":5},{"tag":6}]
	public function getActiveTags() {

    	$this->connectDB();
		$cc = new GetRecord($this);
		$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"daycare\", \"fields\": [\"tag\"], \"where\":{\"status\":\"in\"}, \"order\":[\"tag\"]}"));
		echo $this->ResponseBody; 
		$this->disconnectDB();	
	
	
	} // End function getActiveTags

	// A flexible function to return counts of the dogs in daycare
	// A hard-coded sub-location or location may be passed in the Payload
	// Usage Example:
	// api.php?method=getDogCount
	// Response Body: 9
	//
	// api.php?method=getDogCount&payload=Walk
	// Response Body: 1
	//
	// api.php?method=getDogCount&payload=TimeOut
	// Response Body: 2
	//
	// api.php?method=getDogCount&payload=DogBowl
	// Response Body: 6
	//
	// api.php?method=getDogCount&payload=KettnerArts
	// Response Body: 9
	public function getDogCount() {
		$this->connectDB();
		$cc = new GetRecordCount($this);
		
		if (!isset($this->Request['payload'])) {
			$this->ResponseBody = $cc->dBCount(trim("{\"table\": \"daycare\", \"fields\": [\"*\"], \"where\":{\"status\":\"in\"}}"));
		}
		
		if (strtoupper($this->Request['payload']) == 'WALK') {
			$this->ResponseBody = $cc->dBCount(trim("{\"table\": \"daycare\", \"fields\": [\"*\"], \"where\":{\"status\":\"in\", \"sublocation\":\"Walk\"}}"));
		}

		if (strtoupper($this->Request['payload']) == 'DOGBOWL') {
			$this->ResponseBody = $cc->dBCount(trim("{\"table\": \"daycare\", \"fields\": [\"*\"], \"where\":{\"status\":\"in\", \"sublocation\":\"Dog Bowl\"}}"));
		}

		if (strtoupper($this->Request['payload']) == 'TIMEOUT') {
			$this->ResponseBody = $cc->dBCount(trim("{\"table\": \"daycare\", \"fields\": [\"*\"], \"where\":{\"status\":\"in\", \"sublocation\":\"Time Out\"}}"));
		}	
		
		if (strtoupper($this->Request['payload']) == 'KETTNERARTS') {
			$this->ResponseBody = $cc->dBCount(trim("{\"table\": \"daycare\", \"fields\": [\"*\"], \"where\":{\"status\":\"in\",\"location\":\"Kettner Arts\"}}"));
		}		

		echo $cc->count;
		$this->disconnectDB();	
		
	
	} // End function getDogCount

	// Method used to pull Dog Data and load in the the listview
	// Specifically, this is called when searching on the Check In page
	// The payload must be the unique email address
	// Usage Example:
	// api.php?method=getDogsByOther&payload=christopher.t.morgan@gmail.com
	// Response Body: [{"first_name":"Christopher","last_name":"Morgan","email":"christopher.t.morgan@gmail.com","id":1,"dog_name":"ele","dog_notes":"Nothing NEW'","breed":"BM"},{"first_name":"Christopher","last_name":"Morgan","email":"christopher.t.morgan@gmail.com","id":2,"dog_name":"Morga%27n","dog_notes":"form update customer","breed":""}]	
	public function getDogsByEmail() {
		
		$this->connectDB();
    	$data = new GetRecordInnerJoin($this);

		//if (isset($this->Request['payload'])) {
		//echo " customer: ";
    		$this->ResponseBody = $data->dBQuery(trim("{\"table\": \"customer\", \"join\": \"dog\", \"fields\": [\"first_name\", \"last_name\", \"email\", \"dog.id\", \"dog_name\", \"dog_notes\", \"breed\"], \"where\": {\"email\":\"" . $this->payload . "\"}, \"on\":{\"customer\":\"id\", \"dog\":\"owner_id\"}}"));

		//}
		
    	echo $this->ResponseBody;
		$this->disconnectDB();	
	} // End function getDogsByEmail
	
	// Method used to pull Dog Data and load in the the listview
	// Specifically, this is called when searching on the Check In page
	// The payload can be the customer marketing email, phone, last name, or dog's first name
	// Usage Example:
	// api.php?method=getDogsByOther&payload=8083669175
	// Response Body: [{"first_name":"Christopher","last_name":"Morgan","email":"christopher.t.morgan@gmail.com","id":1,"dog_name":"ele","dog_notes":"Nothing NEW'","breed":"BM"},{"first_name":"Christopher","last_name":"Morgan","email":"christopher.t.morgan@gmail.com","id":2,"dog_name":"Morga%27n","dog_notes":"form update customer","breed":""}]
	public function getDogsByOther() {
		$this->connectDB();
    	$data = new GetRecordInnerJoin($this);

		//if (isset($this->Request['payload'])) {
		//echo " customer: ";
    		$this->ResponseBody = $data->dBQuery(trim("{\"table\": \"customer\", \"join\": \"dog\", \"fields\": [\"first_name\", \"last_name\", \"email\", \"dog.id\", \"dog_name\", \"dog_notes\", \"breed\"], \"where\": {\"email\":\"" . $this->payload . "\"}, \"on\":{\"customer\":\"id\", \"dog\":\"owner_id\"}}"));

		//}

		if ($this->ResponseBody == '[]') {
			//echo " marketing: ";
			$this->ResponseBody = $data->dBQuery(trim("{\"table\": \"customer\", \"join\": \"dog\", \"fields\": [\"first_name\", \"last_name\", \"email\", \"dog.id\", \"dog_name\", \"dog_notes\", \"breed\"], \"where\": {\"marketing_email\":\"" . $this->payload . "\"}, \"on\":{\"customer\":\"id\", \"dog\":\"owner_id\"}}"));
		}
		
		if ($this->ResponseBody == '[]') {
			//echo " phone: ";
			$this->ResponseBody = $data->dBQuery(trim("{\"table\": \"customer\", \"join\": \"dog\", \"fields\": [\"first_name\", \"last_name\", \"email\", \"dog.id\", \"dog_name\", \"dog_notes\", \"breed\"], \"where\": {\"phone\":\"" . $this->payload . "\"}, \"on\":{\"customer\":\"id\", \"dog\":\"owner_id\"}}"));
		}

		if ($this->ResponseBody == '[]') {
			//echo " last name: ";
			$this->ResponseBody = $data->dBQuery(trim("{\"table\": \"customer\", \"join\": \"dog\", \"fields\": [\"first_name\", \"last_name\", \"email\", \"dog.id\", \"dog_name\", \"dog_notes\", \"breed\"], \"where\": {\"last_name\":\"" . $this->payload . "\"}, \"on\":{\"customer\":\"id\", \"dog\":\"owner_id\"}}"));
		}

		if ($this->ResponseBody == '[]') {
			//echo " last name: ";
			$this->ResponseBody = $data->dBQuery(trim("{\"table\": \"customer\", \"join\": \"dog\", \"fields\": [\"first_name\", \"last_name\", \"email\", \"dog.id\", \"dog_name\", \"dog_notes\", \"breed\"], \"where\": {\"dog_name\":\"" . $this->payload . "\"}, \"on\":{\"customer\":\"id\", \"dog\":\"owner_id\"}}"));
		}	
	
    	echo $this->ResponseBody;
		$this->disconnectDB();		
	} // End function getDogsByOther

	// Function to return a list of trainers assigned to a dog with a status of "In" at daycare
	// Can accept a hard-coded Payload value of "walk"
	// Usage Example:
	// api.php?method=getActiveTrainers
	// Response Body: [{"trainer":"Chris"},{"trainer":"DBA"}]
	//
	// api.php?method=getActiveTrainers&payload=Walk
	// Response Body: [{"trainer":"Chris"}]
	public function getActiveTrainers () {
		$this->connectDB();
    	$cc = new GetRecord($this);
		
		if (!isset($this->Request['payload'])) {
			$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"daycare\", \"fields\": [\"trainer\"], \"where\":{\"Status\":\"In\"}, \"distinct\":[\"trainer\"]}"));
		}
		
		if (strtoupper($this->Request['payload']) == 'WALK') {
			$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"daycare\", \"fields\": [\"trainer\"], \"where\":{\"Status\":\"In\", \"sublocation\":\"Walk\"}, \"distinct\":[\"trainer\"]}"));
		}
		
		echo $this->ResponseBody;    	
		$this->disconnectDB();
	
	} // End function getActiveTrainers

	// Usage Example:
	// api.php?method=getDogsOnWalk
	// Request 
	// Response Body: [{"id":208,"tag":1,"dog_name":"East","dog_id":130,"customer_last_name":"Morgan","location":"Kettner Arts","sublocation":"Walk","updated":"2014-07-14 18:14:21","trainer":"Ben"},{"id":214,"tag":6,"dog_name":"Ele","dog_id":225,"customer_last_name":"Morgan","location":"Kettner Arts","sublocation":"Walk","updated":"2014-07-14 18:14:17","trainer":"Ben"}]
	public function getDogsOnWalk () {
	//{"table": "daycare", "fields": ["id", "tag", "dog_name", "dog_id", "customer_last_name", "location", "sublocation", "updated", "trainer"], "where":{"Status":"In", "sublocation":"Walk"}}
		$this->connectDB();
    	$cc = new GetRecord($this);
		$this->ResponseBody = $cc->dBQuery(trim("{\"table\": \"daycare\", \"fields\": [\"id\", \"tag\", \"dog_name\", \"dog_id\", \"customer_last_name\", \"location\", \"sublocation\", \"updated\", \"trainer\"], \"where\":{\"Status\":\"In\", \"sublocation\":\"Walk\"}}"));
		echo $this->ResponseBody;    	
		$this->disconnectDB();	
	} // End function getDogsOnWalk


/*
functions to create here:

x getTotalDogs - dashboard.js
x getDogsOnWalk
x getDogsInDogBowl
x getDogsInKettnerArts
x getDogsInBack
getActiveTrainers
getTrainersOnWalk

3 more from checkin.js

x daycareCheckOutDog (exists in v1, must have gotten deleted)
x isDogAlreadyCheckedIn(dogId) - getRecordCount
getNextTag
x actionTimeOut(dogRecord) - updateRecord
x actionDogBowl(dogRecord) - updateRecord
x TakeDogOnWalk(dogRecord) - updateRecord
x getData() - getRecordInnerJoin
x updateDogRecord - updateAllRecord (playcare_record)
x getDogById - getRecord (playcare_record)
x isDogAlreadyCheckedIn - getRecordCount
*/


} // End Class
 
?>