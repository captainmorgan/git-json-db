<?php
/*
	Class to both store and return record(s) from the database as a JSON String
	Given a JSON String in the appropriate format:
	
	{					
    "table": "customer",				<-- table name (required)
    "fields": [							<-- array containing valid fields to SELECT
        "id",							<--    they may be in any order
        "first_name",					<-- (required)
        "last_name"
    ],
    "where": {							<-- WHERE clause, such as "WHERE last_name = 'morgan'
        "last_name": "Morgan"			<-- (optional)
    },
    "order": [							<-- array contain valid fields to ORDER BY
        "first_name"					<-- (optional)
    ],
    "range": "10",						<-- range of records to return
    "range_start": "5"					<-- starting record to return, such as LIMIT rs, r
										<-- (optional)
	"distinct":["last_name"]			<-- (Optional).  Returns only unique values for the first field
	
	The JSON String containing the results is returned and also stored as $this->record
	
	Examples:
	{"table": "customer", "fields": ["first_name"], "where": {"id":"1"}}
	{"table": "customer", "fields": ["first_name", "last_name", "email"], "where": {"id":"1"}}
	{"table": "customer", "fields": ["id", "first_name", "last_name"], "order": ["created"]}
	
*/

class GetRecord extends APIObject {

	// JSON String-formatted input parameters
	//public $schemaJSON;
	//private $dataJSON;
	
	// Convert the JSONs to Associative Arrays
	protected $dataArr;

	protected $sTable;
	protected $aColumns;
	protected $sWheres;
	protected $sWheresClause;
	protected $sOrder;
	protected $sOrderClause;
	protected $sRangeStart;
	protected $sRange;
	protected $sLimitClause;
	protected $sDistinct;
	
	public $record;
	
	protected $parent_object;

	function GetRecord($object) {
		// We are overiding the parent constructor, but we want to call it explicietly here
		parent::__construct($this->Request);
		
		$dataArr = array();
		// Set default values in case the JSON doesn't have these elements
		$this->sWheresClause = "1";
		$this->sRangeStart = "0";
		$this->sDistinct = null;

		$this->parent_object = $object;
		//echo "payload: ".$this->parent_object->payload;
		//echo "CONN ".$this->parent_object->conn;

	}

