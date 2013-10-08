<?php session_start(); ?>
<!doctype html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>Screen&sup2; Build 5</title>
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="http://d3js.org/d3.v3.js"></script>
	<script src="./js/rickshaw/rickshaw.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script type="text/javascript" src="js/twitterfeed.js"></script>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <link href="css/styles.css" rel="stylesheet" type="text/css" />
    <link href="css/rickshaw.css" rel="stylesheet" type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,700' rel='stylesheet' type='text/css'>
    <style>
		.chart-container {
			width: 700px;
			margin: 0 auto 0 auto;
		}
	</style>
    <script>
	//JSON Parser 
	//developerdrive.com
    function QueryStringToJSON() {            
		var pairs = location.search.slice(1).split('&');
		var result = {};
	
		pairs.forEach(function(pair) {
			pair = pair.split('=');
			result[pair[0]] = decodeURIComponent(pair[1] || '');
		});
	
		return JSON.parse(JSON.stringify(result));
	}
	
	
	var query_string = QueryStringToJSON();
	
	//https://github.com/shutterstock/rickshaw/blob/master/examples/hover.html
	Rickshaw.namespace('Rickshaw.Graph.TweetDetails');
	Rickshaw.Graph.TweetDetails = Rickshaw.Class.create({
	
		initialize: function(args) {
	
			var graph = this.graph = args.graph;
	
			this.xFormatter = args.xFormatter || function(x) {
				return new Date( x * 1000 ).toUTCString();
			};
	
			this.yFormatter = args.yFormatter || function(y) {
				return y === null ? y : y.toFixed(2);
			};
	
			var element = this.element = document.createElement('div');
			element.className = 'tweets';
	
			this.visible = true;
			graph.element.appendChild(element);
	
			this.lastEvent = null;
			this._addListeners();
	
			this.onShow = args.onShow;
			this.onHide = args.onHide;
			this.onRender = args.onRender;
	
			this.formatter = args.formatter || this.formatter;
	
		},
	
		formatter: function(series, x, y, formattedX, formattedY, d) {
			return series.name + ':&nbsp;' + formattedY;
		},
	
		update: function(e) {
	
			e = e || this.lastEvent;
			if (!e) return;
			this.lastEvent = e;
	
			if (!e.target.nodeName.match(/^(path|svg|rect|circle)$/)) return;
	
			var graph = this.graph;
	
			var eventX = e.offsetX || e.layerX;
			var eventY = e.offsetY || e.layerY;
	
			var j = 0;
			var points = [];
			var nearestPoint;
	
			this.graph.series.active().forEach( function(series) {
	
				var data = this.graph.stackedData[j++];
	
				if (!data.length)
					return;
	
				var domainX = graph.x.invert(eventX);
	
				var domainIndexScale = d3.scale.linear()
					.domain([data[0].x, data.slice(-1)[0].x])
					.range([0, data.length - 1]);
	
				var approximateIndex = Math.round(domainIndexScale(domainX));
				if (approximateIndex == data.length - 1) approximateIndex--;
	
				var dataIndex = Math.min(approximateIndex || 0, data.length - 1);
	
				for (var i = approximateIndex; i < data.length - 1;) {
	
					if (!data[i] || !data[i + 1]) break;
	
					if (data[i].x <= domainX && data[i + 1].x > domainX) {
						dataIndex = Math.abs(domainX - data[i].x) < Math.abs(domainX - data[i + 1].x) ? i : i + 1;
						break;
					}
	
					if (data[i + 1].x <= domainX) { i++ } else { i-- }
				}
	
				if (dataIndex < 0) dataIndex = 0;
				var value = data[dataIndex];
	
				var distance = Math.sqrt(
					Math.pow(Math.abs(graph.x(value.x) - eventX), 2) +
					Math.pow(Math.abs(graph.y(value.y + value.y0) - eventY), 2)
				);
	
				var xFormatter = series.xFormatter || this.xFormatter;
				var yFormatter = series.yFormatter || this.yFormatter;
	
				var point = {
					formattedXValue: xFormatter(value.x),
					formattedYValue: yFormatter(series.scale ? series.scale.invert(value.y) : value.y),
					series: series,
					value: value,
					distance: distance,
					order: j,
					name: series.name
				};
	
				if (!nearestPoint || distance < nearestPoint.distance) {
					nearestPoint = point;
				}
	
				points.push(point);
	
			}, this );
	
			if (!nearestPoint)
				return;
	
			nearestPoint.active = true;
	
			var domainX = nearestPoint.value.x;
			var formattedXValue = nearestPoint.formattedXValue;
	
			this.element.innerHTML = '';
			this.element.style.left = graph.x(domainX) + 'px';
	
			this.visible && this.render( {
				points: points,
				detail: points, // for backwards compatibility
				mouseX: eventX,
				mouseY: eventY,
				formattedXValue: formattedXValue,
				domainX: domainX
			} );
		},
	
		hide: function() {
			this.visible = false;
			this.element.classList.add('inactive');
	
			if (typeof this.onHide == 'function') {
				this.onHide();
			}
		},
	
		show: function() {
			this.visible = true;
			this.element.classList.remove('inactive');
	
			if (typeof this.onShow == 'function') {
				this.onShow();
			}
		},
	
		render: function(args) {
	
			var graph = this.graph;
			var points = args.points;
			var point = points.filter( function(p) { return p.active } ).shift();
	
			if (point.value.y === null) return;
	
			var formattedXValue = point.formattedXValue;
			var formattedYValue = point.formattedYValue;
	
			this.element.innerHTML = '';
			this.element.style.left = graph.x(point.value.x) + 'px';
	
			var xLabel = document.createElement('div');
			
			var date = new Date(Date.parse(formattedXValue));
			var hours = date.getHours();
			var day = date.getDate();
			var month = date.getMonth() + 1; //Months are zero based
			var year = date.getFullYear();
			
			date = year + "-" + month + "-" + day;// + " " + hours + ":00:00";
			
			$.get('./search.php?q=' + point.series.name + '&count=10&until=' + date, function(data){
				data = JSON.parse(data);
				xLabel.innerHTML = data;
			});
	
			xLabel.className = 'x_label';
			xLabel.innerHTML = formattedXValue;
			this.element.appendChild(xLabel);
	
			var item = document.createElement('div');
	
			item.className = 'item';
	
			// invert the scale if this series displays using a scale
			var series = point.series;
			var actualY = series.scale ? series.scale.invert(point.value.y) : point.value.y;
	
			item.innerHTML = this.formatter(series, point.value.x, actualY, formattedXValue, formattedYValue, point);
			item.style.top = this.graph.y(point.value.y0 + point.value.y) + 'px';
	
			this.element.appendChild(item);
	
			var dot = document.createElement('div');
	
			dot.className = 'dot';
			dot.style.top = item.style.top;
			dot.style.borderColor = series.color;
	
			this.element.appendChild(dot);
	
			if (point.active) {
				item.className = 'item active';
				dot.className = 'dot active';
			}
	
			this.show();
	
			if (typeof this.onRender == 'function') {
				this.onRender(args);
			}
		},
	
		_addListeners: function() {
	
			this.graph.element.addEventListener(
				'click',
				function(e) {
					this.visible = true;
					this.update(e);
				}.bind(this),
				false
			);
	
			this.graph.onUpdate( function() { this.update() }.bind(this) );
	
			/*this.graph.element.addEventListener(
				'mouseout',
				function(e) {
					if (e.relatedTarget && !(e.relatedTarget.compareDocumentPosition(this.graph.element) & Node.DOCUMENT_POSITION_CONTAINS)) {
						this.hide();
					}
				}.bind(this),
				false
			);*/
		}
	});
	

	
	Rickshaw.namespace('Rickshaw.Graph.Renderer.DigitalInc');
	//Modified lineplot renderer for Rickshaw
	Rickshaw.Graph.Renderer.DigitalInc = Rickshaw.Class.create( Rickshaw.Graph.Renderer, {
	
		name: 'digitalinc',
	
		defaults: function($super) {
	
			return Rickshaw.extend( $super(), {
				unstack: true,
				fill: false,
				stroke: true,
				padding:{ top: 0.01, right: 0.01, bottom: 0.01, left: 0.01 },
				dotSize: 3,
				strokeWidth: 2
			} );
		},
	
		initialize: function($super, args) {
			$super(args);
		},
	
		seriesPathFactory: function() {
	
			var graph = this.graph;
	
			var factory = d3.svg.line()
				.x( function(d) { return graph.x(d.x) } )
				.y( function(d) { return graph.y(d.y) } )
				.interpolate(this.graph.interpolation).tension(this.tension);
	
			factory.defined && factory.defined( function(d) { return d.y !== null } );
			return factory;
		},
	
		_renderDots: function() {
	
			var graph = this.graph;
	
			graph.series.forEach(function(series) {
	
				if (series.disabled) return;
	
				var nodes = graph.vis.selectAll("x")
					.data(series.stack.filter( function(d) { return d.y !== null } ))
					.enter().append("svg:circle")
					.attr("cx", function(d) { return graph.x(d.x) })
					.attr("cy", function(d) { return graph.y(d.y) })
					.attr("r", function(d) { return ("r" in d) ? d.r : graph.renderer.dotSize});
	
				Array.prototype.forEach.call(nodes[0], function(n) {
					if (!n) return;
					n.setAttribute('data-color', series.color);
					//changed from 'white' to series color
					n.setAttribute('fill', series.color);
					n.setAttribute('stroke', series.color);
					n.setAttribute('stroke-width', this.strokeWidth);
	
				}.bind(this));
	
			}, this);
		},
	
		_renderLines: function() {
	
			var graph = this.graph;
	
			var nodes = graph.vis.selectAll("path")
				.data(this.graph.stackedData)
				.enter().append("svg:path")
				.attr("d", this.seriesPathFactory());
	
			var i = 0;
			graph.series.forEach(function(series) {
				if (series.disabled) return;
				series.path = nodes[0][i++];
				this._styleSeries(series);
			}, this);
		},
	
		render: function() {
	
			var graph = this.graph;
	
			graph.vis.selectAll('*').remove();
	
			this._renderLines();
			this._renderDots();
		}
	} );
	

    
		        
    $(document).ready(function () {
		
		//Code to get saved searches from database 
		$.get('http://digitalinc.ie/screen2-build5/get-saved-searches.php', function(data){
			$('div.saved').append(data);
			if (typeof query_string.keywords === 'undefined') { 
                     return;
              } 
              var keywords = query_string.keywords.split(',');
			
	
			$('div.saved input').each(function(){
				if($.inArray($(this).val(), keywords) > -1) {
					$(this).attr('checked', 'checked');
				}
			});
		});
		
        var palette = new Rickshaw.Color.Palette( { scheme: 'cool' } );	
		
		var config = {
			element: document.getElementById("chart"),
			width: 700,
			height: 500,
			renderer: 'digitalinc',
			stroke: true,
			preserve: true,
			interpolation: 'linear',
			dataURL: 'http://digitalinc.ie/screen2-build5/chart-data.php?keywords=' + query_string.keywords + '&hours=15000',
			/*onData: function(d) { 
				return d;
			},*/
			onComplete: function(transport) {
				var graph = transport.graph;
				var detail = new Rickshaw.Graph.HoverDetail({ graph: graph });
				
				var tweets = new Rickshaw.Graph.TweetDetails({ graph: graph });
				//tweets.render();
				
				var slider = new Rickshaw.Graph.RangeSlider( {
					graph: graph,
					element: $('#slider')
				} );
				
				
				var ticksTreatment = 'glow';

				var xAxis = new Rickshaw.Graph.Axis.Time( {
					graph: graph,
					ticksTreatment: ticksTreatment,
					tickFormat: function(d) {
						return d3.time.format('%b %e %X')(d);
					},
					timeFixture: new Rickshaw.Fixtures.Time.Local()
				} );
				
				xAxis.render();
				
				var yAxis = new Rickshaw.Graph.Axis.Y( {
					graph: graph,
					tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
					ticksTreatment: ticksTreatment
				} );
				
				yAxis.render();

				//graph.update();
				graph.render();
			}
		};
		
		// instantiate our graph!			
		var graph = new Rickshaw.Graph.Ajax( config );
		
		$('#refresh').click(function(event) {
			event.preventDefault();
			var keywords = '';
			$('div.saved input:checked').each(function(){
				keywords += ',' + $(this).val();
			});
			//remove leading comma
			keywords = keywords.substring(1);
			
			document.location = "index.php?keywords=" + keywords + '&hours=15000';
			
			return false;
		});
		
		

		 <!--Snippet for accordion taken from  http://jqueryui.com/accordion/#no-auto-height -->
		$( "#accordion" ).accordion({
			heightStyle: "content",
			collapsible: true,
			active: false
		});
	
		
		
		
		//END Saved Searches
		
		
		//Code to get the top ten trends
		
		// Parse out the JSON object 
		$.get('http://digitalinc.ie/screen2-build5/trend.php', function(data){
			var ul = $('div.top-ten ul');
			data = JSON.parse(data);
			 $(data).each(function(index, trend) {
				 //<input type="checkbox" value="' . $row['search_terms'] . '" />' . $row['search_terms'] . '
				//ul.append($(document.createElement('li')).text(trend.name));
				ul.append('<li><input type="checkbox" value="' + trend.name + '" />' + trend.name + '</li>');
			});
			
			
			$('div.top-ten ul').click(function(event){
				var target = $(event.target);
				if(target.is('input')){
					document.location = "update-data.php?q=" + target.text().substr(1) + '&hours=15000';
					//$('#s').val(target.text());
					//return updateChart(event);
				}
			});
		});
		//END Top Ten Trends
    });
    </script>
    </head>

