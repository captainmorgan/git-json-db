<?php

// Returns a JSON Object of coordinates.  Those coordinates are the stored value of a signature.
// The database connection is initialized in 'init.php'

require_once('./dbcore.class.php');
require_once('./desc_schema.php');

//
//$mysql_host 	= 	'127.0.0.1';
//$mysql_db 		= 	'fetchv1';
//$mysql_user 	= 	'captain';
//$mysql_pass 	= 	'11sumrall11';
$mysql_host 	= 	'127.0.0.1';
$mysql_db 		= 	'jquery';
$mysql_user 	= 	'signature';
$mysql_pass 	= 	'signature';

try {
    $conn = new PDO("mysql:host=$mysql_host;dbname=$mysql_db", $mysql_user, $mysql_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Make sure we are talking to the database in UTF-8
    $conn->exec('SET NAMES utf8');
	# echo "It worked.";
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
//

	// Get the Signature PK from 
	$sid = $_POST['id'];

try {

	$data = $conn->query("SELECT signature FROM signatures WHERE id= " . $conn->quote(($sid)));
 
	while($row = $data->fetch(PDO::FETCH_BOTH)) {
    	//print_r($row);
    	echo $row[0];
	}

} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
 
?>
