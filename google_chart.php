<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

	// Load Charts and the corechart and barchart packages.
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawPieChart);
	google.charts.setOnLoadCallback(drawColumnChart);
	google.charts.setOnLoadCallback(lineChart);
	
	
	// variables
	var width = 600;
	var height = 400;
	
	var options = {
		title:'Pie Chart: How Much Pizza I Ate Last Night',
		width:width,
		height:height
	};

	function drawPieChart() {
		
		

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Topping');
		data.addColumn('number', 'Slices');
		data.addRows([
		  ['Mushrooms', 3],
		  ['Onions', 1],
		  ['Olives', 1],
		  ['Zucchini', 1],
		  ['Pepperoni', 2]
		]);

		
		
		
		// pie char
		var piechart = new google.visualization.PieChart(document.getElementById('piechart_div'));
		piechart.draw(data, options);

		
		
		// Pie chart
		var pie_options = {
			title:'Pie Chart: How Much Pizza I Ate Last Night',
			width:width,
			height:height,
			pieHole: 0.4
		};
		var barchart = new google.visualization.PieChart(document.getElementById('donut_div'));
		barchart.draw(data, pie_options);

	}
	  
	  
	function drawColumnChart() {
      var data = google.visualization.arrayToDataTable([
        ["Element", "Density", { role: "style" } ],
        ["Copper", 8.94, "#b87333"],
        ["Silver", 10.49, "silver"],
        ["Gold", 19.30, "gold"],
        ["Platinum", 21.45, "color: #e5e4e2"]
      ]);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: "Density of Precious Metals, in g/cm^3",
        width: width,
        height: height,
        bar: {groupWidth: "95%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));
      chart.draw(view, options);
	  
	  // bar chart
	  var barchart = new google.visualization.BarChart(document.getElementById('barchart_div'));
	  barchart.draw(data, options);
  }
  
  
  
	function lineChart() {
		
		var data = google.visualization.arrayToDataTable([
		  ['Year', 'Sales', 'Expenses'],
		  ['2004',  1000,      400],
		  ['2005',  1170,      460],
		  ['2006',  660,       1120],
		  ['2007',  1030,      540]
		]);

		var options = {
		  title: 'Company Performance',
		  legend: { position: 'bottom' },
		  width:width,
		  height:height
		};

		// line chart
		var chart = new google.visualization.LineChart(document.getElementById('line_chart'));
		chart.draw(data, options);
		
		// curve line chart
		var options = {
			title: 'Company Performance',
			curveType: 'function',
			legend: { position: 'bottom' },
			width:width,
			height:height
		};
		var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
		chart.draw(data, options);
		
	}
</script>
<body>
    <!--Table and divs that hold the pie charts-->
    <table class="columns">
      <tr>
        <td>
			<h3>Pie Chart</h3>
			<div id="piechart_div" style="border: 1px solid #ccc"></div>
		</td>
		<td>
			<h3>Donut Chart</h3>
			<div id="donut_div" style="border: 1px solid #ccc"></div>
		</td>
	  </tr>
	  <tr>
		<td>
			<h3>Bar Chart</h3>
			<div id="barchart_div" style="border: 1px solid #ccc"></div>
		</td>
		<td>
			<h3>Column Chart</h3>
			<div id="columnchart_values" style="border: 1px solid #ccc"></div>
		</td>
	  </tr>
	  <tr>
		<td>
			<h3>Line Chart</h3>
			<div id="line_chart" style="border: 1px solid #ccc"></div>
		</td>
		<td>
			<h3>Curve Line Chart</h3>
			<div id="curve_chart" style="border: 1px solid #ccc"></div>
		</td>
	  </tr>
    </table>
  </body>
</html>