<?php
require 'app_tokens.php';
require 'tmhOAuth.php';
$connection = new tmhOAuth(array(
'consumer_key' => $consumer_key,
'consumer_secret' => $consumer_secret,
'user_token' => $user_token,
'user_secret' => $user_secret
));


// Get trends
$connection->request('GET',$connection->url('1.1/trends/place.json?'), 
array( 'id' => '23424803'));
// Get the HTTP response code for the API request
$response_code = $connection->response['code'];

// Convert the JSON response into an array
$response_data = json_decode($connection->response['response'],true);
// A response code of 200 is a success
if ($response_code <> 200) {
print "Error: $response_code\n";
} else {
	//instead of echoing out all of the information, we're just echoing out the trends 
	//ran into rate limits at this point. 
	echo json_encode( $response_data[0]['trends']);
	
}
// removed debug output we don't need

?>
