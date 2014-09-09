<?php
require_once('../dbcore.class.php');


DBConfig::write('db.user', 'captain_file');
DBConfig::write('db.password', '11sumrall11');
    
         
$sql_user       = DBConfig::read('db.user');
$sql_pass 	    = DBConfig::read('db.password');
$sql_db         = DBConfig::read('db.basename');
$sql_host       = DBConfig::read('db.host');       
    
// Connect to the database
// $mysqli = new mysqli('localhost', 'my_user', 'my_password', 'my_db');
//$dbLink = new mysqli('127.0.0.1', 'signature', 'signature', 'jquery');
$dbLink = new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);


// Connect to the database
$dbLink = new mysqli('127.0.0.1', 'signature', 'signature', 'jquery');
if(mysqli_connect_errno()) {
    die("MySQL connection failed: ". mysqli_connect_error());
}
 
// Query for a list of all existing files
$sql = 'SELECT `id`, `name`, `mime`, `size`, `created` FROM `photo`';
$result = $dbLink->query($sql);
 
// Check if it was successfull
if($result) {
    // Make sure there are some files in there
    if($result->num_rows == 0) {
        echo '<p>There are no files in the database</p>';
    }
    else {
        // Print the top of a table
        echo '<table width="100%">
                <tr>
                    <td><b>Name</b></td>
                    <td><b>Mime</b></td>
                    <td><b>Size (bytes)</b></td>
                    <td><b>Created</b></td>
                    <td><b>Download</b></td>
                    <td><b>Thumbnail</b></td>
                </tr>';
 
        // Print each file
        while($row = $result->fetch_assoc()) {
            echo "
                <tr>
                    <td>{$row['name']}</td>
                    <td>{$row['mime']}</td>
                    <td>{$row['size']}</td>
                    <td>{$row['created']}</td>
                    <td><a href='get_photo.php?id={$row['id']}'>Download</a></td>
                    <td><a href='get_photo.php?id={$row['id']}'><img src='get_photo.php?id={$row['id']}' width=25 height=25 border=0 /></a></td>
                </tr>";
        }
 
        // Close table
        echo '</table>';
    }
 
    // Free the result
    $result->free();
}
else
{
    echo 'Error! SQL query failed:';
    echo "<pre>{$dbLink->error}</pre>";
}
 
// Close the mysql connection
$dbLink->close();
?>