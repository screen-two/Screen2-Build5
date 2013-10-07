// JavaScript Document


jQuery(document).ready(function($) {
	// Make an Ajax request
	$.ajax({
			
		// Define the URL being called by Ajax
		// This URL is for illustration only
		// You MUST change it to call your server
		url: 'http://dechasamediablog.com/trend/trend.php',
		
		// Put the results into the display element
		success: function(data){
			$('#ajax_results').html(data);
		},
		
		// Display an error message if the request fails
		error: function(data) {
			$('#ajax_results').html('Ajax request failed');
		}
	})
});