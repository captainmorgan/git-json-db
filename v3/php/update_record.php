<?php

/*
	The UpdateRecord class extends the CreateRecord class, but behaves more like the
	GetRecord class.
	Update Record takes the Request Body and expects a JSON string in the following format:
	{
		"table": "tablename",
		"set": {
				"column1": "newvalue",
				"column2": "newvalue"
				},
		"where": {
				"id": "currentvalue"			<-- Doesn't have to be "id", but any column
				}
	}

	It returns with the number of rows affected by the update and updates the record.
	
	Usage Example:
	api.php?method=updateRecord&schema=dogtest
	
	Request Body:
	{"table": "dogtest",  "set":{"dog_name": "Nothing NEW"}, "where": {"id": "2"}}
	
	The SQL statement that is assembled by the dBUpdate($s) method is more-similar to
	GetRecord.  The SET and WHERE clause parsing are very similar.  Be careful because if
	WHERE = 1, it will update every record in the table!
	
	NOTE: Currently this has some validation errors.
	
	NOTE: UpdateRecord should require the dataArr / dataArr component!
	It should match the table and column names!
	It should only need an array of column names from the schema library; opposite of how
	CreateRecord works (gets an array of data, matched against the full schema)


*/


class UpdateRecord extends CreateRecord {

	private $schemaArr;			// The known and vetted structure of the table we are altering
	private $dataArr;			// The data we will alter the table with
	
	protected $sWheres;
	protected $sWheresClause;
	protected $sSet;
	protected $sCommonFields;
	protected $sSetClause;
	
	protected $parent_object;	// Used for the the dynamic db credentials
	
	function UpdateRecord($object) {
		// We are overiding the parent constructor, but we want to call it explicietly here
		parent::__construct($this->Request);

		$schemaArr = array();		
		$dataArr = array();
		
		$this->parent_object = $object;

		// Zero won't update any rows, this is a protection
		$this->sWheresClause = "0";		
	}

	// Set the Schema Associative Array from the JSON input we received
   protected function setSchemaJSON($s) {
   		$this->schemaArr = (json_decode($s, true));
   		//$this->sTable = $this->schemaArr['table'];
   		$this->aColumns = $this->schemaArr['fields'];
   }


	// This function should be reveiwed
	// This function takes the JSON in the Request Body and attempts to pull valid
	// SQL parameters from it, based upon our standard format, for the SQL statement
	// These "parameters" include the table, the columns and values to set (to update)
	// and the 'where criteria'
   	public function setQueryParamsFromData($s) {
   	
   		//echo " --- Running setQueryParamsFromData ---";
   	
   	   	$this->dataArr = (json_decode($s, true));
   		
   		// Check to see if the JSON is not valid
   		if (!$this->dataArr) {
   			//print("Error.  Invalid JSON received");
   			return false;
   		}

		// We need a table name, exit if we didn't get one
		// NOTE: this should come from the known schema
		if (isset($this->dataArr['table'])) {
   			$this->sTable = $this->dataArr['table'];
   		}
   		else {
   			return false;
   		}

   		// We need an element named 'set' and it can't be an empty list
		//if ((isset($this->dataArr['set'])) && (count($this->dataArr['set']) > 0)) {
		if (isset($this->dataArr['set'])) {
   			$this->sSet = $this->dataArr['set'];
			$this->setSetClause();
   		}
   		else {
   			return false;
   		}
   		
   		if (isset($this->dataArr['where'])) {
			$this->sWheres = $this->dataArr['where'];
			$this->setWheresClause();
		}
		
		return true;
   	}


	// Builds the SET clause of the SQL statement
	private function setSetClause() {
	
		//echo "************* sSet *********";
		//var_dump(array_keys($this->sSet));
		//echo "************* aColumns *********";
		//var_dump($this->aColumns);
		//$result = array_diff($this->aColumns, array_keys($this->sSet));     // array(1) { [0]=> string(2) "id" }
		
				//echo " sSet is ->> ";
		//var_dump($this->sSet);
		$a = array_keys($this->sSet);
		//echo " ### Intersect: ";
		$this->sCommonFields = array();
		$this->sCommonFields = (array_intersect($a, $this->aColumns));
	
		//$this->sSetClause = null;
		$i=1;		
		foreach ($this->sSet as $key => $value) {
		
			//$this->sSetClause .= $b[$i-1]." = :".$key."";
			//$this->sSetClause .= $key." = :".$key."";
			
			if ($key == $this->sCommonFields[$i-1]) {
				//echo "we have a match --> ";
				//var_dump($this->sSet);
				//echo " <-- ";
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
	}

private function array_equal_values(array $a, array $b) {
    return !array_diff($a, $b) && !array_diff($b, $a);
}


	// Builds the WHERE clause of the SQL statement
	// If a 'where' element was not included in the JSON, use 'WHERE 0'
	private function setWheresClause() {
		$this->sWheresClause = null;
		$i=1;		
		foreach ($this->sWheres as $key => $value) {
			//$sql->bindValue($name, $value, PDO::PARAM_STR);
			//print($key." = '".$value."'");
			$this->sWheresClause .= $key." = :".$key."";
			if ((count($this->sWheres) > 1) && ($i++ != count($this->sWheres))) {
				//print(" and ");
				$this->sWheresClause .= " and ";
			}
			// Use this instead? ArrayIterator::valid
		}
		//echo " --- Where Clause: ".$this->sWheresClause. " --- ";
	}


	// Function to insert a record into the DB
	public function dBUpdate($s) {
	
	$this->setQueryParamsFromData($s);
	
	/*
		$mysql_host 	= 	'localhost';
		$mysql_user 	= 	'signature';
		$mysql_pass 	= 	'signature';
		$mysql_db 		= 	'jquery';
*/


		/*
	
		try {
    		$conn = new PDO("mysql:host=$mysql_host;dbname=$mysql_db", $mysql_user, $mysql_pass);
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		// Make sure we are talking to the database in UTF-8
   			$conn->exec('SET NAMES utf8');
			# echo "It worked.";
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
		}
		
		*/
	
		// Build a dynamic SQL Statement from the original JSON Array
			$sQuery = "
				UPDATE $this->sTable SET ".$this->sSetClause."
				WHERE ".$this->sWheresClause."
				";

		try {
				//$sql = $conn->prepare($sQuery);
				$sql = $this->parent_object->conn->prepare($sQuery);


				if (isset($this->dataArr['set'])) {
				$i=0;
					foreach ($this->sSet as $key => $value) {
						if ($key == $this->sCommonFields[$i]) {
							$name = ':'.$key;
							$sql->bindValue($name, $value, PDO::PARAM_STR);
						}
						$i++;
					}
				}

				// Bind the PDO variables for the WHERE clause				
				if (isset($this->dataArr['where'])) {
					foreach ($this->sWheres as $key => $value) {
						$name = ':'.$key;
						//$sql->bindValue($name, $value, PDO::PARAM_STR);
						// Apostrophie bug
						$sql->bindValue($name, trim(str_replace('\'', ' ', ($value))), PDO::PARAM_STR);
					}
				}

			$sql->execute();

			echo $sql->rowCount();
			} 	catch(PDOException $e) {
  				echo 'Error: ' . $e->getMessage();
			} 

		
	} // END function dbInsert()
} // END Class

?>