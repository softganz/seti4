<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function green_my_land_form($self, $landId = NULL) {
	$shopId = ($shopInfo = R::Model('green.shop.get', 'my')) ? $shopInfo->shopId : NULL;//location('ibuy/green/my/shop');

	if ($landId) {
		$stmt = 'SELECT
			l.*
			, CONCAT(X(l.`location`),",",Y(l.`location`)) `latlng`
			, X(l.`location`) `lat`
			, Y(l.`location`) `lnt`
			, l.`house`
			, CAST(SUBSTR(l.`areacode`,7,2) AS UNSIGNED) `village`
			, cosub.`subdistname` `tambonName`
			, codist.`distname` `ampurName`
			, copv.`provname` `changwatName`
			, SUBSTR(l.`areacode`,1,2) `changwatCode`
			, SUBSTR(l.`areacode`,3,2) `ampurCode`
			, SUBSTR(l.`areacode`,5,2) `tambonCode`
			FROM %ibuy_farmland% l
				LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(l.`areacode`,2)
				LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(l.`areacode`,4)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = LEFT(l.`areacode`,6)
				LEFT JOIN %co_village% covi ON covi.`villid` = l.`areacode`
			WHERE l.`landid` = :landid AND l.`orgid` = :orgid
			LIMIT 1';
		$data = mydb::select($stmt, ':landid', $landId, ':orgid', $shopId);
		$data->address = SG\implode_address($data);
	}

	$ret = '';

	$ret = '<header class="header">'._HEADER_BACK.'<h3>แปลงผลิต @'.$shopInfo->name.'</h3></header>';


	$provinceOptions = array();

	$stmt = 'SELECT
		*
		, IF(`provid`>= 80, "ภาคใต้","ภาคอื่น") `zone`
		FROM %co_province%
		ORDER BY CASE WHEN `provid`>= 80 THEN -1 ELSE 1 END ASC, CONVERT(`provname` USING tis620) ASC';
	foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->zone][$rs->provid] = $rs->provname;

	$form = new Form('data', url('green/my/info/land.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | reload');

	$form->addField('landid', array('type' => 'hidden', 'value' => $data->landid));

	$form->addField(
		'landname',
		array(
			'label' => 'ชื่อแปลง',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->landname),
			'placeholder' => 'ระบุชื่อแปลง',
		)
	);

	$form->addField(
		'deedno',
		array(
			'label' => 'เลขที่โฉนดที่ดิน',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($data->deedno),
			'placeholder' => 'ระบุเลขที่ของโฉนดที่ดิน',
		)
	);

	$form->addText('<div class="area -sg-flex -justify-left">');
	$form->addField(
		'arearai',
		array(
			'label' => 'พื้นที่ :',
			'type' => 'text',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->arearai),
			'posttext' => '<div class="input-append"><span>ไร่</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
//			'posttext' => '<div class="input-append"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-searchqt").val("");$(this).closest("form").submit()\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
		)
	);
	$form->addField(
		'areahan',
		array(
			'type' => 'text',
			'label' => '&nbsp;',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->areahan),
			'posttext' => '<div class="input-append"><span>งาน</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
		)
	);
	$form->addField(
		'areawa',
		array(
			'type' => 'text',
			'label' => '&nbsp;',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->areawa),
			'posttext' => '<div class="input-append -nowrap"><span>ตร.วา</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
		)
	);
	$form->addText('</div>');

	$form->addField(
		'areacode',
		array(
			'type'=>'hidden',
			'label'=>'เลือกตำบลในที่อยู่',
			'value'=>$data->areacode,
			'require'=>true
		)
	);

	$form->addField(
		'address',
		array(
			'type'=>'text',
			'label'=>'ที่อยู่แปลงที่ดิน',
			'class'=>'sg-address -fill',
			'maxlength'=>100,
			'attr'=>array('data-altfld'=>'edit-data-areacode'),
			'placeholder'=>'เลขที่ ถนน หมู่ที่ ตำบล ตามลำดับ แล้วเลือกจากรายการที่แสดง หรือ เลือกจากช่องเลือกด้านล่าง',
			'value'=>htmlspecialchars($data->address)
		)
	);

	$form->addField('changwat',
		array(
		//	'label' => 'จังหวัด:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
			'value' => $data->changwat,
			'attr' => 'data-altfld="#edit-data-areacode"',
		)
	);

	$form->addField('ampur',
		array(
		//	'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill -hidden',
			'options' => array('' => '== เลือกอำเภอ =='),
			'value' => $data->ampur,
			'attr' => 'data-altfld="#edit-data-areacode"',
		)
	);

	$form->addField('tambon',
		array(
		//	'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill -hidden',
			'options' => array('' => '== เลือกตำบล =='),
			'value' => $data->tambon,
			'attr' => 'data-altfld="#edit-data-areacode"',
		)
	);

	$form->addField(
		'producttype',
		array(
			'label' => 'ประเภท',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($data->producttype),
			'placeholder' => 'เช่น ข้าว,ผักอินทรีย์',
		)
	);

	$form->addField(
		'location',
		array(
			'label' => 'พิกัด GPS',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($data->latlng),
			'placeholder' => 'เช่น 7.0000,100.0000 หรือ '.htmlspecialchars('7° 5\' 3" N / 100° 40\' 9 E"'),
		)
	);

	$form->addField(
		'detail',
		array(
			'label' => 'รายละเอียด',
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->detail,
			'placeholder' => 'เช่น ปลูกพืชอะไร',
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

	$ret .= $form->build();

	//$ret .= print_o($data, '$data');
	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '<style type="text/css">
	.area .form-item .form-text {width: 50px;}
	.form-item.-edit-data-arearai>label {margin-right: 16px; white-space: nowrap}
	</style>';

	return $ret;
}
?>