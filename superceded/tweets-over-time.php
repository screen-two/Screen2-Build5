<?php session_start(); ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">

<title>Tweet Graph</title>
<link href="css/styles.css" rel="stylesheet" type="text/css" />
<link href="css/graph-styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
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
	
$(document).ready(function () {
	
	$('#s').keypress(function (event) {
	  if (event.which == 13) {
		event.preventDefault();

		
		d3.tsv("http://digitalinc.ie/authenticate/visuals/graph-search.php?q=" + $('#s').val(), function(error, data) {
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
			
			  svg.append("path")
				  .datum(data)
				  .attr("class", "line")
				  .attr("d", line);
		});
		return false;
	  }
	});
});
</script>

	<section id="content-wrapper">
            
            <div class="search">
                <input id="s" results=5 type="search" name="s" value="Type keyword and press enter to search" />
            </div>
        <div class="clear"></div>
    </section>


</body>
</html>
