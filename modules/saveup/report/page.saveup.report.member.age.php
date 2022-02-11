<?php
/**
* Saveup :: Report Member Age
* Created 2018-05-16
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/member/age
*/

$debug = true;

function saveup_report_member_age($self) {
	// Data Model
	$getAge = post('age');

	if ($getAge) {
		mydb::where('`status` = "active"');
		if ($getAge == 'ไม่ระบุ') mydb::where('`birth` IS NULL');
		else if ($getAge == '1-9 Alpha') mydb::where('YEAR(NOW())-YEAR(birth) BETWEEN 0 AND 9');
		else if ($getAge == '10-24 Gen Z') mydb::where('YEAR(NOW())-YEAR(birth) BETWEEN 10 AND 24');
		else if ($getAge == '25-39 Gen Y') mydb::where('YEAR(NOW())-YEAR(birth) BETWEEN 25 AND 39');
		else if ($getAge == '40-54 Gen X') mydb::where('YEAR(NOW())-YEAR(birth) BETWEEN 40 AND 54');
		else if ($getAge == '55-72 Baby Boomer') mydb::where('YEAR(NOW())-YEAR(birth) BETWEEN 55 AND 72');
		else if ($getAge == '73  Builder') mydb::where('YEAR(NOW())-YEAR(birth) >= 73');
		//mydb::where($getAge == 'na' ? '`birth` IS NULL' : 'FLOOR((YEAR(NOW())-YEAR(birth))/5)=:age');

		$stmt = 'SELECT
			`mid`, `status`, CONCAT(`firstname`," ",`lastname`) name
			, `birth`, `date_regist`
			FROM %saveup_member%
			%WHERE%
			ORDER BY `birth` ASC';
		$dbs = mydb::select($stmt, ':age', $getAge);

		$tables = new Table();
		$tables->addClass('saveup-report-detail');
		$tables->thead=array('no'=>'ลำดับ','เลขที่สมาชิก','ชื่อ-นามสกุล','date birth'=>'วันเกิด','amt age'=>'อายุ(ปี)','date register'=>'วันที่เริ่มเป็นสมาชิก');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(++$no, $rs->mid,
				$rs->name,
				$rs->birth?sg_date($rs->birth,'ว ดด ปปปป'):'-',
				$rs->birth?date('Y')-sg_date($rs->birth,'Y'):'-',
				$rs->date_regist?sg_date($rs->date_regist,'ว ดด ปปปป'):''
			);
		}
		$ret .= $tables->build();
		return $ret;
	} 

	mydb::value('$LABEL$', 'FLOOR(`age` / 5)');
	mydb::value('$LABEL$', 'CASE
		WHEN `age` <= 9 THEN " 1-9 Alpha"
		WHEN `age` <= 24 THEN "10-24 Gen Z"
		WHEN `age` <= 39 THEN "25-39 Gen Y"
		WHEN `age` <= 54 THEN "40-54 Gen X"
		WHEN `age` <= 72 THEN "55-72 Baby Boomer"
		WHEN `age` <= 200 THEN "73+ Builder"
		ELSE NULL
		END', false);

	mydb::where('status = "active"');
	$stmt = 'SELECT
		$LABEL$ `groupage`
		, COUNT(*) AS `amt`
		FROM
			(SELECT YEAR(NOW()) - YEAR(`birth`) `age`
			FROM %saveup_member%
			WHERE `status` = "active") a
		GROUP BY `groupage`
		ORDER BY `groupage` IS NULL, `groupage` DESC';

	$reports = mydb::select($stmt);
	//debugMsg(mydb()->_query);


	// View Model
	$self->theme->title = 'รายงานช่วงอายุของสมาชิก';


	$tables = new Table();
	$tables->addClass('saveup-report-main');
	$tables->caption = $self->theme->title;
	$tables->thead = array(
		'age -amt'=>'ช่วงอายุ(ปี)',
		'total -amt'=>'จำนวนสมาชิก(คน)',
		'view -center' => '',
	);
	$total = $no = 0;

	$pie->items[] = array('รายการ','จำนวน');

	foreach ($reports->items as $rs) {
		$label = SG\getFirst($rs->groupage, 'ไม่ระบุ'); //is_numeric($rs->groupage)?($rs->groupage*5).' - '.($rs->groupage*5+4):'ไม่ระบุ';
		$tables->rows[] = array(
			$label,
			$rs->amt,
			'<a class="sg-action btn -link" href="'.url('saveup/report/member/age',array('age' => $label)).'" data-rel="box" data-width="640">รายชื่อ</a>'
		);

		$total += $rs->amt;
		$pie->items[] = array($label,intval($rs->amt));
	}

	$tables->tfoot[] = array('รวม',$total,'');

	$ret .= $tables->build();


	$data->title = 'รายงานช่วงอายุของสมาชิก';
	$ghead[] = 'พื้นที่';
	$data->items[] = $ghead;
	$graphType = 'col';
	$chartTypes = array(
		'bar'=>'BarChart',
		'pie'=>'PieChart',
		'col'=>'ColumnChart',
		'line'=>'LineChart'
	);

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

	return $ret;
}
?>