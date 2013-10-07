<?php session_start(); 
require("twitteroauth/twitteroauth.php");  

if(!empty($_SESSION['username'])){  

//print_r($_SESSION);

//echo 'token' . $_SESSION['access_token']['oauth_token'].'<br/>';
//echo 'secret'. $_SESSION['access_token']['oauth_token_secret'].'<br/>'.'<br/>'.'<br/>'.'<br/>';


	//Make usre to use access_token as an ARRAY! 
	$twitteroauth = new TwitterOAuth('oXRHpijPXqkmpI01vB3XKQ', 'EBxsXvSZaDeiN08kHHtWaiiZyiGOdpsIP0UGBwy2g', $_SESSION['access_token']['oauth_token'], $_SESSION['access_token']['oauth_token_secret']); 
	$user_info = $twitteroauth->get('users/show/'. $_SESSION['oauth_uid']); 
	
	if(!isset($user_info->error)){  
		
		?>
        <div class="profile">
            <p><img src="<?php echo $user_info->profile_image_url ?>" id="avatar" /></p><!-- END avatar -->
            <h3>@<?php echo $user_info->screen_name ?></h3> <!-- END screen_name -->
            <div class="counts"> 
                <span class="number-of-tweets"><p><?php echo $user_info->statuses_count ?><br />Tweets</p></span>
                <span class="number-following"><p><?php echo $user_info->friends_count ?><br />Following</p></span>
                <span class="number-of-followers"><p><?php echo $user_info->followers_count ?><br />Followers</p></span>
                
            </div>
            <!-- END counts -->
        </div>
        <!-- END profile -->
            
        <?php
	}else {
		echo 'error: ' .$user_info->error;
	}
}
?>