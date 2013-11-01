<?php

/*

	This is UpdateAll.  It updates every field in a record, given the primary key
	
	It is designed to work like:
	
	End Point:
	http://127.0.0.1:8080/forms/php/api.php?method=updateAllRecord&schema=dogtest&pkey=7
	
	Request Body:
	{"dog_name":"Piper", "dog_notes":"chicken", "owner_id":"1"}

	Response Body:
	0 if no updates were made
	1 if a successful update was made

*/


class UpdateAllRecord extends CreateRecord {

	private $schemaArr;			// The known and vetted structure of the table we are altering
	private $dataArr;			// The data we will alter the table with
	
	protected $sWheres;
	protected $sWheresClause;
	protected $sSet;
	protected $sCommonFields;
	protected $sSetClause;
	
	private $aData;
	
	protected $parent_object;	// Used for the the dynamic db credentials
	
	protected $pkeyid;			// Holds the primary key to update (from the payload)
	
	function UpdateAllRecord($object) {
		// We are overiding the parent constructor, but we want to call it explicietly here
		parent::__construct($this->Request);

		$schemaArr = array();		
		$dataArr = array();
	
		$this->parent_object = $object;		// Used for the dynamic db credentials
	}

	// Set the the Table to update from the "schema" parameter
	protected function setTableFromSchema($s) {
   		$this->schemaArr = (json_decode($s, true));
   		$this->sTable = $this->schemaArr['table'];
   		$this->aColumns = $this->schemaArr['fields'];
   		//print_r("Table: ". $this->sTable ." ");
	}

	// Set the the column names/fields from the hard-coded schema
	protected function setFieldsFromSchema($s) {
   		$this->schemaArr = (json_decode($s, true));
   		$this->aColumns = $this->schemaArr['fields'];
   		//print_r($this->aColumns);
	}
   
	// Set the data to update from the JSON in the Request Body
	protected function setDataFromJSON($s) {
		$this->dataArr = (json_decode($s, true));
   		//print_r($this->dataArr);
	}

	// Set the the Primary Key ID to update
	// This is what the payload's value is
	protected function setIDfromPayload($s) {
   		$this->pkeyid = $s;
   		//print_r("PK ID: ". $this->pkeyid ." ");
	}


	// Builds the SET clause of the SQL statement
	// The SET clause is in a format, such as:
	// "field_name1 = :field_name1, field_name2 = :field_name3..."
	// Or for example:
	// "dog_name = :dog_name, dog_notes = :dog_notes" and is ready for PDO processing
	public function setSetClause($s) {
		
		
		// working here
		$this->dataArr = (json_decode($s, true));
		if (isset($this->dataArr)) {
   			$this->sSet = $this->dataArr;
			//$this->setSetClause();
   		}
		
		
		//echo " dataArr ---->>> ";
		//var_dump($this->dataArr);
		//echo " <<<---- ";
		
		//echo " sSet is ->> ";
		//var_dump($this->sSet);
		$a = array_keys($this->sSet);
		//echo " ### Intersect: ";
		$this->sCommonFields = array();
		$this->sCommonFields = (array_intersect($a, $this->aColumns));
		//var_dump($this->sCommonFields);
		//echo " ### ";
		
		//echo " ### sSet: ";
		//var_dump($this->sSet);
		//echo " ### ";

		//echo " ### sSet Array Keys: ";
		//var_dump(array_keys($this->sSet));
		//echo " ### ";

		//echo " ### sSet Array Values: ";
		//var_dump(array_values($this->sSet));
		//echo " ### ";

		$i=1;		
		foreach ($this->sSet as $key => $value) {
			
			if ($key == $this->sCommonFields[$i-1]) {
				//echo "we have a match";
				$this->sSetClause .= $key." = :".$key."";
			}
			else {
				//echo "not the same";
			}
				//$this->sSetClause .= $this->aColumns[$i++]." = :".$key."";			// Doesn't work with fields out of order or skipped...
				if ((count($this->sCommonFields) > 1) && ($i++ != count($this->sCommonFields))) {
					$this->sSetClause .= ", ";
				}
		}

		//echo " --- Set Clause: ". $this->sSetClause. " --- ";
		//echo " --- sSet: ". $this->sSet. " --- ";
		//echo " --- sCommonFields: ". $this->sCommonFields. " --- ";
	}


	// Function to insert a record into the DB
	public function dBUpdate($s) {
	
	//$this->setSetClause($s);

/*
		// Build a dynamic SQL Statement from the original JSON Array
			$sQuery = "
				UPDATE $this->sTable SET ".$this->sSetClause."
				WHERE `id` = ".$this->pkeyid."
				";
*/
		// Build a dynamic SQL Statement from the original JSON Array
			$sQuery = "
				UPDATE $this->sTable SET ".$this->sSetClause."
				WHERE `id` = :pkid
				";

//echo " -- SQL Statement -----> ";
//echo $sQuery;
//echo " <----- SQL Statement --";

		try {
				$sql = $this->parent_object->conn->prepare($sQuery);

				// Bind the PDO variables for the WHERE clause				
				if (isset($this->pkeyid)) {
						$sql->bindValue(':pkid', $this->pkeyid, PDO::PARAM_INT);
				}
				
				
				if (isset($this->dataArr)) {
				$i=0;
					foreach ($this->sSet as $key => $value) {
						if ($key == $this->sCommonFields[$i]) {
							$name = ':'.$key;
							//echo "NAME: ". $name;
							$sql->bindValue($name, $value, PDO::PARAM_STR);
						}
						$i++;
					}
				}


// UPDATE Customers SET ContactName='Alfred Schmidt', City='Hamburg' WHERE CustomerName='Alfreds Futterkiste';


//$sql = $this->parent_object->conn->prepare($sQuery);
//echo " ----- PDO SQL Statement --->> ";
//print_r($sql);
//echo " <<--- PDO SQL Statement --- ";

			$sql->execute();
			
			echo $sql->rowCount();
			} 	catch(PDOException $e) {
  				echo 'Error: ' . $e->getMessage();
			} 

		
	} // END function dbInsert()
} // END Class

?>