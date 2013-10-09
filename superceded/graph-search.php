<?php 
//code from The Web Dev Door by Tom Elliott
//Adapted by Siobhan Schnittger 
//http://www.webdevdoor.com/php/authenticating-twitter-feed-timeline-oauth/
header('Content-type: text/plain');

session_start();
require_once("twitteroauth/twitteroauth.php"); //Path to twitteroauth library

$q="";

if( isset($_GET) && isset($_GET['q']) ){
	$q=$_GET['q'];
} else {
	die('no params provided');
}
 
$twitteruser = "thisdigitalinc";
$notweets = 30;
$consumerkey = "oXRHpijPXqkmpI01vB3XKQ";
$consumersecret = "EBxsXvSZaDeiN08kHHtWaiiZyiGOdpsIP0UGBwy2g";
$accesstoken = "1615587769-mhzmHR2bWQVlueppiW1mjwkrGMdGdw4qmoC6IMU";
$accesstokensecret = "AcKfb0CjRi3mun0dpFQAhubh4Br8hLmlwac8G4IDJE";
 
function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}
 
$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

$status = array();
$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?count=100&include_entities=0&q=".$q);

$status = array_merge($status, $tweets->{'statuses'});

$meta = $tweets->{'search_metadata'};
$next = $meta->{'next_results'};
$max = 20;
while(isset($next) && $max > 0){
	
	$new_tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json".$next);
	$status = array_merge($status, $new_tweets->{'statuses'});
	
	$meta = $new_tweets->{'search_metadata'};
	
	if(isset($meta))
		$next = $meta->{'next_results'};
	else
		unset($next);
	
	$max -= 1;
}

//echo "Found " . count($status) . " tweets in " . ( ( $max - 20 ) * -1 ) . " iterations <br/>";

$counts = array();

foreach ($status as $tweet){
	$date = date_parse( $tweet->{"created_at"} );
	$key = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
	
	if(!array_key_exists($key, $counts))
		$counts[$key] = 0;
		
	$counts[$key] = $counts[$key] + 1;
}

echo "date\tcount\n";

foreach ($counts as $date => $count)
{
	echo $date . "\t" . $count . "\n";
}
?>



