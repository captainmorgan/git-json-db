<?php

// Uses PDO to save the customer signature into the database
// The signature is stored as a JSON Object of a series of coordinates
// (Or a compressed version of a JSON Object)
// The database connection is initialized in 'init.php'

session_start();

require_once('PhpConsole.php');
PhpConsole::start(true, true, dirname(__FILE__));
require_once('dbcore.class.php');
require_once('dbconfig.class.php');

DBConfig::write('db.basename', 'jquery');
DBConfig::write('db.user', 'signature');
DBConfig::write('db.password', 'signature');

// Tracks what fields have validation errors
#$errors = array();

	//$name = $_POST['name'];
	//$sig = $_POST['sig'];
	$name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
	$sig = filter_input(INPUT_POST, 'sig', FILTER_UNSAFE_RAW);
	$cid = $_SESSION['cid'];
	debug("CID from Session: ".$cid);
	
	if (empty($name)) {
		$name = "Blank";
		#$errors['name'] = true;
	}

	if (empty($sig)) {
		// The form handles validation, so if we see this string, something else is wrong
		$sig = "[{\"lx\":0,\"ly\":0,\"mx\":0,\"my\":0},{\"lx\":0,\"ly\":0,\"mx\":0,\"my\":0}]";
	}

	if (empty($cid)) {
		$cid = "Blank";
	}

    // Create some other pieces of information about the user
    //  to confirm the legitimacy of their signature
    $sig_hash = sha1($sig);
    $created = time();
    // We are also using UTC_TIMESTAMP to record the time in a readable format and in the user's timezone (not the server's)
    $ip = $_SERVER['REMOTE_ADDR'];

	try {

// FYI, this chunk of code has been automated in the API experiments
		$core = DBCore::getInstance();
    	
		$sql = $core->conn->prepare('
      	INSERT INTO signatures (customer_id, signator, signature, sig_hash, ip, created, createddt)
     	 VALUES (:customer_id, :signator, :signature, :sig_hash, :ip, :created, UTC_TIMESTAMP())
    		');
    		$sql->bindValue(':customer_id', $cid, PDO::PARAM_STR);
    		$sql->bindValue(':signator', $name, PDO::PARAM_STR);
   		 	$sql->bindValue(':signature', $sig, PDO::PARAM_STR);
    		$sql->bindValue(':sig_hash', $sig_hash, PDO::PARAM_STR);
    		$sql->bindValue(':ip', $ip, PDO::PARAM_STR);
    		$sql->bindValue(':created', $created, PDO::PARAM_INT);
    		$sql->execute();
	
		echo "Customer saved.";
		} catch(PDOException $e) {
  		echo 'Error: ' . $e->getMessage();
		}
	
?>