	// Function is called by dBQuery(POST Reponse Body)
	// Function takes the ResponseBody, ensures it is a valid JSON String and sets
	//  the class attributes from it.
	// Returns true if everything checks out.
	// Returns false if the JSON is not valid, or if the 'table' or 'fields' elements
	//  were not in the JSON
	// I probably should have named this function, 'buildSQLfromJSON' or something like that
   	protected function setQueryParamsFromData($s) {
   
   
   		$this->dataArr = (json_decode($s, true));
   		
   		// Check to see if the JSON is not valid
   		if (!$this->dataArr) {
   			// print("Error.  Invalid JSON received");
   			return false;
   		}

		// We need a table name, exit if we didn't get one
		// *** This is vulnerable to SQL Injection
		// Test Case: {"table": "dog;","fields": ["dog_name","dog_notes","owner_id"],"where": {"dog_name": "ele", "id": "1"}}
		// Also {"table": "dog","fields": ["dog_name","dog_notes","owner_id` from dog;"],"where": {"dog_name": "ele", "id": "1"}}
		if (isset($this->dataArr['table'])) {
		
   			// Perform a regex to make sure table is only an alphanumeric string
   			// This is important to prevent SQL Injection attacks, such as:
			// Test Case: {"table": "dog;","fields": ["dog_name","dog_notes","owner_id"],"where": {"dog_name": "ele", "id": "1"}}
			// Also {"table": "dog","fields": ["dog_name","dog_notes","owner_id` from dog;"],"where": {"dog_name": "ele", "id": "1"}}   			
			if (preg_match('/[^a-z_\-0-9]/i', $this->dataArr['table'])) {
   				echo "Illegal character found. ";
   				return false;
   			}
   			
   			$this->sTable = mysql_real_escape_string($this->dataArr['table']);
   			
   		}
   		else {
   			return false;
   		}
   		
   		// We need an element named 'fields' and it can't be an empty list
   		// It would be nice if we checked the table to see if these were real fields...
		if ((isset($this->dataArr['fields'])) && (count($this->dataArr['fields']) > 0)) {
		
			// Perform a regex to prevent against SQL Injection
			// We allow alphanumeric, *, and a space.  * and space are needed for getRecordCount
			// Prevents against: {"table": "dog","fields": ["dog_name","dog_notes","owner_id` from dog;"],"where": {"dog_name": "ele", "id": "1"}}  
			foreach ($this->dataArr['fields'] as $key => $value) {
				if (preg_match('/[^a-z_\-0-9\(*)(\" \")]/i', $value)) {
   					echo "Illegal character found. ";
   					return false;
   				}
			}
		
   			$this->aColumns = $this->dataArr['fields'];
   		}
   		else {
   			return false;
   		}
		
		// DISTINCT option.  Allows you to getRecords with unique values
		// Usage: {"table": "daycare", "fields": ["id", "trainer"], "where":{"Status":"In"}, "distinct":["trainer"]}
		// TODO: This isn't exactly finished yet.  It ignores the value in the brackets, only works on one field,
		// and has to be the first field in the field[] list.
		if (isset($this->dataArr['distinct'])) {
			foreach ($this->dataArr['distinct'] as $key => $value) {
				if (preg_match('/[^a-z_\-0-9]/i', $value)) {
   					return false;
				}	
			}
			//print("there is a distinct possibility. key: ". $key . " value: " . $value);
			$this->sDistinct = "DISTINCT";
			
		}
		
		if (isset($this->dataArr['where'])) {
			
			// Prevent SQL Injection.  We allow alphanumeric and % only.
			foreach ($this->dataArr['where'] as $key => $value) {
				if (preg_match('/(%)[a-z_\-0-9](%)/i', $key) || preg_match('/(%)[^a-z_\-0-9](%)/i', $value)) {
   					echo "Illegal character found. ";
   					return false;
				}		
			}
			$this->sWheres = $this->dataArr['where'];
			$this->setWheresClause();
		}
		
		if (isset($this->dataArr['order'])) {

			// Prevent SQL Injection.  We allow alphanumeric only.
			foreach ($this->dataArr['order'] as $key => $value) {
				if (preg_match('/[^a-z_\-0-9]/i', $value)) {
   					return false;
				}		
			}
			$this->sOrder = $this->dataArr['order'];
			$this->setOrderClause();
		}
		
		// If the Range and Range Start provided are negative, they are ignored
		if ((isset($this->dataArr['range'])) && ((int)$this->dataArr['range'] > 0)) {
			// Technically, we should only allow digits, but with the (int) cast, a letter would fail above
			if (preg_match('/[^a-z_\-0-9]/i', $this->dataArr['range'])) {
				return false;
			}
			$this->sRange = $this->dataArr['range'];
				// Can't have a Range Start without a Range
				if ((isset($this->dataArr['range_start'])) && ((int)$this->dataArr['range_start'] >= 0)) {
					if (preg_match('/[^a-z_\-0-9]/i', $this->dataArr['range_start'])) {
						return false;
					}
				$this->sRangeStart = $this->dataArr['range_start'];
			}
			$this->setLimitClause();
		}			
		
		return true;
	}

	// Builds the WHERE clause of the SQL statement
	// If a 'where' element was not included in the JSON, use 'WHERE 1'
	private function setWheresClause() {
		$this->sWheresClause = null;
		$i=1;		
		foreach ($this->sWheres as $key => $value) {
			//$sql->bindValue($name, $value, PDO::PARAM_STR);
			//print($key." = '".$value."'");
			// By using 'LIKE' instead of '=', you can use wildcards like '%'
			$this->sWheresClause .= $key." LIKE :".$key."";
			if ((count($this->sWheres) > 1) && ($i++ != count($this->sWheres))) {
				//print(" and ");
				$this->sWheresClause .= " and ";    // What if we wanted OR...
			}
			// Use this instead? ArrayIterator::valid
		}	
	}

