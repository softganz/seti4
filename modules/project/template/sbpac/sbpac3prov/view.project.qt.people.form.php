<?php
/**
* Module Method
*
* @param
* @return String
*/

function view_project_qt_people_form($projectInfo, $data, $options = '{}') {
	$defaults='{debug:false, readonly:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	//R::View('project.toolbar',$self, 'แบบสำรวจความคิดเห็นของประชาชน', NULL, $projectInfo);


	$ocupaData = array();
	foreach ($data->trans as $key => $item) {
		$ocupaData[$key] = $item->value;
	}

	$ret.='<p align="right">แบบสัมภาษณ์ประชาชนในหมู่บ้าน</p>'
		.'<h2 class="-sg-text-center">แบบสำรวจความคิดเห็นของประชาชน<br />โครงการหมู่บ้าน/ชุมชนเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน ประจำปี 2560</h2>'
		.'<p><em>คำชี้แจง แบบสอบถามมี 3 ส่วน คือ<br />ส่วนที่ 1 ข้อมูลทั่วไปของผู้ตอบแบบสอบถาม<br />ส่วนที่ 2 ความคิดเห็นของประชาชนในหมู่บ้าน<br />ส่วนที่ 3 ความคิดเห็นอื่นๆ (การดำเนินงาน ปัญหา ผลการดำเนินงานและข้อเสนอแนะ)<br />เพื่อประโยชน์ในการขับเคลื่อนโครงการหมู่บ้านชุมชนเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน ไปสู่ความสำเร็จ จึงใคร่ขอให้ท่านให้ความเห็นตามที่เป็นจริง</em></p>';

	$form = new Form('qt', $options->readonly ? NULL : url('project/qt/'.$projectInfo->tpid.'/people/save'), NULL, NULL);

	if ($options->readonly) $form->addConfig('readonly', true);

	$form->addText('<h3>ส่วนที่ 1 ข้อมูลทั่วไปของผู้ตอบแบบสอบถาม</h3>');

	$form->addField('qtref', array('type' => 'hidden', 'value' => $data->qtref));

	$form->addField(
					'SEX',
					array(
						'type' => 'radio',
						'label' => '1.1 เพศ:',
						'options' => array('ชาย' => 'ชาย', 'หญิง' => 'หญิง'),
						'value' => $data->trans['SEX']->value,
					)
				);

	$form->addField(
					'AGE',
					array(
						'label' => '1.2 อายุ:',
						'type' => 'radio',
						'options' => array('18-30 ปี' => '18-30 ปี' ,'31-40 ปี' =>'31-40 ปี', '41-50 ปี' => '41-50 ปี', '51-60 ปี' => '51-60 ปี', '60 ปี ขึ้นไป' => '60 ปี ขึ้นไป'),
						'value' => $data->trans['AGE']->value,
					)
				);

	$form->addField(
					'RELIGION',
					array(
						'type' => 'radio',
						'label' => '1.3 ศาสนา:',
						'options' => array(
													'อิสลาม' => 'อิสลาม',
													'พุทธ' => 'พุทธ',
													'คริสต์' => 'คริสต์',
													'อื่น ๆ' => 'อื่น ๆ ระบุ '
														.'<input type="text" name="qt[RELIGION-OTHER]" placeholder="ระบุ" value="'.(in_array($data->trans['RELIGION']->value, array('อิสลาม', 'พุทธ', 'คริสต์' => 'คริสต์')) ? '' : htmlspecialchars($data->trans['RELIGION']->value)).'" />'),
						'value' => $data->trans['RELIGION']->value,
					)
				);

	$form->addField(
					'EDUCATION',
					array(
						'type' => 'radio',
						'label' => '1.4 ระดับการศึกษา:',
						'options' => array(
													'ประถมศึกษา' => 'ประถมศึกษา',
													'มัธยมศึกษา' => 'มัธยมศึกษา',
													'อนุปริญญา' => 'อนุปริญญา',
													'ปริญญาตรี' => 'ปริญญาตรี',
													'ปริญญาโท' => 'ปริญญาโท',
													'ปริญญาเอก' => 'ปริญญาเอก',
													'อื่น ๆ' => 'อื่น ๆ '
														.'<input type="text" name="qt[EDUCATION-OTHER]" placeholder="ระบุ" value="'.(in_array($data->trans['EDUCATION']->value, array('ประถมศึกษา', 'มัธยมศึกษา', 'อนุปริญญา', 'ปริญญาตรี', 'ปริญญาโท', 'ปริญญาเอก')) ? '' : htmlspecialchars($data->trans['EDUCATION']->value)).'" />',
												),
						'value' => $data->trans['EDUCATION']->value,
					)
				);

	$form->addField(
					'OCCUPA',
					array(
						'label' => '1.6 อาชีพ: <em>(ท่านสามารถตอบได้มากกว่า 1 ข้อ)</em>',
						'type' => 'checkbox',
						'multiple' => true,
						'options' => array(
													'&nbsp;<b>1 อาชีพการเกษตร</b>',
														'ทำนา' => 'ทำนา',
														'ทำสวนยางพารา' => 'ทำสวนยางพารา',
														'ทำสวนปาล์ม' => 'ทำสวนปาล์ม',
														'ทำสวนไม้ผล' => 'ทำสวนไม้ผล',
														'เลี้ยงสัตว์' => 'เลี้ยงสัตว์',
														'การเกษตร อื่น ๆ'=>'อาชีพการเกษตร อื่น ๆ <input type="text" name="qt[OCCUPA][การเกษตร อื่น ๆ]" placeholder="ระบุ" value="'.htmlspecialchars($data->trans['OCCUPA.การเกษตร อื่น ๆ']->value).'" />',
													'&nbsp;<b>2 อาชีพประมง</b>',
														'ประมงพื้นบ้านชายฝั่ง' => 'ประมงพื้นบ้านชายฝั่ง',
														'ประมงเชิงพาณิชย์' => 'ประมงเชิงพาณิชย์',
														'ประมงน้ำจืด' => 'ประมงน้ำจืด',
														'เพาะเลี้ยงสัตว์น้ำ' => 'เพาะเลี้ยงสัตว์น้ำ',
														'ประมง อื่น ๆ' => 'อาชีพประมง อื่น ๆ <input type="text" name="qt[OCCUPA][ประมง อื่น ๆ]" placeholder="ระบุ" value="'.htmlspecialchars($data->trans['OCCUPA.ประมง อื่น ๆ']->value).'" />',
													'รับจ้าง' => '3 รับจ้าง',
													'ค้าขาย' => '4 ค้าขาย',
													'ประกอบกิจการส่วนตัว' => '5 ประกอบกิจการส่วนตัว',
													'อื่น ๆ' => '6 อื่น ๆ <input type="text" name="qt[OCCUPA][อื่น ๆ]" placeholder="ระบุ" value="'.htmlspecialchars($data->trans['OCCUPA.อื่น ๆ']->value).'" />',
												),
						'value' => $ocupaData,
					)
				);

	$form->addText('<h3>ส่วนที่ 2 ผลการสำรวจความคิดเห็นของประชาชน</h3>');

	$tables = new Table();
	$tables->addClass('qt-list');
	$tables->thead = '<tr><th rowspan="2">รายการ</th><th colspan="5">ระดับความคิดเห็น</th></tr><tr style="vertical-align: top;"><th>มากที่สุด<br />(5)</th><th>มาก<br />(4)</th><th>ปานกลาง<br />(3)</th><th>น้อย<br />(2)</th><th>น้อยที่สุด<br />(1)</th></tr>';
	$qtList = array(
							'2.1' => 'ด้านความรู้ความเข้าใจ',
							211 => '1. ท่านทราบถึงนโยบายของรัฐบาลในการแก้ไขปัญหาและพัฒนา จชต. มากน้อยเพียงใด',
							212 => '2. ท่านมีความรู้และเข้าใจบทบาทหน้าที่ของคณะกรรมการหมู่บ้านมากน้อยเพียงใด',
							213 => '3. ท่านทราบเป้าหมาย/ผลลัพธ์ของ โครงการชุมชนเข้มแข็ง มั่นคง มั่งคั่ง มากน้อยเพียงใด',
							219 => array('4. ข้อเสนอแนะ', 'textarea'),

							'2.2' => 'ด้านการมีส่วนร่วม',
							221 => '5. ท่านมีส่วนร่วมในการเข้าประชุมหมู่บ้านร่วมกับคณะกรรมการหมู่บ้านมากน้อยเพียงใด (ร่วมคิด)',
							222 => '6. ท่านมีส่วนร่วมในการดำเนินกิจกรรมของหมู่บ้านมากน้อยเพียงใด (ร่วมทำ)',
							223 => '7. ท่านมีส่วนร่วมในการตรวจสอบ ติดตาม และประเมินผล กิจกรรมของหมู่บ้านมากน้อยเพียงใด (ร่วมติดตาม)',
							229 => array('8. ข้อเสนอแนะ', 'textarea'),

							'2.3' => 'ด้านความพึงพอใจ',
							231 => '9. ท่านคิดว่านโยบายการพัฒนาหมู่บ้านมั่นคง มั่งคั่ง ยั่งยืน นี้สามารถแก้ไขปัญหาและพัฒนาพื้นที่ หมู่บ้านได้มากน้อยเพียงใด',
							232 => '10. ท่านมีความพึงพอใจต่อนโยบายการพัฒนาหมู่บ้านมั่นคง มั่งคั่ง ยั่งยืน มากน้อยเพียงใด',
							233 => '11. ท่านคิดว่านโยบายการพัฒนาหมู่บ้านมั่นคง มั่งคั่ง ยั่งยืนนี้ ควรดำเนินการต่อไปมากน้อยเพียงใด',
							234 => array('12. ท่านคิดว่าคณะกรรมการหมู่บ้านท่านใดที่ท่านประทับใจ คือ', 'textarea'),
							239 => array('13. ข้อเสนอแนะ', 'textarea'),
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
					'COMMENT',
					array(
						'label' => '',
						'type' => 'textarea',
						'class' => '-fill',
						'value' => $data->trans['COMMENT']->value,
					)
				);

	$form->addField(
					'GOODBOARD',
					array(
						'label' => 'กม.ขวัญใจจากประชาชน',
						'type' => 'text',
						'class' => '-fill',
						'value' => htmlspecialchars($data->trans['GOODBOARD']->value),
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
							'pretext' => ' <a class="btn -cancel -link" href="'.url('project/qt/'.$projectInfo->tpid.'/people').'">ยกเลิก</a> ',
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