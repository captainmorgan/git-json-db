<?php
/*
	Class to both return, in the proper JSON format, the schema description of the table the
	API is about to manipulate.
	
	Example Usage:
	URL/Endpoint:	api.php?method=descSchema
	Request Body:	customertest
			or
	Endpoint:		method=descSchema&payload=customertest
	Output:			"{\"table\":\"customertest\",\"fields\":[\"first_name\",\"last_name\",\"phone\",\"dob\"]}"
	
*/

class DescSchema extends APIObject {
	
	public $record;
	
	private $table;
	
	protected $parent_object;

	function DescSchema($object) {
		// We are overiding the parent constructor, but we want to call it explicietly here
		parent::__construct($this->Request);

		$this->parent_object = $object;
		
		//echo " HI " $object->RequestBody;
		
	}

	
	// Function to set the table that we want to describe.
	// Table must be letters and numbers only
	// Table must be in the Request Body
	public function setTable($t) {

		// The table can only be alphanumeric
		// Note: This may be problematic if we introduct an underscore
		if(preg_match('/[^a-z_\-0-9]/i', $t))
		{
			echo "Error. A valid table was not provided in the Request Body.";
  			return false;
		}
		else
		{
			$this->table = $t;
			//echo "TABLE: " . $this->table;
			return $t;
		}
	} // End function setTable

	public function queryDB() {
	
	try {
			$sQuery = "
				SHOW COLUMNS FROM $this->table
				";
		$sql = $this->parent_object->conn->prepare($sQuery);
		$sql->execute();
		$result = $sql->fetchAll(PDO::FETCH_ASSOC);
		//return $result;
		
//print_r($result);
//print(" <br />  <br />");


$c = Array();

	foreach ($result as $key => $value) {
		if ($result[$key]['Key'] != "PRI") {
			$c[$key-1] = $result[$key]['Field'];
			//echo " ** Found an auto-incrementing field at:  " . $result[$key]['Field'] . " ** ";
		}
	}

$t = json_encode($c, true);

$h = "{\"table\":\"" . $this->table . "\",\"fields\":" . $t . "}";
echo "\"" . addslashes($h) . "\"";
//


		} 	catch(PDOException $e) {
  			echo 'Error: ' . $e->getMessage();
		} 
	
	} // End public function describe

	
} // END Class

?>