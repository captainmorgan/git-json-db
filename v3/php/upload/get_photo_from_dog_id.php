<?php

require_once('../PhpConsole/__autoload.php');
PhpConsole\Connector::getInstance();
PhpConsole\Helper::register();


require_once('../dbcore.class.php');

session_start();

// Make sure an ID was passed
if(isset($_GET['id'])) {
// Get the ID
    $id = intval($_GET['id']);

    // Make sure the ID is in fact a valid ID
    if($id <= 0) {
        die('The ID is invalid!');
    }
    else {

DBConfig::write('db.user', 'captain_file');
DBConfig::write('db.password', '11sumrall11');
// ideal use case:
// setDBConnection(captain_file); -> returns password
    

         
$sql_user       = DBConfig::read('db.user');
$sql_pass 	    = DBConfig::read('db.password');
$sql_db         = DBConfig::read('db.basename');
$sql_host       = DBConfig::read('db.host');       
    
// Connect to the database
$dbLink = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
        if(mysqli_connect_errno()) {
            die("MySQL connection failed: ". mysqli_connect_error());
        }
 
 
 
 
        // Fetch the file information
        $query = "
            SELECT `mime`, `name`, `size`, `data`
            FROM `photo`
            WHERE `linked_table` = 'dog' AND `linked_id` = {$id}";
        $result = $dbLink->query($query);
 
        if($result) {
            // Make sure the result is valid
            if($result->num_rows >= 1) {
            // Get the row
            $row = mysqli_fetch_assoc($result);



                // Print headers
                header("Content-Type: ". $row['mime']);
                header("Content-Length: ". $row['size']);
                //header("Content-Disposition: attachment; filename=". $row['name']);
 
                // Print data
                echo $row['data'];
  
  /*              
while($row = mysqli_fetch_assoc($result))
{
   echo $row['data'];
}
*/                
              
                
            }
            else {
                //No results -- show the default image
                
                $query = "
            		SELECT `mime`, `name`, `size`, `data`
            		FROM `photo`
            		WHERE `id` = 1";
        		$result = $dbLink->query($query);

				if($result) {
				
                	$row = mysqli_fetch_assoc($result);
 
                	// Print headers
                	header("Content-Type: ". $row['mime']);
                	header("Content-Length: ". $row['size']);

                	// Print data
                	echo $row['data'];
                	PC::debug("row" . $row['data']);
				
				}
                
                
            }
 
            // Free the mysqli resources
            @mysqli_free_result($result);
        }
        else {
            echo "Error! Query failed: <pre>{$dbLink->error}</pre>";
        }
        @mysqli_close($dbLink);
    }
}
else {
    echo 'Error! No ID was passed.';
}
?>