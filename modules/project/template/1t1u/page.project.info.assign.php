<?php
/**
* Project :: Job Assign Detail
* Created 2021-01-26
* Modify  2021-01-26
*
* @param Object $self
* @param Object $projectInfo
* @param Int $tranId
* @return String
*
* @usage project/{id}/info.assign/{tr}
*/

$debug = true;

function project_info_assign($self, $projectInfo, $tranId) {
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isRight = $projectInfo->right->isOwner;

	if (!$isRight) return message('error', 'ขออภัย ท่านไม่สามารถใช้งานเมนูนี้ได้');

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

	$ret = '<section>'
		. '<header class="header">'._HEADER_BACK.'<h3>แบบมอบหมายแผนการปฎิบัติงานให้ผู้ถูกจ้างงาน</h3></header>';
	//$ret .= print_o($data,'$data');

	$ret .= '<p>มอบหมายให้ <b>'.$projectInfo->title.'</b></p>'
		. 'เดือน'.sg_date($data->assignMonth, 'ดดด ปปปป');
	if ($data->dataAnalytics) {
		$ret .= '<h5>การวิเคราะห์ข้อมูล (Data Analytics)</h5>'
		. '<div>'.nl2br($data->dataAnalytics).'</div>';
	}
	if ($data->covid19) {
		$ret .= '<h5>การเฝ้าระวัง ประสานงานและติดตามข้อมูลสถานการณ์การระบาดของ COVID-19 และโรคระบาดใหม่</h5>'
		. '<div>'.nl2br($data->covid19).'</div>';
	}
	if ($data->digitalizing) {
		$ret .= '<h5>การจัดทำข้อมูลราชการในพื้นที่เป็นข้อมูลอิเล็กทรอนิกส์ (Digitalizing Government Data)</h5>'
		. '<div>'.nl2br($data->digitalizing).'</div>';
	}
	if ($data->otop) {
		$ret .= '<h5>การพัฒนาสัมมาชีพและสร้างอาชีพใหม่ (การยกระดับสินค้า OTOP/อาชีพอื่นๆ) การสร้างและพัฒนา Creative Economy (การยกระดับการท่องเที่ยว) การนำองค์ความรู้ไปช่วยบริการชุมชน (Health Care/เทคโนโลยีด้านต่างๆ) และการส่งเสริมด้านสิ่งแวดล้อม/Circular Economy (การเพิ่มรายได้หมุนเวียนให้แก่ชุมชน) ให้แก่ชุมชน</h5>'
		. '<div>'.nl2br($data->otop).'</div>';
	}
	if ($data->job) {
		$ret .= '<h5>การพัฒนาทักษะอาชีพใหม่จากความหลากหลายทางชีวภาพและความหลากหลายทางวัฒนธรรมของชุมชน</h5>'
		. '<div>'.nl2br($data->job).'</div>';
	}
	if ($trainList = (Array) json_decode($data->train)) {
		$ret .= '<h5>แนะนำให้เพิ่มหรือส่ง เข้าฝึกอบรมทักษะ ในด้าน</h5>';
		$ret .= '<div><ul>';
		foreach ($trainList as $value) {
			$ret .= '<li>'.$value.'</li>';
		}
		$ret .= '</ul></div>';
	}

	if ($data->other) {
		$ret .= '<h3>งานอื่นๆ ที่มอบหมาย</h3>'
			. '<div>'.nl2br($data->other).'</div>';
	}

	$ret .= '</section>';
	$ret .= '<style type="text/css">
	section>div {padding: 0 0 16px 16px}
	</style>';

		/*
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
	*/
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