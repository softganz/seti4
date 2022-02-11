<?php
/**
* Project :: Fund Estimation Form
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @param Object $data
* @param Boolean $editMode
* @return String
*
* @call project/fund/$orgId/estimate.form
*/

$debug = true;

function project_fund_eval_ltc($self, $fundInfo, $tranId = NULL, $editMode = false) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isEdit = $fundInfo->right->edit;

	if ($isEdit && ($editMode || $tranId == 'new')) $editMode = true;
	if (is_numeric($tranId)) $data = R::Model('qt.get', $tranId);

	$header = $data->header;
	$rate = $data->rate;

	if ($isEdit && !$editMode) {
		$ret .= '<nav class="btn-floating -right-bottom -no-print">'
			. '<a class="sg-action btn -floating -circle48" href="'.url('project/fund/'.$orgId.'/eval.ltc/'.$tranId.'/edit').'" data-rel="#main"><i class="icon -material">edit</i></a>'
			.'<a class="sg-action btn -floating -circle48" class="sg-action" href="'.url('project/fund/'.$orgId.'/info/eval.delete/'.$tranId).'" data-rel="none" data-done="reload:'.url('project/fund/'.$orgId.'/eval').'" data-title="ลบแบบประเมิน" data-confirm="ต้องการลบแบบประเมินนี้ กรุณายืนยัน?"><i class="icon -material">delete</i></a>'
			. '</nav>';
	}

	$form = new Form(
		'data',
		url('project/fund/'.$orgId.'/info/eval.save'),
		'project-fund-eval-form-ltc',
		'sg-form project-fund-eval-form -ltc'
	);

	$form->addData('checkValid',true);
	$form->addData('rel', 'notify');
	if ($data->qtref) {
		$form->addData('done', 'load:#main:'.url('project/fund/'.$orgId.'/eval.ltc/'.$data->qtref));
	} else {
		$form->addData('done', 'reload:'.url('project/fund/'.$orgId.'/eval'));
	}

	$form->addConfig('title','แบบประเมิน กองทุนผู้สูงอายุที่มีภาวะพึ่งพิงและบุคคลอื่นที่มีภาวะพึ่งพิง (LTC)');
	$form->addText('<h4 class="-sg-text-center">'.$fundInfo->name.' เขต '.$fundInfo->areaId.' จังหวัด'.$fundInfo->info->namechangwat.' อำเภอ'.$fundInfo->info->nameampur.'</h4>');

	$form->addField('qtform',array('type' => 'hidden','value' => 107));
	$form->addField('qtref',array('type' => 'hidden','value' => $data->qtref));

	if ($editMode) {
		$form->addText('<div class="box -header">');

		$form->addField(
			'by',
			array(
				'type' => 'hidden',
				'name' => 'header[HEADER.BY]',
				'label' => 'ประเมินโดย',
				'options' => array(
					'พี่เลี้ยงกองทุน' => 'พี่เลี้ยงกองทุน',
					'เจ้าหน้าที่กองทุน' => 'เจ้าหน้าที่กองทุน',
					'ทีมงานเจ้าหน้าที่อำเภอ' => 'ทีมงานเจ้าหน้าที่อำเภอ'
				),
				'require' => true,
				//'value' => $header['HEADER.BY'],
				'value' => 'พี่เลี้ยงกองทุน',
			)
		);

		$form->addField(
			'year',
			array(
				'type' => 'select',
				'label' => 'ปีงบประมาณ :',
				'class' => '-fill',
				'require' =>true,
				'options' =>array(
					date('Y')-1=>date('Y')+543-1,
					date('Y')=>date('Y')+543,
					date('Y')+1=>date('Y')+543+1,
				),
				'value' =>sg_date(SG\getFirst($data->info->qtdate,date('Y-m-d')),'Y'),
			)
		);

		$form->addField(
			'evaldate',
			array(
				'type' => 'text',
				'name' => 'header[HEADER.EVALDATE]',
				'label' => 'วันที่ประเมิน',
				'class' => 'sg-datepicker -fill',
				'require' => true,
				'value' => $header['HEADER.EVALDATE'] ? sg_date($header['HEADER.EVALDATE'], 'd/m/Y') : date('d/m/Y'),
				'placeholder' => '01/31/2560',
			)
		);

		$form->addField(
			'collectname',
			array(
				'type' => 'text',
				'label' => 'ชื่อผู้บันทึก',
				'class' => '-fill',
				'require' =>true,
				'value' =>htmlspecialchars($data->info->collectname),
			)
		);

		$form->addField(
			'cid',
			array(
				'type' => 'text',
				'name' => 'header[HEADER.CID]',
				'label' => 'เลขบัตรประชาชน',
				'class' => '-fill',
				'require' =>true,
				'maxlength' =>13,
				'value' => htmlspecialchars($header['HEADER.CID']),
			)
		);

		$form->addField(
			'email',
			array(
				'type' => 'text',
				'name' => 'header[HEADER.EMAIL]',
				'label' => 'อีเมล์',
				'class' => '-fill',
				'require' =>true,
				'value' =>htmlspecialchars($header['HEADER.EMAIL']),
			)
		);

		$form->addField(
			'phone',
			array(
				'type' => 'text',
				'name' => 'header[HEADER.PHONE]',
				'label' => 'เบอร์โทรศัพท์',
				'class' => '-fill',
				'require' =>true,
				'value' =>htmlspecialchars($header['HEADER.PHONE']),
			)
		);

		$form->addField(
			'position',
			array(
				'type' => 'text',
				'name' => 'header[HEADER.POSITION]',
				'label' => 'ตำแหน่ง',
				'class' => '-fill',
				'require' =>true,
				'value' =>htmlspecialchars($header['HEADER.POSITION']),
			)
		);

		$form->addField(
			'orgname',
			array(
				'type' => 'text',
				'name' => 'header[HEADER.ORGNAME]',
				'label' => 'หน่วยงาน',
				'class' => '-fill',
				'require' =>true,
				'value' =>htmlspecialchars($header['HEADER.ORGNAME']),
			)
		);

		$form->addText('</div><!-- box -->');

	}

	$qtList = array(
		array(
			'qtid' => '01.1',
			'title' => '1. การแต่งตั้งคณะอนุกรรมการ LTC (17 คะแนน)',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน : การแต่งตั้งคณะอนุกรรมการ LTC  ครบองค์ประกอบ 9 องค์ประกอบ จำนวน 10 คน ลงนามคำสั่งโดยประธานคณะกรรมการกองทุนหลักประกันสุขภาพระดับพื้นที่ ถูกต้องครบถ้วน',
			'radio' => array(
				10 => 'มีเอกสารการแต่งตั้งคณะกรรมการครบองค์ประกอบ และมีการลงนามคำสั่งถูกต้องและครบถ้วน',
				6 => 'มีเอกสารการแต่งตั้งคณะกรรมการไม่ครบองค์ประกอบ',
				1 => 'ไม่มีเอกสารการแต่งตั้งคณะกรรมการ',
			),
		),
		array(
			'qtid' => '02.1',
			'title' => '2. การประชุมพิจารณาอนุมัติ CAREPLAN',
			'point' => 15,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน : มีการประชุมพิจารณาอนุมัติ CAREPLAN หลังจากได้รับงบประมาณ ไม่เกิน 30 วัน(ตรวจสอบจากระบบ)',
			'radio' => array(
				15 => 'มีการประชุมพิจารณาอนุมัติ CAREPLAN หลังจากได้รับงบประมาณ ไม่เกิน 30 วัน',
				12 => 'มีการประชุมพิจารณาอนุมัติ CAREPLAN หลังจากได้รับงบประมาณ ไม่เกิน 31 - 60 วัน',
				9 => 'มีการประชุมพิจารณาอนุมัติ CAREPLAN หลังจากได้รับงบประมาณ ไม่เกิน 61 - 90 วัน',
				6 => 'มีการประชุมพิจารณาอนุมัติ CAREPLAN หลังจากได้รับงบประมาณ ไม่เกิน 90 - 120 วัน',
				1 => 'มากกว่า 120 วัน หรือยังไม่มีการบันทึกเสนอรายชื่อ',
			),
		),
		array(
			'qtid' => '03.1',
			'title' => '3. การโอนงบประมาณให้หน่วยจัดบริการ (ศูนย์พัฒนาคุณภาพชีวิต/รพ.สต./รพ.อื่นๆ)',
			'point' => 15,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน : มีการโอนงบประมาณให้หน่วยจัดบริการ(ศูนย์พัฒนาคุณภาพชีวิตหรือ รพสต.หรือ รพ.หรืออื่นๆ)',
			'radio' => array(
				15 => 'มีการโอนงบประมาณให้หน่วยจัดบริการ ไม่เกิน 15 วัน',
				12 => 'มีการโอนงบประมาณให้หน่วยจัดบริการ ไม่เกิน 16 - 20 วัน',
				9 => 'มีการโอนงบประมาณให้หน่วยจัดบริการ ไม่เกิน 21 - 30 วัน',
				6 => 'มีการโอนงบประมาณให้หน่วยจัดบริการ ไม่เกิน 31 - 45 วัน',
				1 => 'มากกว่า 45 วัน หรือยังไม่มีการพิจารณาอนุมัติ CAREPLAN',
			),
		),
		array(
			'qtid' => '04.1',
			'title' => '4. มีการบันทึก ADL เมื่อครบ 9 เดือน',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน : มีการบันทึก ADL เมื่อครบ 9 เดือน',
			'radio' => array(
				10 => 'มีการบันทึก ADL เมื่อครบ 9 เดือน',
				6 => 'มีการอนุมัติ CAREPLAN แล้ว แต่ยังไม่ครบ 9 เดือน',
				1 => 'ยังไม่มีการพิจารณาอนุมัติ CAREPLAN',
			),
		),
	);




	$tables = new Table();
	$tables->addClass('project-fund-eval-table');
	$tables->thead = array(
		'เกณฑ์การประเมิน',
		'maxpoint -center' => 'คะแนนเต็ม',
		'getpoint -center' => 'คะแนนที่ได้'
	);

	foreach ($qtList as $item) {
		if ($item['group']) {
			$tables->rows[] = '<header>';
			$tables->rows[] = array(
				'<h4>'.$item['group'].'</h4>',
				$item['point'],
				'',
				'config' => '{class: "-group"}',
			);
		}
		$tables->rows[] = array(
			'<b>'.$item['title'].'</b>',
			$item['point'],
			'',
			'config' => '{class: "-title"}',
		);
		if ($item['detail']) {
			$tables->rows[] = array($item['detail'], '', '', 'config' => '{class: "-detail"}');
		}
		if ($item['qtid']) {
			if ($item['radio']) {
				foreach ($item['radio'] as $point => $value) {
					$row = array();
					$radioText = '<label class="'.($editMode ? '' : '-disabled').'">'
						. '<input class="-hidden" type="radio" name="rate[RATE.'.$item['qtid'].']" value="'.$point.'" '.(isset($data->tran['RATE.'.$item['qtid']]->rate) && $data->tran['RATE.'.$item['qtid']]->rate == $point ? 'checked="checked"' : '').'/>'
						. $value
						. '<i class="icon -material">check_circle</i>'
						. '</label>';
					$row[] = '<td colspan="2">'.$radioText.'</td>';
					$row[] = '('.$point.')';
					$row['config'] = array('class' => '-qtitem');
					$tables->rows[] = $row;
				}
			}
		}
	}

	$tables->rows[] = array('<b>รวมคะแนน</b>', 50, number_format($data->info->rates));

	$form->addText($tables->build());

	if ($editMode) {
		$form->addField(
			'submit',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/fund/'.$orgId.($data->qtref ? '/eval.ltc/'.$data->qtref : '/eval')).'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				)
			);
	}

	$ret .= $form->build();

	//$ret .= print_o($data, '$data');
	return $ret;
}
?>