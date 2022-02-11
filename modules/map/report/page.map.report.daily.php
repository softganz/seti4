<?php
function map_report_daily($self) {
	$getFrom = post('from');
	$getTo = post('to');

	$self->theme->title='จำนวนการปักหมุดในแต่ละวัน';
	$ret.='<form method="GET"><label for="from">จากวันที่</label>
<input type="text" id="from" name="from" class="form-text" value="'.$getFrom.'" />
<label for="to">ถึง </label><input type="text" id="to" name="to" class="form-text" value="'.$getTo.'" /> <button>ดูรายงาน</button></form>';

	if ($getFrom) mydb::where('`created` >= :from ',':from',sg_date($getFrom,'U'));
	if ($getTo) mydb::where('`created` <= :to ',':to',sg_date($getTo,'U'));

	$stmt='SELECT FROM_UNIXTIME(`created`, "%Y-%m-%d") `date`, COUNT(*) `amt`
					FROM %map_networks%
					%WHERE%
					GROUP BY `date` ORDER BY `date` ASC';

	$dbs=mydb::select($stmt);

	$lastDate=SG\getFirst($getTo,end($dbs->items)->date);
	$date=$firstDate=SG\getFirst($getFrom,reset($dbs->items)->date);

	$graph->title='จำนวนหมุด';
	$graph->items[]=array('วัน-เดือน-ปี','จำนวนหมุด');

	foreach ($dbs->items as $rs) $data[$rs->date]=$rs->amt;
	do {
		$graph->items[]=array(sg_date($date,'d-m'),SG\getFirst($data[$date],0));
		list($y,$m,$d)=explode('-', $date);
		$date=date('Y-m-d',mktime(0,0,0, intval($m), intval($d)+1, intval($y) ));
	} while ($date<=$lastDate);

	head('jspi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

	$ret.='<div id="chart_div" style="width: 100%; height: 600px;"></div>';
	$ret.='<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart() {
	var data = google.visualization.arrayToDataTable('.json_encode($graph->items).');
	var options = {title: "'.$graph->title.'",};
	var chart = new google.visualization.LineChart(document.getElementById("chart_div"));
	chart.draw(data, options);
}
$(function() {
	$("#from").datepicker({
		defaultDate: "-1m",
		changeMonth: true,
		numberOfMonths: 3,
		dateFormat: "yy-mm-dd",
		onClose: function( selectedDate ) {
			$("#to").datepicker( "option", "minDate", selectedDate );
		}
	});
	$("#to").datepicker({
		defaultDate: "-1m",
		changeMonth: true,
		numberOfMonths: 3,
		dateFormat: "yy-mm-dd",
		onClose: function( selectedDate ) {
			$( "#from" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
});
</script>';
	return $ret;
}
?>