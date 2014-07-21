<?php

// Describe Schema.  Returns the schema (table and columns) for the API to digest.

require_once('../../php-console-master/src/PhpConsole/__autoload.php');
PhpConsole\Connector::getInstance();
PhpConsole\Helper::register();

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

PC::debug("hi");

//$sth = $conn->prepare("SELECT id, first_name, phone FROM customer");
$sth = $conn->prepare("SHOW COLUMNS FROM customer");
$sth->execute();

print("PDO::FETCH_BOTH  <br />");
print("Return next row as an array indexed by both column name and number  <br />");
//$result = $sth->fetch(PDO::FETCH_BOTH);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);
print_r($result);
print(" <br />  <br />");


$r = '';
$c = Array();

	foreach ($result as $key => $value) {
		$r .= $result[$key]['Field'] . ', ';
		$c[$key] = $result[$key]['Field'];
	}

$r = substr($r, 0, -2);
echo "String: " . $r . " -- ";
print(" <br /> <br />");

$t = json_encode($c, true);
echo " JSON: " . $t . " -- ";
echo " with slashes " . addslashes($t) . " -- ";

//$t = substr($t, 1);
//$t = "\"fields\":" . $t;
//echo " new t: " . $t;

$h = "{\"table\":\"customertest\",\"fields\":" . $t . "}";
echo " h: " . addslashes($h);

// Goal:
// protected $schemaLabel_customertest = 
//"{\"table\":\"customertest\",\"fields\":[\"first_name\",\"last_name\",\"phone\",\"dob\"]}";
//"{\"table\":\"customertest\",\"fields\":[\"first_name\",\"last_name\",\"phone\",\"dob\"]}"
// [\"id\",\"salutation\",\"first_name\",\"last_name\",\"phone\",\"email\",\"marketing_email\",\"address_street\",\"address2\",\"city\",\"state\",\"zip\",\"country\",\"company\",\"work_phone\",\"work_street1\",\"work_street2\",\"work_city\",\"work_state\",\"work_zip\",\"work_country\",\"email_pref\",\"referral\",\"dob\",\"created\"] --
?>
