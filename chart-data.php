<?php 
//set the response header to json
header('Content-type: application/json');

session_start();

//connect to the database
mysql_connect('mysql1859int.cp.blacknight.com', 'u1148707_screen2', 'Arch1p3lag0');  
mysql_select_db('db1148707_screen2');  

//retrieve querystring parameters

$keywords="";
$hours = 168;

//grab keywords, if not there die
if( isset($_GET) && isset($_GET['keywords']) ){
	$keywords = $_GET['keywords'];
} else {
	die('no params provided');
}


//grab hours, if not there die
if( isset($_GET) && isset($_GET['hours']) ){
	$hours = intval( $_GET['hours'] );
} else {
	die('no params provided');
}

//escape keywords by turning into array from csv and then wrapping them in ','
$search = implode("','", explode(",", $keywords));

//Grab the all the results for the number of hours and keywords given
$sql = sprintf("SELECT * FROM saved_search AS saved
INNER JOIN saved_search_history AS history
	ON history.saved_search_id = saved.saved_search_id
WHERE saved.search_terms IN ( '" . $search . "' ) AND history.timestamp >= DATE_SUB(CURDATE(), INTERVAL %d HOUR) ORDER BY saved.search_terms, timestamp ASC", $hours);

$query = mysql_query($sql);  

//create array of arrays for result sets and data points
$results = array();
$positions = array();
$colors = array( '#1d9abd', '#0d4757', '#77a9b7', '#265a68'  );
$pos = 0;

//read results from sql query
while ($row = mysql_fetch_assoc($query)) {	
	$term = $row['search_terms'];
	
	$dataset = array();
	
	//keep track of search term position in results array
	if(array_key_exists($term, $positions)){
		$pos = $positions[$term];
	} else {
		$positions[$term] = count($positions);
		$pos = $positions[$term];
	}
	
	//initialize the dataset array
	if(array_key_exists($pos, $results)){
		$dataset = $results[$pos];
	} else {
		$color = $colors[count($colors)-1];
		unset($colors[count($colors)-1]);
		$dataset = array( name => $term, color => $color, renderer => 'lineplot', data => array() );
		$results[$pos] = $dataset;
	}
	
	
	//grab history values
	$epoch = strtotime( $row['timestamp'] );
	$count = intval($row['count']);
	
	//store datapoint values in data array
	$entry = array( 'x' => $epoch, 'y' => $count, 'r' => 6);
	$data = $dataset['data'];
	array_push($data, $entry);
	
	//update parent arrays
	$dataset['data'] = $data;
	$results[$pos] = $dataset;
}

//print out results
print_r( json_encode( $results ) );

?>



