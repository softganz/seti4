<?php
/**
* Calendar :: Room Report
* Created  :: 2019-08-03
* Modify   :: 2022-12-05
* Version  :: 2
*
* @return Widget
*
* @usage calendar/room/report
*/

class CalendarRoomReport extends Page {

	function build() {
		$roomProperty = property('calendar.room');

		$from=SG\getFirst($_REQUEST['from'],date('01/01/Y'));
		$to=SG\getFirst($_REQUEST['to'],date('d/m/Y'));
		$roomid=$_REQUEST['roomid'];
		$orgname=$_REQUEST['orgname'];
		$ret.='<form class="form-report" id="report-disease">';
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
		$ret.='<button type="submit" class="btn -primary"><i class="icon -material">search</i><span>ดูรายงาน</span></button></div><br clear="all" />';
		$ret.='</form>';

		mydb::where('checkin BETWEEN :from AND :to',':from',sg_date($from,'Y-m-d'), ':to',sg_date($to,'Y-m-d'));
		if ($roomid) mydb::where('`roomid` = :roomid',':roomid',$roomid);
		if ($orgname) mydb::where('`org_name` = :orgname',':orgname',$orgname);

		$dbs = mydb::select(
			'SELECT
				`checkin`
			, DATE_FORMAT(`checkin`,"%Y-%m") `month`
			, YEAR(`checkin`) `year`
			, roomid
			, org_name
			, COUNT(*) amt
			FROM %calendar_room% r
			%WHERE%
			GROUP BY `month`
			ORDER BY checkin ASC'
		);

		if ($dbs->_empty) return $ret.message('error','ไม่มีข้อมูลตามเงื่อนไขที่กำหนด');

		// $ret.='<div id="chart_div" style="height:400px;"></div>';

		// $data->title='รายงานการใช้ห้องประชุม';
		// $ghead[]='เดือน-ปี';
		// $ghead[]='จำนวนครั้ง';
		// $data->items[]=$ghead;

		$startDate = strtotime(sg_date($from,'Y-m-d'));
		$endDate   = strtotime(sg_date($to,'Y-m-d'));

		$currentDate = $endDate;

		while ($currentDate >= $startDate) {
			$months[date('Y-m',$currentDate)]=0;
			$currentDate = strtotime( date('Y/m/01/',$currentDate).' -1 month');
		}
		ksort($months);
		foreach ($dbs->items as $rs) $months[$rs->month]=$rs->amt;

		// foreach ($months as $k=>$v) {
		// 	$tables->rows[]=array(sg_date($k.'-01','ดดด ปปปป'),number_format($v));
		// 	$total+=$v;
		// 	unset($gdata);
		// 	$gdata[]=sg_date($k.'-01','ดด ปป');
		// 	$gdata[]=intval($v);
		// 	$data->items[]=$gdata;
		// }

		//		$ret.=print_o($months,'$months');
		//		$ret.=print_o($dbs,'$dbs');
		//		$ret.=print_o($where,'$where');

		// head('<script type="text/javascript" src="https://www.google.com/jsapi" charset="utf-8"></script>');

		// $ret.='<script type="text/javascript">
		// google.load("visualization", "1", {packages:["corechart"]});
		// google.setOnLoadCallback(drawChart);
		// function drawChart() {
		// 	var data = google.visualization.arrayToDataTable('.json_encode($data->items).');
		// 	var options = {title: "'.$data->title.'",
		// 							hAxis: {title: "เดือน-ปี", titleTextStyle: {color: "black"}},
		// 							vAxis: {title: "จำนวน (ครั้ง)"},
		// 			};
		// 	var chart = new google.visualization.ColumnChart(document.getElementById("chart_div"));
		// 	chart.draw(data, options);
		// }
		// </script>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Calendar Room Report',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$ret,

					new Table([
						'class' => '-center',
						'caption' => 'รายงานการใช้ห้องประชุม',
						'thead' => ['เดือน-ปี','amt times'=>'จำนวนครั้ง'],
						'children' => array_map(
							function($value, $key) use($total) {
								$total += $value;
								return [sg_date($key.'-01','ดดด ปปปป'), number_format($value)];
							}
							, $months, array_keys($months)
						),
						'tfoot' => [
							['รวมทั้งสิ้น',number_format($total)],
						],
					]), // Table

				], // children
			]), // Widget
		]);
	}
}
?>