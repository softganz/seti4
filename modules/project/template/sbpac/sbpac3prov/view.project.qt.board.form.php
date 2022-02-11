<?php
/**
* Project Board Quatation Form
*
* @param Object $projectInfo
* @param Object $data
* @param JSON String $options
* @return String
*/

function view_project_qt_board_form($projectInfo, $data, $options = '{}') {
	$defaults='{debug:false, readonly:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	//R::View('project.toolbar',$self, 'แบบสำรวจความคิดเห็นของประชาชน', NULL, $projectInfo);


	$ocupaData = array();
	foreach ($data->trans as $key => $item) {
		$ocupaData[$key] = $item->value;
	}

	$ret .= '<p align="right">แบบสำรวจความคิดเห็นของกลไกคณะกรรมการหมู่บ้าน</p>'
		.'<h2 class="-sg-text-center">แบบสำรวจความคิดเห็นของกลไกคณะกรรมการหมู่บ้าน<br /><!-- (ด้าน........................) -->การดำเนินงานหมู่บ้าน/ชุมชนเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน ประจำปี 2560</h2>';
	$ret .= '<p><em>คำชี้แจง แบบสอบถามมี 3 ส่วน คือ<br />ส่วนที่ 1 ข้อมูลทั่วไปของผู้ตอบแบบสอบถาม<br />ส่วนที่ 2 ผลการประเมินกลไกคณะกรรมการหมู่บ้าน<br />	ส่วนที่ 3 ความคิดเห็นอื่นๆ (การดำเนินงาน ปัญหา ผลการดำเนินงานและข้อเสนอแนะ)<br />เพื่อประโยชน์ในการขับเคลื่อนโครงการหมู่บ้านชุมชนเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน ไปสู่ความสำเร็จอย่างยั่งยืนจึงใคร่ขอให้ท่านให้ความเห็นตามที่เป็นจริง</em></p>';

	$form = new Form('qt', $options->readonly ? NULL : url('project/qt/'.$projectInfo->tpid.'/board/save'), NULL, NULL);

	if ($options->readonly) $form->addConfig('readonly', true);

	$form->addText('<h3>ส่วนที่ 1 ข้อมูลทั่วไปของผู้ตอบแบบสอบถาม</h3>');

	$form->addField('qtref', array('type' => 'hidden', 'value' => $data->qtref));

	$form->addField(
		'POSITION',
		array(
			'label' => '1.1 ท่านดำรงตำแหน่งในคณะกรรมหมู่บ้าน ด้านใด:',
			'type' => 'radio',
			'options' => array(
				'ด้านอำนวยการ' => 'ด้านอำนวยการ',
				'ด้านการปกครองและรักษาความสงบเรียบร้อย' => 'ด้านการปกครองและรักษาความสงบเรียบร้อย',
				'ด้านแผนพัฒนาหมู่บ้าน' => 'ด้านแผนพัฒนาหมู่บ้าน',
				'ด้านส่งเสริมเศรษฐกิจ' => 'ด้านส่งเสริมเศรษฐกิจ',
				'ด้านสังคมสิ่งแวดล้อมและสาธารณาสุข' => 'ด้านสังคมสิ่งแวดล้อมและสาธารณาสุข',
				'ด้านการศึกษา ศาสนา' => 'ด้านการศึกษา ศาสนา',
				'ด้านอื่น ๆ' => 'ด้านอื่น ๆ '
					.'<input type="text" name="qt[POSITION-OTHER]" placeholder="ระบุ" value="'.(in_array($data->trans['POSITION']->value, array('ด้านอำนวยการ', 'ด้านการปกครองและรักษาความสงบเรียบร้อย', 'ด้านแผนพัฒนาหมู่บ้าน', 'ด้านส่งเสริมเศรษฐกิจ', 'ด้านสังคมสิ่งแวดล้อมและสาธารณาสุข', 'ด้านการศึกษา ศาสนา')) ? '' : htmlspecialchars($data->trans['POSITION']->value)).'" />'
			),
			'value' => $data->trans['POSITION']->value,
		)
	);

	$form->addField(
		'BOARDTYPE',
		array(
			'label' => '1.2  ท่านเป็นคณะกรรมการหมู่บ้านประเภทใด:',
			'type' => 'radio',
			'options' => array('โดยตำแหน่ง' => 'โดยตำแหน่ง', 'โดยการคัดเลือก' => 'โดยการคัดเลือก'),
			'value' => $data->trans['BOARDTYPE']->value,
		)
	);

	$form->addField(
		'SEX',
		array(
			'type' => 'radio',
			'label' => '1.3 เพศ:',
			'options' => array('ชาย' => 'ชาย', 'หญิง' => 'หญิง'),
			'value' => $data->trans['SEX']->value,
		)
	);

	$form->addField(
		'AGE',
		array(
			'label' => '1.4 อายุ:',
			'type' => 'radio',
			'options' => array('18-30 ปี' => '18-30 ปี' ,'31-40 ปี' =>'31-40 ปี', '41-50 ปี' => '41-50 ปี', '51-60 ปี' => '51-60 ปี', '60 ปี ขึ้นไป' => '60 ปี ขึ้นไป'),
			'value' => $data->trans['AGE']->value,
		)
	);

	$form->addField(
		'RELIGION',
		array(
			'type' => 'radio',
			'label' => '1.5 ศาสนา:',
			'options' => array(
				'อิสลาม' => 'อิสลาม',
				'พุทธ' => 'พุทธ',
				'คริสต์' => 'คริสต์',
				'อื่น ๆ' => 'อื่น ๆ ระบุ '
					.'<input type="text" name="qt[RELIGION-OTHER]" placeholder="ระบุ" value="'.(in_array($data->trans['RELIGION']->value, array('อิสลาม', 'พุทธ', 'คริสต์' => 'คริสต์')) ? '' : htmlspecialchars($data->trans['RELIGION']->value)).'" />'
				),
			'value' => $data->trans['RELIGION']->value,
		)
	);

	$form->addField(
		'COMMUPOS',
		array(
			'type' => 'radio',
			'label' => '1.6 สถานะในชุมชน:',
			'options' => array(
				'กลุ่มผู้นำท้องที่' => 'กลุ่มผู้นำท้องที่',
				'กลุ่มผู้นำท้องถิ่น' => 'กลุ่มผู้นำท้องถิ่น',
				'กลุ่มผู้นำศาสนา' => 'กลุ่มผู้นำศาสนา',
				'กลุ่มผู้นำทางธรรมชาติ' => 'กลุ่มผู้นำทางธรรมชาติ',
				'กลุ่มสตรี' => 'กลุ่มสตรี',
				'กลุ่ม อสม.' => 'กลุ่ม อสม.',
				'กลุ่มเยาวชน' => 'กลุ่มเยาวชน',
				'อื่น ๆ' => 'อื่น ๆ ระบุ '
					.'<input type="text" name="qt[COMMUPOS-OTHER]" placeholder="ระบุ" value="'.(in_array($data->trans['COMMUPOS']->value, array('กลุ่มผู้นำท้องที่', 'กลุ่มผู้นำท้องถิ่น', 'กลุ่มผู้นำศาสนา', 'กลุ่มผู้นำทางธรรมชาติ', 'กลุ่มสตรี', 'กลุ่ม อสม.', 'กลุ่มเยาวชน')) ? '' : htmlspecialchars($data->trans['COMMUPOS']->value)).'" />'
			),
			'value' => $data->trans['COMMUPOS']->value,
		)
	);


	$form->addField(
		'OCCUPA',
		array(
			'label' => '1.7 อาชีพ: <em>(ท่านสามารถตอบได้มากกว่า 1 ข้อ)</em>',
			'type' => 'checkbox',
			'multiple' => true,
			'options' => array(
				'&nbsp;<b>1 อาชีพการเกษตร</b>',
					'ทำนา' => 'ทำนา',
					'ทำสวนยางพารา' => 'ทำสวนยางพารา',
					'ทำสวนปาล์ม' => 'ทำสวนปาล์ม',
					'ทำสวนไม้ผล' => 'ทำสวนไม้ผล',
					'เลี้ยงสัตว์' => 'เลี้ยงสัตว์',
					'การเกษตร อื่น ๆ'=>'อื่น ๆ <input type="text" name="qt[OCCUPA][การเกษตร อื่น ๆ]" placeholder="ระบุ" value="'.htmlspecialchars($data->trans['OCCUPA.การเกษตร อื่น ๆ']->value).'" />',
				'&nbsp;<b>2 อาชีพประมง</b>',
					'ประมงพื้นบ้านชายฝั่ง' => 'ประมงพื้นบ้านชายฝั่ง',
					'ประมงเชิงพาณิชย์' => 'ประมงเชิงพาณิชย์',
					'ประมงน้ำจืด' => 'ประมงน้ำจืด',
					'เพาะเลี้ยงสัตว์น้ำ' => 'เพาะเลี้ยงสัตว์น้ำ',
					'ประมง อื่น ๆ' => 'อื่น ๆ <input type="text" name="qt[OCCUPA][ประมง อื่น ๆ]" placeholder="ระบุ" value="'.htmlspecialchars($data->trans['OCCUPA.ประมง อื่น ๆ']->value).'" />',
				'รับจ้าง' => '3 รับจ้าง',
				'ค้าขาย' => '4 ค้าขาย',
				'ประกอบกิจการส่วนตัว' => '5 ประกอบกิจการส่วนตัว',
				'อื่น ๆ' => '6 อื่น ๆ <input type="text" name="qt[OCCUPA][อื่น ๆ]" placeholder="ระบุ" value="'.htmlspecialchars($data->trans['OCCUPA.อื่น ๆ']->value).'" />',
			),
			'value' => $ocupaData,
		)
	);

	$form->addText('<h3>ส่วนที่ 2 ผลการประเมินกลไกคณะกรรมการหมู่บ้าน</h3>');

	$tables = new Table();
	$tables->addClass('qt-list');
	$tables->thead = '<tr><th rowspan="2">รายการ</th><th colspan="5">ระดับความคิดเห็น</th></tr><tr style="vertical-align: top;"><th>มากที่สุด<br />(5)</th><th>มาก<br />(4)</th><th>ปานกลาง<br />(3)</th><th>น้อย<br />(2)</th><th>น้อยที่สุด<br />(1)</th></tr>';
	$qtList = array(
		'2.1' => 'ด้านความรู้ความเข้าใจ',
		211 => '1. ท่านทราบถึงนโยบายของรัฐบาลในการแก้ไขปัญหาและพัฒนา จชต. มากน้อยเพียงใด',
		212 => '2. ท่านมีความรู้และเข้าใจบทบาทหน้าที่ของคณะกรรมการหมู่บ้านมากน้อยเพียงใด',
		213 => '3. ท่านทราบเป้าหมาย/ผลลัพธ์ของ โครงการชุมชนเข้มแข็ง มั่นคง มั่งคั่ง มากน้อยเพียงใด ',
		219 => array('4. ข้อเสนอแนะ', 'textarea'),

		'2.2' => 'ด้านการมีส่วนร่วม',
		221 => '5. หมู่บ้านของท่านมีการจัดประชุมคณะกรรมการหมู่บ้านมากน้อยเพียงใด',
		222 => '6. ท่านมีส่วนร่วมในการเข้าประชุมคณะกรรมการหมู่บ้านมากน้อยเพียงใด',
		223 => '7. มีหน่วยงาน/ส่วนราชการ เข้าร่วมในการประชุมคณะกรรมการหมู่บ้านมากน้อยเพียงใด',
		229 => array('8. ข้อเสนอแนะ', 'textarea'),

		'2.3' => 'ด้านประสิทธิภาพของกลไกคณะกรรมการหมู่บ้าน',
		231 => '9. คณะทำงานมีการสำรวจและจัดทำฐานข้อมูลมากน้อยเพียงใด',
		232 => '10. คณะทำงานได้เชิญผู้มีส่วนได้เสีย/ส่วนราชการ/หน่วยงานที่เกี่ยวข้อง เข้าร่วมประชุม วางแผน มากน้อยเพียงใด',
		233 => '11. คณะทำงานมีการจัดทำแผนงาน/โครงการ/กิจกรรม เพื่อนำไปแก้ไขปัญหาและพัฒนาในหมู่บ้านมากน้อยเพียงใด',
		234 => '12. คณะทำงานมีการบันทึกการประชุมมากน้อยเพียงใด',
		235 => '13. คณะทำงานมีการบูรณาการและประสานงานกับหน่วยงาน/ส่วนราชการที่เกี่ยวข้องมากน้อยเพียงใด',
		236 => '14. คณะทำงานมีการติดตามประเมินผลงานรายกิจกรรม/โครงการ ที่ดำเนินการ มากน้อยเพียงใด',
		237 => '15 คณะทำงานของท่านมีการทบทวน/ปรับปรุง แผนงานโครงการมากน้อยเพียงใด',
		238 => '16. คณะทำงานจัดให้มีการประชุมสรุปผลและจัดทำรายงานประจำปี พร้อมส่งให้กับ ศปก. อำเภอ และ ศอ.บต. เพื่อนำไปปรับปรุงและใช้ประโยชน์ ',
		239 => array('17. ข้อเสนอแนะ', 'textarea'),

		'2.4' => 'ด้านการสร้างความเข้าใจกับประชาชนในพื้นที่',
		241 => '18. คณะทำงานของท่านได้สร้างการรับรู้การปฏิบัติงานให้กับประชาชนได้รับทราบมากน้อยเพียงใด',
		242 => '19. คณะทำงานของท่านมีการประชาสัมพันธ์ตามช่องทางต่างๆในพื้นที่ เช่น เสียงตามสาย การติดประกาศ ฯ ได้รับทราบมากน้อยเพียงใด',
		243 => '20. คณะทำงานของท่านได้เปิดโอกาสให้ประชาชนได้แสดงความคิดเห็น เช่น ผ่านตู้จดหมายชุมชน การเปิดเวทีรับฟังความคิดเห็น ฯ มากน้อยเพียงใด',
		244 => '21. คณะทำงานของท่านเปิดโอกาสให้ประชาชนมีส่วนร่วมในกิจกรรมการจัดซื้อ จัดจ้าง และเป็นกรรมการตรวจสอบ/ตรวจรับ ฯ มากน้อยเพียงใด',
		249 => array('22. ข้อเสนอแนะ', 'textarea'),
	);

	foreach ($qtList as $key => $item) {
		if (is_string($key)) {
			$groupKey = $key;
			$tables->rows[] = array('<th colspan="6" style="text-align: left;">'.$groupKey.' '.$item.'</th>');
			continue;
		}

		$itemKey = 'SEC.'.$groupKey.'.'.$key;
		if (is_array($item)) {
			$tables->rows[] = array('<td colspan="6">'.$item[0].'<textarea class="form-textarea -fill" name="qt['.$itemKey.']">'.($data->trans[$itemKey]->value).'</textarea></td>');
		} else {
			$tables->rows[] = array(
				$item,
				'<input type="radio" name="qt['.$itemKey.']" value="5" '.($data->trans[$itemKey]->value == 5 ? 'checked="checked"' : '').' />',
				'<input type="radio" name="qt['.$itemKey.']" value="4" '.($data->trans[$itemKey]->value == 4 ? 'checked="checked"' : '').' />',
				'<input type="radio" name="qt['.$itemKey.']" value="3" '.($data->trans[$itemKey]->value == 3 ? 'checked="checked"' : '').' />',
				'<input type="radio" name="qt['.$itemKey.']" value="2" '.($data->trans[$itemKey]->value == 2 ? 'checked="checked"' : '').' />',
				'<input type="radio" name="qt['.$itemKey.']" value="1" '.($data->trans[$itemKey]->value == 1 ? 'checked="checked"' : '').' />',
			);
		}
	}
	$form->addText($tables->build());

	$form->addText('<h3>ส่วนที่ 3 ความคิดเห็นอื่นๆ (การดำเนินงาน ปัญหา ผลการดำเนินงาน และข้อเสนอแนะ)</h3>');

	$form->addField(
		'PROUD',
		array(
			'label' => '1. กิจกรรมโดดเด่นที่ท่านมีความภาคภูมิใจ',
			'type' => 'textarea',
			'class' => '-fill',
			'value' => $data->trans['PROUD']->value,
		)
	);

	$form->addText('2. บุคคลดีเด่นที่สามารถเป็นต้นแบบในการถ่ายทอดให้กับบุคคลภายนอกได้ ได้แก่');

	$tables = new Table();
	$tables->thead = array('no' => '', 'ชื่อสกุล', 'ต้นแบบเรื่อง', 'หมายเลขโทรศัพท์');
	for ($i = 1; $i <= 5; $i++) {
		$itemKey = 'PMODEL.'.$i.'.';
		$tables->rows[] = array(
			$i,
			'<input class="form-text -fill" type="text" name="qt['.($itemKey.'NAME').']" placeholder="ระบุชื่อ-นามสกุล" value="'.htmlspecialchars($data->trans[$itemKey.'NAME']->value).'" />',
			'<input class="form-text -fill" type="text" name="qt['.($itemKey.'ABOUT').']" placeholder="ระบุเรื่อง" value="'.htmlspecialchars($data->trans[$itemKey.'ABOUT']->value).'" />',
			'<input class="form-text -fill" type="text" name="qt['.($itemKey.'PHONE').']" placeholder="ระบุโทรศัพท์" value="'.htmlspecialchars($data->trans[$itemKey.'PHONE']->value).'" />',
		);
	}
	$form->addText($tables->build());

	$form->addField(
		'PROBLEM',
		array(
			'label' => '3. ปัญหาอุปสรรค',
			'type' => 'textarea',
			'class' => '-fill',
			'value' => $data->trans['PROBLEM']->value,
		)
	);

	$form->addField(
		'SOLUTION',
		array(
			'label' => '4. แนวทางการแก้ไข',
			'type' => 'textarea',
			'class' => '-fill',
			'value' => $data->trans['SOLUTION']->value,
		)
	);

	$form->addField(
		'COMMENT',
		array(
			'label' => '5. ข้อเสนอแนะอื่นๆ',
			'type' => 'textarea',
			'class' => '-fill',
			'value' => $data->trans['COMMENT']->value,
		)
	);

	$form->addText('ขอขอบคุณในความกรุณากรอกแบบสอบถาม');

	$form->addField(
		'collectname',
		array(
			'type' => 'text',
			'label' => 'ชื่อผู้เก็บข้อมูล',
			'value' => $data->collectname,
			'placeholder' => 'ระบุชื่อผู้เก็บข้อมูล',
		)
	);

	$form->addField(
		'qtdate',
		array(
			'type' => 'text',
			'label' => 'วันที่เก็บข้อมูล',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'value' => $data->qtdate ? sg_date($data->qtdate,'d/m/Y') : date('d/m/Y'),
		)
	);

	if (!$options->readonly) {
		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>บันทึก</span>',
				'pretext' => ' <a class="btn -cancel -link" href="'.url('project/qt/'.$projectInfo->tpid.'/board').'">ยกเลิก</a> ',
			)
		);
	}

	$ret .= $form->build();

	//$ret .= print_o($ocupaData, '$ocupaData');
	//$ret .= print_o($data, '$data');


	$ret.='<style type="text/css">
	.qt-list th:nth-child(n+1) {white-space: nowrap;}
	.qt-list td:nth-child(n+2) {text-align: center;}
	.form-item.-edit-qt-save {text-align: right;}
	.btn.-cancel {margin-right: 32px;}
	</style>';
	return $ret;
}
?>