	// Builds the ORDER BY clause of the SQL statement
	// The string includes the keywords "ORDER BY", and works a bit differently than the WHERE clause
	private function setOrderClause() {
		$this->sOrderClause = "ORDER BY `".str_replace(" , ", " ", implode("`, `", $this->sOrder))."`";
		//$sOrderClause = "`first_name`, `last_name`, `id`";
		//print(" OrderClause: ".$this->sOrderClause);
	}	
	

	// Builds the LIMIT clause of the SQL statement
	// Our API uses the terminology "Range" and RangeStart ala "LIMIT RangeStart, Range"
	// If a 'range' element was not included in the JSON, use 'WHERE 1'
	private function setLimitClause() {
		//$this->sLimitClause = "LIMIT ".$this->sRangeStart.", ".$this->sRange;
		$this->sLimitClause = "LIMIT :range_start, :range";
	}


/*   
   protected function setDataJSON($s) {
   		//
   }
*/
   
   // Function to echo back important characteristics about this sub-class instance
   public function reportWhatIKnow() {
   
   		print("Reporting on What I Know...");
   		print(" Schema: \n");
   		print_r($this->dataArr);
 //  		print(" Data: \n");
   		print(" sTable: \n");
   		print_r($this->sTable);
   		print(" Columns: \n");
   		print_r($this->aColumns);		   		
   }   


	// The "main function" of this class, returns and stores the query as a JSON String
	// 	
	public function dBQuery($s) {

		// Check to make sure the JSON we received is valid
		if (!$this->setQueryParamsFromData($s)) {
			echo "Error.  The JSON String received was not valid, or was not in the correct format.";
			return false;
		}
		else {
			$this->setQueryParamsFromData($s);
		}




/*
// *********  DB Connection Test	
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

// *****************
*/

//$sOrderClause = "`first_name`, `last_name`, `id`";

		try {	

			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS $this->sDistinct `".str_replace(" , ", " ", implode("`, `", $this->aColumns))."`
				FROM $this->sTable 
				WHERE ".$this->sWheresClause."
				$this->sOrderClause
				$this->sLimitClause
				";
/*
heredoc

 $sql = <<<SQL
SELECT blahs 
SQL;
*/

//propell - MVC database extendors
//doctrine

			
			//print(" THE STATEMENT --- ".$sQuery." ---- ");

				$sql = $this->parent_object->conn->prepare($sQuery);
			//$sql = $conn->prepare($sQuery);
				// Bind the PDO variables for the WHERE clause
				if (isset($this->dataArr['where'])) {
					foreach ($this->sWheres as $key => $value) {
						$name = ':'.$key;
						$sql->bindValue($name, $value, PDO::PARAM_STR);
					}
				}
				if (isset($this->dataArr['range'])) {
					$sql->bindValue(':range', (int)$this->sRange, PDO::PARAM_INT);
					$sql->bindValue(':range_start', (int)$this->sRangeStart, PDO::PARAM_INT);
				}

			$sql->execute();
			
			// ************
			/*
			//$result = $sql->fetchAll(PDO::FETCH_ASSOC);		// Returns: [{"time_check_in":"2013-12-23 19:11:00"}]
			//$result = $sql->fetchAll(PDO::FETCH_NUM);			// Returns: [["2013-12-23 19:11:00","2013-12-23 19:41:00",1]]
			//$result = $sql->fetchAll(PDO::FETCH_LAZY);			// Fails
			$result = $sql->fetchAll(PDO::FETCH_COLUMN);	// Returns: ["2013-12-23 19:11:00"]
			$this->record = json_encode($result, true);			// This works!
			//$this->record = implode(', ', $result);
			return $this->record;
			*/
			// ************
			
			// ************
			$result = $sql->fetchAll(PDO::FETCH_ASSOC);
			$this->record = json_encode($result, true);
			return rawurldecode($this->record);
			// ************
					
			} 	catch(PDOException $e) {
  				echo 'Error: ' . $e->getMessage();
			} 

		
	} // END function dbInsert()
} // END Class

?>