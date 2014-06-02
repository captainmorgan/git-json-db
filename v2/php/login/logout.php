<?php

//require_once('PhpConsole.php');
//PhpConsole::start(true, true, dirname(__FILE__));
require_once('../smartfield.class.php');

session_start(); //must call session_start before using any $_SESSION variables

try {
    $_SESSION = array(); //destroy all of the session variables
    session_destroy();
    //echo "Logged out";
    header('Location: ../../numpad_simple.html');

	} catch(Exception $e) {
  		echo 'Error: ' . $e->getMessage();
	}
	
	exit();

?>
