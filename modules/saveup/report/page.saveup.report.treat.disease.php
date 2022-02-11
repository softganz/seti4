<?php
/**
* Saveup :: Report Treat Disease
* Created 2017-04-08
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/treat/disease
*/

$debug = true;

function saveup_report_treat_disease($self) {
	$getYear = SG\getFirst(post('year'),date('Y'));
	$getOrder=SG\getFirst(post('order'),'disease');

	$self->theme->title='ค่าสวัสดิการแยกตามโรค '.($getYear == '*' ? 'ทุกปี' : 'ประจำปี '.($getYear+543));


	$yearList = mydb::select('SELECT DISTINCT YEAR(`date`) `year` FROM %saveup_treat% t ORDER BY `year` DESC')->items;

	$optionYear = array('*' => '* ทุกปี *');
	foreach ($yearList as $rs) $optionYear[$rs->year] = 'พ.ศ.'.($rs->year+543);

	$form = new Form([
		'action' => url('saveup/report/treat/disease'),
		'class' => '-inlineitem',
		'children' => [
			'year' => ['type'=>'select', 'options'=>$optionYear,'value'=>$getYear],
			'order' => [
				'type'=>'select',
				'options'=> [''=>'== เรียงลำดับ ==', 'disease'=>'โรค','total'=>'จำนวนเงิน'],
				'value'=>$getOrder
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

	$stmt = 'SELECT disease,sum(amount) AS total
		FROM %saveup_treat%
		%WHERE%
		GROUP BY disease
		ORDER BY $ORDER$';
	$dbs=mydb::select($stmt);


	$tables = new Table();
	$tables->caption=$self->theme->title;
	$tables->thead=array('โรค','money'=>'จำนวนเงิน');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->disease?$rs->disease:'ไม่ระบุ',number_format($rs->total,2));
		$total+=$rs->total;
	}
	$tables->tfoot[]=array('รวมทั้งสิ้น',number_format($total,2));

	$ret .= $tables->build();
	return $ret;
}
?>