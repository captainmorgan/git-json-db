<?php

//require_once('../../../php-console-master/src/PhpConsole/__autoload.php');
//PhpConsole\Connector::getInstance();
//PhpConsole\Helper::register();

require_once('../smartfield.class.php');
require_once('../dbcore.class.php');
require_once('../dbconfig.class.php');

session_start(); //must call session_start before using any $_SESSION variables

//$username = $_POST['company'];
//$password = $_POST['numberkey'];

// Retrieve our data from POST and validate using Regex
unset($username);
$username = new SmartField(trim($_POST['company']));
if ($username->isUsernameFriendly()) {
	$username = (string)$username->set();
	//PC::debug("Sent back username: ".$username);
}
else {
	unset($username);
	//PC::debug("A username was provided, but was not alphanumeric");
	//header('Location: ../register.html');
	exit();
}

unset($password);
$password = new SmartField(trim($_POST['numberkey']));

if ($password->isNumbers()) {
	$password = (string)$password->set();
	//PC::debug("Valid numberkey: ".$password);
}
else {
	unset($password);
	//PC::debug("A pin was provided, but was not numeric");
	session_destroy();
	//header('Location: ../login.html');
	echo "Wrong username or password.";
	exit();
}


if (isValidLogin($username, $password)) {
	//PC::debug("Valid login");
	header('Location: ./membersonly_simple.php');
}
else {
	//PC::debug("Not a valid login");
	//header('Location: ../../numpad_simple.html');
	//echo "<h1>Wrong username or password.";
	
	print <<< END
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<title>Access Denied</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.5, maximum-scale=1.0">
			<meta http-equiv="refresh" content="4; url=../../index.html" />
			</head>
			<body>
				<br /><br />
				<h1><center>Incorrect PIN</center></h1>
				<h2><center><a href="../../index.html">Click to try again</a></center></h2>
			</body>
			</html>
END;
	
	exit;
}


function setUserSession($u)
{
	// The User Session is a cookie that gets stored by the user's browser
	// It includes data for this session and is what "logs in" the user
    session_regenerate_id (); //this is a security measure
    $_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'] . "Ele the Dog" . $_SERVER['REMOTE_ADDR']);
	//PC::debug("Session fingerprint:".$_SESSION['fingerprint']);
    //$_SESSION['valid'] = 1;
    $_SESSION['username'] = $u['username'];
    $_SESSION['companyid'] = $u['id'];
    $_SESSION['db'] = $u['username'];
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['time'] =  time();
}

function isLoggedIn()
{
	// Need to update 'valid' to 'fingerprint' and test
    if(isset($_SESSION['valid']) && $_SESSION['valid'])
        return true;
    return false;
}


function logout()
{
    $_SESSION = array(); //destroy all of the session variables
    session_destroy();
}

// Function returns TRUE if this is a valid login
function isValidLogin($username, $password) {

//PC:debug("hello");

	$table = 'sys_company';

	try {
	
	DBConfig::write('db.host', '127.0.0.1');
	DBConfig::write('db.port', '3306');
	DBConfig::write('db.basename', 'main');
	DBConfig::write('db.user', 'captain_main');
	DBConfig::write('db.password', '12sumrall12');
	
		$core = DBCore::getInstance();
			
		$sth = $core->conn->prepare("SELECT password, salt, username, id FROM ". $table ." WHERE username = :username");
		$sth->execute(array(':username' => $username));
  		$result = $sth->fetch(PDO::FETCH_BOTH);
	
	} catch(PDOException $e) {
  		echo 'Error: ' . $e->getMessage();
  		return false;
	}
  	
  	//no such user exists
  	if (empty($result)) {
  	    //header('Location: ../register.html');
    	//exit;
    	return false;
  	}
  	
	$userData = $result;
	$hash = hash('sha256', $userData['salt'] . hash('sha256', $password) );

	if($hash != $userData['password']) //incorrect password
	{
		//echo "Wrong username or password.";
    	//header('Location: ../register.html');
    	//exit;
    	return false;
	}
	else
	{
    	setUserSession($userData); //sets the session data for this user
    
    	return true;
    	/*
    	echo "You have successfully logged in. <br />";
		echo "<a href=\"membersonly.php\">Members Only</a> <br /><br />";
		echo "<a href=\"logout.php\">Logout</a>";
		*/
		
	}

} // End function isValidLogin



?>
