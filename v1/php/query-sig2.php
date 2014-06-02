<?php

// Returns a JSON Object of coordinates.  Those coordinates are the stored value of a signature.
// The database connection is initialized in 'init.php'

require_once('../../php-console-master/src/PhpConsole/__autoload.php');
PhpConsole\Connector::getInstance();
PhpConsole\Helper::register();
require_once('dbcore.class.php');

PC::debug("alan jackson");

//
//$mysql_host 	= 	'127.0.0.1';
//$mysql_db 		= 	'fetchv1';
//$mysql_user 	= 	'captain';
//$mysql_pass 	= 	'11sumrall11';
//$mysql_host 	= 	'127.0.0.1';
//$mysql_db 		= 	'jquery';
//$mysql_user 	= 	'signature';
//$mysql_pass 	= 	'signature';

    	DBConfig::write('db.user', 'captain_read');
    	DBConfig::write('db.password', '11sumrall11');  
		connectDB();


	// Get the Signature PK from 
	$sid = $_POST['id'];
	PC::debug("sid: " . $sid);

/*

	DBConfig::write('db.host', '127.0.0.1');
	DBConfig::write('db.port', '3306');
	DBConfig::write('db.basename', 'main');
	DBConfig::write('db.user', 'captain_main');
	DBConfig::write('db.password', '12sumrall12');
	
		$core = DBCore::getInstance();
			
		$sth = $core->conn->prepare("SELECT password, salt, username, id FROM ". $table ." WHERE username = :username");
		$sth->execute(array(':username' => $username));
  		$result = $sth->fetch(PDO::FETCH_BOTH);

*/


try {
PC::debug("here");
	$data = $conn->query("SELECT signature FROM signatures WHERE id= " . $conn->quote(($sid)));
	$conn->exec('SET NAMES utf8');
	PC::debug("Data: " . $data);
 
	while($row = $data->fetch(PDO::FETCH_BOTH)) {
    	//print_r($row);
    	PC::debug($row);
    	echo $row[0];
	}

}
catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    PC::debug("Error in catch");
}
 
 

	function connectDB () {
 	
 		try {
 			$db = DBCore::getInstance();
 			$db->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$db->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->conn->exec('SET NAMES utf8');
			$conn = $db->conn;
			PC::debug("Successfully connected to database.");
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
   			PC::debug("Unsuccessful in connecting to database...");
   			exit();
		} 			
 	
 	} 
 
 
?>
