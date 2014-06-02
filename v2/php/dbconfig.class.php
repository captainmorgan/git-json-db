<?php

// Courtesy: http://stackoverflow.com/questions/2047264/use-of-pdo-in-classes


//session_start(); //must call session_start before using any $_SESSION variables

// Default DB Connection Parameters
// The 'main' DB handles access, we access it by default.

// Dev Values
DBConfig::write('db.host', '127.0.0.1');
DBConfig::write('db.port', '3306');
DBConfig::write('db.basename', 'jquery');
DBConfig::write('db.user', 'signature');
DBConfig::write('db.password', 'signature');

// Production Values
//DBConfig::write('db.host', '127.0.0.1');
//DBConfig::write('db.port', '3306');
//DBConfig::write('db.basename', 'fetchv1');
//DBConfig::write('db.user', 'captain');
//DBConfig::write('db.password', '11sumrall11');

// Database Users
// captain, 11sumrall11
// captain_read, 11sumrall11
// captain_update, 11sumrall11
// captain_write, 11sumrall11
// captain_hotel, 

class DBConfig
{
    static $confArray;

    public static function read($name)
    {//debug('in read');
        return self::$confArray[$name];
    }

    public static function write($name, $value)
    {//debug('in write');
        self::$confArray[$name] = $value;
    }

}


?>
