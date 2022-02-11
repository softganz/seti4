<?php
/**
* Project :: Fund Area Information
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @usage project/fund/$orgId/info.area
*/

$debug = true;

function project_fund_info_area($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	if (!$fundInfo->right->edit) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$data = $fundInfo->info;

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
		$stmt = 'SELECT * FROM %co_district% WHERE LEFT(`distid`,2) = :changwat';
		foreach (mydb::select($stmt, ':changwat', $data->changwat)->items as $rs) {
			$ampurOptions[substr($rs->distid,2,2)] = $rs->distname;
		}
	}

	if ($data->ampur) {
		$stmt = 'SELECT * FROM %co_subdistrict% WHERE LEFT(`subdistid`,4) = :changwat';
		foreach (mydb::select($stmt, ':changwat', $data->changwat.$data->ampur)->items as $rs) {
			$tambonOptions[substr($rs->subdistid,4,2)] = $rs->subdistname;
		}
	}


	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>ข้อมูลพื้นฐานของพื้นที่ดำเนินงาน</h3></header>';

	$form = new Form('data', url('project/fund/'.$orgId.'/info/area.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load:.box-page');

	$form->fundname=array('type'=>'textfield','value'=>'<strong><big><em>ชื่อกองทุนหลักประกันสุขภาพระดับท้องถิ่น อบต./เทศบาล : '.$fundInfo->name.' อำเภอ'.$fundInfo->info->nameampur.' จังหวัด'.$fundInfo->info->namechangwat.'</em></big></strong>');

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อกองทุน',
			'require' => true,
			'class' => '-fill',
			'value' => $data->name,
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
		'orgphone',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 50,
			'value' => $data->orgphone,
		)
	);

	$form->addField(
		'orgfax',
		array(
			'type' => 'text',
			'label' => 'โทรสาร',
			'class' => '-fill',
			'maxlength' => 500,
			'value' => $data->orgfax,
		)
	);

	$form->addField(
		'orgemail',
		array(
			'type' => 'text',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'maxlength' => 100,
			'value' => $data->orgemail,
		)
	);

	$form->addField(
		'orgsize',
		array(
			'label' => 'ขนาดขององค์กรปกครองส่วนท้องถิ่น/เทศบาล:',
			'type' => 'radio',
			'require' => true,
			'options' => array(
				'องค์การบริหารส่วนตำบล:'=>array('1'=>'อบต.ขนาดเล็ก','2'=>'อบต.ขนาดกลาง','3'=>'อบต.ขนาดใหญ่'),
				'เทศบาล:'=>array('4'=>'เทศบาลตำบล','5'=>'เทศบาลเมือง','6'=>'เทศบาลนคร'),
			),
			'value' => $data->orgsize,
			'display' => 'inline',
		)
	);

	$form->addField(
		'amttambon',
		array(
			'type' => 'radio',
			'label' => 'พื้นที่รับผิดชอบ:',
			'options' => array(1=>1,2=>2,3=>3),
			'posttext' => ' ตำบล',
			'display' => 'inline',
			'value' => $data->tambonnum?$data->tambonnum:1,
		)
	);

	$form->addField(
		'moonum',
		array(
			'type' => 'select',
			'label' => 'จำนวนหมู่บ้าน/ชุมชนในพื้นที่รับผิดชอบ:',
			'options' => '1..300',
			'posttext' => ' ชุมชน/หมู่บ้าน',
			'value' => $data->moonum,
		)
	);

	$openYearOptions = array(-1 => '== เลือกปีงบประมาณ ==');
	for ($i=2006; $i <= date('Y'); $i++) $openYearOptions[$i]='พ.ศ. '.($i+543);

	$form->addField(
		'openyear',
		array(
			'type' => 'select',
			'label' => 'จัดตั้งกองทุนเมื่อปีงบประมาณ:',
			'require' => true,
			'options' => $openYearOptions,
			'value' => $data->openyear,
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
		)
	);


	$ret .= $form->build();
	return $ret;
}
?>