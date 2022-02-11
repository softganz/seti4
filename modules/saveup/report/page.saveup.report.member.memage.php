<?php
/**
* Saveup :: Report Member Group By Birthday Age
* Created 2017-04-08
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/member/memage
*/

$debug = true;

function saveup_report_member_memage($self) {
	$self->theme->title='รายงานช่วงอายุการเป็นสมาชิก';

	$stmt='SELECT YEAR(NOW())-YEAR(date_approve) groupage,
			count(*) AS amt
		FROM %saveup_member%
		WHERE status="active"
		GROUP BY YEAR(NOW())-YEAR(date_approve)
		ORDER BY `groupage` IS NULL, groupage ASC';

	$reports=mydb::select($stmt);

	$tables = new Table();
	$tables->addClass('saveup-report-main');
	$tables->caption=$self->theme->title;
	$tables->thead=array('amt age'=>'อายุการเป็นสมาชิก(ปี)','amt'=>'จำนวนสมาชิก(คน)','');
	$total=$no=0;
	$pie->items[]=array('รายการ','จำนวน');
	foreach ($reports->items as $rs) {
		$label=is_numeric($rs->groupage)?($rs->groupage):'ไม่ระบุ';
		$tables->rows[]=array($label,$rs->amt,'<a href="'.url('saveup/report/member/memage','age='.(is_numeric($rs->groupage)?$rs->groupage:'na')).'">รายละเอียด</a>');
		$pie->items[]=array($label,intval($rs->amt));
		$total+=$rs->amt;
	}
	$tables->tfoot[]=array('รวม',$total,'');

	$ret .= $tables->build();

	if ($_REQUEST['age']!='') {
		$age=$_REQUEST['age'];
		$stmt='SELECT `mid`, `status`, CONCAT(`firstname`," ",`lastname`) name, `birth`, `date_approve` FROM %saveup_member%
						WHERE `status`="active" AND '.($age=='na'?'`date_approve` IS NULL':'YEAR(NOW())-YEAR(date_approve)=:age').'
						ORDER BY `date_approve` ASC';
		$dbs=mydb::select($stmt,':age',$_REQUEST['age']);

		$tables = new Table();
		$tables->addClass('saveup-report-detail');
		$tables->thead=array('no'=>'ลำดับ','เลขที่สมาชิก','ชื่อ-นามสกุล','date register'=>'วันที่เริ่มเป็นสมาชิก', 'date birth'=>'วันเกิด', 'amt age'=>'อายุ(ปี)');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(++$no, $rs->mid,
				$rs->name,
				$rs->date_approve?sg_date($rs->date_approve,'ว ดด ปปปป'):'-',
				$rs->birth?sg_date($rs->birth,'ว ดด ปปปป'):'-',
				$rs->birth?date('Y')-sg_date($rs->birth,'Y'):'-',
			);
		}
		$ret .= $tables->build();
	} else {
		$data->title='รายงานอายุการเป็นสมาชิก';
		$ghead[]='พื้นที่';
		$data->items[]=$ghead;
		$graphType='col';
		$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');

		head('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

		$ret.='<div class="saveup-report-detail"><div id="chart_div"></div>';
		$ret.='</div>';
		$ret.='
<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart() {
var data = google.visualization.arrayToDataTable('.json_encode($pie->items).');
	var options = {title: "'.$data->title.'",};

var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart_div"));
chart.draw(data, options);
}
</script>';
	}
	return $ret;
}
?>