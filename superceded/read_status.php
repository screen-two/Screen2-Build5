<?php

require("twitteroauth/twitteroauth.php"); 
	 
session_start();
	if(!empty($_SESSION['username'])){  
		
		$twitteroauth = new TwitterOAuth('oXRHpijPXqkmpI01vB3XKQ', 'EBxsXvSZaDeiN08kHHtWaiiZyiGOdpsIP0UGBwy2g', $_SESSION['oauth_token'], $_SESSION['oauth_secret']);  
		
		$home_timeline = $twitteroauth->get('statuses/home_timeline', array('count' => 40));  
	
	print_r($home_timeline);
	
} else {  
    // Something's missing, go back to square 1  
    header('Location: twitter_login.php'); 
	
}	
	

?>