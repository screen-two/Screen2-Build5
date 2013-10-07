<?
/*
 * Following code will list all the students on a course
 */

// array for JSON response
$response = array();

// include db connect class
require_once __DIR__ . '/db_connect.php';

//$requestData = $_GET["saved_search"];

// connecting to db
$db = new DB_CONNECT();
// get all courses from course table
$result = mysql_query("SELECT * FROM saved_search") or die(mysql_error());
// check for empty result
if (mysql_num_rows($result) > 0)
 {
    // looping through all results
    $response["saved_search"] = array();
    while ($row = mysql_fetch_array($result)) 
     {
        // temp student array
        $student = array();
        $student["saved_search_id"] = $row["saved_search_id"];
        $student["search_terms"] = $row["search_terms"];
        

       // push single product into final response array
        array_push($response["saved_search"], $saved_search);
    }
    // success
    $response["success"] = 1;
    // echoing JSON response
    print (json_encode($response));

}
else {
    // no products found
    $response["success"] = 0;
    $response["message"] = "No searches found";

    // echo no users JSON
    print (json_encode($response));

}


?>

