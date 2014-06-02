
$('#submit_button').click(function() {
	// grab values
	var name = $('#name').val();
	// Fixes an awkward error where the compression library will crash if you send it a null value.
	if ( $('#sig').val() ){
		var sig = deflateFromJsonSignature( $('#sig').val() ); // Compress the JSON
	} else { var sig = "1"; } // A value of 1 is better than a crash, amiright?
	
	// show loading text
	$("#sig_status_details").html(sig);

	// perform http request
	// POST the name and compressed signature to 'create_sig.php'
	// uses a callback function to collect a string that 'create_sig.php' is returning
	$.post('./php/create_sig.php', { name: name, sig: sig }, function(data) {
		//$('#sig_status').text(data);
		window.location.replace("./thanks.html");
	});
});