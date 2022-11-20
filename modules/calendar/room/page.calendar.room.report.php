<?php
/**
* Calendar Room Report
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_room_report($self) {
	R::View('calendar.toolbar',$self, 'รายงาน');

	$roomProperty = property('calendar.room');

	$from=SG\getFirst($_REQUEST['from'],date('01/01/Y'));
	$to=SG\getFirst($_REQUEST['to'],date('d/m/Y'));
	$roomid=$_REQUEST['roomid'];
	$orgname=$_REQUEST['orgname'];
	$ret.='<form class="report-form" id="report-disease">';
	$ret.='<div class="form-item" style="width:90%;float:left;">';
	$ret.='<label for="from">ตั้งแต่วันที่</label>
	<input type="text" id="from" name="from" class="form-text sg-datepicker" value="'.$from.'" />
	<label for="to">ถึง</label>
	<input type="text" id="to" name="to" class="form-text sg-datepicker" value="'.$to.'" />';
	$ret.='<label>ห้องประชุม : </label><select name="roomid" id="roomid" class="form-select"><option value="">ทุกห้อง</option><optgroup label="เลือกห้องประชุม">';
	foreach (model::get_taxonomy_tree($roomProperty['roomvid']) as $term) {
		$ret.='<option value="'.$term->tid.'"'.($term->tid==$roomid?' selected="selected"':'').'>'.$term->name.'</option>';
	}
	$ret.='</optgroup></select>';
	$ret.='<label>หน่วยงาน : </label><select name="orgname" id="orgname" class="form-select"><option value="">ทุกหน่วยงาน</option><optgroup label="เลือกหน่วยงาน">';
	foreach (model::get_taxonomy_tree(cfg('calendar.room.vid.org')) as $term) {
		$ret.='<option value="'.$term->name.'"'.($term->name==$orgname?' selected="selected"':'').'>'.$term->name.'</option>';
	}
	$ret.='</optgroup></select>';
	$ret.='</div><div class="form-item" style="width:10%;float:right;"><input type="submit" class="button" value="ดูรายงาน" style="display:block;width:100%;font-size:1.3em;" /></div><br clear="all" />';
	$ret.='</form>';

	mydb::where('checkin BETWEEN :from AND :to',':from',sg_date($from,'Y-m-d'), ':to',sg_date($to,'Y-m-d'));
	if ($roomid) mydb::where('`roomid` = :roomid',':roomid',$roomid);
	if ($orgname) mydb::where('`org_name` = :orgname',':orgname',$orgname);

	$stmt = 'SELECT
						`checkin`
					, DATE_FORMAT(`checkin`,"%Y-%m") `month`
					, YEAR(`checkin`) `year`
					, roomid
					, org_name
					, COUNT(*) amt
					FROM %calendar_room% r
					%WHERE%
					GROUP BY `month`
					ORDER BY checkin ASC';

	$dbs=mydb::select($stmt);

	$ret.='<style type="text/css">
	.widget-table td {text-align:center;}
	.widget-table>tfoot>tr>td {font-weight:bold;background:#DDDDDD;}
	.form-item label {display:inline-block;}
	.form-text {width:80px;}
	.report-form {margin:0;padding:5px;border-radius:4px;background:#EDEDED;}
	</style>';

	if ($dbs->_empty) return $ret.message('error','ไม่มีข้อมูลตามเงื่อนไขที่กำหนด');

	$ret.='<div id="chart_div" style="height:400px;"></div>';

	$data->title='รายงานการใช้ห้องประชุม';
	$ghead[]='เดือน-ปี';
	$ghead[]='จำนวนครั้ง';
	$data->items[]=$ghead;

	$startDate = strtotime(sg_date($from,'Y-m-d'));
	$endDate   = strtotime(sg_date($to,'Y-m-d'));

	$currentDate = $endDate;

	while ($currentDate >= $startDate) {
		$months[date('Y-m',$currentDate)]=0;
		$currentDate = strtotime( date('Y/m/01/',$currentDate).' -1 month');
	}
	ksort($months);
	foreach ($dbs->items as $rs) $months[$rs->month]=$rs->amt;

	$tables = new Table();
	$tables->caption='รายงานการใช้ห้องประชุม';
	$tables->thead=array('เดือน-ปี','amt times'=>'จำนวนครั้ง');
	foreach ($months as $k=>$v) {
		$tables->rows[]=array(sg_date($k.'-01','ดดด ปปปป'),number_format($v));
		$total+=$v;
		unset($gdata);
		$gdata[]=sg_date($k.'-01','ดด ปป');
		$gdata[]=intval($v);
		$data->items[]=$gdata;
	}
	$tables->tfoot[]=array('รวมทั้งสิ้น',number_format($total));

	$ret .= $tables->build();

	//		$ret.=print_o($months,'$months');
	//		$ret.=print_o($dbs,'$dbs');
	//		$ret.=print_o($where,'$where');

	head('<script type="text/javascript" src="https://www.google.com/jsapi" charset="utf-8"></script>');

	$ret.='<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable('.json_encode($data->items).');
		var options = {title: "'.$data->title.'",
								hAxis: {title: "เดือน-ปี", titleTextStyle: {color: "black"}},
								vAxis: {title: "จำนวน (ครั้ง)"},
				};
		var chart = new google.visualization.ColumnChart(document.getElementById("chart_div"));
		chart.draw(data, options);
	}
	</script>';
	return $ret;
}
?>