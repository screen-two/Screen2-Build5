<?php 
//set the response header to json
header('Content-type: application/json');

session_start();
require_once("twitteroauth/twitteroauth.php"); //Path to twitteroauth library, code from The Web Dev Door by Tom Elliott (http://www.webdevdoor.com/php/authenticating-twitter-feed-timeline-oauth/)

$q="";
//get search term from querystring
if( isset($_GET) && isset($_GET['q']) ){
	$q=$_GET['q'];
} else {
	die('no params provided');
}

$until="";
//get until from querystring
if( isset($_GET) && isset($_GET['until']) ){
	$until=$_GET['until'];
} else {
	die('no params provided');
}

$count=100;
//get until from querystring
if( isset($_GET) && isset($_GET['count']) ){
	$count=$_GET['count'];
}


$twitteruser = "thisdigitalinc";
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


$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?q=".$q."&count=".$count."&include_entities=0&until=" . $until);


$status = array_merge($status, $tweets->{'statuses'});

print_r(json_encode($status));