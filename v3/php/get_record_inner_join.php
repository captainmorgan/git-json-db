<?php
/*

	GetRecordInnerJoin is a more-complex version of GetRecord designed for SQL queries
	using an Inner Join.  An Inner Join is the intersection of two tables.
	
	The INNER JOIN keyword selects all rows from both tables as long as there is a match between the columns in both tables.
	
	Syntax:
	SELECT column_name(s)
	FROM table1
	INNER JOIN table2
	ON table1.column_name=table2.column_name;
	
	API Usage:
	
	{					
    "table": "customer",				<-- table name (required)    
    "join": "dog",						<-- the table to join the first table on
    "on": {								<-- The ON clause
    	"tableA":"field",				<-- probably the primary key of the first table
    	"tableB:"field",				<-- probably the related field of the second table
    },
    "fields": [							<-- array containing valid fields to SELECT
        "id",							<--    they may be in any order
        "first_name",					<-- (required)
        "last_name"
    ],
    "where": {							<-- WHERE clause, such as "WHERE last_name = 'morgan'
        "last_name": "Morgan"			<-- (optional)
        "tableB.field" : "Value"		<-- (optional) You may designate tables in the WHERE clause
    },
    "order": [							<-- array contain valid fields to ORDER BY
        "first_name"					<-- (optional)
    ],
    "range": "10",						<-- range of records to return
    "range_start": "5"					<-- starting record to return, such as LIMIT rs, r
	}									<-- (optional)
	
	Examples:
	{"table": "customer", "fields": ["first_name"], "where": {"id":"1"}}
	{"table": "customer", "fields": ["first_name"], "where": {"id":"1"}, "order": ["dog_name"]}
	{"table": "customer", "join": "dog", "fields": ["first_name", "dog_name", "last_name", "dog.id"], "where": {"owner_id":"1"}}
	{"table": "customer", "join": "dog", "fields": ["first_name", "dog_name", "last_name", "dog.id"], "where": {"owner_id":"1"}, "on":{"customer":"id", "dog":"owner_id"}}
	{"table": "customer", "join": "dog", "fields": ["id", "first_name", "last_name", "email"], "where": {"email":"christopher.t.morgan@gmail.com"}, "on":{"customer":"id", "dog":"owner_id"}}
	{"table": "customer", "join": "dog", "fields": ["first_name", "last_name", "email", "dog.id"], "where": {"email":"dandiego@gmail.com"}, "on":{"customer":"id", "dog":"owner_id"}}
	
	{"table": "delegate", "join": "dog", "fields": ["id", "first_name", "last_name", "owner_id"], "where": {"owner_id":"123"}, "on":{"delegate":"owner_id", "dog":"owner_id"}}
	
	
	The JSON String containing the results is returned and also stored as $this->record
	
	
*/

class GetRecordInnerJoin extends GetRecord {

	protected $dataArr;

	protected $sTable;
	protected $sJoinTable;
	protected $aColumns;
	protected $sWheres;
	protected $sWheresClause;
	protected $sOn;
	protected $sOnClause;	
	protected $sOrder;
	protected $sOrderClause;
	protected $sRangeStart;
	protected $sRange;
	protected $sLimitClause;
	
	public $record;
	
	protected $parent_object;

	function GetRecordInnerJoin($object) {
		// We are overiding the parent constructor, but we want to call it explicietly here
		parent::__construct($this->Request);
		
		$dataArr = array();
		// Set default values in case the JSON doesn't have these elements
		$this->sWheresClause = "1";
		$this->sRangeStart = "0";

		$this->parent_object = $object;
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
		// *** TODO: This is vulnerable to SQL Injection
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
   			
   			// Set the "left" table
   			$this->sTable = mysql_real_escape_string($this->dataArr['table']);
   			
   			//Set the "right", the "joined" table
   			$this->sJoinTable = mysql_real_escape_string($this->dataArr['join']);
   			
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
				if (preg_match('/[^a-z_\-0-9\(*)(\" \" \.)]/i', $value)) {
   					echo "Illegal character found. ";
   					return false;
   				}
			}
		
