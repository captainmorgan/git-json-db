<?php
//require_once('../../../../fetch2/php/PhpConsole/__autoload.php');
//PhpConsole\Connector::getInstance();
//PhpConsole\Helper::register();

require_once('../dbcore.class.php');

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */


// Collect the variables we passed from 'datatable_builder.php'
// 'q' holds the comma-separated lists of fields to display
// 't' holds the table or view name
$q = $_GET["q"];
$t = $_GET["t"];

// Build array from the comma-separated list
$columnArray = explode(",",$q);

// DB table to use
$table = $t;

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
/*$columns = array(
	array( 'db' => 'first_name', 'dt' => 0 ),
	array( 'db' => 'last_name',  'dt' => 1 ),
	array( 'db' => 'dog_name',   'dt' => 2 ),
	array( 'db' => 'phone',     'dt' => 3 ),
	array( 'db' => 'email',     'dt' => 4 ),
	array( 'db' => 'id',     	'dt' => 5 )
	
);*/

$columns = array();

// The original author stores the columns as associative arrays with an array
// Here we loop through the provided columns and add them to an array
foreach ($columnArray as $key => $value) {
	array_push($columns, array( 'db' => $value, 'dt' => $key ));
}

// SQL server connection information
/*
$sql_details = array(
	'user' => 'signature',
	'pass' => 'signature',
	'db'   => 'jquery',
	'host' => '127.0.0.1'
);
*/

// Requires the dbcore.class.php, where we store the database connection information
// Database connection information
$sql_details['user']       = DBConfig::read('db.user');
$sql_details['pass'] 	   = DBConfig::read('db.password');
$sql_details['db']         = DBConfig::read('db.basename');
$sql_details['host']       = DBConfig::read('db.host');


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);


