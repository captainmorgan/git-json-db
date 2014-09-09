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
if(isset($_FILES['uploaded_file'])) {
    // Make sure the file was sent without errors
    if($_FILES['uploaded_file']['error'] == 0) {

PC::debug("Dog ID associated with photo: " . ($_POST['dog_lookup_id']));

DBConfig::write('db.user', 'captain_file');
//DBConfig::write('db.password', '11sumrall11');
    
         
$sql_user       = DBConfig::read('db.user');
$sql_pass 	    = DBConfig::read('db.password');
$sql_db         = DBConfig::read('db.basename');
$sql_host       = DBConfig::read('db.host');       
    
    
    
        // Connect to the database
        // $mysqli = new mysqli('localhost', 'my_user', 'my_password', 'my_db');
        //$dbLink = new mysqli('127.0.0.1', 'signature', 'signature', 'jquery');
        $dbLink = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
        
        if(mysqli_connect_errno()) {
            die("MySQL connection failed: ". mysqli_connect_error());
        }
 
        // Gather all required data
        $name = $dbLink->real_escape_string($_FILES['uploaded_file']['name']);
        $mime = $dbLink->real_escape_string($_FILES['uploaded_file']['type']);
        $data = $dbLink->real_escape_string(file_get_contents($_FILES  ['uploaded_file']['tmp_name']));
        $size = intval($_FILES['uploaded_file']['size']);
 
 
        // Create the SQL query
        $query = "
            INSERT INTO `file` (
                `name`, `mime`, `size`, `data`, `linked_table`, `linked_id`, `created`
            )
            VALUES (
                '{$name}', '{$mime}', {$size}, '{$data}', '', '', NOW()
            )";
 
        // Execute the query
        $result = $dbLink->query($query);
        echo $dbLink->insert_id;
        PC::debug("Row: " . $dbLink->insert_id);
        //PC::debug("last row: " . $dbLink->insert_id);
        
        // test
        unset($_FILES);
 
        // Check if it was successfull
        if($result) {
            echo 'Success! Your file was successfully added!';
        }
        else {
            echo 'Error! Failed to insert the file'
               . "<pre>{$dbLink->error}</pre>";
        }
    }
    else {
        echo 'An error accured while the file was being uploaded. '
           . 'Error code: '. intval($_FILES['uploaded_file']['error']);
    }
 
    // Close the mysql connection
    $dbLink->close();
}
else {
    echo 'Error! A file was not sent!';
}
 
// Echo a link back to the main page
echo '<p>Click <a href="index.html">here</a> to go back</p>';
?>
 
 