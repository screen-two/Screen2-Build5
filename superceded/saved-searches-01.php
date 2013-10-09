<?php

// connecting to db
mysql_connect('mysql1859int.cp.blacknight.com', 'u1148707_screen2', 'Arch1p3lag0');  
mysql_select_db('db1148707_screen2'); 

$sql="SELECT * FROM saved_search";

$result = mysql_query($sql);


        
echo '<ul>';

while($row = mysql_fetch_array($result))
 {
 echo '<li><a href="http://digitalinc.ie/visual-with-search/search-graph-working.php?q=' . $row['search_terms'] . '">' . $row['search_terms'] . '</a></li>';
 }
echo '</ul>';

?>

