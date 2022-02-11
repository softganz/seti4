<?php
/**
* Green :: My Plant Form
* Created 2019-11-04
* Modify  2020-12-03
*
* @param Object $self
* @param Int $plantId
* @return String
*
* @usage green/my/plant/form
*/

$debug = true;

function green_my_plant_form($self, $plantId = NULL) {
	$getLandId = post('land');
	$landList = Array();
	$data = new stdClass();
	$orgInfo = NULL;
	$ret = '';

	$isAdmin = is_admin('green');
	$isAddPlant = false;

	if ($plantId) {
		$data = R::Model('green.plant.get', $plantId)->info;
		$orgInfo = R::Model('green.shop.get', $data->orgid);
	} else {
		$data->orgid = ($orgInfo = R::Model('green.shop.get', 'my')) ? $orgInfo->orgId : NULL;
		if ($getLandId) $data->landid = $getLandId;
	}

	$isOrgAdmin = $isAdmin || $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddPlant = $isOrgAdmin || $orgInfo->is->membership;

	if (!$isAddPlant) return message('error', 'Access Denied');

	if ($data->orgid) {
		$landList = R::Model(
			'green.land.get',
			'{orgId: '.$data->orgid.', me: '.($isOrgAdmin ? 'false' : 'true').'}',
			'{debug: false, limit: "*"}'
		);
	}

	$landOptions = array('' => '== เลือกแปลงที่ดิน ==');
	foreach ($landList->items as $rs) {
		$landOptions[$rs->landid] = $rs->landname;
	}






	// Start View

	$ret = '<header class="header -hidden">'._HEADER_BACK.'<h3>เพิ่มผลผลิตรอบใหม่ @'.$orgInfo->name.'</h3></header>';

	$form = new Form(NULL, url('green/my/info/activity.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load: .box-page | load');

	if ($data->msgid) {
		$form->addField('msgid', array('type' => 'hidden', 'value' => $data->msgid));
	}

	if ($data->plantid) {
		$form->addField('plantid', array('type' => 'hidden', 'value' => $data->plantid));
	}
	$form->addField('tagname', array('type' => 'hidden', 'value' => 'GREEN,PLANT'));

	$form->addField(
		'landid',
		array(
			'type' => 'select',
			'label' => 'แปลงที่ดิน:',
			'class' => '-fill',
			'require' => true,
			'options' => $landOptions,
			'value' => $data->landid,
		)
	);

	$form->addField(
		'productname',
		array(
			'label' => 'ชื่อผลผลิต',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->productname),
			'placeholder' => 'ระบุชื่อผลผลิต',
		)
	);

	$form->addField(
		'treelevel',
		array(
			'type' => 'select',
			'label' => 'ไม้ 5 ระดับ',
			'class' => '-fill',
			'require' => true,
			'options' => array(
				'' => '== เลือกระดับ ==',
				'5' => 'สูง - ระดับที่ 5',
				'4' => 'กลาง - ระดับที่ 4',
				'3' => 'เตี้ย - ระดับที่ 3',
				'2' => 'เรี่ยดิน - ระดับที่ 2',
				'1' => 'ใต้ดิน - ระดับที่ 1',
			),
			'value' => $data->treelevel,
		)
	);

	$productOptions = R::Model('category.get', array('vid' => cfg('ibuy.vocab.category')), 'tid', '{debug: false, result: "group"}');
	/*
	$productOptions['สินค้าเกษตร']['ข้าว'] = $productOptions['ข้าว'];
	$ret .= print_o($productOptions,'$productOptions');
	*/

	$form->addField(
		'catid',
		array(
			'type' => 'select',
			'label' => 'หมวด:',
			'class' => '-fill',
			'require' => true,
			'value' => $data->catid,
			'options' => array_merge_recursive(array('' => '== เลือกหมวด =='), $productOptions),
		)
	);

	$form->addField(
		'startdate',
		array(
			'label' => 'วันที่เริ่มลงแปลง',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'require' => true,
			'readonly' => true,
			'value' => $data->startdate ? sg_date($data->startdate, 'd/m/Y') : '',
			'placeholder' => '31/12/2562',
		)
	);

	$form->addField(
		'cropdate',
		array(
			'label' => 'วันที่คาดว่าจะเก็บเกี่ยว',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'value' => $data->cropdate ? sg_date($data->cropdate, 'd/m/Y') : '',
			'placeholder' => '31/12/2562',
		)
	);

	$form->addField(
		'qty',
		array(
			'type' => 'text',
			'label' => 'ปริมาณการผลิต',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->qty),
		)
	);

	$form->addField(
		'unit',
		array(
			'type' => 'select',
			'label' => 'หน่วยการผลิต:',
			'maxlength' => 20,
			'options' => array('กก.' => 'กก.', 'ลิตร' => 'ลิตร', 'ก้อน' => 'ก้อน', 'ชิ้น' => 'ชิ้น', 'ตัว' => 'ตัว', 'แพ็ค' => 'แพ็ค', 'ขวด' => 'ขวด', 'ลัง' => 'ลัง'),
			'value' => htmlspecialchars($data->unit),
		)
	);

	$form->addField(
		'saleprice',
		array(
			'type' => 'text',
			'label' => 'ราคาขาย (บาท)',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->saleprice),
		)
	);

	$form->addField(
		'bookprice',
		array(
			'type' => 'text',
			'label' => 'ราคาจอง (บาท)',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->bookprice),
		)
	);

	/*
	$form->addField(
		'safety',
		array(
			'type' => 'radio',
			'label' => 'ประเภทของการผลิต:',
			'options' => array(1 => 'ปลอดภัย', 2 => 'ไร้สารพิษ'),
			'value' => htmlspecialchars($data->safety),
		)
	);
	*/

	$form->addField(
		'detail',
		array(
			'label' => 'รายละเอียด',
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->detail,
			'placeholder' => 'เช่น ปลูกพืชอะไร รายละเอียด/วิธีการปลูก',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$form->addText(
		'<ul>
		<li><b>ระดับที่ 1 ใต้ดิน</b> - ปลูกพืชที่มีหัวฝังดิน เช่น ขิง ข่า หัวหอม กระเทียม สายบัว เผือก มัน ฯลฯ โดยจะปลูกพวกพืชหัว เพื่อเป็นอาหาร ได้แก่มันสำประหลัง มันเทศ</li>
		<li><b>ระดับที่ 2 เรี่ยดิน</b> - ปลูกไม้เลื้อย เช่น บวบ น้ำเต้า ถั่ว แตง มะระ ตำลึง ผักบุ้ง ฯลฯ</li>
		<li><b>ระดับที่ 3 เตี้ย</b> - ปลูกไม้พันธุ์เตี้ย เป็นการใช้ประโยชน์จากต้นไม้ที่มีทรงพุ่มเตี้ย เช่น พริก มะเขือ กะเพรา ตะไคร้ ข้าว ฟ้ารทะลายโจร ทานตะวัน ไม้ดอก พืชสมุนไพรต่างๆ ฯลฯ</li>
		<li><b>ระดับที่ 4 กลาง</b> - ปลูกไม้ระดับกลาง เป็นชั้นที่มีความสูงเป็นรองกลุ่มไม้ยืนต้น เช่น ขี้เหล็ก มะกรูด มะนาว มะพร้าว ส้มโอ ขนุน ทุเรียน มะม่วง ดอกแค กล้วย ชะอม พืชไร่ พืชสวน ทุกชนิด ฯลฯ</li>
		<li><b>ระดับที่ 5 สูง</b> - ต้นไม้ทรงสูง อยู่ในอากาศ ในกลุ่มนี้จะปลูกไม้ใหญ่ ไม้ยืนต้นซึ่งเป็นไม้ติดแผ่นดิน ช่วยรักษาระบบนิเวศน์ อีกทั้งเป็นการออมเพื่ออนาคตสำหรับตนเอง และลูกหลาน เช่น ตะเคียน ยางนา มะค่า มะฮอกกานี ประดู่ ต้นสักฯลฯ</li>
		</ul>');

	$ret .= $form->build();

	//$ret .= print_o($data, '$data');
	//$ret .= print_o($landList,'$landList');
	//$ret .= print_o($orgInfo, '$orgInfo');

	return $ret;
}
?>