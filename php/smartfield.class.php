<?php

require_once('PhpConsole.php');
PhpConsole::start(true, true, dirname(__FILE__));

/*
	Class to manage Regex checks on input fields
	This is to prevent SQL Injection attacks when POST values are received.
	Usage Example:

	$username = new SmartField($_POST['username']);
	if ($username->isUsernameFriendly()) {
		$username = (string)$username->set();            <--- $username is 
	}
	else {
		unset($username);
	}
	
	
*/


Class SmartField {

	protected $validatedData;
	public $inputData;
	
	// Constructor
	// Object instantiation expects an input parameter of something to validate
	function SmartField ($data) {
		
		$this->inputData = $data;
	}

    public function __destruct()
    {
        unset($this->inputData);
        unset($this->validatedData);
        debug("Called SmartField Destructor");
    }
	
	// Do a validation against SQL Injection.  We allow alphanumeric, @, -, _ (No .)
	public function isUsernameFriendly () {
	
		if(preg_match('/[^a-z_\-0-9\(@)(-)(_)]/i', $this->inputData)) {
			debug("Data was provided, but was not alphanumeric");
			return false;
		}
		else {
			$this->validatedData = $this->inputData;
			return true;
		}
	
	} // End isAlphanumeric
	
	// Allows 0-9 only
	public function isNumbers () {
	
		if(preg_match('/^[0-9]+$/', $this->inputData)) {
			debug("A valid number");
			$this->validatedData = $this->inputData;
			return true;
		}
		else {
			debug("Not a number");
			return false;
		}
	}

	// Valid Email Address
	public function isEmail () {
	
		if(preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $this->inputData)) {
			debug("A valid email");
			$this->validatedData = $this->inputData;
			return true;
		}
		else {
			debug("Not an email");
			return false;
		}
	}

	// Function to set the validated value to the original POST variable
	// Requires $validatedData to be set, which it won't be unless a check has been run
	public function set() {
	
		if (isset($this->validatedData)) {
			return $this->validatedData;
		}
		else {
			debug("Nothing to set");
			}
	}

}


?>
