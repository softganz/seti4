<?php
/**
* Project :: Fund Address Information
* Created 2020-07-20
* Modify  2020-07-20
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @usage project/fund/$orgId/info.address
*/

$debug = true;

function project_fund_info_address($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	if (!$fundInfo->right->edit) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$data = $fundInfo->info;

	//$ret .= print_o($fundInfo,'$fundInfo');

	$provinceOptions = array();
	$ampurOptions = array();
	$tambonOptions = array();

	$stmt = 'SELECT
		*
		, IF(`provid`>= 80, "ภาคใต้","ภาคอื่น") `zone`
		FROM %co_province%
		ORDER BY CASE WHEN `provid`>= 80 THEN -1 ELSE 1 END ASC, CONVERT(`provname` USING tis620) ASC';
	//$ret .= print_o(mydb::select($stmt),'dbs');
	foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->zone][$rs->provid] = $rs->provname;

	if ($data->changwat) {
		$stmt = 'SELECT * FROM %co_district% WHERE LEFT(`distid`,2) = :changwat AND RIGHT(`distname`,1) != "*" ';
		foreach (mydb::select($stmt, ':changwat', $data->changwat)->items as $rs) {
			$ampurOptions[substr($rs->distid,2,2)] = $rs->distname;
		}
	}

	if ($data->ampur) {
		$stmt = 'SELECT * FROM %co_subdistrict% WHERE LEFT(`subdistid`,4) = :changwat AND RIGHT(`subdistname`,1) != "*" ';
		foreach (mydb::select($stmt, ':changwat', $data->changwat.$data->ampur)->items as $rs) {
			$tambonOptions[substr($rs->subdistid,4,2)] = $rs->subdistname;
		}
	}


	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>ที่อยู่กองทุน</h3></header>';

	$form = new Form('data', url('project/fund/'.$orgId.'/info/address.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'load | close');

	$form->addText('<b>'.$data->name.'</b>');

	$form->addField(
		'address',
		array(
			'type' => 'text',
			'label' => 'ที่อยู่',
			'require' => true,
			'class' => '-fill',
			'value' => $data->address,
		)
	);

	$form->addField(
		'orgzip',
		array(
			'type' => 'text',
			'label' => 'รหัสไปรษณีย์',
			'require' => true,
			'maxlength' => 5,
			'value' => $data->orgzip,
		)
	);

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
			'label'=>'ที่อยู่',
			'class'=>'sg-address -fill',
			'maxlength'=>200,
			'require'=>true,
			'attr'=>array('data-altfld'=>'edit-data-areacode'),
			'placeholder'=>'เลขที่ ถนน หมู่ที่ ตำบล ตามลำดับ แล้วเลือกจากรายการที่แสดง หรือ เลือกจากช่องเลือกด้านล่าง',
			'value'=>htmlspecialchars($data->address)
		)
	);

	$form->addField('changwat',
		array(
			'label' => 'จังหวัด:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'require'=>true,
			'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
			'value' => $data->changwat,
		)
	);

	$form->addField('ampur',
		array(
			'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill'.($data->changwat ? '' : ' -hidden'),
			'require'=>true,
			'options' => array('' => '== เลือกอำเภอ ==') + $ampurOptions,
			'value' => $data->ampur,
		)
	);

	$form->addField('tambon',
		array(
			'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill'.($data->ampur ? '' : ' -hidden'),
			'require'=>true,
			'options' => array('' => '== เลือกตำบล ==') + $tambonOptions,
			'value' => $data->tambon,
			'attr' => array('data-altfld' => '#edit-data-areacode'),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
		)
	);


	$ret .= $form->build();
	return $ret;
}
?>