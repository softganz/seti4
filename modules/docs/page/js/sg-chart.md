<code>
$('.sg-chart').each(function(index) {
	var $container=$(this);
	var chartId=$container.attr("id");
	var chartTitle=$container.find("h3").text();
	var chartType=$container.data("chartType");
	var $chartTable=$(this).find("table");
	var chartData=[];
	var chartColumn=[];
	var options={};

	if (chartType==undefined) chartType="col"

	console.log("=== sg-chart create "+chartId+" ===")
	console.log('Chart Title : '+chartTitle)
	console.log('Chart Type : '+chartType)


	var defaults={
					pointSize: 4,
					vAxis: {
						viewWindowMode: "explicit",
					},
					hAxis: {
						textStyle: {
							fontSize:10,
						}
					},
					annotations: {
						textStyle: {
							fontSize:9,
						},
					},
			};
	if ($container.data("series")==2) {
		defaults.series={
										0:{targetAxisIndex:0},
										1:{targetAxisIndex:1},
									}
	}
	var options = $.extend(defaults, $(this).data('options'));
	//options=$(this).data('options');
	//console.log(defaults);

	$.each($chartTable.find('tbody>tr'),function(i,eachRow){
		var $row=$(this)
		//console.log($row.text())
		var rowData=[]
		$.each($row.find('td'),function(j,eachCol){
			var $col=$(this)
			var colKey=$col.attr('class').split(':')
			if (i==0) chartColumn.push([colKey[0],colKey[1],colKey[2]==undefined?'':colKey[2]])
			var colValue
			if (colKey[0]=="string") colValue=$col.text()
			else colValue=Number($col.text().replace(/[^\d\.]/g,''))
			console.log($col.attr('class')+$col.text())
			rowData.push(colValue)
		})
		chartData.push(rowData)
		console.log(rowData)
	})
	console.log('Chart Data')
	console.log(chartData)
	console.log('Chart Column : '+chartColumn)

	google.charts.load("current", {"packages":["corechart"]});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		/*
		options = {
										pointSize: 4,
										vAxis: {
											viewWindowMode: "explicit",
										},
									};
		if ($container.data("series")==2) {
			options.series={
											0:{targetAxisIndex:0},
											1:{targetAxisIndex:1},
										}
		}
		if (chartType=="pie") {
			options = {
											legend: {position: "none"},
											// chartArea: {width:"100%",height:"80%"},
										};
			console.log($container.data("options"))
			if ($container.data("options")) options=$container.data("options");
			//options.legend="label";
			//options.pieSliceText="percent";
			//options.legend.position="labeled";
			//options.legend.position=$container.data("legendSeries")?$container.data("legendSeries"):"none";
											// chartArea: {width:"100%",height:"80%"},
		}
										*/
		options.title=chartTitle;
		console.log(options);
		var data = new google.visualization.DataTable();
		// Add chart column
		$.each(chartColumn,function(i){
			if (chartColumn[i][2]=='role') data.addColumn({type: chartColumn[i][0], role: 'annotation'});
			else data.addColumn(chartColumn[i][0],chartColumn[i][1]);
		})
		// Add chart rows
		data.addRows(chartData);

		var chartContainer=document.getElementById(chartId)
		var chart
		//var chart = new google.visualization.PieChart(chartContainer);
		if (chartType=="line") {
			chart = new google.visualization.LineChart(chartContainer);
		} else if (chartType=="bar") {
			chart = new google.visualization.BarChart(chartContainer);
		} else if (chartType=="col") {
			chart = new google.visualization.ColumnChart(chartContainer);
		} else if (chartType=="pie") {
			chart = new google.visualization.PieChart(chartContainer);
		} else if (chartType=="combo") {
			chart = new google.visualization.ComboChart(chartContainer);
		}
		if ($container.data("image")) {
			google.visualization.events.addListener(chart, 'ready', function () {
				var imgUri = chart.getImageURI();
				// do something with the image URI, like:
				document.getElementById($container.data("image")).src = imgUri;
			});
		}
		chart.draw(data, options);
	}
});
</code>