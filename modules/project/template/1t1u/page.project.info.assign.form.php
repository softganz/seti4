<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function project_info_assign_form($self, $projectInfo, $tranId = NULL) {
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isRight = is_admin('project') || $projectInfo->right->isOwner;

	if (!$isRight) return message('error', 'ขออภัย ท่านไม่สามารถใช้งานเมนูนี้ได้');

	$data = NULL;
	if ($tranId) {
		$stmt = 'SELECT
			  `trid`
			, `tpid`
			, DATE_FORMAT(`date1`, "%Y-%m") `assignMonth`
			, `text1` `dataAnalytics`
			, `text2` `covid19`
			, `text3` `digitalizing`
			, `text4` `otop`
			, `text5` `job`
			, `text6` `other`
			, `text10` `train`
			FROM %project_tr%
			WHERE `trid` = :trid
			LIMIT 1';
		$data = mydb::select($stmt, ':trid', $tranId);
	} else {
		$data->trid = $tranId;
		$data->tpid = post('id');
		$data->assignMonth = post('assignMonth');
	}

	$assignProjectInfo = R::Model('project.get', $data->tpid);

	$ret = '<header class="header">'._HEADER_BACK.'<h3>แบบมอบหมายแผนการปฎิบัติงานให้ผู้ถูกจ้างงาน</h3></header>';
	//$ret .= print_o($data,'$data');

	$form = new Form('data', url('project/'.$projectId.'/info/assign.save/'.$tranId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addText('<p>มอบหมายให้ <b>'.$assignProjectInfo->title.'</b></p>');

	$form->addField('trid', array('type' => 'hidden', 'value' => $data->trid));
	$form->addField('projectId', array('type' => 'hidden', 'value' => $data->tpid));
	$form->addField(
		'assignMonth',
		array(
			'type' => 'text',
			'label' => 'เดือน',
			'readonly' => true,
			'value' => $data->assignMonth,
		)
	);

	/*
		$form->addField(
		'dataAnalytics',
		array(
			'type' => 'checkbox',
			'options' => array('1' => 'การวิเคราะห์ข้อมูล (Data Analytics)'),
			'value' => $data->dataAnalytics,
		)
	);
	*/

	$form->addField(
		'dataAnalytics',
		array(
			'type' => 'textarea',
			'label' => 'การวิเคราะห์ข้อมูล (Data Analytics)',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->dataAnalytics,
			'placeholder' => 'ระบุรายละเอียด',
		)
	);

	/*
	$form->addField(
		'covid19',
		array(
			'type' => 'checkbox',
			'options' => array('1' => 'การเฝ้าระวัง ประสานงานและติดตามข้อมูลสถานการณ์การระบาดของ COVID-19 และโรคระบาดใหม่'),
			'value' => $data->covid19,
		)
	);
	*/

	$form->addField(
		'covid19',
		array(
			'type' => 'textarea',
			'label' => 'การเฝ้าระวัง ประสานงานและติดตามข้อมูลสถานการณ์การระบาดของ COVID-19 และโรคระบาดใหม่',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->covid19,
			'placeholder' => 'ระบุรายละเอียด',
		)
	);

	/*
	$form->addField(
		'digitalizing',
		array(
			'type' => 'checkbox',
			'options' => array('1' => 'การจัดทำข้อมูลราชการในพื้นที่เป็นข้อมูลอิเล็กทรอนิกส์ (Digitalizing Government Data)'),
			'value' => $data->covid19,
		)
	);
	*/

	$form->addField(
		'digitalizing',
		array(
			'type' => 'textarea',
			'label' => 'การจัดทำข้อมูลราชการในพื้นที่เป็นข้อมูลอิเล็กทรอนิกส์ (Digitalizing Government Data)',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->digitalizing,
			'placeholder' => 'ระบุรายละเอียด',
		)
	);

	/*
	$form->addField(
		'otop',
		array(
			'type' => 'checkbox',
			'options' => array('1' => 'การพัฒนาสัมมาชีพและสร้างอาชีพใหม่ (การยกระดับสินค้า OTOP/อาชีพอื่นๆ) การสร้างและพัฒนา Creative Economy (การยกระดับการท่องเที่ยว) การนำองค์ความรู้ไปช่วยบริการชุมชน (Health Care/เทคโนโลยีด้านต่างๆ) และการส่งเสริมด้านสิ่งแวดล้อม/Circular Economy (การเพิ่มรายได้หมุนเวียนให้แก่ชุมชน) ให้แก่ชุมชน'),
			'value' => $data->otop,
		)
	);
	*/

	$form->addField(
		'otop',
		array(
			'type' => 'textarea',
			'label' => 'การพัฒนาสัมมาชีพและสร้างอาชีพใหม่ (การยกระดับสินค้า OTOP/อาชีพอื่นๆ) การสร้างและพัฒนา Creative Economy (การยกระดับการท่องเที่ยว) การนำองค์ความรู้ไปช่วยบริการชุมชน (Health Care/เทคโนโลยีด้านต่างๆ) และการส่งเสริมด้านสิ่งแวดล้อม/Circular Economy (การเพิ่มรายได้หมุนเวียนให้แก่ชุมชน) ให้แก่ชุมชน',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->otop,
			'placeholder' => 'ระบุรายละเอียด',
		)
	);

	/*
	$form->addField(
		'job',
		array(
			'type' => 'checkbox',
			'options' => array('1' => 'การพัฒนาทักษะอาชีพใหม่จากความหลากหลายทางชีวภาพและความหลากหลายทางวัฒนธรรมของชุมชน'),
			'value' => $data->job,
		)
	);
	*/

	$form->addField(
		'job',
		array(
			'type' => 'textarea',
			'label' => 'การพัฒนาทักษะอาชีพใหม่จากความหลากหลายทางชีวภาพและความหลากหลายทางวัฒนธรรมของชุมชน',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->job,
			'placeholder' => 'ระบุรายละเอียด',
		)
	);

	$form->addField(
		'train',
		array(
			'type' => 'checkbox',
			'label' => 'แนะนำให้เพิ่มหรือส่งเข้าฝึกอบรมทักษะ',
			'multiple' => true,
			'options' => array(
				'Digital Literacy' => 'Digital Literacy',
				'English Competency' => 'English Competency',
				'Financial Literacy' => 'Financial Literacy',
				'Social Literacy' => 'Social Literacy',
			),
			'value' => (Array) SG\json_decode($data->train),
		)
	);

	$form->addField(
		'other',
		array(
			'type' => 'textarea',
			'label' => 'งานอื่นๆ ที่มอบหมาย',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->other,
			'placeholder' => 'ระบุรายละเอียด',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
			'container' => '{class: "-sg-text-right"}',
		)
	);
	$ret .= $form->build();

	/*
	$ret .= '<pre>
เดือน.......................... 

1. มอบหมายให้.......ชื่อ – สกุล ................
2. รายละเอียดภาระกิจแต่ล่ะด้านที่ต้องการ ให้ปฏิบัติงาน   

รายละเอียดการปฏิบัติงาน		เป้าหมาย(จำนวน)		หมายเหตุ

การวิเคราะห์ข้อมูล (Data Analytics)
1.
2.
3.

การเฝ้าระวัง ประสานงานและติดตามข้อมูลสถานการณ์การระบาดของ COVID-19 และโรคระบาดใหม่
1.
2.
3.

การจัดทำข้อมูลราชการในพื้นที่เป็นข้อมูลอิเล็กทรอนิกส์ (Digitalizing Government Data)
1.
2.
3.

การพัฒนาสัมมาชีพและสร้างอาชีพใหม่ (การยกระดับสินค้า OTOP/อาชีพอื่นๆ) การสร้างและพัฒนา Creative Economy (การยกระดับการท่องเที่ยว) การนำองค์ความรู้ไปช่วยบริการชุมชน (Health Care/เทคโนโลยีด้านต่างๆ) และการส่งเสริมด้านสิ่งแวดล้อม/Circular Economy (การเพิ่มรายได้หมุนเวียนให้แก่ชุมชน) ให้แก่ชุมชน
1
2
3

การพัฒนาทักษะอาชีพใหม่จากความหลากหลายทางชีวภาพและความหลากหลายทางวัฒนธรรมของชุมชน
1
2
3

การพัฒนาทักษะอาชีพใหม่จากความหลากหลายทางชีวภาพและความหลากหลายทางวัฒนธรรมของชุมชน
1
2
3

3. แนะนำให้เพิ่มหรือส่ง เข้าฝึกอบรมทักษะ ในด้าน..ระบุรายละเอียด
    • Digital Literacy,
    • English Competency, 
    • Financial Literacy
    • Social Literacy
.............................................
4. งานอื่นๆ ที่มอบหมาย
.............................................
</pre>';
*/
	return $ret;
}
?>