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

function garage_job_in_code($self, $jobInfo, $repairType, $carTypeId, $position) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'NO JOB');

	if ($repairType == 'do') $repairTypeId = _GARAGE_REPAIR_DO;
	else if ($repairType == 'part') $repairTypeId = _GARAGE_REPAIR_PART;

	$ret = '<header class="header">'._HEADER_BACK.'<h3>รายการสั่งซ่อมที่ใช้บ่อยในตำแหน่ง '.$position.'</h3></header>';

	$form = new Form(NULL, url('garage/info/'.$carTypeId.'/in.code.save/'.$position),'in-code', 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'javascript: $(\'#in-code #edit-repairname\').val(\'\'); $(\'#in-code #edit-repairid\').val(\'\') | load:parent:'.url('garage/job/'.$jobId.'/in.code/'.$repairType.'/'.$carTypeId.'/'.$position).' | load:#pos-select:'.url('garage/job/'.$jobId.'/in.pos/'.$carTypeId.'/'.$position));
	$form->addAttr('style', 'padding: 0 8px;');

	$form->addField('repairid', array('type' => 'hidden', 'value' => ""));

	$form->addField(
		'repairname',
		array(
			'type' => 'text',
			'name' => false,
			'class' => 'sg-autocomplete -fill',
			'attr' => array(
				'data-query' => url('garage/api/repaircode', array('type' => $repairTypeId)),
				'data-altfld' => "edit-repairid",
				//'data-callback' => 'submit',
			),
			'value' => htmlspecialchars($insuName),
			'placeholder' => 'ค้นรายการสั่งซ่อม',
			'pretext' => '<div class="input-prepend">'
				. '<span><a class="btn" href="javascript:void(0)" onclick=\'$("#in-code #edit-repairid").val("");$("#in-code #edit-repairname").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
				. '</div>',
			'posttext' => '<div class="input-append">'
				. '<span class="-primary"><button class="btn -primary"><i class="icon -material">add</i><span>เพิ่ม</span></button></span>'
				. '</div>',
			'container' => '{class: "-group", style: "padding: 8px 0;"}',
		)
	);

	//$form->addField('go',array('type'=>'button','value'=>'<i class="icon -material">add</i>','container'=>'{class: "hidden"}'));

	$ret .= $form->build();


	$stmt = 'SELECT cp.*, rc.`repaircode`, rc.`repairname`
		FROM %garage_carpos% cp
			LEFT JOIN %garage_repaircode% rc USING(`repairid`)
		WHERE (cp.`shopid` = 0 OR cp.`shopid` IN (:shopId)) AND cp.`cartypeid` = :cartypeid AND cp.`position` = :position AND rc.`repairtype` = :repairtype;';

	$dbs = mydb::select($stmt, ':cartypeid', $carTypeId, ':position', $position, ':shopId', $jobInfo->shopInfo->branchId, ':repairtype', $repairTypeId);
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead = array('code -nowrap' => 'รหัส','title -fill -hover-parent' => 'รายการสั่งซ่อม');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->repaircode,
			$rs->repairname
			.'<nav class="nav -icons -hover">'
			. ($rs->shopid > 0 ? '<a class="sg-action btn -link" href="'.url('garage/info/'.$carTypeId.'/in.code.remove/'.$position, array('repairid' => $rs->repairid)).'" data-rel="notify" data-done="remove:parent tr | load:#pos-select:'.url('garage/job/'.$jobId.'/in.pos/'.$carTypeId.'/'.$position).'"><i class="icon -material">cancel</i></a>' : '')
			. '</nav>',
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>