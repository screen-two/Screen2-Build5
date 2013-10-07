<?php 

ini_set('log_errors', 1);  
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');  
error_reporting(E_ALL);

require("twitteroauth/twitteroauth.php"); 
 
session_start();
//print_r($_SESSION);
if(!empty($_SESSION['username'])){  
	
	echo 'initializing twitter oAuth ';
		
	$twitteroauth = new TwitterOAuth('oXRHpijPXqkmpI01vB3XKQ', 'EBxsXvSZaDeiN08kHHtWaiiZyiGOdpsIP0UGBwy2g', $_SESSION['oauth_token'], $_SESSION['oauth_secret']);  
	echo 'checking relationship ';
	try {
	$follows_thisdigitalinc= $twitteroauth->get('friendships/show', array('source_screen_name' => $_SESSION['username'], 'target_screen_name' => 'thisdigitalinc')); 
	//echo 'relationship result'.$follows_thisdigitalinc;
	 
	if(!$follows_thisdigitalinc){  
		echo 'You are NOT following @thisdigitalinc!';  
		
		$twitteroauth->post('friendships/create', array('screen_name' => 'thisdigitalinc')); 
		 
	}  else {
		echo 'You are following @thisdigitalinc';
		}
	} catch(Exception $e){
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
} else {  
    // Something's missing, go back to square 1  
    header('Location: twitter_login.php'); 	
	
}	



?>