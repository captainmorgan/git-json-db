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
        $password = DBConfig::read('db.password');

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

    // other global functions
}

?>
