<?php
/**
* Module Method
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_in_pos($self, $jobInfo, $carType, $position) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'NO JOB');

	$ret = '';

	$damagecodeList = mydb::select('SELECT * FROM %garage_damage%')->items;
	$damagecodeOptions = '<option value="">???</option>';
	foreach ($damagecodeList as $v) {
		$damagecodeOptions .= '<option value="'.$v->damagecode.'" '.($jobTranInfo->damagecode == $v->damagecode?'selected="selected"':'').' data-pretext="'.$v->pretext.'">'.$v->damagecode.' : '.$v->damagename.'</option>';
	}

	$ret .= '<div class="sg-tabs" style="position: relative;">'
		. '<ul class="tabs">'
		. '<li class="-active"><a class="btn -link" href="#wage"><i class="icon -material">handyman</i><span>รายการสั่งซ่อม</span></a></li>'
		. '<li><a class="btn -link" href="#part"><i class="icon -material">agriculture</i><span>รายการอะไหล่</span></a></li>'
		. '</ul>'
		. '<nav class="nav -close"><a class="btn -link" onclick=\'$(this).closest(".sg-tabs").remove()\'><i class="icon -material">close</i></a></nav>';

	$stmt = 'SELECT *
		FROM %garage_carpos% cp
			LEFT JOIN %garage_repaircode% rc USING(`repairid`)
		WHERE (cp.`shopid` = 0 OR cp.`shopid` IN (:shopId))
			AND cp.`cartypeid` = :cartypeid AND cp.`position` = :position
			AND rc.`repairtype` = :repairtype';

	$dbs = mydb::select($stmt, ':cartypeid', $carType, ':position', $position, ':shopId', $jobInfo->shopInfo->branchId, ':repairtype', _GARAGE_REPAIR_DO);
	//$ret .= mydb()->_query;

	$ret .= '<div id="wage">';

	$ui = new Ui();
	$ui->header('<h3 class="-sg-text-center">รายการสั่งซ่อม</h3>');
	foreach ($dbs->items as $rs) {
		$ui->add('<form class="sg-form form -in-pos" action="'.url('garage/job/'.$jobId.'/info/tran.save').'" data-rel="none" data-done="remove:parent li | load->replace:.garage-in-form">'
			. '<input type="hidden" name="qty" value="1" />'
			. '<input type="hidden" name="repairid" value="'.$rs->repairid.'" />'
			. '<div class="form-item -group"><span class="form-group">'
			. '<input class="form-text -fill" type="text" name="repairname" value="'.htmlspecialchars($rs->repairname).'" data-src="'.htmlspecialchars($rs->repairname).'" />'
			. '<select class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select>'
			. '<div class="input-append"><span><button class="btn"><i class="icon -material">add_circle_outline</i></button></span></div>'
			. '</span></div>'
			. '</form>');
	}

	if ($dbs->_empty) $ui->add('<p class="notify">ไม่มีรายการสั่งซ่อมตำแหน่งนี้</p>');
	$ret .= $ui->build(true);

	$ret .= '<nav class="nav -code-add -sg-text-center"><a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/in.code/do/'.$carType.'/'.$position).'" data-rel="box" data-width="480" data-height="480"><i class="icon -material">add</i><span>เพิ่มรายการสั่งซ่อมที่ใช้บ่อยในตำแหน่ง <span>'.$position.'</span></span></a></nav>';
	$ret .= '</div>';

	$stmt = 'SELECT *
		FROM %garage_carpos% cp
			LEFT JOIN %garage_repaircode% rc USING(`repairid`)
		WHERE (cp.`shopid` = 0 OR cp.`shopid` IN (:shopId))
			AND cp.`cartypeid` = :cartypeid AND cp.`position` = :position
			AND rc.`repairtype` = :repairtype';

	$dbs = mydb::select($stmt, ':cartypeid', $carType, ':position', $position, ':shopId', $jobInfo->shopInfo->branchId, ':repairtype', _GARAGE_REPAIR_PART);
	//$ret .= mydb()->_query;

	$ret .= '<div id="part" class="-hidden">';

	$tables = new Table();
	$tables->thead = array('title -fill' => 'รายการอะไหล่','qty -amt' => 'จำนวน');

	$qtyOptions = '';
	for ($i=1; $i <= 20; $i++) $qtyOptions .= '<option value="'.$i.'">'.$i.'</option>';

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->repairname,
			'<form class="sg-form" action="'.url('garage/job/'.$jobId.'/info/tran.save').'" data-rel="none" data-done="remove:parent tr | load->replace:.garage-in-form">'
			. '<input type="hidden" name="repairid" value="'.$rs->repairid.'" />'
			. '<div class="form-item -group"><span class="form-group"><select class="form-select" name="qty">'.$qtyOptions.'</select>'
			. '<div class="input-append"><span><button class="btn"><i class="icon -material">add_circle_outline</i></button></span></div>'
			. '</span></div>'
			. '</form>',
		);
	}


	$ret .= $tables->build();

	if ($dbs->_empty) $ret .= '<p class="notify">ไม่มีรายการอะไหล่ตำแหน่งนี้</p>';

	$ret .= '<nav class="nav -code-add -sg-text-center"><a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/in.code/part/'.$carType.'/'.$position).'" data-rel="box" data-width="480" data-height="480"><i class="icon -material">add</i><span>เพิ่มรายการอะไหล่ที่ใช้บ่อยตำแหน่ง <span>'.$position.'</span></span></a></nav>';

	$ret .= '</div>';

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($jobInfo,'$jobInfo');
	return $ret;
}
?>