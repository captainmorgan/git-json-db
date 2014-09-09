<?php

/*
	Checks whether a username has been previously registered.
	Given a POST of a username, this queries the database.
	Returns a 1 (cast as an int!) if the username is taken.
	Courtesy: http://papermashup.com/jquery-php-mysql-username-availability-checker/
	
	Usage example (from form.html):
	
		$('#email').keyup(unique_field_check);
  	
	
	    // Unique Field Validator
    	// Makes sure that an email has not already been registered and displays
    	// a visual cue.  Hides the Customer Submit button (can't submit with that error)
    	// This fires on keyUp, so autoform completion won't trigger it
    	function unique_field_check() {
    		var email = $('#email').val();
    		if (email == '' || email.length < 11) {
        		$('#email').css('border', '3px #CCC solid');
        		$('#tick').hide();
    		}
    		// It doesn't hit the database until at least 11 characters are entered in the field
    		// An email address has: @.com = 5 chars, domain = 3 chars, user = 3 chars at least    		
    		else {
        		jQuery.ajax({
					type: 'POST',
           	 		url: './php/check_unique_field.php',
           			data: 'email=' + email,
        			cache: false,
        			success: function (response) {
        				console.log("Response: " + response);
               		 	if (response == 1) {
                		// This email already exists in the database
                  		  	$('#email').css('border', '3px #C33 solid');
                    		$('#tick').hide();
                    		$('#cross').fadeIn();
                    		$('#submit_customer').hide();
                		}    		
                		else {
                		// This is a new, valid email
                    		$('#email').css('border', '3px #090 solid');
                    		$('#cross').hide();
                    		$('#tick').fadeIn();
                    		$('#submit_customer').show();
                }}});
    		} // End else block
		} // End function unique_field_check
	
*/

//require_once('PhpConsole.php');
//PhpConsole::start(true, true, dirname(__FILE__));
require_once('smartfield.class.php');
require_once('dbcore.class.php');
require_once('dbconfig.class.php');

// Initialize a connection to the database
//DBConfig::write('db.basename', 'jquery');
//DBConfig::write('db.user', 'signature');
//DBConfig::write('db.password', 'signature');

DBConfig::write('db.user', 'captain_read');
DBConfig::write('db.password', '11sumrall11');

$table = 'customer';
$uniqueField = 'email';

// Grab the username being typed in progress
$email = trim(strtolower($_POST['email']));
$email = mysql_real_escape_string($email);


	// Do a validation against SQL Injection.  We allow alphanumeric, @, -, _ (No .)
	$field = new SmartField($email);
	if (!$field->isEmail()) {
		echo 1;
	}
	else {

// Query the database for this username.  Return an integer 1 if is found
try {
	$core = DBCore::getInstance();

	$sth = $core->conn->prepare("SELECT ".$uniqueField." FROM ". $table ." WHERE ".$uniqueField." = :email LIMIT 1");
	$sth->execute(array(':email' => $email));
  	$num = $sth->fetch();
	echo (int)$num;
	} catch(PDOException $e) {
  		echo 'Error: ' . $e->getMessage();
	}

}

// Apparently, it is best practice to not use the end php tag...