<body>
	<header>
    	<div id="header-left">
        
            <div class="menu-icon">
                <button id="showLeft"></button>
            </div>
    		<!-- END menu-icon-->
             
 		</div>
      	<!-- END header-left -->
        
      	<div id="header-right">
        
            
    		<div class="clear"></div>
    
        </div>      
        <!-- END header-right --> 
    </header>
<!-- END header -->

    <div id="menu" class="main-navigation cbp-spmenu-push">
        <nav class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left" id="cbp-spmenu-s1">
			<?php 
                if(!empty($_SESSION['username'])){  ?>
            <?php 
                    require_once('user-detail.php');
                }
        	?>
        	<a href="#" id="refresh">Update Chart</a>
        	<div class="clear"></div>
        	<!--Snippet for accordion taken from  http://jqueryui.com/accordion/ -->
        	<div id="accordion">
            	<h3>Saved Searches</h3>
          		<div class="saved"> </div>
          		<h3>Trends</h3>
          		<div class="top-ten">
                    <ul>
                    </ul>
          	</div>
            </div>
        	<div id="logout-wrapper">
              <button id="logout">Logout</button>
            </div>
      	</nav>
          <!-- END nav cbp-spmenu-s1 --> 
    </div>
    <!-- END main-navigation -->

	<?php 
    if(!empty($_SESSION['username'])){  ?>
    
    <div class="content">
        <div class="search">
            <form class="search" action="update-data.php" method="get">
                <input id="q" results=5 type="search" name="q" value="Search #, Keyword, Topic " />
                <input id="update" type="button" value="Go" />
            </form>
        </div>
        <!-- END search -->
    
		<div class="chart-container">
            <div id="chart"></div>
            <div id="timeline"></div>
            <h6>Slider</h6>
			<div id="slider"></div>
        </div>
    </div>
    <!-- END content -->
	
    
    <!-- Classie - class helper functions by @desandro https://github.com/desandro/classie --> 
    <script src="js/classie.js"></script> 
    <script>
      var menuLeft = document.getElementById( 'cbp-spmenu-s1' ),
          body = document.body;

      showLeft.onclick = function() {
          classie.toggle( this, 'active' );
          classie.toggle( menuLeft, 'cbp-spmenu-open' );
          disableOther( 'showLeft' );
      };
              
    </script>
    
    
    <?php 
    } else {
     ?>
    <div class="login-wrapper">
    	<img src="./images/twitter-icon.png" alt="twitter-bird" />
        <h3>Sign in to your account</h3>
        <div class="twitter-login">
        <div class="twitter-login-icon">
            
        </div>
        <form action="twitter_login.php" method="get">
              <input type="submit" class="twitter-submit" value="Sign In" >
        </form>
        </div>
        <!-- END login-with-twitter --> 
    </div>
    <!-- END login-wrapper -->

    <div class="clear"></div>
    <?php } ?>

    
</body>
</html>