   			$this->aColumns = $this->dataArr['fields'];
   		}
   		else {
   			return false;
   		}

		if (isset($this->dataArr['where'])) {
			
			// Prevent SQL Injection.  We allow alphanumeric and % only.
			foreach ($this->dataArr['where'] as $key => $value) {
				if (preg_match('/(%)[a-z_\-0-9](%)(\.)/i', $key) || preg_match('/(%)[^a-z_\-0-9](%)/i', $value)) {
   					echo "Illegal character found in WHERE clause. ";
   					return false;
				}		
			}
			$this->sWheres = $this->dataArr['where'];
			$this->setWheresClause();
		}

		if (isset($this->dataArr['on'])) {
			
			// Prevent SQL Injection.  We allow alphanumeric and % only.
			foreach ($this->dataArr['on'] as $key => $value) {
				if (preg_match('/(%)[a-z_\-0-9](%)(\.)/i', $key) || preg_match('/(%)[^a-z_\-0-9](%)/i', $value)) {
   					echo "Illegal character found in ON clause. ";
   					return false;
				}		
			}
			$this->sOn = $this->dataArr['on'];
			$this->setOnClause();
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
			// By using 'LIKE' instead of '=', you can use wildcards like '%'
			// What we are building here will be parsed by PDO
			
			// start test
			//$goodUrl = str_replace('?/', '?', $badUrl);
			
			if (strpos($key,'.') !== false) {
   				 //print(" **** TRUE no PDO **** ");
   				 //print($value);
   				 $this->sWheresClause .= $key." = ". $value;
			}
			else {
				//print(" **** FASLSE use PDO **** ");
				$this->sWheresClause .= $key." = :".$key."";
			}
			
			// end test
			
			//$this->sWheresClause .= $key." = :".$key."";
			//$this->sWheresClause .= $key." = 'Mike Dog 1'"; // Works
			//$this->sWheresClause .= $key." = ". $value;
			if ((count($this->sWheres) > 1) && ($i++ != count($this->sWheres))) {
				$this->sWheresClause .= " and ";
			}
		}
		//print(" Where Clause: " . $this->sWheresClause . " ");
	}


	// Builds the ON clause of the SQL statement
	// The ON clause is used for the join and is in the following format:
	// "on":{"tableA":"field", "tableB":"field"}
	private function setOnClause() {
		$this->sOnClause = null;
		$i=1;		
		foreach ($this->sOn as $key => $value) {
			$this->sOnClause .= $key.".".$value."";
			if ((count($this->sOn) > 1) && ($i++ != count($this->sOn))) {
				$this->sOnClause .= " = ";
			}
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


	// The "main function" of this class, returns and stores the query as a JSON String
	public function dBQuery($s) {

		// Check to make sure the JSON we received is valid
		if (!$this->setQueryParamsFromData($s)) {
			echo "Error.  The JSON String received was not valid, or was not in the correct format.";
			echo "is it ".$s;
			return false;
		}
		else {
			$this->setQueryParamsFromData($s);
		}

		try {

				/*
					Syntax:
					SELECT column_name(s)
					FROM table1
					INNER JOIN table2
					ON table1.column_name=table2.column_name;
	
					SELECT first_name, last_name, dog_name
					FROM customer
					INNER JOIN dog ON customer.id = owner_id
					WHERE dog.owner_id='1'
					
					Statement to find siblings, in this case Delegates of a Dog
					select d.first_name, d.last_name from delegate d join dog g on d.owner_id = g.owner_id where g.id = '136'
					 or
					select first_name, last_name from delegate join dog on delegate.owner_id = dog.owner_id where dog.id = '136'
					
				*/

//print(" Where STATEMENT --- ".$this->sWheresClause." ---- ");

			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $this->aColumns))."
				FROM $this->sTable 
				JOIN ".$this->sJoinTable." ON ".$this->sOnClause."
				WHERE ".$this->sWheresClause."
				$this->sOrderClause
				$this->sLimitClause
				";

//print(" THE STATEMENT --- ".$sQuery." ---- ");

				$sql = $this->parent_object->conn->prepare($sQuery);
				// Bind the PDO variables for the WHERE clause
				if (isset($this->dataArr['where'])) {
					foreach ($this->sWheres as $key => $value) {
						//print(" -w- key: " . $key . " value: " . $value . " ");
						$name = ':'.$key;
						//$name = ':136';
						//print(" -w- name: " . $name . " ");
						$sql->bindValue($name, $value, PDO::PARAM_STR);
					}
				} 
				if (isset($this->dataArr['range'])) {
					$sql->bindValue(':range', (int)$this->sRange, PDO::PARAM_INT);
					$sql->bindValue(':range_start', (int)$this->sRangeStart, PDO::PARAM_INT);
				}

			$sql->execute();
			
			$result = $sql->fetchAll(PDO::FETCH_ASSOC);
			$this->record = json_encode($result, true);
			return $this->record;
					
			} 	catch(PDOException $e) {
  				echo 'Error: ' . $e->getMessage();
			} 

		
	} // END function dbInsert()
} // END Class

?>