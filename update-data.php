<?php 

session_start();
require_once("twitteroauth/twitteroauth.php"); //Path to twitteroauth library, code from The Web Dev Door by Tom Elliott (http://www.webdevdoor.com/php/authenticating-twitter-feed-timeline-oauth/)

$q="";
//get search term from querystring
if( isset($_GET) && isset($_GET['q']) ){
	$q=$_GET['q'];
} else {
	die('no params provided');
}

//connect to database
mysql_connect('mysql1859int.cp.blacknight.com', 'u1148707_screen2', 'Arch1p3lag0');  
mysql_select_db('db1148707_screen2'); 


//initialize to default value
$search_id = 0;

//Do we have this search already saved?
//http://php.net/manual/en/function.mysql-query.php
$query = mysql_query(sprintf("SELECT * FROM saved_search WHERE search_terms = '%s'", mysql_real_escape_string($q)));  
$result = mysql_fetch_array($query);

$last_tweet_id = '';
if(empty($result)){  
	//insert search
	$query = mysql_query(sprintf("INSERT INTO saved_search ( search_terms, last_tweet_id ) VALUES ('%s', '%s')", mysql_real_escape_string($q), $last_tweet_id));  
	$query = mysql_query("SELECT * FROM saved_search WHERE saved_search_id = " . mysql_insert_id());  
	$result = mysql_fetch_array($query);  
	$search_id = $result['saved_search_id'];
	$last_tweet_id = $result['last_tweet_id'];
} else {  
	$search_id = $result['saved_search_id'];
	$last_tweet_id = $result['last_tweet_id'];
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

$latitude = "53.339381";
$longitude = "-6.260533";
$radius = "1000km";

$status = array();

if(isset($last_tweet_id) && !empty($last_tweet_id)) {
	$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?count=100&include_entities=0&since_id=" . $last_tweet_id . "&geocode=" . $latitude . "," . $longitude . "," . $radius . "&q=".$q);
}
else {
	$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?count=100&include_entities=0&geocode=" . $latitude . "," . $longitude . "," . $radius . "&q=".$q);
}

$status = array_merge($status, $tweets->{'statuses'});

$meta = $tweets->{'search_metadata'};
//$next is sent from the twitter api and details the next querystring to use. 
$next = $meta->{'next_results'};
$max = 20;
$last_tweet_id = $meta->{'max_id_str'};
$meta->{'refresh_url'};

while(isset($next) && $max > 0){
	
	$new_tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json".$next);
	$status = array_merge($status, $new_tweets->{'statuses'});
	
	$meta = $new_tweets->{'search_metadata'};
	
	if(isset($meta)) {
		$next = $meta->{'next_results'};
	}
	else
	{
		unset($next);
	}
	
	$max -= 1;
}

$counts = array();

foreach ($status as $tweet){
	$date = date_parse( $tweet->{"created_at"} );
	$key = $date['year'] . '-' . $date['month'] . '-' . $date['day'] . ' ' . $date['hour'] . ':00:00';
	
	if(!array_key_exists($key, $counts))
		$counts[$key] = 0;
		
	$counts[$key] = $counts[$key] + 1;
}	

//update the last tweet id and refresh_url for this search
$query = mysql_query(sprintf("UPDATE saved_search SET last_tweet_id  = '%s', refresh_url = '%s' WHERE saved_search_id = '%d'", $last_tweet_id, $refesh_url, $search_id));


//Save all of the counts (or update as needed)
foreach ($counts as $date => $count)
{
	$sql = sprintf("SELECT * FROM saved_search_history WHERE saved_search_id = '%d' AND timestamp = '%s'", $search_id, $date);
	$query = mysql_query($sql);  
	$result = mysql_fetch_array($query);
	
	if(empty($result)){ 
		$sql = sprintf("INSERT INTO saved_search_history ( saved_search_id, timestamp, count ) VALUES ('%d', '%s', '%d')", $search_id, $date, $count);
		$query = mysql_query($sql); 
		
	} else {
		$history_id = $result['saved_search_history_id'];
		
		//Add counts together, because we used last tweet id
		$query = mysql_query(sprintf("UPDATE saved_search_history SET count = count + %d WHERE saved_search_history_id = '%d'", $count, $history_id));
	}
}
header('Location: index.php?keywords=' . $q . '&hours=15000');
exit;
?>



