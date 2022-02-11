<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_shop_member_form($self, $shopInfo, $userId = NULL) {
	if (!($shopId = $shopInfo->shopid)) return message('error', 'PROCESS ERROR');

	$isEdit = in_array($shopInfo->iam, array('ADMIN','MANAGER'));

	if (!$isEdit) return message('error', 'Access denied');

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>'.($userId ? 'แก้ไขข้อมูลพนักงาน' : 'สร้างพนักงาน').'</h3></header>';

	if ($userId) {
		$stmt = 'SELECT
			gu.*, u.`name`, u.`email`, u.`phone`
			FROM %garage_user% gu
				LEFT JOIN %users% u USING(`uid`)
			WHERE `shopid` IN (:branch) AND `uid` = :uid
			LIMIT 1';

		$data = mydb::select($stmt, ':branch', 'SET:'.implode(',',$shopInfo->branch), ':uid', $userId);
		$data->membership = $data->membership.':'.$data->position;
	}

	$branchOptions = array();
	foreach (mydb::select('SELECT * FROM %garage_shop% WHERE `shopid` IN (:branch)', ':branch', 'SET:'.implode(',',$shopInfo->branch))->items as $rs) {
		$branchOptions[$rs->shopid] = $rs->shopname.'@'.$rs->shortname;
	}

	$form = new Form('user',url('garage/shop/0/info/user.save'.($userId ? '/'.$userId : '')),NULL,'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel','notify');
	$form->addData('done', 'close | load');

	if (empty($userId)) {
		$form->addField(
			'username',
			array(
				'type'=>'text',
				'label'=>'Username',
				'class'=>'-fill',
				'require'=>true,
				'value'=>$data->username,
			)
		);

		$form->addField(
			'password',
			array(
				'type'=>'text',
				'label'=>'Password',
				'class'=>'-fill',
				'require'=>true,
				'value'=>$data->password,
			)
		);
	}

	$form->addField(
		'name',
		array(
			'type'=>'text',
			'label'=>'ชื่อ-นามสกุล',
			'class'=>'-fill',
			'require' => true,
			'value'=>$data->name,
		)
	);

	$form->addField(
		'shopid',
		array(
			'type' => 'select',
			'label' => 'สำนักงาน/สาขา',
			'class' => '-fill',
			'require' => true,
			'options' => $branchOptions,
			'value' => $data->shopid,
		)
	);

	$form->addField(
		'membership',
		array(
			'label' => 'กลุ่ม',
			'type' => 'select',
			'class' => '-fill',
			'require' => true,
			'options' => array(
				'' => '== เลือกกลุ่ม ==',
				'MANAGER:ผู้จัดการ' => 'ผู้จัดการ (MANAGER)',
				'ACCOUNTING:เจ้าหน้าที่บัญชี' => 'เจ้าหน้าที่บัญชี (ACCOUNTING)',
				'INVENTORY:สินค้าคงคลัง' => 'สินค้าคงคลัง (INVENTORY)',
				'CARIN:แผนกรับรถ' => 'แผนกรับรถ (CARIN)',
				'FOREMAN:หัวหน้าช่าง' => 'หัวหน้าช่าง (FOREMAN)',
				'TECHNICIAN:ช่างเคาะ' => 'ช่างเคาะ (TECHNICIAN)',
				'TECHNICIAN:ช่างพื้น' => 'ช่างพื้น (TECHNICIAN)',
				'TECHNICIAN:ช่างพ่นสี' => 'ช่างพ่นสี (TECHNICIAN)',
				'TECHNICIAN:ช่างประกอบ' => 'ช่างประกอบ (TECHNICIAN)',
			),
			'value' => $data->membership,
		)
	);

	$form->addField(
		'email',
		array(
			'type'=>'text',
			'label'=>'อีเมล์',
			'class'=>'-fill',
			'value'=>$data->email,
			'placeholder'=>'name@example.com',
		)
	);

	$form->addField(
		'phone',
		array(
			'type'=>'text',
			'label'=>'โทรศัพท์',
			'class'=>'-fill',
			'value'=>$data->phone,
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>'.($userId ? 'บันทึกข้อมูล' : 'สร้างสมาชิกใหม่').'</span>',
			'pretext'=>'<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}'
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>