// Is this file used by anything?


$('#go_button').click(function() {
	// grab values
	var id = "Nothin here";
	var output = null;

	// show loading text
	$("#results").html(id);

	// perform http request
	$.post('./php/query-sig.php', { id: id}, function(data) {
		output = data;
		//$('#results').text(output);
	});
});