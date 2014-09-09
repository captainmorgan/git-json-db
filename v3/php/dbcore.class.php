<?php

// Courtesy: http://stackoverflow.com/questions/2047264/use-of-pdo-in-classes
require_once('dbconfig.class.php');

class DBCore
{
    public $conn; // handle the db connection
    private static $instance;

    private function __construct()
    {
        // Get db parametes from the DBConfig Class
        $dsn = 'mysql:host=' . DBConfig::read('db.host') .
               ';dbname='    . DBConfig::read('db.basename') .
               ';port='      . DBConfig::read('db.port') .
               ';connect_timeout=15';             
        $user = DBConfig::read('db.user');           
        //$password = DBConfig::read('db.password');
        $password = $this->pwtest($user);
        //PC::debug($password);

        $this->conn = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // Courtesy: http://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php
    }
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }


	// Experimental
	
    public function pwtest($captain) {

$mysql_host 	= 	DBConfig::read('db.host');
$mysql_user 	= 	'signature';
$mysql_pass 	= 	'signature';
$mysql_db 		= 	'jquery';

/*
		$mysql_host 	= 	'localhost';
		$mysql_user 	= 	'signature';
		$mysql_pass 	= 	'signature';
		$mysql_db 		= 	'jquery';
*/

/*
		$mysql_host 	= 	DBConfig::read('db.host');
		$mysql_user 	= 	DBConfig::read('db.user');
		$mysql_pass 	= 	DBConfig::read('db.password');
		$mysql_db 		= 	DBConfig::read('db.basename');
*/

		try {
    		$conn = new PDO("mysql:host=$mysql_host;dbname=$mysql_db", $mysql_user, $mysql_pass);
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		// Make sure we are talking to the database in UTF-8
   			$conn->exec('SET NAMES utf8');
			# echo "It worked.";
		} catch(PDOException $e) {
   			echo 'ERROR: ' . $e->getMessage();
		}
		$sQuery = "
				SELECT `password` FROM sys_captain where `username` = '" . $captain . "'";			
		$sql = $conn->prepare($sQuery);		
		$sql->execute();
		$result = $sql->fetchAll(PDO::FETCH_ASSOC);
		//echo $result[0]['password'];
		// Keep it silent
		return $result[0]['password'];
    }
    
}

?>
