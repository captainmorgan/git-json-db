<?php
/*
	Class to both return, in the proper JSON format, the schema description of the table the
	API is about to manipulate.
	
	Example Usage:
	URL/Endpoint:	api.php?method=descSchema&payload=dog
	
	Response: {"table":"dog","fields":["dog_name","breed","behaviors","allergies","dog_birthday","dog_dhp","dog_parvo","dog_rabies","dog_bordetella","dog_giardia","vet","dog_notes","gender","furcolor","pawwidth","neckgirth","waistgirth","height","license","microchip","owner_id"]}
	
*/

class DescSchema {

	protected $table;
//protected $parent_object;	// Used for the the dynamic db credentials
protected $conn;
	public $record;
	public $fields = array();
	public $field_list = array();

	// Constructor
	function DescSchema($object) {
	
	//$this->fields = array();
 		try {
 		
 			$parent_object->db = DBCore::getInstance();
 			$parent_object->db->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$parent_object->db->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$parent_object->db->conn->exec('SET NAMES utf8');
			$this->conn = $parent_object->db->conn;
			PC::debug("Successfully connected to database.");
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
   			//debug("Unsuccessful in connecting to database...");
   			exit();
		} 	
	
	}
	
	// Helper function used to set the table
	public function setTable($table) {
		$this->table = $table;

		//$field_list = array();
		
		$stmt = "describe " . $this->table . "";
		
		try {
			$sql = $this->conn->prepare($stmt);	
			$sql->execute();
			$result = $sql->fetchAll(PDO::FETCH_ASSOC);
			
			// We want to remove the following "platform" fields: id, created, updated
			foreach ($result as $key => $value) {
				if (($value['Field'] != 'id') && ($value['Field'] != 'created') && ($value['Field'] != 'updated'))
				{
					array_push($this->field_list, $value['Field']);
				}
			}


			//$this->fields = $field_list;
			$this->record = json_encode($this->field_list, true);
			//$this->fields = array();
			//$this->fields = json_encode($this->field_list, true);
			//echo " lots " . $this->fields . " no mas ";

			//$this->record = addslashes("{\"table\":\"" . $this->table . "\",\"fields\":") . addslashes($this->record) . "}";
			$this->record = "{\"table\":\"" . $this->table . "\",\"fields\":" . $this->record . "}";
			
			// Experiment
			//echo "{" . implode(":\"\", ", $this->field_list) . ":\"\"} ";

		} 	catch(PDOException $e) {
  			echo 'Error: ' . $e->getMessage();
		}

		
	} // End function setTable
	
	
	// Helper function that is called to return the description of the schema
	public function getDesc() {
		echo $this->record;
		return $this->record;
	} // End function getDesc


	// Help function to return the description, but not echo it
	public function getDescSilent() {
		return $this->record;
	} // End function getDesc
	
	// Function to return an example of a CreateRecord JSON data payload
	// Example: {"first_name":"", "last_name":"", "phone":"", "dob":""}
	public function getExample()
	{
		return "{\"" . implode("\":\"\", \"", $this->field_list) . "\":\"\"} ";
	} // End function getExample

} // END Class

?>