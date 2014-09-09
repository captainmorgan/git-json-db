<?php

//require_once('../../php-console-master/src/PhpConsole/__autoload.php');
//PhpConsole\Connector::getInstance();
//PhpConsole\Helper::register();

/*
Datatable_builder.php allows the dynamic rendering of database data into a DataTable object.
You pass the file two parameters; a comma-separated list of the fields you want displayed and 
the table name.  (NOTE: This should eventually be changed to accept a standard JSON object
in the same format as the rest of the API.)

Datatable_builder calls '../php/datatable_fetch.php' which grabs the data from the database.
Note that datatable_fetch.php does not use PDO and currently requires hard-coded db authentication.

The number of columns in the DataTable is automatically calculated based on the number of values
in the 'q' parameter.

*/

//?q=id,first_name,last_name,email,phone,zip&t=customer
$q = $_GET["q"];
$t = $_GET["t"];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">

	<title>Hairy & Merry Our <?php echo ucfirst($t) ?>s</title>
	<link rel="stylesheet" type="text/css" href="../../css/datatables/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="../../css/datatables/shCore.css">
	<link rel="stylesheet" type="text/css" href="../../css/datatables/resources/demo.css">
	<link rel="stylesheet" type="text/css" href="../../css/datatables/dataTables.responsive.css">
	<link rel="stylesheet" type="text/css" href="../../../../../../css/jquery-mobile-themes/font-awesome-css/font-awesome.min.css">
	
	<style type="text/css" class="init">

	</style>
	<script type="text/javascript" language="javascript" src="../../js/datatables/jquery.js"></script>
	<script type="text/javascript" language="javascript" src="../../js/datatables/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="../../js/datatables/shCore.js"></script>
	<script type="text/javascript" language="javascript" src="../../js/datatables/demo.js"></script>
	<script type="text/javascript" language="javascript" src="../../js/datatables/dataTables.responsive.min.js"></script>	
	
	<script type="text/javascript" language="javascript" class="init">

$(document).ready(function() {

	// These variables allow us flexibility in the building the DataTable
	// We can configure these to match the data

	// Collect the parameters from the address bar
	var columns = "<?php echo $q ?>";
	var table = "<?php echo $t ?>";

	$('#example').dataTable( {
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": "server_processing.php?q="+columns+"&t="+table+""
	} );
} );

	</script>

	</head>
	
	
<body class="dt-example">
	<!-- <div class="container"> //-->
		<section>
			
			<h1><a href="../../index.html"><i class="fa fa-arrow-circle-o-left fa"></i></a>&nbsp;&nbsp;Our <?php echo ucfirst($t) ?>s</h1>
			
			<table id="example" class="display" cellspacing="0" width="100%">
				<thead>

					<?php
						// Build an array of columns from the comma-separated list
						$aColumns = explode(",",$q);

						// Dynamically build as many <th> elements as their are columns
						foreach ($aColumns as $th) {
							// Replace any '_' characters with spaces and capitalize each word
    						echo '<th>'.ucwords(str_replace('_',' ', $th)).'</th>';
						}
					?>

				</thead>

				<tfoot>

					<?php
						// Dynamically build as many <th> elements as their are columns
						foreach ($aColumns as $th) {
							// Replace any '_' characters with spaces and capitalize each word
    						echo '<th>'.ucwords(str_replace('_',' ', $th)).'</th>';
						}
					?>

				</tfoot>
			</table>

		</section>
	<!-- </div> //-->

</body>	


</html>