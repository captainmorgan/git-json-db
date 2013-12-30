<?php

// Returns a JSON Object of coordinates.  Those coordinates are the stored value of a signature.
// The database connection is initialized in 'init.php'

include 'init.php';

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
