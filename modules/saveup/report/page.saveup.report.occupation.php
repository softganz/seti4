<?php
/**
* Saveup :: Report Member Occupation
* Created 2017-04-08
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/occupation
*/

$debug = true;

function saveup_report_occupation($self) {
	$self->theme->title='รายงานอาชีพสมาชิก';

	$detail=post('dt');
	$stmt='SELECT  `occupa`,
			count(*) AS amt
		FROM %saveup_member%
		WHERE status="active"
		GROUP BY  `occupa`
		ORDER BY `occupa` IS NULL, occupa ASC';
	$reports=mydb::select($stmt);

	$tables = new Table();
	$tables->addClass('saveup-report-main');
	$tables->caption=$self->theme->title;
	$tables->thead=array('label'=>'อาชีพ','amt'=>'จำนวนสมาชิก(คน)','');
	$total=$no=0;
	$pie->items[]=array('รายการ','จำนวน');
	foreach ($reports->items as $rs) {
		$label=$rs->occupa?$rs->occupa:'ไม่ระบุ';
		$tables->rows[]=array($label,$rs->amt,'<a href="'.url('saveup/report/occupation','dt='.($rs->occupa?$rs->occupa:'na')).'">รายละเอียด</a>');
		$total+=$rs->amt;
		$pie->items[]=array($label,intval($rs->amt));
	}
	$tables->tfoot[]=array('รวม',$total,'');

	$ret .= $tables->build();

	if ($detail!='') {
		$stmt = 'SELECT `mid`, `status`, CONCAT(`firstname`," ",`lastname`) name, `birth`, `date_regist`
			FROM %saveup_member%
			WHERE `status`="active" AND `occupa`=:occupa
			ORDER BY `name` ASC';
		$dbs=mydb::select($stmt,':occupa',$detail=='na'?'':$detail);

		$tables = new Table();
		$tables->addClass('saveup-report-detail');
		$tables->thead=array('no'=>'ลำดับ','ชื่อ-นามสกุล','date birth'=>'วันเกิด','mtype'=>กลุ่มสมาชิก,'date register'=>'วันที่เริ่มเป็นสมาชิก');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(++$no,
				'<a href="'.url('saveup/member/view/'.$rs->mid).'" target="_blank">'.$rs->name.'</a>',
				$rs->birth?sg_date($rs->birth,'ว ดด ปปปป'):'-',
				$rs->mtype,
				$rs->date_regist?sg_date($rs->date_regist,'ว ดด ปปปป'):''
			);
		}

		$ret .= $tables->build();
	} else {
		$data->title='รายงานกลุ่มสมาชิก';
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