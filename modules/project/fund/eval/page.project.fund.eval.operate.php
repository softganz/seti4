<?php
/**
* Project :: Fund Evalulate Operate Form
* Created 2020-10-23
* Modify  2020-10-23
*
* @param Object $self
* @param Object $fundInfo
* @param Int $tranId
* @param Boolean $editMode
* @return String
*
* @call project/fund/$orgId/eval.operate
*/

$debug = true;

function project_fund_eval_operate($self, $fundInfo, $tranId = NULL, $editMode = false) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isEdit = $fundInfo->right->edit;

	if ($isEdit && ($editMode || $tranId == 'new')) $editMode = true;
	if (is_numeric($tranId)) $data = R::Model('qt.get', $tranId);

	$header = $data->header;
	$rate = $data->rate;

	if ($isEdit && !$editMode) {
		$ret .= '<nav class="btn-floating -right-bottom -no-print">'
			. '<a class="sg-action btn -floating -circle48" href="'.url('project/fund/'.$orgId.'/eval.operate/'.$tranId.'/edit').'" data-rel="#main"><i class="icon -material">edit</i></a>'
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
		$form->addData('done', 'load:#main:'.url('project/fund/'.$orgId.'/eval.operate/'.$data->qtref));
	} else {
		$form->addData('done', 'reload:'.url('project/fund/'.$orgId.'/eval'));
	}

	$form->addConfig('title','แบบประเมิน กองทุนหลักประกันสุขภาพระดับท้องถิ่นหรือพื้นที่');

	$form->addText('<h4 class="-sg-text-center">'.$fundInfo->name.' เขต '.$fundInfo->areaId.' จังหวัด'.$fundInfo->info->namechangwat.' อำเภอ'.$fundInfo->info->nameampur.'</h4>');

	$form->addField('qtform',array('type' => 'hidden','value' => 106));
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
				'value' =>htmlspecialchars($header['HEADER.CID']),
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
			'group' => 'ด้านที่ 1 : สรุปการดำเนินงานกองทุนหลักประกันสุขภาพระดับท้องถิ่นหรือพื้นที่ปีที่ผ่านมา',
			),
		array(
			'qtid' => '01.1',
			'title' => '1.1 ความครบถ้วนถูกต้องของเอกสารประกอบการเบิกจ่าย',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐานประกอบการเบิกจ่ายเงินกองทุน<br />1) สรุปการประชุมคณะกรรมการกองทุน วาระการพิจารณา อนุมัติแผนงาน โครงการ กิจกรรมที่มีการเบิกจ่ายเงินสนับสนุน<br />2) ใบฎีกาเบิกจ่ายเงินกองทุน<br />3) แบบเสนอแผนงาน โครงการ กิจกรรม กองทุนหลักประกันสุขภาพ',
			'radio' => array(
				10 => 'มีเอกสารประกอบการเบิกจ่าย ตามข้อ 1) - ข้อ 3) และมีการลงลายมือชื่อขอผู้ที่เกี่ยวข้อง ครบถ้วน ถูกต้อง ทุกโครงการ',
				5 => 'มีเอกสารประกอบการเบิกจ่าย ตามข้อ 1) - ข้อ 3) และมีการลงลายมือชื่อขอผู้ที่เกี่ยวข้อง ครบถ้วน ถูกต้อง บางโครงการ',
				0 => 'ไม่มีเอกสารประกอบการเบิกจ่าย ตามข้อ 1) - ข้อ 3)',
			),
		),
		array(
			'qtid' => '01.2',
			'title' => '1.2 ความครบถ้วนของเอกสารผลการดำเนินงาน',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน ผลการดำเนินงานตามแผน<br />1) แบบเสนอโครงการ กิจกรรม กองทุนหลักประกันสุขภาพ<br />2) เอกสารผลการดำเนินงาน สิ่งส่งมอบที่ปรากฏตามข้อตกลงดำเนินงาน)',
			'radio' => array(
				10 => 'มีการลงสรุปผลการดำเนินงาน ตามข้อ 1) และมีเอกสารผลการดำเนินงานครบทุกโครงการ',
				5 => 'การลงสรุปผลการดำเนินงาน ตามข้อ 1) และมีเอกสารผลการดำเนินงานไม่ครบทุก โครงการ',
				0 => 'ไม่มีการลงสรุปผลการดำเนินงาน ตามข้อ 1) และไม่มีเอกสารผลการดำเนินงานประกอบทุกโครงการ',
			),
		),
		array(
			'qtid' => '01.3',
			'title' => '1.3 กองทุนมีการบันทึกรายงานการเงินรายเดือน รายปี ของปีที่ผ่านมา และปิดโครงการครบถ้วนสมบูรณ์ ผ่านโปรแกรม https://localfund.happynetwork.org',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน<br />1) รายงานการเงินรายเดือน รายไตรมาส รายปี ของปีที่ผ่านมา<br />2) รายงานการปิดโครงการ ผ่านโปรแกรม https://localfund.happynetwork.org',
			'radio' => array(
				10 => 'มีครบทั้งสองรายงาน',
				6 => 'มีไม่ครบ ขาดรายงานใดรายการหนึ่ง',
				1 => 'ไม่มีรายงานตามที่กำหนด',
			),
		),
		array(
			'group' => 'ด้านที่ 2 : การดำเนินงานกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่ ปีปัจจุบัน'
		),
		array(
			'qtid' => '02.1',
			'title' => '2.1 การแต่งตั้งคณะกรรมการกองทุน',
			'point' => 5,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน การแต่งตั้งคณะกรรมการกองทุน<br />1) คำสั่งแต่งตั้งคณะกรรมการกองทุน',
			'radio' => array(
				5 => 'มีการดำเนินการแต่งตั้งคณะกรรมการกองทุนครบถ้วน',
				3 => 'มีการแต่งตั้งคณะกรรมการกองทุน แต่ไม่ครบถ้วน',
				0 => 'ไม่มีคำสั่งแต่งตั้งคณะกรรมการกองทุน',
			),
		),
		array(
			'qtid' => '02.2',
			'title' => '2.2 การจัดทำแผนการเงินประจำปีและโครงการบริหารกองทุน',
			'point' => 5,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน การจัดทำแผนการเงินประจำปี และโครงการบริหารกองทุน<br />1) แบบฟอร์มแผนการเงินประจำปีของกองทุน<br />2) มีการจัดทำแผนโครงการบริหารกองทุน',
			'radio' => array(
				5 => 'มีเอกสารตามข้อ 1) และ ข้อ 2)',
				3 => 'มีแผน ข้อใดข้อหนึ่ง',
				0 => 'ไม่มีการจัดทำแผนการเงินประจำปี',
			),
		),
		array(
			'qtid' => '02.3',
			'title' => '2.3 การสมทบเงินของ อปท. ให้กับกองทุน',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน การสมทบเงินของ อปท. ให้กับกองทุน<br />1) รายงานการเงินสมทบของ อปท.จากสมุดบัญชีเงินฝากธนาคารของกองทุน<br /><span style="color:red">** หมายเหตุ : กองทุนไม่ได้รับการจัดสรรเงินจาก สปสช.ในปีปัจจุบัน ไม่ต้องประเมินข้อนี้</span>',
			'radio' => array(
				10 => 'มีการสมทบเงินของ อปท. ตามอัตราที่ต้องสมทบ ภายใน 31 มีนาคม ของปีนั้นๆ',
				5 => 'มีการสมทบเงินของ อปท. ตามอัตราที่ต้องสมทบ เกินวันที่ 31 มีนาคม ของปีนั้นๆ',
				3 => 'มีการสมทบเงินของ อปท. น้อยกว่าอัตราที่ต้องสมทบ ภายใน 31 มีนาคม ของปีนั้นๆ',
				1 => 'มีการสมทบเงินของ อปท. น้อยกว่าอัตราที่ต้องสมทบ เกินวันที่ 31 มีนาคม ของปีนั้นๆ',
				0 => 'ไม่มีการสมทบเงินของ อปท.',

			),
		),
		array(
			'qtid' => '02.4',
			'title' => '2.4 กองทุนประชุมกรรมการกองทุน เพื่ออนุมัติแผนงานโครงการ กิจกรรม ประจำปี',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน<br />1) เอกสารสรุปการประชุมกรรมการของกองทุน ต้องมีวาระพิจารณาแผนงาน/โครงการ และผลการพิจารณา',
			'radio' => array(
				10 => 'มีการประชุมกรรมการกองทุน เพื่ออนุมัติแผนงาน โครงการ กิจกรรม แล้ว',
				0 => 'ไม่มีการประชุมกรรมการกองทุน เพื่ออนุมัติแผนงานโครงการ กิจกรรม',
			),
		),
		array(
			'qtid' => '02.5',
			'title' => '2.5 กองทุนมีการทำแผนสุขภาพตำบลในประเด็นอาหารและโภชนาการ , การเพิ่มการเคลื่อนไหวทางกาย , ปัจจัยเสี่ยงต่อสุขภาพ (สุรา / ยาสูบ / สารเสพติด) , กลุ่มผู้ประกอบอาชีพเสี่ยง เป็นต้น',
			'point' => 10,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน<br />1) แผนสุขภาพตำบลในประเด็นอาหารและโภชนาการ , การเพิ่มการเคลื่อนไหวทางกาย , ปัจจัยเสี่ยงต่อสุขภาพ (สุรา / ยาสูบ / สารเสพติด) , กลุ่มผู้ประกอบอาชีพเสี่ยง (แผนใดแผนหนึ่ง)',
			'radio' => array(
				10 => 'มีแผนสุขภาพตำบล และมีการเบิกจ่ายเงินแล้ว ตั้งแต่ร้อยละ 70 ขึ้นไป',
				5 => 'มีแผนสุขภาพตำบล และมีการเบิกจ่ายเงินแล้ว ตั้งแต่ร้อยละ 26–69.99',
				3 => 'มีแผนสุขภาพตำบล และมีการเบิกจ่ายเงินแล้ว ตั้งแต่ร้อยละ 1 –25.99',
				0 => 'ไม่มีการเบิกจ่ายเงินจากกองทุน',
			),
		),
		array(
			'qtid' => '02.6',
			'title' => '2.6 กองทุนมีโครงการฯ ตามข้อ 2.5',
			'point' => 15,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน<br />1) เอกสารโครงการประเด็นอาหารและโภชนาการ , การเพิ่มการเคลื่อนไหวทางกาย , ปัจจัยเสี่ยงต่อสุขภาพ (สุรา / ยาสูบ / สารเสพติด) , กลุ่มผู้ประกอบอาชีพเสี่ยงที่ได้รับการอนุมัติจากกรรมการกองทุน<br />2) ใบฎีกาเบิกจ่ายเงินของกองทุนสนับสนุนโครงการประเด็นอาหารและโภชนาการ , การเพิ่มการเคลื่อนไหวทางกาย , ปัจจัยเสี่ยงต่อสุขภาพ (สุรา / ยาสูบ / สารเสพติด) , กลุ่มผู้ประกอบอาชีพเสี่ยง',
			'radio' => array(
				15 => 'มีโครงการประเด็นอาหารและโภชนาการ , การเพิ่มการเคลื่อนไหวทางกาย , ปัจจัยเสี่ยงต่อสุขภาพ (สุรา / ยาสูบ / สารเสพติด) , กลุ่มผู้ประกอบอาชีพเสี่ยง ที่ได้รับการอนุมัติจากกรรมการกองทุนและเบิกจ่ายเงินสนับสนุนแล้ว',
				10 => 'มีโครงการประเด็นอาหารและโภชนาการ , การเพิ่มการเคลื่อนไหวทางกาย , ปัจจัยเสี่ยงต่อสุขภาพ (สุรา / ยาสูบ / สารเสพติด) , กลุ่มผู้ประกอบอาชีพเสี่ยง ที่ได้รับการอนุมัติจากกรรมการกองทุน แต่ยังไม่ได้เบิกจ่ายเงินสนับสนุน',
				0 => 'ไม่มีโครงการประเด็นอาหารและโภชนาการ , การเพิ่มการเคลื่อนไหวทางกาย , ปัจจัยเสี่ยงต่อสุขภาพ (สุรา / ยาสูบ / สารเสพติด) , กลุ่มผู้ประกอบอาชีพเสี่ยง',
			),
		),
		array(
			'qtid' => '02.7',
			'title' => '2.7 กองทุนมีโครงการ NCD หรือพัฒนาการเด็ก',
			'point' => 15,
			'detail' => 'ตรวจสอบเอกสารหลักฐาน<br />1) เอกสารโครงการโรคเรื้อรัง NCD (เบาหวาน/ความดันโลหิตสูง) หรือพัฒนาการเด็ก ที่ได้รับการอนุมัติจากกรรมการกองทุน<br />2) ใบฎีกาเบิกจ่ายเงินของกองทุนสนับสนุนโครงการโรคเรื้อรัง NCD (เบาหวาน/ความดันโลหิตสูง) หรือพัฒนาการเด็ก ที่ได้รับการอนุมัติจากกรรมการกองทุน',
			'radio' => array(
				15 => 'มีโครงการโรคเรื้อรัง NCD (เบาหวาน/ความดันโลหิตสูง) หรือพัฒนาการเด็ก ที่ได้รับการอนุมัติจากกรรมการกองทุนและเบิกจ่ายเงินสนับสนุนแล้ว',
				10 => 'มีโครงการโรคเรื้อรัง NCD (เบาหวาน/ความดันโลหิตสูง) หรือพัฒนาการเด็ก ที่ได้รับการอนุมัติจากกรรมการกองทุน แต่ยังไม่ได้เบิกจ่ายเงินสนับสนุน',
				0 => 'ไม่มีโครงการโรคเรื้อรัง NCD (เบาหวาน/ความดันโลหิตสูง) หรือพัฒนาการเด็ก',
			),
		),
	);





	$tables = new Table();
	$tables->addClass('project-fund-eval-table');
	$tables->addConfig('showHeader', false);
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
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/fund/'.$orgId.($data->qtref ? '/eval.operate/'.$data->qtref : '/eval')).'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				)
			);
	}

	$ret .= $form->build();

	//$ret .= print_o($data, '$data');
	return $ret;
}
?>