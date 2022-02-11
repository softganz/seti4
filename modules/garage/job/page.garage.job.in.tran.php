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

function garage_job_in_tran($self, $jobInfo, $carInId) {
	$jobId = $jobInfo->tpid;
	$shopInfo = $jobInfo->shopInfo;
	$isEditable = R::Model('garage.right', $shopInfo, 'carin') || $bigData->ucreated == i()->uid;

	$ret = '';

	$carInCode = array();
	$inputDetail = NULL;

	$stmt = 'SELECT
		t.*
		, c.`repairtype`
		, c.`repairname`
		, tr.`jobtrid`
		, tr.`damagecode`
		, tr.`qty`
		, tr.`description`
		FROM %garage_jobtemplatetr% t
			LEFT JOIN %garage_repaircode% c USING(`repairid`)
			LEFT JOIN %garage_jobtr% tr ON tr.`tpid` = :jobId AND tr.`repairid` = t.`repairid`
		WHERE `templateid` = :templateid
		UNION
		SELECT
		j.`shopid`, NULL, tr.`repairid`, NULL, tr.`sorder`
		, c.`repairtype`
		, c.`repairname`
		, tr.`jobtrid`
		, tr.`damagecode`
		, tr.`qty`
		, tr.`description`
		FROM %garage_jobtr% tr
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_repaircode% c USING(`repairid`)
		WHERE tr.`tpid` = :jobId AND (tr.`repairid` NOT IN (SELECT `repairid` FROM %garage_jobtemplatetr% WHERE `templateid` = :templateid AND `shopid` = :shopid))
		ORDER BY `repairtype`,`sorder` ASC';

	$stmt = 'SELECT
		j.`shopid`, NULL, tr.`repairid`, NULL, tr.`sorder`
		, c.`repairtype`
		, c.`repairname`
		, tr.`jobtrid`
		, tr.`damagecode`
		, tr.`qty`
		, tr.`description`
		FROM %garage_jobtr% tr
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_repaircode% c USING(`repairid`)
		WHERE tr.`tpid` = :jobId
		ORDER BY `repairtype`,`sorder` ASC';

	$templateList = mydb::select($stmt, ':jobId', $jobId, ':templateid', $jobInfo->templateid, ':shopid', $shopInfo->shopid)->items;

	//$ret .= mydb()->_query;


	foreach ($templateList as $key => $rs) {
		if ($rs->inputdetail) {
			$inputDetail = $rs;
			if (!$rs->jobtrid) unset($templateList[$key]);
		}
	}

	if ($inputDetail) {
		$templateList[] = (Object) array(
			'repairid' => $inputDetail->repairid,
			'repairname' => $rs->repairname,
			'inputdetail' => 'YES',
			'forinput' => 'YES',
		);
	}

	//debugmsg($templateList, '$templateList');
	//debugMsg($inputDetail, '$inputDetail');

	$form = new Form(NULL, url('garage/job/'.$jobId.'/in'), NULL, 'sg-form garage-in-form');
	$form->addData('rel', 'notify');
	$form->addData('url', url('garage/job/'.$jobId.'/in.tran'));
	$form->addData('done', 'reload:'.url('garage/in'));
	$form->addConfig('title', '<i class="icon -material">verified</i>รายการความเสียหาย');
	$form->addField('cartype', array('type' => 'hidden', 'value' => 'SALOON'));


	$tables = new Table();
	$tables->addId('garage-in-form-item');
	$tables->addClass('garage-in-form-item');
	$tables->addConfig('showHeader', false);
	$tables->thead = array(
		'รายการสั่งซ่อม',
		'qty -amt' => 'จำนวน',
		'a -center' => 'รหัส',
		'e -center' => 'แก้ไข',
		'i -center' => '',
	);

	if (empty($templateList)) $form->addText('<p class="notify" style="margin: 32px 0; padding: 16px;">ยังไม่มีรายการ</p>');

	$showRepairType1 = $showRepairType2 = false;
	foreach ($templateList as $rs) {
		if ($rs->repairtype == 1 && !$showRepairType1) {
			$tables->rows[] = '<header>';
			$showRepairType1 = true;
		}
		if ($rs->repairtype == 2 && !$showRepairType2) {
			$tables->rows[] = array('<th colspan="">รายการอะไหล่</th><th>จำนวน</th><th></th><th></th><th></th>');
			$showRepairType2 = true;
		}

		$tables->rows[] = array(
			SG\getFirst($rs->description,$rs->repairname),
			$rs->qty,
			$rs->repairtype == 1 ? $rs->damagecode : '',
			$rs->jobtrid ? '<span class="inline-edit-item '.($notCodeA2D ? ' -active' : '').'"><label><a class="sg-action" href="'.url('garage/job/'.$jobId.'/in.tran.detail/'.$rs->jobtrid).'" data-rel="box" data-width="340"><i class="icon -material">grading</i></a></label></span>' : '',
			'<label><a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.delete/'.$rs->jobtrid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">cancel</i></a></label>',
			'config' => array(
				'class' => '-type-'.$rs->repairtype.' '
					. ($rs->forinput ? '-forinput' : ''),
			),
		);
	}

	$form->addText($tables->build());




	// Old Version
	$tables = new Table();
	$tables->addId('garage-in-form-item');
	$tables->addClass('garage-in-form-item');
	$tables->addConfig('showHeader', false);
	$tables->thead = array(
		'รายการสั่งซ่อม',
		'qty -amt' => 'จำนวน',
		'a -center' => 'A',
		'b -center' => 'B',
		'c -center' => 'C',
		'd -center' => 'D',
		'o -center' => 'อื่นๆ',
		'e -center' => 'แก้ไข',
		'',
	);

	$no = 0;

	if (empty($templateList)) $form->addText('<p class="notify" style="margin: 32px 0; padding: 16px;">ยังไม่มีรายการ</p>');

	$showRepairType1 = $showRepairType2 = false;
	foreach ($templateList as $rs) {
		++$no;
		$notCodeA2D = $rs->damagecode && !in_array($rs->damagecode, array('A','B','C','D'));

		if ($rs->repairtype == 1 && !$showRepairType1) {
			$tables->rows[] = '<header>';
			$showRepairType1 = true;
		}
		if ($rs->repairtype == 2 && !$showRepairType2) {
			$tables->rows[] = array('<th colspan="">รายการอะไหล่</th><th>จำนวน</th><th colspan="5"></th><th></th>');
			$showRepairType2 = true;
		}

		$tables->rows[] = array(
			$rs->inputdetail ? view::inlineedit(
				array(
					'group' => 'in:'.$no,
					'name' => 'description',
					'tr' => $rs->jobtrid,
					'repairid' => $rs->repairid,
					'repairname' => $rs->repairname,
					'qty' => $rs->qty,
					'callback' => 'garageJobInDetailCallback',
					'options' => '{var: "description", class: "-fill", placeholder: "ระบุ", xcallback: "garageJobInDetailCallback"}',
				),
				$rs->description,
				$isEditable,
				'text'
			) : SG\getFirst($rs->description,$rs->repairname),
			$rs->qty,
			$rs->repairtype == 2 ? ''
			:
			view::inlineedit(
				array(
					'group' => 'in:'.$no,
					'carId' => $carInId,
					'name' => $no,
					'tr' => $rs->jobtrid,
					'repairid' => $rs->repairid,
					'repairname' => $rs->repairname,
					'qty' => SG\getFirst($rs->qty,1),
					'damagecode' => 'A',
					'value' => $rs->damagecode,
					'options' => '{onBefore: "garageInFormBefore", callback: "garageJobInRadioCallback"}',
				),
				'A:<i class="icon -material">check_circle</i>',
				$isEditable,
				'radio'
			),
			view::inlineedit(
				array(
					'group' => 'in:'.$no,
					'carId' => $carInId,
					'name' => $no,
					'tr' => $rs->jobtrid,
					'repairid' => $rs->repairid,
					'repairname' => $rs->repairname,
					'qty' => SG\getFirst($rs->qty,1),
					'damagecode' => 'B',
					'value' => $rs->damagecode,
					'options' => '{callback: "garageJobInRadioCallback"}',
				),
				'B:<i class="icon -material">check_circle</i>',
				$isEditable,
				'radio'
			),
			view::inlineedit(
				array(
					'group' => 'in:'.$no,
					'carId' => $carInId,
					'name' => $no,
					'tr' => $rs->jobtrid,
					'repairid' => $rs->repairid,
					'repairname' => $rs->repairname,
					'qty' => SG\getFirst($rs->qty,1),
					'damagecode' => 'C',
					'value' => $rs->damagecode,
					'options' => '{callback: "garageJobInRadioCallback"}',
				),
				'C:<i class="icon -material">check_circle</i>',
				$isEditable,
				'radio'
			),
			view::inlineedit(
				array(
					'group' => 'in:'.$no,
					'carId' => $carInId,
					'name' => $no,
					'tr' => $rs->jobtrid,
					'repairid' => $rs->repairid,
					'repairname' => $rs->repairname,
					'qty' => SG\getFirst($rs->qty,1),
					'damagecode' => 'D',
					'value' => $rs->damagecode,
					'options' => '{callback: "garageJobInRadioCallback"}',
				),
				'D:<i class="icon -material">check_circle</i>',
				$isEditable,
				'radio'
			),
			'<span class="inline-edit-item '.($notCodeA2D ? ' -active' : '').'"><label><i class="icon -material">check_circle</i>'.$rs->damagecode.'</label></span>',
			$rs->jobtrid ? '<span class="inline-edit-item '.($notCodeA2D ? ' -active' : '').'"><label><a class="sg-action" href="'.url('garage/job/'.$jobId.'/in.tran.detail/'.$rs->jobtrid).'" data-rel="box" data-width="340"><i class="icon -material">grading</i></a></label></span>' : '',
			'<label><a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.delete/'.$rs->jobtrid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">cancel</i></a></label>',
			'config' => array(
				'data-rowid' => $no,
				'class' => '-type-'.$rs->repairtype.' -show-code '
					. ($rs->forinput ? '-forinput' : ''),
			),
		);

		/*
		$tables->rows[] = array(
			$rs->repairname,
			'<div class="form-item'.($carInCode[$rs->repairid] == 'A' ? ' -active"' : '' ).'"><label><input class="code" type="radio" name="code['.$rs->repairid.']" value="A"'.($carInCode[$rs->repairid] == 'A' ? ' checked="checked"' : '' ).' /><i class="icon -material">check_circle</i></label></div>',
			'<div class="form-item'.($carInCode[$rs->repairid] == 'B' ? ' -active"' : '' ).'"><label><input class="code" type="radio" name="code['.$rs->repairid.']" value="B"'.($carInCode[$rs->repairid] == 'B' ? ' checked="checked"' : '' ).' /><i class="icon -material">check_circle</i></label></div>',
			'<div class="form-item'.($carInCode[$rs->repairid] == 'C' ? ' -active"' : '' ).'"><label><input class="code" type="radio" name="code['.$rs->repairid.']" value="C"'.($carInCode[$rs->repairid] == 'C' ? ' checked="checked"' : '' ).' /><i class="icon -material">check_circle</i></label></div>',
			'<div class="form-item'.($carInCode[$rs->repairid] == 'D' ? ' -active"' : '' ).'"><label><input class="code" type="radio" name="code['.$rs->repairid.']" value="D"'.($carInCode[$rs->repairid] == 'D' ? ' checked="checked"' : '' ).' /><i class="icon -material">check_circle</i></label></div>',
		);
		*/
	}

	//$form->addText($tables->build());

	$form->addText('<script type="text/javascript">
		$(".inline-edit-item.-radio input:checked").each(function() {
		var $icon = $(this).parent("label").find(".icon")
		$(this).closest(".inline-edit-item").addClass("-active")
		//console.log($(this))
	})
	</script>');

	$ret .= $form->build();

	//$ret .= print_o($templateList,'$templateList');


	return $ret;
}
?>