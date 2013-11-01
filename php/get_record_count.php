<?php
/*
	Class to both store and return the count of the records as an integer
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
	}									<-- (optional)
	
	The integer is returned and also stored as $this->count
	
	
*/

require_once('PhpConsole.php');
PhpConsole::start(true, true, dirname(__FILE__));
require_once('get_record.php');

class GetRecordCount extends GetRecord {
	
	// The number of returned records
	// "count(*):
	// This is what dBCount returns	
	public $count;


	public function dBCount($s) {

		// Check to make sure the JSON we received is valid
		if (!$this->setQueryParamsFromData($s)) {
			echo "Error.  The JSON String received was not valid, or was not in the correct format.";
			return false;
		}
		else {
			$this->setQueryParamsFromData($s);
		}
	
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

//$sOrderClause = "`first_name`, `last_name`, `id`";

		try {	

			$sQuery = "
				SELECT count(".str_replace(" , ", " ", implode("`, `", $this->aColumns)).")
				FROM $this->sTable 
				WHERE ".$this->sWheresClause."
				$this->sOrderClause
				$this->sLimitClause
				";

			
			//print(" THE STATEMENT --- ".$sQuery." ---- ");
		
			$sql = $conn->prepare($sQuery);
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
			
			$result = $sql->fetch(PDO::FETCH_BOTH);
			//$this->count = json_encode($result, true);
			//print_r($result);
			$this->count = (int)$result[0];
			return $this->count;
					
			} 	catch(PDOException $e) {
  				echo 'Error: ' . $e->getMessage();
			} 

		
	} // END function dbInsert()

} // END Class

?>