<?php

/*
	CreateRecord takes two JSONs are input; one is the schema (the table and columns)
	the other is the data to insert into the record.
	
	The API hardcodes in the schemas because 1. We know them and 2. We don't want to allow
	users to pick system tables, or otherwise create records in tables unless we explicately
	allow it.
	
	Usage Example:
		api.php?method=createRecord&schema=customer
		
		Request Body:
		{"first_name":"Christopher","last_name":"Morgan","phone":"8083669175","email":"christopher.t.morgan@gmdddail.com","marketing_email":"","address_street":"2146 Erie Street","address2":"","city":"San Diego","state":"CA","zip":"92110","country":"United States","company":"","work_phone":"8083669175","work_street1":"","work_street2":"","work_city":"San Diego","work_state":"CA","work_zip":"","work_country":"United States","email_pref":"1","referral":"Something-else","dob":"1982-10-04"}
	
		Notes:
			1. The number of values in the Data JSON must match the number of fields in the Schema
			2. The order of the data values is important and must match
	
	--
	
		Data JSON Example:
		{"first_name":"Christopher","last_name":"Morgan","phone":"8083669175","email":"christopher.t.morgan@gmdddail.com","marketing_email":"","address_street":"2146 Erie Street","address2":"","city":"San Diego","state":"CA","zip":"92110","country":"United States","company":"","work_phone":"8083669175","work_street1":"","work_street2":"","work_city":"San Diego","work_state":"CA","work_zip":"","work_country":"United States","email_pref":"1","referral":"Something-else","dob":"1982-10-04"}


*/


class CreateRecord extends APIObject {

	// JSON String-formatted input parameters
	private $schemaJSON; // Are these used?
	private $dataJSON;
	
	// Convert the JSONs to Associative Arrays
	private $schemaArr;
	private $dataArr;

	private $sTable;
	private $aColumns;

	protected $parent_object;	// Used for the the dynamic db credentials
	
	public $createdID;		//lastInsertId();

	function CreateRecord() {
		// We are overiding the parent constructor, but we want to call it explicietly here
		parent::__construct($this->Request);
		
		$schemaArr = array();
		$dataArr = array();
		
		//$this->RequestBody = getBody();
		//echo "######";
		//echo $this->RequestBody;
	}

	// Set the Schema Associative Array from the JSON input we received
   protected function setSchemaJSON($s) {
   		$this->schemaArr = (json_decode($s, true));
   		$this->sTable = $this->schemaArr['table'];
   		$this->aColumns = $this->schemaArr['fields'];
   		//print_r($this->aColumns);
   		//print_r($this->sTable);
   }
   
   // Set the Load Data Associative Array from the JSON input we received
   protected function setDataJSON($s) {
   		$this->dataArr = (json_decode($s, true));
   }
   
   // Function to echo back important characteristics about this sub-class instance
   public function reportWhatIKnow() {
   
   		print("Reporting on What I Know...");
   		print(" Schema: \n");
   		print_r($this->schemaArr);
   		print(" Data: \n");
   		print_r($this->dataArr);
   		print(" sTable: \n");
   		print_r($this->sTable);
   		print(" Columns: \n");
   		print_r($this->aColumns);		   		
   }   

	
	// Function to insert a record into the DB
	protected function dBInsert() {
	
	
	
		$mysql_host 	= 	'localhost';
		$mysql_user 	= 	'signature';
		$mysql_pass 	= 	'signature';
		$mysql_db 		= 	'jquery';

		try {
    		$conn = new PDO("mysql:host=$mysql_host;dbname=$mysql_db", $mysql_user, $mysql_pass);
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		// Make sure we are talking to the database in UTF-8
   			$conn->exec('SET NAMES utf8');
			# echo "It worked.";
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
		}
		
	
		if(count($this->aColumns) == count($this->dataArr))
		{
		// Build a dynamic SQL Statement from the original JSON Array
			$sQuery = "
				INSERT INTO $this->sTable ( `".str_replace(" , ", " ", implode("`, `", $this->aColumns))."` )
				VALUES (:".str_replace(" , ", " ", implode(", :", array_keys($this->dataArr))).")
				";

/*
			echo $sQuery;
			
			SQL Statement:
			INSERT INTO customer ( `first_name`, `last_name`, `phone`, `email`, `marketing_email`, `address_street`, `address2`, `city`, `state`, `zip`, `country`, `company`, `work_phone`, `work_street1`, `work_street2`, `work_city`, `work_state`, `work_zip`, `work_country`, `email_pref`, `referral`, `dob`, `email2` ) VALUES (:first_name, :last_name, :phone, :email, :marketing_email, :address_street, :address2, :city, :state, :zip, :country, :company, :work_phone, :work_street1, :work_street2, :work_city, :work_state, :work_zip, :work_country, :email_pref, :referral, :dob_1, :email2)
*/			
		try {
			//$sql = $this->parent_object->conn->prepare($sQuery);
			$sql = $conn->prepare($sQuery);		
			// Bind the PDO variables
			foreach ($this->dataArr as $key => $value) {
				$name = ':'.$key;
				$sql->bindValue($name, $value, PDO::PARAM_STR);
			}		
			$sql->execute();
			//echo $conn->lastInsertId();
			$this->createdID = (int)$conn->lastInsertId();
			return $this->createdID;
			
			} 	catch(PDOException $e) {
  				echo 'Error: ' . $e->getMessage();
			} 
		} // End If		
		else
		{
			print("Error. Expecting ". count($this->aColumns) . " values and " . count($this->dataArr) . " were received. <br />");
		}
		
	} // END function dbInsert()
} // END Class

?>