<?php

// connecting to db
//connect to database
mysql_connect('mysql1859int.cp.blacknight.com', 'u1148707_screen2', 'Arch1p3lag0');  
mysql_select_db('db1148707_screen2'); 

$sql="SELECT * FROM saved_search ORDER BY last_tweet_id DESC LIMIT 10";
$result = mysql_query($sql);


        
echo '<ul>';
while($row = mysql_fetch_array($result))
{
	echo '<li><input type="checkbox" value="' . $row['search_terms'] . '" />' . $row['search_terms'] . '</li>';
}
echo '</ul>';

?>

