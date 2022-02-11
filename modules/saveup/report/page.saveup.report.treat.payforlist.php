<?php
/**
* Saveup :: Report Member Treat Pay For List
* Created 2017-04-08
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/treat/payforlist
*/

$debug = true;

function saveup_report_treat_payforlist($self) {
	$getPayFor = post('payfor');
	$getYear = SG\getFirst(post('year'),date('Y'));
	$getOrder = SG\getFirst(post('order'),'disease');

	$self->theme->title='ค่าสวัสดิการแยกประเภทค่าใช้จ่าย สำหรับ '.$getPayFor;

	$form = new Form([
		'action' => url('saveup/report/treat/payforlist'),
		'class' => '-inlineitem',
		'children' => [
			'payfor' => [
				'type'=>'text',
				'value' => htmlspecialchars($getPayFor),
				'placeholder'=>'จ่ายสำหรับ เช่น ยา',
			],
			'year' => [
				'type' => 'select',
				'options' => (function() {
					$yearList = mydb::select('SELECT DISTINCT YEAR(`date`) `year` FROM %saveup_treat% t ORDER BY `year` DESC')->items;
					$options = ['*' => '* ทุกปี *'];
					foreach ($yearList as $rs) $options[$rs->year] = 'พ.ศ.'.($rs->year+543);
					return $options;
				})(),
				'value' => $getYear
			],
			'go' => ['type'=>'button','value'=>'<i class="icon -material">search</i><span>Go</span>'],
		], // children
	]);
	$ret .= $form->build();



	if ($getYear == '*') {
	} else {
		mydb::where('YEAR(`date`) = :year', ':year', $getYear);
	}
	mydb::value('$ORDER$', $getOrder == 'disease' ? 'CONVERT('.$getOrder.' USING tis620) ASC' : $getOrder.' DESC');



	if (!$getPayFor) return $ret;

	mydb::where('t.`payfor` LIKE :payfor', ':payfor', '%'.$getPayFor.'%');
	if ($getYear != '*') mydb::where('YEAR(`date`) = :year', ':year', $getYear);

	$stmt = 'SELECT
			t.`tid`
		, CONCAT(m.`firstname`," ",m.`lastname`) `name`
		, t.`date`, t.`payfor`, t.`amount`
		FROM %saveup_treat% t
			LEFT JOIN %saveup_member% m USING (`mid`)
		%WHERE%
		ORDER BY CONVERT(`name` using tis620) ASC,`date` ASC';

	$dbs=mydb::select($stmt);



	$tables = new Table();
	$tables->caption=$self->theme->title;
	$tables->thead=array('วันที่','ชื่อ-สกุล','เพื่อเป็นค่า','money'=>'จำนวนเงิน');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->date,'ว ดด ปปปป'),$rs->name,$rs->payfor?$rs->payfor:'ไม่ระบุ',number_format($rs->amount,2));
		$total+=$rs->amount;
	}
	$tables->tfoot[]=array('','','รวมทั้งสิ้น',number_format($total,2));
	$ret .= $tables->build();
	return $ret;
}
?>