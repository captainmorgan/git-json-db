<?php

//require_once('../../../php-console-master/src/PhpConsole/__autoload.php');
//PhpConsole\Connector::getInstance();
//PhpConsole\Helper::register();

require_once('../dbcore.class.php');
require_once('../dbconfig.class.php');

session_start(); //must call session_start before using any $_SESSION variables
//PC::debug("Session fingerprint:".$_SESSION['fingerprint']);

// User is NOT logged in
if(!isLoggedIn()) {
	session_destroy();
	//PC::debug("You are not logged in");
    header('Location: ../../numpad_simple.html');
    exit;
}

// The user is logged in
else {
	//PC::debug("You are logged in");
	
	/*
	
	echo "I see that you are logged in. <br />";
	echo "IP: ".$_SESSION['ip']." <br />";
	echo "Time: ".$_SESSION['time']." <br />";
	echo "Username: ".$_SESSION['username']." <br />";
	echo "Fingerprint: ".$_SESSION['fingerprint']." <br />";
	
	
	print <<< END
	
	<p>I see that you are logged in. <br />
	IP: {$_SESSION['ip']} <br />
	Time: {$_SESSION['time']} <br />
	Username: {$_SESSION['username']} <br />
	Fingerprint: {$_SESSION['fingerprint']} <br />
	</p>
	
END;
// END must be at start of line

*/

	print <<< END
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Hairy & Merry Home</title>
<link rel="stylesheet" type="text/css" href="../../css/view.css">
<link rel="stylesheet" type="text/css" href="../../css/grid.css">
<link rel="stylesheet" href="../../css/jquery-mobile-themes/font-awesome-css/font-awesome.min.css">
<link rel="icon" type="image/png" href="../../img/favicon_redbowtie.ico">
<link rel="apple-touch-icon" href="../../img/ios-120x120.png" />
<script type="text/javascript" src="../../js/jquery-1.10.2.min.js"></script>

  <script>
    $(document).ready(function() {
    
        	$("#submit_button").click( function()
           {
           		// By default, Buttons within forms submit the form
           		// We don't want that here, so we prevent it
           		event.preventDefault();
				
				window.location.replace("./form.html");
           }); // End the Dog Next button function	
 
    });
  </script>

</head>
<body id="main_body" >
	
	<img id="top" src="../../img/top.png" alt="" style="width:800px;">
	<div id="sig_container">
	
	<h1><a>Index</a></h1>

	<div class="fauxform">
	
	<div class="form_description">
		<h2>Home</h2>
		<p>Please select one of the options below</p>
	</div>


<div class="section group">
	<div class="col span_1_of_2">
		<a href="../../requirements.html"><i class="fa fa-pencil-square-o fa-4x"></i>&nbsp;Register a new customer for Play-Care or Overnight Care</a><br /><br />
		<a href="../../datatables/datatable_builder.php?q=id,first_name,last_name,email,phone,zip&t=customer"><i class="fa fa-male fa-4x"></i>&nbsp;View all customers</a><br /><br />
		<a href="../../datatables/datatable_builder.php?q=id,dog_name,breed,gender,dog_notes,owner_id&t=dog"><i class="fa fa-heart fa-4x"></i>&nbsp;View all dogs</a><br /><br />
		<a href="../../datatables/datatable_builder.php?q=id,first_name,last_name,email,phone,owner_id&t=delegate"><i class="fa fa-users fa-4x"></i>&nbsp;View all delegates</a><br /><br />
	</div>
	<div class="col span_1_of_2">
		<a href="../../playcare_all.html"><i class="fa fa-mobile fa-5x"></i>&nbsp;Launch the Fetch App for Trainers</a><br /><br />
		<a href="http://www.hairyandmerry.com/forms/overnightcare/"><i class="fa fa-calendar-o fa-4x"></i>&nbsp;Launch the Overnight Care Scheduling App</a><br /><br />
		<a href="../../help.html"><i class="fa fa-question-circle fa-4x"></i>&nbsp;Get Help</a><br /><br />
		<a href="./logout.php"><i class="fa fa-power-off fa-4x"></i>&nbsp;Logout</a><br /><br />
	</div>
</div>


<!--
	<table cellspacing="2" cellpadding="2" border="0">
	<tr>

	<td><center><a href="../../requirements.html"><i class="fa fa-pencil-square-o fa-4x"></i></center></td><td>Register a new customer for Play-Care or Overnight Care</a></td>
	
	</tr>
	
	<tr>
	
	<td><center><a href="../../datatables/datatable_builder.php?q=id,first_name,last_name,email,phone,zip&t=customer"><i class="fa fa-male fa-4x"></i></center></td><td>View all customers</a></td>
	
	</tr>
	
	<tr>
	
	<td><center><a href="../../datatables/datatable_builder.php?q=id,dog_name,breed,gender,dog_notes,owner_id&t=dog"><i class="fa fa-heart fa-4x"></i></center></td><td>View all dogs</a></td>
	
	</tr>

	<tr>
	
	<td><center><a href="../../datatables/datatable_builder.php?q=id,first_name,last_name,email,phone,owner_id&t=delegate"><i class="fa fa-users fa-4x"></i></center></td><td>View all delegates</a></td>
	
	</tr>
		
	<tr>
	
	<td><center><a href="../../playcare_all.html"><i class="fa fa-mobile fa-5x"></i></center></td><td>Launch the Fetch App for Trainers</a></td>
	
	</tr>

	<tr>
	
	<td><center><a href="http://www.hairyandmerry.com/forms/overnightcare/"><i class="fa fa-calendar-o fa-4x"></i></center></td><td>Launch the Overnight Care Scheduling App</a></td>
	
	</tr>

	<tr>
	
	<td><center><a href="../../help.html"><i class="fa fa-question-circle fa-4x"></i></center></td><td>Get Help</a></td>
	
	</tr>

	<tr>
	
	<td><center><a href="./logout.php"><i class="fa fa-power-off fa-4x"></i></center></td><td>Logout</a></td>
	
	
	</tr>
	
	</table>
//-->	
	</div>
	</div>
	<img id="bottom" src="../../img/bottom.png" alt="" style="width:800px;">
	

	
</body>
</html>
END;
	
	/* ----------------------------------------------------- */
	
	
}

function isLoggedIn() {
	if ($_SESSION['fingerprint'] != md5($_SERVER['HTTP_USER_AGENT'] . "Ele the Dog" . $_SERVER['REMOTE_ADDR'])) {       
    	//session_destroy();
    	//header('Location: ../login.html');
    	//PC::debug("isLoggedIn returning false");
    	return false;  
	}
	else {
		//PC::debug("Time: ".time()." Session Time: ".$_SESSION['time']);
		// If user has been logged in over a day
		// 100		- 99	= 1
		// 101		- 99	= 2
		// 199		- 99	= 100
	    if (time() - $_SESSION['time'] >= 360*24 ) {
	    	//PC::debug("isLoggedIn timeout");
   			return false;
   			}
		//PC::debug("isLoggedIn returning true");
		return true;
	}
}


?>
