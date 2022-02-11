<?php
/**
* Saveup :: Report Member No Cost
* Created 2017-04-08
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/nocost
*/

$debug = true;

function saveup_report_nocost($self) {
	$getYear = SG\getFirst(post('year'),date('Y'));
	$getOrder=SG\getFirst(post('order'),'mid');

	$self->theme->title='รายชื่อผู้ไม่เคยเบิกค่ารักษาพยาบาล '.($getYear == '*' ? 'ทุกปี' : 'ประจำปี '.($getYear+543));

	$yearList = mydb::select('SELECT DISTINCT YEAR(`date`) `year` FROM %saveup_treat% t ORDER BY `year` DESC')->items;

	$optionYear = array('*' => '* ทุกปี *');
	foreach ($yearList as $rs) $optionYear[$rs->year] = 'พ.ศ.'.($rs->year+543);

	$form = new Form([
		'action' => url('saveup/report/nocost'),
		'class' => '-inlineitem',
		'method' => 'GET',
		'children' => [
			'year' => ['type'=>'select', 'options'=>$optionYear,'value'=>$getYear],
			'order' => [
				'type'=>'select',
				'options' => [''=>'== เรียงลำดับ ==', 'mid'=>'รหัส','name'=>'ชื่อ'],
				'value'=>$getOrder,
			],
			'go' => [
				'type'=>'button',
				'value'=>'<i class="icon -material">search</i><span>Go</span>'
			],
		],
	]);
	$ret .= $form->build();


	mydb::where('m.`status` = "active"');

	mydb::value('$JOINCOND$', $getYear == '*' ? 'USING(`mid`)' : 'ON t.`mid` = m.`mid` AND YEAR(t.`date`) = '.$getYear);
	mydb::value('$ORDER$', $getOrder);

	$stmt = 'SELECT
		  m.`mid`
		, CONCAT(m.`firstname`," ",m.`lastname`) `name`
		, m.`date_approve`
		-- , amount
		, SUM(t.`amount`) `totals`
		-- , SUM(IF(t.`amount` > 0, t.`amount`, 0)) `totals`
		FROM %saveup_member% m
			LEFT JOIN %saveup_treat% t $JOINCOND$
		%WHERE%
		GROUP BY m.`mid`
		HAVING `totals` IS NULL OR `totals` = 0
		ORDER BY $ORDER$ ASC';

	$dbs = mydb::select($stmt,':year',$getYear);
	//$ret .= print_o($dbs,'$dbs');


	$tables = new Table();
	$tables->caption=$self->theme->title;
	$tables->thead=array('รหัส','ชื่อ-นามสกุล', 'date'=>'วันที่เริ่มเป็นสมาชิก');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->mid,
			$rs->name,
			$rs->date_approve ? sg_date($rs->date_approve, 'ว ดด ปปปป') : '',
		);
	}
	$tables->tfoot[]=array('','รวมทั้งสิ้น <strong>'.$dbs->_num_rows.'</strong> คน','');
	$ret .= $tables->build();
	return $ret;
}
?>