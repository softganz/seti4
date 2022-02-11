<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_inno_form($self, $projectInfo) {
	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><h3>ผลลัพธ์โครงการ</h3></header>';

	$form = new Form('tran', url('project/'.$tpid.'/info/tran.add/valuation'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField(
		'part',
		array(
			'type' => 'select',
			'label' => 'นวัตกรรม:',
			'class' => '-fill',
			'require' => true,
			'options' => array(
				'' => '==เลือกนวัตกรรม==',
				'นวัตกรรมชุมชน' => array(
					'inno.1.1' => 'ระบบบริหารจัดการและเทคโนโลยีเพื่อเพิ่มประสิทธิภาพและลดต้นทุน',
					'inno.1.2' => 'ระบบการค้าที่เป็นธรรมและการจัดการคุณภาพแบบมีส่วนร่วม',
					'inno.1.3' => 'การจัดการฐานทรัพยากรชุมชนที่สร้างประโยชน์เชิงพาณิชย์',
					'inno.1.4' => 'การเพิ่ม productivity พันธุ์พื้นเมืองและระบบจัดการแบบครบวงจร',
				),
				'นวัตกรรมเกษตร' => array(
					'inno.2.1' => 'นวัตกรรมด้านเทคโนโลยีการผลิต',
					'inno.2.2' => 'นวัตกรรมเพื่อเพิ่มผลผลิต',
					'inno.2.3' => 'นวัตกรรมเพื่อเพิ่มคุณภาพ',
					'inno.2.4' => 'นวัตกรรมเพื่อยืดอายุหลังการเก็บเกี่ยว',
					'inno.2.5' => 'นวัตกรรมเครื่องมือทางการเกษตร',
					'inno.2.6' => 'นวัตกรรมการใช้ประโยชน์จากของเหลือทิ้งทางการเกษตร',
					'inno.2.7' => 'นวัตการแปรรูปผลผลิต',
				),
				'นวัตกรรมแก้จน' => array(
					'inno.3.1' => 'นวัตกรรมแก้จน',
				),
				'นวัตกรรมสังคม' => array(
					'inno.4.1' => 'นวัตกรรมสังคม',
				),
			),
		)
	);

	$form->addField(
		'text1',
		array(
			'type' => 'textarea',
			'label' => 'รายละเอียด',
			'class' => '-fill',
			'require' => true,
			'rows' => 10,
			'value' => '',
			'placeholder' => 'อธิบายรายละเอียดของนวัตกรรม',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>