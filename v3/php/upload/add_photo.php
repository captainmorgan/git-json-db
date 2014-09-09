<?php

require_once('../PhpConsole/__autoload.php');
PhpConsole\Connector::getInstance();
PhpConsole\Helper::register();

require_once('../dbcore.class.php');

session_start();

/*

How can we utilize the existing API to upload files as blobs to the database?

*/

// Check if a file has been uploaded
if(isset($_FILES['uploaded_photo'])) {
    // Make sure the file was sent without errors
    if($_FILES['uploaded_photo']['error'] == 0) {

DBConfig::write('db.user', 'captain_file');
PC::debug("mister anderson");

 		try {
 		
 			$db = DBCore::getInstance();
 			$db->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$db->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->conn->exec('SET NAMES utf8');
			$conn = $db->conn;
			//PC::debug("Successfully connected to database.");
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
   			//PC::debug("Unsuccessful in connecting to database...");
   			exit();
		} 


       
//$sql_user       = DBConfig::read('db.user');
//$sql_pass 	    = DBConfig::read('db.password');
$sql_db         = DBConfig::read('db.basename');
$sql_host       = DBConfig::read('db.host');       

    
/*    
        // Connect to the database
        // $mysqli = new mysqli('localhost', 'my_user', 'my_password', 'my_db');
        //$dbLink = new mysqli('127.0.0.1', 'signature', 'signature', 'jquery');
        $dbLink = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
        
        if(mysqli_connect_errno()) {
            die("MySQL connection failed: ". mysqli_connect_error());
        }
*/ 

        // Gather all required data
        $name = mysql_real_escape_string($_FILES['uploaded_photo']['name']);
        $mime = mysql_real_escape_string($_FILES['uploaded_photo']['type']);
        $size = intval($_FILES['uploaded_photo']['size']);
 		$data = mysql_real_escape_string(file_get_contents($_FILES['uploaded_photo']['tmp_name']));
 		
 		//echo "the data: ";
 		//echo $data;
 /*
        // Gather all required data
        $name = $dbLink->real_escape_string($_FILES['uploaded_photo']['name']);
        $mime = $dbLink->real_escape_string($_FILES['uploaded_photo']['type']);
        $data = $dbLink->real_escape_string(file_get_contents($_FILES  ['uploaded_photo']['tmp_name']));
        $size = intval($_FILES['uploaded_photo']['size']);
 */
 
 		/*
        // Create the SQL query
        $query = "
            INSERT INTO `photo` (
                `name`, `mime`, `size`, `data`, `linked_table`, `linked_id`, `created`
            )
            VALUES (
                '{$name}', '{$mime}', {$size}, '{$data}', '', '', NOW()
            )";
            */


        $query = "
            INSERT INTO `photo` (
                `name`, `mime`, `size`, `data`, `linked_table`, `linked_id`, `created`
            )
            VALUES (
                '{$name}', '{$mime}', {$size}, '{$data}', '', '', NOW()
            )";            
            
/* 
        // Execute the query
        $result = $dbLink->query($query);
        echo $dbLink->insert_id;
        PC::debug("Row: " . $dbLink->insert_id);
*/
        // Execute the query
        $result = $conn->query($query);
        echo $conn->lastInsertId();
        
        
        // test
        unset($_FILES);
 
        // Check if it was successfull
        if($result) {
            echo 'Success! Your photo was successfully added!';
        }
        else {
            echo 'Error! Failed to insert the photo';
               //. "<pre>{$dbLink->error}</pre>";
        }
    }
    else {
        echo 'An error accured while the file was being uploaded. '
           . 'Error code: '. intval($_FILES['uploaded_photo']['error']);
    }
 
    // Close the mysql connection
    //$dbLink->close();
}
else {
    echo 'Error! A photo was not sent!';
}
 
// Echo a link back to the main page
echo '<p>Click <a href="index.html">here</a> to go back</p>';
?>
 
 