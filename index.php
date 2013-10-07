<?php session_start(); ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">

<title>Tweet Graph</title>
<link href="css/styles.css" rel="stylesheet" type="text/css" />
<link href="css/graph-styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
<script src="http://d3js.org/d3.v3.js"></script>

</head>


<body>
<script>


var margin = {top: 20, right: 20, bottom: 30, left: 50},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y-%m-%d").parse;

var x = d3.time.scale()
    .range([0, width]);

var y = d3.scale.linear()
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");

var line = d3.svg.line()
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.count); });

var svg = d3.select("body").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
	
function updateChart(event){
	event.preventDefault();
	d3.tsv("http://digitalinc.ie/visual-with-search/graph-search-with-caching.php?q=" + $('#s').val(), function(error, data) {
		data.forEach(function(d) {
			d.date = parseDate(d.date);
			d.count = +d.count;
		});

		x.domain(d3.extent(data, function(d) { return d.date; }));
		y.domain(d3.extent(data, function(d) { return d.count; }));

		svg.append("g")
			.attr("class", "x axis")
			.attr("transform", "translate(0," + height + ")")
			.call(xAxis);

		svg.append("g")
			.attr("class", "y axis")
			.call(yAxis)
			.append("text")
			.attr("transform", "rotate(-90)")
			.attr("y", 6)
			.attr("dy", ".71em")
			.style("text-anchor", "end")
			.text("Tweets");

		/* svg.append("path")
		.datum(data)
		.attr("class", "line")
		.attr("d", line);*/

		svg.selectAll(".select")
			.data(data)
			.enter().append("circle")
			.attr("class", "select")
			//.attr("d", line)
			.attr("cx", function(d) { return x(d.date); })
			.attr("cy", function(d) { return y(d.count); })
			.attr("r", 60)
			.style("fill", "blue")
			.style("stroke", "none")
			.style("stroke-opacity", 1e-6)
			.style("stroke-width", 3)
			.transition()
			.duration(750)
			.attr("r", 12)
			.style("stroke-opacity", 1); 
	});
	return false;
}
	
$(document).ready(function () {
	
	$('#s').keypress(function (event) {
		if (event.which == 13) {
			return updateChart(event);
		}
	});
	
	$('input#update').click(function (event) {
		return updateChart(event);
	});
});
</script>

	<section id="content-wrapper">
            
            <div class="search">
				<input id="s" results=5 type="search" name="s" value="Type keyword and press go to search" data-theme="a"/>
				<input id="update" type="button" value="go" data-theme="a"/>
            </div>
        <div class="clear"></div>
    </section>
    
    


</body>
</html>
